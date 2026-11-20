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