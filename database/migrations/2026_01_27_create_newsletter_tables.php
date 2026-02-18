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
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade')->comment('FK to users (if registered)');
            $table->string('email', 100)->unique()->comment('Abonenta e-pasts');
            $table->string('token', 64)->unique()->comment('Unikāls tokens atteikšanās saitei');
            $table->boolean('is_verified')->default(false)->comment('Vai e-pasts ir apstiprināts');
            $table->timestamp('verified_at')->nullable()->comment('Apstiprināšanas datums');
            $table->boolean('is_active')->default(true)->comment('Vai abonements ir aktīvs');
            $table->timestamp('unsubscribed_at')->nullable()->comment('Atteikšanās datums');

            // Preferences
            $table->boolean('receive_news')->default(true)->comment('Saņemt jaunumus');
            $table->boolean('receive_promotions')->default(true)->comment('Saņemt piedāvājumus');
            $table->boolean('receive_announcements')->default(true)->comment('Saņemt paziņojumus');

            $table->timestamps();

            $table->index('email');
            $table->index('is_active');
        });

        // Tabula ekskluzīviem abonentu piedāvājumiem
        Schema::create('subscriber_offers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Atlaides kods');
            $table->string('title_lv', 100)->comment('Nosaukums LV');
            $table->string('title_en', 100)->comment('Nosaukums EN');
            $table->text('description_lv')->nullable()->comment('Apraksts LV');
            $table->text('description_en')->nullable()->comment('Apraksts EN');
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage')->comment('Atlaides tips');
            $table->decimal('discount_value', 10, 2)->comment('Atlaides vērtība');
            $table->decimal('min_order_amount', 10, 2)->nullable()->comment('Minimālā pasūtījuma summa');
            $table->integer('max_uses')->nullable()->comment('Maksimālais izmantošanas skaits');
            $table->integer('used_count')->default(0)->comment('Izmantots reižu skaits');
            $table->boolean('subscribers_only')->default(true)->comment('Tikai abonentiem');
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriber_offers');
        Schema::dropIfExists('newsletter_subscribers');
    }
};
