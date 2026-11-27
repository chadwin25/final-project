<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_staff(): void
    {
        $this->get(route('staff.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_create_an_account_with_avatar(): void
    {
        Storage::fake('public');
        $manager = User::factory()->create(['role' => 'admin', 'email' => 'admin@example.test']);

        $this->actingAs($manager)
            ->post(route('staff.store'), [
                'full_name' => 'Jordan Cruz',
                'email' => 'jordan@example.test', 'role' => 'admin',
                'password' => 'LongTestPassword123!',
                'password_confirmation' => 'LongTestPassword123!',
                'avatar' => UploadedFile::fake()->image('jordan.jpg', 400, 400),
            ])
            ->assertRedirect(route('staff.index'));

        $newStaff = User::where('email', 'jordan@example.test')->firstOrFail();

        $this->assertSame('Jordan Cruz', $newStaff->full_name);
        $this->assertSame('staff', $newStaff->role);
        $this->assertTrue(Hash::check('LongTestPassword123!', $newStaff->password));
        $this->assertNotSame('LongTestPassword123!', $newStaff->password);
        $this->assertNotNull($newStaff->avatar);
        Storage::disk('public')->assertExists($newStaff->avatar);
    }

    public function test_duplicate_email_and_invalid_avatar_are_rejected(): void
    {
        Storage::fake('public');
        $manager = User::factory()->create(['role' => 'admin', 'email' => 'admin@example.test']);

        $this->actingAs($manager)
            ->post(route('staff.store'), [
                'full_name' => 'Duplicate Account',
                'email' => 'admin@example.test',
                'password' => 'LongTestPassword123!',
                'password_confirmation' => 'LongTestPassword123!',
                'avatar' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
            ])
            ->assertSessionHasErrors(['email', 'avatar']);

        $this->assertSame(1, User::count());
    }

    public function test_admin_can_update_without_changing_password_and_replace_avatar(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/old.jpg', 'old image');

        $manager = User::factory()->create(['role' => 'admin', 'email' => 'admin@example.test']);
        $staff = User::factory()->create([
            'email' => 'old@example.test',
            'avatar' => 'avatars/old.jpg',
        ]);
        $originalPassword = $staff->password;

        $this->actingAs($manager)
            ->put(route('staff.update', $staff), [
                'full_name' => 'Updated Staff',
                'email' => 'updated@example.test',
                'password' => '',
                'password_confirmation' => '',
                'avatar' => UploadedFile::fake()->image('new.png', 400, 400),
            ])
            ->assertRedirect(route('staff.index'));

        $staff->refresh();

        $this->assertSame('Updated Staff', $staff->full_name);
        $this->assertSame('updated@example.test', $staff->email);
        $this->assertSame($originalPassword, $staff->password);
        Storage::disk('public')->assertExists($staff->avatar);
        Storage::disk('public')->assertMissing('avatars/old.jpg');
    }

    public function test_admin_cannot_archive_their_own_account(): void
    {
        $manager = User::factory()->create(['role' => 'admin', 'email' => 'admin@example.test']);

        $this->actingAs($manager)
            ->delete(route('staff.destroy', $manager))
            ->assertSessionHasErrors('staff');

        $this->assertNotSoftDeleted($manager);
    }

    public function test_admin_can_archive_and_restore_another_account(): void
    {
        $manager = User::factory()->create(['role' => 'admin', 'email' => 'admin@example.test']);
        $staff = User::factory()->create([
            'email' => 'temporary@example.test',
            'password' => 'LongTestPassword123!',
        ]);

        $this->actingAs($manager)
            ->delete(route('staff.destroy', $staff))
            ->assertRedirect(route('staff.index'));

        $this->assertSoftDeleted($staff);
        $this->assertFalse(Auth::attempt([
            'email' => 'temporary@example.test',
            'password' => 'LongTestPassword123!',
        ]));

        $this->actingAs($manager)
            ->patch(route('staff.restore', $staff->id))
            ->assertRedirect(route('staff.index', ['view' => 'archived']));

        $this->assertNotSoftDeleted($staff);
    }

    public function test_regular_staff_cannot_open_or_mutate_staff_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@example.test']);
        $staff = User::factory()->create(['username' => 'cashier']);
        $this->actingAs($staff, 'web');
        foreach (['staff.index', 'staff.create', 'staff.edit'] as $route) {
            $this->get(route($route, $admin))->assertForbidden();
        }
        foreach ([['post', 'staff.store'], ['put', 'staff.update'], ['delete', 'staff.destroy'], ['patch', 'staff.restore']] as [$method, $route]) {
            $this->$method(route($route, $admin), [
                'full_name' => 'Unauthorized Staff', 'email' => 'unauthorized@example.test',
                'password' => 'LongTestPassword123!', 'password_confirmation' => 'LongTestPassword123!',
            ])->assertForbidden();
        }
        $this->assertDatabaseCount('users', 2);
        $this->assertNotSoftDeleted($admin);
        $this->get(route('dashboard'))->assertOk()->assertDontSee('href="'.route('staff.index').'"', false);
        $this->get(route('products.create'))->assertOk()->assertSee('name="category"', false);
    }

    public function test_admin_can_change_email_without_losing_admin_access(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@example.test']);
        $password = $admin->password;
        $this->actingAs($admin, 'web')->get(route('dashboard'))->assertOk()
            ->assertSee('href="'.route('staff.index').'"', false);
        $this->put(route('staff.update', $admin), [
            'full_name' => $admin->full_name, 'email' => 'updated.admin@example.test', 'role' => 'staff', 'password' => '',
        ])->assertRedirect(route('staff.index'));
        $this->assertSame('updated.admin@example.test', $admin->fresh()->email);
        $this->assertSame('admin', $admin->fresh()->role);
        $this->assertTrue($admin->fresh()->can('manage-staff'));
        $this->assertSame($password, $admin->fresh()->password);
    }

    public function test_staff_email_cannot_duplicate_a_customer_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = Customer::create(['full_name' => 'Customer', 'email' => 'customer@example.test']);
        $this->actingAs($admin)->post(route('staff.store'), [
            'full_name' => 'Staff', 'email' => $customer->email,
            'password' => 'StaffPassword123!', 'password_confirmation' => 'StaffPassword123!',
        ])->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }
}
