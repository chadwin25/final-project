<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class SaleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_record_sale_page(): void
    {
        $this->get(route('sales.create'))
            ->assertRedirect(route('login'));
    }

    public function test_guest_cannot_record_a_sale(): void
    {
        $product = $this->createProduct(stock: 5);

        $this->post(route('sales.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('sales', 0);
        $this->assertSame(5, $product->fresh()->stock_quantity);
    }

    public function test_staff_can_open_record_sale_page_with_only_available_products(): void
    {
        $staff = User::factory()->create();
        $available = $this->createProduct(stock: 5);
        $this->createProduct(stock: 0, name: 'Sold Out Product');
        $archived = $this->createProduct(stock: 4, name: 'Archived Product');
        $archived->delete();

        $this->actingAs($staff)->get(route('sales.create'))
            ->assertOk()
            ->assertViewIs('sales.create')
            ->assertViewHas('products', fn ($products) => $products->modelKeys() === [$available->id]);
    }

    public function test_guest_cannot_open_sales_history(): void
    {
        $this->get(route('sales.history'))
            ->assertRedirect(route('login'));
    }

    public function test_sales_history_displays_timestamps_in_manila_time(): void
    {
        $staff = User::factory()->create();
        $product = $this->createProduct(stock: 5);
        $sale = Sale::create([
            'product_id' => $product->id,
            'sold_by' => $staff->id,
            'quantity' => 1,
            'total_price' => '9.50',
        ]);
        $sale->forceFill([
            'created_at' => Carbon::parse('2026-11-20 15:21:00', 'UTC'),
        ])->save();

        $this->actingAs($staff)
            ->get(route('sales.history'))
            ->assertOk()
            ->assertSee('Nov 20, 2026 · 11:21 PM')
            ->assertSee('2026-11-20T23:21:00+08:00');
    }

    public function test_sales_history_is_newest_first_and_paginated(): void
    {
        $staff = User::factory()->create(['full_name' => 'Taylor Staff']);
        $product = $this->createProduct(stock: 20);
        $customer = Customer::create([
            'full_name' => 'Casey Customer',
            'email' => 'casey@example.test',
        ]);

        foreach (range(1, 16) as $number) {
            $sale = Sale::create([
                'product_id' => $product->id,
                'customer_id' => $number === 16 ? $customer->id : null,
                'sold_by' => $staff->id,
                'quantity' => $number,
                'total_price' => number_format($number * 9.5, 2, '.', ''),
            ]);
            $sale->forceFill([
                'created_at' => now()->subMinutes(17 - $number),
            ])->save();
        }

        $staff->delete();
        $product->delete();

        $this->actingAs(User::factory()->create())
            ->get(route('sales.history'))
            ->assertOk()
            ->assertSee('Sales history')
            ->assertSee('Test Product')
            ->assertSee('Taylor Staff')
            ->assertSee('Archived')
            ->assertSee('No customer linked')
            ->assertSee('Casey Customer')
            ->assertSee('₱152.00')
            ->assertDontSee('₱9.50')
            ->assertSee('Page 1 of 2');

        $customer->delete();

        $this->get(route('sales.history'))
            ->assertOk()
            ->assertSee('No customer linked')
            ->assertDontSee('Casey Customer');

        $this->get(route('sales.history', ['page' => 2]))
            ->assertOk()
            ->assertSee('₱9.50')
            ->assertSee('Page 2 of 2');
    }

    public function test_valid_sale_records_database_price_and_reduces_stock(): void
    {
        /** @var User $staff */
        $staff = User::factory()->create();
        $customer = Customer::create([
            'full_name' => 'Casey Customer',
            'email' => 'casey@example.test',
        ]);
        $product = $this->createProduct(stock: 5);

        $this->actingAs($staff)
            ->post(route('sales.store'), [
                'product_id' => $product->id,
                'customer_id' => $customer->id,
                'quantity' => 2,
            ])
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Sale recorded. Test Product has 3 unit(s) remaining.');

        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseHas('sales', [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'sold_by' => $staff->id,
            'quantity' => 2,
            'total_price' => '19.00',
        ]);
        $this->assertSame(3, $product->fresh()->stock_quantity);
    }

    #[DataProvider('unlinkedCustomerInputs')]
    public function test_valid_sale_without_a_customer(array $customerInput): void
    {
        $staff = User::factory()->create();
        $product = $this->createProduct(stock: 5);

        $this->actingAs($staff)->post(route('sales.store'), array_merge([
            'product_id' => $product->id,
            'quantity' => 3,
        ], $customerInput))
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseHas('sales', [
            'product_id' => $product->id,
            'customer_id' => null,
            'sold_by' => $staff->id,
            'quantity' => 3,
            'total_price' => '28.50',
        ]);
        $this->assertSame(2, $product->fresh()->stock_quantity);
    }

    public static function unlinkedCustomerInputs(): array
    {
        return [
            'omitted customer' => [[]],
            'null customer' => [['customer_id' => null]],
            'blank customer from form' => [['customer_id' => '']],
        ];
    }

    public function test_sale_ignores_browser_price_total_and_staff_id(): void
    {
        $staff = User::factory()->create();
        $otherStaff = User::factory()->create();
        $product = $this->createProduct(stock: 5);
        $unselectedProduct = $this->createProduct(stock: 7, name: 'Other Product');

        $this->actingAs($staff)->post(route('sales.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => '0.01',
            'total_price' => '0.02',
            'sold_by' => $otherStaff->id,
        ])
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseHas('sales', [
            'product_id' => $product->id,
            'sold_by' => $staff->id,
            'quantity' => 2,
            'total_price' => '19.00',
        ]);
        $this->assertSame(3, $product->fresh()->stock_quantity);
        $this->assertSame(7, $unselectedProduct->fresh()->stock_quantity);
    }

    public function test_selling_exact_remaining_stock_leaves_zero(): void
    {
        $staff = User::factory()->create();
        $product = $this->createProduct(stock: 2);

        $this->actingAs($staff)->post(route('sales.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ])
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasNoErrors();

        $this->assertSame(0, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseHas('sales', [
            'product_id' => $product->id,
            'quantity' => 2,
            'total_price' => '19.00',
        ]);
    }

    public function test_invalid_quantities_are_rejected_without_creating_a_sale(): void
    {
        /** @var User $staff */
        $staff = User::factory()->create();
        $product = $this->createProduct(stock: 5);

        foreach ([0, -1, '1.5', null, '', 'invalid'] as $quantity) {
            $response = $this->actingAs($staff)->post(route('sales.store'), [
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);

            $response->assertSessionHasErrors('quantity');
            $this->assertSame(5, $product->fresh()->stock_quantity);
            $this->assertDatabaseCount('sales', 0);
        }

        $this->actingAs($staff)->post(route('sales.store'), [
            'product_id' => $product->id,
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(5, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_invalid_product_or_customer_does_not_change_stock_or_sales(): void
    {
        $staff = User::factory()->create();
        $product = $this->createProduct(stock: 5);
        $deletedCustomer = Customer::create([
            'full_name' => 'Deleted Customer',
            'email' => 'deleted@example.test',
        ]);
        $deletedCustomer->delete();

        $invalidInputs = [
            [['quantity' => 1], 'product_id'],
            [['product_id' => $product->id + 1, 'quantity' => 1], 'product_id'],
            [['product_id' => 'invalid', 'quantity' => 1], 'product_id'],
            [['product_id' => $product->id, 'customer_id' => $deletedCustomer->id, 'quantity' => 1], 'customer_id'],
            [['product_id' => $product->id, 'customer_id' => 'invalid', 'quantity' => 1], 'customer_id'],
        ];

        foreach ($invalidInputs as [$input, $field]) {
            $this->actingAs($staff)->post(route('sales.store'), $input)
                ->assertSessionHasErrors($field);

            $this->assertDatabaseCount('sales', 0);
            $this->assertSame(5, $product->fresh()->stock_quantity);
        }
    }

    public function test_over_stock_sale_is_rejected_without_changing_stock_or_sales(): void
    {
        /** @var User $staff */
        $staff = User::factory()->create();
        $product = $this->createProduct(stock: 2);

        $this->actingAs($staff)
            ->from(route('sales.create'))
            ->post(route('sales.store'), [
                'product_id' => $product->id,
                'quantity' => 3,
            ])
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasErrors('quantity');

        $this->assertSame(2, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_archived_and_sold_out_products_cannot_be_sold(): void
    {
        /** @var User $staff */
        $staff = User::factory()->create();
        $archivedProduct = $this->createProduct(stock: 4);
        $archivedProduct->delete();
        $soldOutProduct = $this->createProduct(stock: 0, name: 'Sold Out Product');

        $this->actingAs($staff)
            ->post(route('sales.store'), [
                'product_id' => $archivedProduct->id,
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('product_id');

        $this->actingAs($staff)
            ->post(route('sales.store'), [
                'product_id' => $soldOutProduct->id,
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('sales', 0);
        $this->assertSame(4, $archivedProduct->fresh()->stock_quantity);
        $this->assertSame(0, $soldOutProduct->fresh()->stock_quantity);
    }

    public function test_failed_transaction_rolls_back_both_sale_and_stock_update(): void
    {
        $staff = User::factory()->create();
        $product = $this->createProduct(stock: 5);
        $failure = new RuntimeException('Simulated failure after stock was saved.');
        $originalDispatcher = Product::getEventDispatcher();

        // Isolate the failure listener so later tests keep their normal model events.
        Product::setEventDispatcher(clone $originalDispatcher);

        try {
            Product::updated(function (Product $updatedProduct) use ($product, $failure): void {
                if ($updatedProduct->id !== $product->id) {
                    return;
                }

                // Both writes must have happened before forcing the rollback.
                $this->assertDatabaseCount('sales', 1);
                $this->assertSame(3, $product->fresh()->stock_quantity);

                throw $failure;
            });

            $this->withoutExceptionHandling();

            try {
                $this->actingAs($staff)->post(route('sales.store'), [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ]);

                $this->fail('The simulated transaction failure was not raised.');
            } catch (RuntimeException $exception) {
                $this->assertSame($failure, $exception);
            }
        } finally {
            Product::setEventDispatcher($originalDispatcher);
        }

        $this->assertDatabaseCount('sales', 0);
        $this->assertSame(5, $product->fresh()->stock_quantity);
    }

    private function createProduct(int $stock, string $name = 'Test Product'): Product
    {
        return Product::create([
            'name' => $name,
            'price' => '9.50',
            'stock_quantity' => $stock,
        ]);
    }
}
