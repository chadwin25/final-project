<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email', 100)->nullable()->unique();
            $table->string('role', 20)->default('staff');
        });

        DB::table('users')->orderBy('id')->eachById(function ($user) {
            DB::table('users')->where('id', $user->id)->update([
                'email' => 'legacy-'.$user->id.'@campusbite.test',
                'role' => $user->username === 'admin' ? 'admin' : 'staff',
            ]);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('password')->nullable()->after('email');
        });

        Schema::create('customer_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 40)->unique();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            $table->string('status', 30)->default('pending');
            $table->string('payment_method', 20);
            $table->string('payment_reference', 100)->nullable();
            $table->string('payment_status', 20)->default('pending');

            $table->decimal('amount_tendered', 10, 2)->nullable();
            $table->decimal('change_due', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2);

            $table->boolean('stock_reserved')->default(true);
            $table->timestamps();
        });

        Schema::create('customer_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_order_id')
                ->constrained('customer_orders')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->string('product_name', 100);
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_order_items');
        Schema::dropIfExists('customer_orders');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('password');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn(['email', 'role']);
        });
    }
};
