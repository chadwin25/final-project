<?php

namespace Tests\Feature;

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
            'username' => 'cashier',
            'password' => 'A-strong-test-password-123',
        ]);

        $this->post('/login', [
            'username' => 'cashier',
            'password' => 'A-strong-test-password-123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->get('/dashboard')->assertOk();
    }

    public function test_wrong_password_does_not_sign_staff_in(): void
    {
        User::factory()->create([
            'username' => 'cashier',
            'password' => 'A-strong-test-password-123',
        ]);

        $this->post('/login', [
            'username' => 'cashier',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('username');

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
}