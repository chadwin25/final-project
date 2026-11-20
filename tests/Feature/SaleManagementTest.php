<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleManagementTest extends TestCase
{
    use RefreshDatabase;

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
            'created_at' => \Illuminate\Support\Carbon::parse('2026-11-20 15:21:00', 'UTC'),
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
            ->assertSessionHas('status', 'Sale recorded. Test Product has 3 unit(s) remaining.');

        $this->assertDatabaseHas('sales', [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'sold_by' => $staff->id,
            'quantity' => 2,
            'total_price' => '19.00',
        ]);
        $this->assertSame(3, $product->fresh()->stock_quantity);
    }

    public function test_invalid_quantities_are_rejected_without_creating_a_sale(): void
    {
        /** @var User $staff */
        $staff = User::factory()->create();
        $product = $this->createProduct(stock: 5);

        foreach ([0, -1, '1.5', null] as $quantity) {
            $response = $this->actingAs($staff)->post(route('sales.store'), [
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);

            $response->assertSessionHasErrors('quantity');
            $this->assertSame(5, $product->fresh()->stock_quantity);
            $this->assertDatabaseCount('sales', 0);
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
        $this->assertSame(0, $soldOutProduct->fresh()->stock_quantity);
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