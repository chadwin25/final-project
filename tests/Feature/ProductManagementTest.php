<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_products(): void
    {
        $this->get(route('products.index'))
            ->assertRedirect(route('login'));
    }

    public function test_staff_can_create_product_with_image(): void
    {
        Storage::fake('public');
        $staff = User::factory()->create();

        $this->actingAs($staff)
            ->post(route('products.store'), [
                'name' => 'Tuna Sandwich',
                'price' => '49.50',
                'stock_quantity' => 8,
                'image' => UploadedFile::fake()->image('sandwich.jpg', 600, 400),
            ])
            ->assertRedirect(route('products.index'));

        $product = Product::where('name', 'Tuna Sandwich')->firstOrFail();

        $this->assertSame('49.50', $product->price);
        $this->assertSame(8, $product->stock_quantity);
        $this->assertNotNull($product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_non_image_upload_is_rejected(): void
    {
        Storage::fake('public');
        $staff = User::factory()->create();

        $this->actingAs($staff)
            ->post(route('products.store'), [
                'name' => 'Invalid Product',
                'price' => '40.00',
                'stock_quantity' => 3,
                'image' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
            ])
            ->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('products', ['name' => 'Invalid Product']);
    }

    public function test_staff_can_update_product_and_replace_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/old.jpg', 'old image');

        $staff = User::factory()->create();
        $product = Product::create([
            'name' => 'Rice Meal',
            'price' => '80.00',
            'stock_quantity' => 5,
            'image' => 'products/old.jpg',
        ]);

        $this->actingAs($staff)
            ->put(route('products.update', $product), [
                'name' => 'Chicken Rice Meal',
                'price' => '85.00',
                'stock_quantity' => 7,
                'image' => UploadedFile::fake()->image('new.jpg', 600, 400),
            ])
            ->assertRedirect(route('products.index'));

        $product->refresh();

        $this->assertSame('Chicken Rice Meal', $product->name);
        Storage::disk('public')->assertExists($product->image);
        Storage::disk('public')->assertMissing('products/old.jpg');
    }

    public function test_staff_can_archive_and_restore_product(): void
    {
        $staff = User::factory()->create();
        $product = Product::create([
            'name' => 'Banana Cue',
            'price' => '25.00',
            'stock_quantity' => 10,
        ]);

        $this->actingAs($staff)
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertSoftDeleted($product);

        $this->actingAs($staff)
            ->patch(route('products.restore', $product->id))
            ->assertRedirect(route('products.index', ['view' => 'archived']));

        $this->assertNotSoftDeleted($product);
    }
}