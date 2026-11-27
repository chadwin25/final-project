<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_staff_can_sign_in_and_open_dashboard(): void
    {
        User::factory()->create([
            'email' => 'cashier@example.test',
            'password' => 'A-strong-test-password-123',
        ]);

        $this->post('/login', [
            'email' => ' CASHIER@EXAMPLE.TEST ',
            'password' => 'A-strong-test-password-123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->get('/dashboard')->assertOk();
    }

    public function test_wrong_password_does_not_sign_staff_in(): void
    {
        User::factory()->create([
            'email' => 'cashier@example.test',
            'password' => 'A-strong-test-password-123',
        ]);

        $this->post('/login', [
            'email' => 'cashier@example.test',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_staff_can_sign_out(): void
    {
        $staff = User::factory()->create();

        $this->actingAs($staff)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_login_pages_have_login_title_and_navigation_between_account_types(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('<h1 id="login-heading">Login</h1>', false)
            ->assertSee('Email address')->assertDontSee('name="username"', false)
            ->assertSee('href="'.route('customer.login').'"', false)->assertSee('Customer login');
        $this->get(route('customer.login'))->assertOk()->assertSee('<h1 id="login-heading">Login</h1>', false)
            ->assertSee('Email address')->assertSee('href="'.route('login').'"', false)->assertSee('Back to staff login');
    }

    public function test_customer_signup_cannot_create_staff_or_gain_staff_access(): void
    {
        User::factory()->create(['username' => 'admin']);
        $this->post(route('customer.signup.submit'), [
            'full_name' => 'Customer', 'email' => 'customer@example.test',
            'password' => 'CustomerPassword123!', 'password_confirmation' => 'CustomerPassword123!',
            'username' => 'forged_staff', 'role' => 'admin', 'is_admin' => true,
        ])->assertRedirect(route('customer.home'));
        $this->assertAuthenticated('customer');
        $this->assertGuest('web');
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('customers', 1);
        foreach (['dashboard', 'products.create', 'staff.create'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
        $this->post(route('staff.store'), [
            'username' => 'forged_staff', 'full_name' => 'Forged Staff',
            'password' => 'CustomerPassword123!', 'password_confirmation' => 'CustomerPassword123!',
        ])->assertRedirect(route('login'));
        $this->assertDatabaseCount('users', 1);
        $this->post(route('login.submit'), ['username' => 'forged_staff', 'password' => 'CustomerPassword123!'])
            ->assertSessionHasErrors('email');
        $this->assertGuest('web');
    }

    public function test_customer_login_clears_staff_session_and_staff_login_clears_customer_session(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.test', 'role' => 'admin', 'password' => 'OriginalAdminPassword123!']);
        $customer = Customer::create(['full_name' => 'Customer', 'email' => 'customer@example.test', 'password' => 'CustomerPassword123!']);
        $this->actingAs($admin, 'web')->post(route('customer.login.submit'), [
            'email' => $customer->email, 'password' => 'CustomerPassword123!',
        ])->assertRedirect(route('customer.home'));
        $this->assertGuest('web');
        $this->assertAuthenticatedAs($customer, 'customer');
        $this->post(route('login.submit'), ['email' => $admin->email, 'password' => 'OriginalAdminPassword123!'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin, 'web');
        $this->assertGuest('customer');
    }

    public function test_customer_can_use_common_email_login_without_pos_access(): void
    {
        $customer = Customer::create(['full_name' => 'Customer', 'email' => 'customer@example.test', 'password' => 'CustomerPassword123!']);
        $this->post(route('login.submit'), ['email' => 'CUSTOMER@EXAMPLE.TEST', 'password' => 'CustomerPassword123!'])
            ->assertRedirect(route('customer.home'));
        $this->assertAuthenticatedAs($customer, 'customer');
        $this->assertGuest('web');
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_customer_login_cannot_authenticate_staff(): void
    {
        $staff = User::factory()->create(['password' => 'StaffPassword123!']);
        $this->post(route('customer.login.submit'), ['email' => $staff->email, 'password' => 'StaffPassword123!'])
            ->assertSessionHasErrors('email');
        $this->assertGuest('web');
        $this->assertGuest('customer');
    }

    public function test_username_and_archived_account_cannot_sign_in(): void
    {
        $old = User::factory()->create(['username' => 'admin', 'email' => 'retired@example.test', 'password' => 'password123']);
        $old->delete();
        $this->post(route('login.submit'), ['username' => 'admin', 'password' => 'password123'])->assertSessionHasErrors('email');
        $this->post(route('login.submit'), ['email' => $old->email, 'password' => 'password123'])->assertSessionHasErrors('email');
        $this->assertGuest('web');
        $this->assertGuest('customer');
    }

    public function test_customer_cannot_register_with_a_staff_email(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.test', 'role' => 'admin']);
        $this->post(route('customer.signup.submit'), [
            'full_name' => 'Forged Admin', 'email' => 'ADMIN@EXAMPLE.TEST',
            'password' => 'CustomerPassword123!', 'password_confirmation' => 'CustomerPassword123!',
            'role' => 'admin',
        ])->assertSessionHasErrors('email');
        $this->assertDatabaseCount('customers', 0);
        $this->assertSame('admin', $admin->fresh()->role);
        $this->assertGuest('web');
    }
}
