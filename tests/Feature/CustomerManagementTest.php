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

    public function test_customer_menu_search_and_categories_preserve_the_cart(): void
    {
        $customer = Customer::create(['full_name' => 'Customer', 'email' => 'customer@example.test']);
        Product::create(['name' => 'Rice Meal', 'category' => 'food', 'price' => '65.00', 'stock_quantity' => 5]);
        $juice = Product::create(['name' => 'Calamansi Juice', 'category' => 'drinks', 'price' => '20.00', 'stock_quantity' => 2]);
        Product::create(['name' => 'Sold Out Juice', 'category' => 'drinks', 'price' => '20.00', 'stock_quantity' => 0]);
        $this->actingAs($customer, 'customer')->withSession(['customer_cart' => [$juice->id => 1]])
            ->get(route('customer.home', ['category' => 'drinks', 'search' => 'Juice']))
            ->assertOk()->assertSee('Calamansi Juice')->assertDontSee('Rice Meal')->assertDontSee('Sold Out Juice')
            ->assertSee('Search menu')->assertSee('Add-on')->assertSee('aria-current="page"', false)
            ->assertSee('Current Order')->assertSessionHas('customer_cart.'.$juice->id, 1);
        $this->get(route('customer.home', ['category' => 'food']))->assertOk()
            ->assertSee('Rice Meal')->assertSee('Calamansi Juice')
            ->assertSessionHas('customer_cart.'.$juice->id, 1);
    }

    public function test_customer_can_increase_decrease_and_remove_cart_items_without_changing_stock(): void
    {
        $customer = Customer::create(['full_name' => 'Customer', 'email' => 'customer@example.test']);
        $product = Product::create(['name' => 'Coffee', 'price' => '9.50', 'stock_quantity' => 3]);
        $this->actingAs($customer, 'customer')->from(route('customer.home'))
            ->post(route('customer.cart.add', $product), ['quantity' => '1'])
            ->assertRedirect(route('customer.home'))->assertSessionHas('customer_cart.'.$product->id, 1);
        $this->get(route('customer.home'))->assertOk()
            ->assertSee('Decrease Coffee quantity')->assertSee('Increase Coffee quantity')
            ->assertSee('Remove Coffee from your order');

        $this->put(route('customer.cart.update', $product), ['quantity' => '2'])
            ->assertSessionHas('customer_cart.'.$product->id, 2);
        $this->get(route('customer.home'))->assertOk()->assertSee('₱19.00');
        $this->put(route('customer.cart.update', $product), ['quantity' => '4'])
            ->assertSessionHasErrors('quantity')->assertSessionHas('customer_cart.'.$product->id, 2);
        $this->put(route('customer.cart.update', $product), ['quantity' => '1'])
            ->assertSessionHas('customer_cart.'.$product->id, 1);
        $this->put(route('customer.cart.update', $product), ['quantity' => '0'])
            ->assertSessionMissing('customer_cart.'.$product->id);
        $this->get(route('customer.home'))->assertSee('Add an item from the menu to start an order.');
        $this->assertSame(3, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('customer_orders', 0);
    }

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
