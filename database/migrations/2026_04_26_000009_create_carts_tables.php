<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('FK to users (NULL for guest carts)');
            $table->string('session_id')->nullable()->comment('Session ID for guest carts');
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->nullable()->constrained('carts')->nullOnDelete()
                ->comment('FK to carts');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('FK to users (NULL = guest cart)');
            $table->string('session_id')->nullable()->comment('Sesijas ID viesiem / Session ID for guests');
            $table->foreignId('product_id')->constrained('products')->comment('FK to products');
            $table->unsignedInteger('quantity')->default(1)->comment('Produkta daudzums / Product quantity');
            $table->string('size', 10)->nullable();
            $table->decimal('price', 10, 2)->nullable()->comment('Cena pirkšanas brīdī / Price at purchase time');
            // total = quantity * price (generated kolonna) — aprēķina lietotnē
            $table->timestamp('created_at')->nullable()->comment('Izveidošanas datums / Created at');
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
