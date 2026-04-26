<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('Role system name');
            $table->string('display_name_lv', 100)->comment('Displeja nosaukums latviski');
            $table->string('display_name_en', 100)->comment('Display name in English');
            $table->text('description_lv')->nullable()->comment('Apraksts latviski');
            $table->text('description_en')->nullable()->comment('Description in English');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
