<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Kuponi ───────────────────────────────────────────────────
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('type')->default('percentage');
            $table->decimal('value', 8, 2);
            $table->decimal('min_order_amount', 8, 2)->default(0);
            $table->decimal('max_discount_amount', 8, 2)->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('used_count')->default(0);
            $table->integer('max_uses_per_user')->default(1);
            $table->integer('cooldown_days')->default(14);
            $table->boolean('subscribers_only')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('description_lv')->nullable();
            $table->text('description_en')->nullable();
            $table->timestamps();
        });

        // ── Kuponu izmantošana ───────────────────────────────────────
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->decimal('discount_amount', 8, 2);
            $table->timestamp('used_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('reusable_at')->nullable();
            $table->timestamps();
        });

        // ── Abonenta piedāvājumi ─────────────────────────────────────
        Schema::create('subscriber_offers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->comment('Atlaides kods');
            $table->string('title_lv', 100)->comment('Nosaukums LV');
            $table->string('title_en', 100)->comment('Nosaukums EN');
            $table->text('description_lv')->nullable()->comment('Apraksts LV');
            $table->text('description_en')->nullable()->comment('Apraksts EN');
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage')
                ->comment('Atlaides tips');
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

        // ── Jaunumu abonenti ─────────────────────────────────────────
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email', 100)->unique()->comment('Abonenta e-pasts');
            $table->string('token', 64)->comment('Unikāls tokens atteikšanās saitei');
            $table->boolean('is_verified')->default(false)->comment('Vai e-pasts ir apstiprināts');
            $table->timestamp('verified_at')->nullable()->comment('Apstiprināšanas datums');
            $table->boolean('is_active')->default(true)->comment('Vai abonements ir aktīvs');
            $table->timestamp('unsubscribed_at')->nullable()->comment('Atteikšanās datums');
            $table->boolean('receive_news')->default(true)->comment('Saņemt jaunumus');
            $table->boolean('receive_promotions')->default(true)->comment('Saņemt piedāvājumus');
            $table->boolean('receive_announcements')->default(true)->comment('Saņemt paziņojumus');
            $table->timestamp('subscription_expires_at')->nullable()
                ->comment('null = abonēšana bez termiņa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('subscriber_offers');
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};
