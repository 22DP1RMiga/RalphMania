<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Produktiem: vai ir izmēru izvēlne + kādas kategorijas slug apzīmē apģērbu
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_sizes')->default(false)->after('is_featured');
        });

        // Grozam: saglabāt izvēlēto izmēru
        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('size', 10)->nullable()->after('quantity');
        });

        // Pasūtījumu rindām: saglabāt izmēru
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('size', 10)->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('has_sizes');
        });
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('size');
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('size');
        });
    }
};
