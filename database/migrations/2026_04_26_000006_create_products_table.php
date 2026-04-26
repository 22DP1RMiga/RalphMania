<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name_lv', 50)->comment('Nosaukums latviski (max 50 chars)');
            $table->string('name_en', 50)->comment('Nosaukums angliski (max 50 chars)');
            $table->string('slug')->unique()->comment('URL-friendly slug');
            $table->string('sku', 100)->nullable()->unique()->comment('Stock Keeping Unit');
            $table->foreignId('category_id')->constrained('categories')->comment('FK to categories');
            $table->string('description_lv', 255)->nullable()->comment('Apraksts latviski (max 255 chars)');
            $table->string('description_en', 255)->nullable()->comment('Apraksts angliski (max 255 chars)');
            $table->decimal('price', 10, 2)->comment('Cena (EUR, formāts XX.XX) / Price');
            $table->decimal('compare_price', 10, 2)->nullable()->comment('Vecā cena (atlaide) / Old price (discount)');
            $table->string('image')->nullable()->comment('Galvenais attēls (.jpg, .png) / Main image');
            $table->unsignedInteger('stock_quantity')->default(0)->comment('Krājums / Stock quantity');
            $table->unsignedInteger('low_stock_threshold')->default(5)->comment('Brīdinājums par zemu krājumu / Low stock warning');
            $table->boolean('is_active')->default(true)->comment('Vai produkts ir aktīvs / Is product active');
            $table->boolean('is_featured')->default(false)->comment('Vai ir featured / Is featured');
            $table->boolean('has_sizes')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
