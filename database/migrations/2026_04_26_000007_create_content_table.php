<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content', function (Blueprint $table) {
            $table->id();
            $table->string('title_lv', 100)->comment('Virsraksts latviski (max 100 chars)');
            $table->string('title_en', 100)->nullable()->comment('Title in English');
            $table->string('slug')->unique()->comment('URL-friendly slug');
            $table->enum('type', ['video', 'blog', 'post', 'announcement'])
                ->comment('Satura veids: video, blogs, ziņas vai paziņojumi / Content type');
            $table->text('description_lv')->nullable()->comment('Apraksts latviski / Description in Latvian');
            $table->text('description_en')->nullable()->comment('Apraksts angliski / Description in English');
            $table->longText('content_body_lv')->nullable()->comment('Pilns saturs latviski (blogs)');
            $table->longText('content_body_en')->nullable()->comment('Full content in English (blog)');
            $table->string('video_url', 500)->nullable()->comment('Video URL (YouTube, TikTok, Instagram, etc.)');
            $table->string('video_platform', 50)->nullable()->comment('Platforma: YouTube, TikTok, Instagram, Facebook, Twitch, Vimeo');
            $table->string('thumbnail')->nullable()->comment('Video thumbnail image');
            $table->string('featured_image')->nullable()->comment('Featured/hero image for blog posts');
            $table->json('blog_images')->nullable()->comment('Blog post images (JSON array of image paths)');
            $table->unsignedInteger('duration')->nullable()->comment('Video ilgums sekundēs / Duration in seconds');
            $table->string('category', 100)->nullable()->comment('Kategorija / Category');
            $table->unsignedInteger('view_count')->default(0)->comment('Skatījumu skaits / View count');
            $table->unsignedInteger('like_count')->default(0)->comment('Patīk skaits / Like count');
            $table->boolean('is_published')->default(false)->comment('Vai ir publicēts / Is published');
            $table->boolean('is_featured')->default(false)->comment('Vai ir featured / Is featured');
            $table->timestamp('published_at')->nullable()->comment('Publicēšanas datums / Publication date');
            $table->foreignId('created_by')->constrained('users')->comment('FK to users - content creator');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content');
    }
};
