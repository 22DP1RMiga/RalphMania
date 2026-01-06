<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('card_last4', 4)->nullable()->after('payment_method')->comment('Last 4 digits of card');
            $table->string('card_brand', 20)->nullable()->after('card_last4')->comment('Card brand (Visa, Mastercard, etc)');
            $table->string('card_exp_month', 2)->nullable()->after('card_brand')->comment('Card expiry month (MM)');
            $table->string('card_exp_year', 4)->nullable()->after('card_exp_month')->comment('Card expiry year (YYYY)');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['card_last4', 'card_brand', 'card_exp_month', 'card_exp_year']);
        });
    }
};
