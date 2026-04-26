<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Atsauksmes (polimorfiskas: content vai products) ─────────
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')
                ->comment('FK to users - kas sniedz atsauksmi / who reviews');
            $table->string('reviewable_type', 50)->comment('content vai products / content or products');
            $table->unsignedBigInteger('reviewable_id')->comment('ID no content vai products / ID from content or products');
            $table->unsignedTinyInteger('rating')->comment('Vērtējums 1-5 zvaigznes / Rating 1-5 stars');
            $table->text('review_text_lv')->nullable()->comment('Atsauksmes teksts latviski / Review text in Latvian');
            $table->text('review_text_en')->nullable()->comment('Atsauksmes teksts angliski / Review text in English');
            $table->boolean('is_approved')->default(false)->comment('Vai ir apstiprināts / Is approved');
            $table->timestamps();

            $table->index(['reviewable_type', 'reviewable_id'], 'reviewable_index');
        });

        // ── Komentāri pie satura (ar thread atbalstu) ────────────────
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')
                ->comment('FK to users - kas komentē / who comments');
            $table->foreignId('content_id')->constrained('content')->onDelete('cascade')
                ->comment('FK to content - kam pievienots / what is commented');
            $table->text('comment_text')->comment('Komentāra teksts (max 300 chars) / Comment text');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->nullOnDelete()
                ->comment('FK to comments - thread/replies');
            $table->boolean('is_approved')->default(false)->comment('Vai ir apstiprināts / Is approved');
            $table->timestamps();
        });

        // ── Komentāru noskaņojuma vērtējumi ─────────────────────────
        Schema::create('comment_moods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('comments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedTinyInteger('score')->comment('Noskaņojuma vērtējums 0–100');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->unique(['comment_id', 'user_id'], 'comment_moods_unique');
        });

        // ── Patīk (polimorfiskas) ────────────────────────────────────
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('likeable_type')->comment('Polymorphic type: Content, Product, etc.');
            $table->unsignedBigInteger('likeable_id')->comment('Polymorphic ID');
            $table->timestamps();

            $table->index(['likeable_type', 'likeable_id'], 'likeable_index');
            $table->unique(['user_id', 'likeable_type', 'likeable_id'], 'likes_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
        Schema::dropIfExists('comment_moods');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('reviews');
    }
};
