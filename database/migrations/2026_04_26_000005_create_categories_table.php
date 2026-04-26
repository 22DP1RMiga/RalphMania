<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name_lv', 100)->comment('Nosaukums latviski / Name in Latvian');
            $table->string('name_en', 100)->comment('Nosaukums angliski / Name in English');
            $table->string('slug', 100)->unique()->comment('URL-friendly slug');
            $table->text('description_lv')->nullable()->comment('Apraksts latviski / Description in Latvian');
            $table->text('description_en')->nullable()->comment('Apraksts angliski / Description in English');
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete()
                ->comment('FK to categories - nested categories');
            $table->unsignedInteger('sort_order')->default(0)->comment('Kārtošanas secība / Sort order');
            $table->boolean('is_active')->default(true)->comment('Vai ir aktīva / Is active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
