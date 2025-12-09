<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')
                ->comment('FK to users - who liked');
            $table->string('likeable_type')->comment('Polymorphic type: Content, Product, etc.');
            $table->unsignedBigInteger('likeable_id')->comment('Polymorphic ID');
            $table->timestamps();

            // Unique constraint - user can like same item only once
            $table->unique(['user_id', 'likeable_type', 'likeable_id'], 'unique_like');

            // Index for faster lookups
            $table->index(['likeable_type', 'likeable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
