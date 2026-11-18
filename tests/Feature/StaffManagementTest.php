<?php

namespace Tests\Feature;

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

    public function test_staff_can_create_an_account_with_avatar(): void
    {
        Storage::fake('public');
        $manager = User::factory()->create();

        $this->actingAs($manager)
            ->post(route('staff.store'), [
                'full_name' => 'Jordan Cruz',
                'username' => 'jordan_cruz',
                'password' => 'LongTestPassword123!',
                'password_confirmation' => 'LongTestPassword123!',
                'avatar' => UploadedFile::fake()->image('jordan.jpg', 400, 400),
            ])
            ->assertRedirect(route('staff.index'));

        $newStaff = User::where('username', 'jordan_cruz')->firstOrFail();

        $this->assertSame('Jordan Cruz', $newStaff->full_name);
        $this->assertTrue(Hash::check('LongTestPassword123!', $newStaff->password));
        $this->assertNotSame('LongTestPassword123!', $newStaff->password);
        $this->assertNotNull($newStaff->avatar);
        Storage::disk('public')->assertExists($newStaff->avatar);
    }

    public function test_duplicate_username_and_invalid_avatar_are_rejected(): void
    {
        Storage::fake('public');
        $manager = User::factory()->create(['username' => 'manager_one']);

        $this->actingAs($manager)
            ->post(route('staff.store'), [
                'full_name' => 'Duplicate Account',
                'username' => 'manager_one',
                'password' => 'LongTestPassword123!',
                'password_confirmation' => 'LongTestPassword123!',
                'avatar' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
            ])
            ->assertSessionHasErrors(['username', 'avatar']);

        $this->assertSame(1, User::count());
    }

    public function test_staff_can_update_without_changing_password_and_replace_avatar(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/old.jpg', 'old image');

        $manager = User::factory()->create();
        $staff = User::factory()->create([
            'username' => 'old_staff',
            'avatar' => 'avatars/old.jpg',
        ]);
        $originalPassword = $staff->password;

        $this->actingAs($manager)
            ->put(route('staff.update', $staff), [
                'full_name' => 'Updated Staff',
                'username' => 'updated_staff',
                'password' => '',
                'password_confirmation' => '',
                'avatar' => UploadedFile::fake()->image('new.png', 400, 400),
            ])
            ->assertRedirect(route('staff.index'));

        $staff->refresh();

        $this->assertSame('Updated Staff', $staff->full_name);
        $this->assertSame('updated_staff', $staff->username);
        $this->assertSame($originalPassword, $staff->password);
        Storage::disk('public')->assertExists($staff->avatar);
        Storage::disk('public')->assertMissing('avatars/old.jpg');
    }

    public function test_staff_cannot_archive_their_own_account(): void
    {
        $manager = User::factory()->create();

        $this->actingAs($manager)
            ->delete(route('staff.destroy', $manager))
            ->assertSessionHasErrors('staff');

        $this->assertNotSoftDeleted($manager);
    }

    public function test_staff_can_archive_and_restore_another_account(): void
    {
        $manager = User::factory()->create();
        $staff = User::factory()->create([
            'username' => 'temporary_staff',
            'password' => 'LongTestPassword123!',
        ]);

        $this->actingAs($manager)
            ->delete(route('staff.destroy', $staff))
            ->assertRedirect(route('staff.index'));

        $this->assertSoftDeleted($staff);
        $this->assertFalse(Auth::attempt([
            'username' => 'temporary_staff',
            'password' => 'LongTestPassword123!',
        ]));

        $this->actingAs($manager)
            ->patch(route('staff.restore', $staff->id))
            ->assertRedirect(route('staff.index', ['view' => 'archived']));

        $this->assertNotSoftDeleted($staff);
    }
}