<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Atlaides summa € (0.00 ja nav kupona)
            $table->decimal('discount_amount', 10, 2)
                ->default(0)
                ->after('shipping_cost')
                ->comment('Kupona atlaide EUR / Coupon discount EUR');

            // Izmantotā kupona kods (NULL ja nav)
            $table->string('coupon_code', 64)
                ->nullable()
                ->after('discount_amount')
                ->comment('Izmantotais kupona kods / Used coupon code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'coupon_code']);
        });
    }
};
