<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_customers(): void
    {
        $this->get(route('customers.index'))
            ->assertRedirect(route('login'));
    }

    public function test_staff_can_create_customer(): void
    {
        $staff = User::factory()->create();

        $this->actingAs($staff)
            ->post(route('customers.store'), [
                'full_name' => 'Maria Santos',
                'email' => 'maria@example.com',
                'phone' => '09171234567',
            ])
            ->assertRedirect(route('customers.index'));

        $this->assertDatabaseHas('customers', [
            'full_name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'phone' => '09171234567',
        ]);
    }

    public function test_invalid_email_is_rejected(): void
    {
        $staff = User::factory()->create();

        $this->actingAs($staff)
            ->post(route('customers.store'), [
                'full_name' => 'Maria Santos',
                'email' => 'not-an-email',
                'phone' => null,
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('customers', 0);
    }

    public function test_staff_can_update_and_search_customer(): void
    {
        $staff = User::factory()->create();
        $customer = Customer::create([
            'full_name' => 'Maria Santos',
            'email' => 'maria@example.com',
        ]);

        $this->actingAs($staff)
            ->put(route('customers.update', $customer), [
                'full_name' => 'Maria Reyes',
                'email' => 'maria.reyes@example.com',
                'phone' => '',
            ])
            ->assertRedirect(route('customers.index'));

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'full_name' => 'Maria Reyes',
            'email' => 'maria.reyes@example.com',
            'phone' => null,
        ]);

        $this->actingAs($staff)
            ->get(route('customers.index', ['search' => 'Reyes']))
            ->assertOk()
            ->assertSee('Maria Reyes');
    }

    public function test_deleting_customer_keeps_sale_without_customer_link(): void
    {
        $staff = User::factory()->create();
        $customer = Customer::create([
            'full_name' => 'Maria Santos',
            'email' => 'maria@example.com',
        ]);
        $product = Product::create([
            'name' => 'Chicken Adobo Rice',
            'price' => '89.00',
            'stock_quantity' => 10,
        ]);
        $sale = Sale::create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'sold_by' => $staff->id,
            'quantity' => 1,
            'total_price' => '89.00',
        ]);

        $this->actingAs($staff)
            ->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'));

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'customer_id' => null,
            'total_price' => '89.00',
        ]);
    }
}