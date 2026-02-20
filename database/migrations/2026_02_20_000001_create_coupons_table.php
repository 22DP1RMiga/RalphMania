<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();                     // Kupona kods (lierie burti)
            $table->string('type')->default('percentage');            // percentage | fixed
            $table->decimal('value', 8, 2);                          // 10 = 10% vai 10 = €10
            $table->decimal('min_order_amount', 8, 2)->default(0);   // Min pasūtījums
            $table->decimal('max_discount_amount', 8, 2)->nullable(); // Maks atlaide €
            $table->integer('max_uses')->nullable();                  // null = neierobežoti
            $table->integer('used_count')->default(0);
            $table->integer('max_uses_per_user')->default(1);        // Cik reizes 1 user var izmantot
            $table->integer('cooldown_days')->default(14);           // Dienas līdz atjaunošanai
            $table->boolean('subscribers_only')->default(false);      // Tikai abonentiem
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('description_lv')->nullable();
            $table->text('description_en')->nullable();
            $table->timestamps();
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('discount_amount', 8, 2);                // Cik € atlaidi saņēma
            $table->timestamp('used_at');
            $table->timestamp('reusable_at')->nullable();            // Kad var atkal izmantot
            $table->timestamps();

            $table->index(['coupon_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};
