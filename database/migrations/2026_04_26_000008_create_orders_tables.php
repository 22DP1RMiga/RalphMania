<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Pasūtījumi ───────────────────────────────────────────────
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique()->comment('Pasūtījuma numurs (auto) / Order number');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('FK to users (NULL = guest)');
            $table->string('customer_name', 100)->comment('Klienta vārds / Customer name');
            $table->string('customer_email', 100);
            $table->string('customer_phone', 20);
            $table->string('delivery_country', 50)->default('Latvia');
            $table->string('delivery_city', 50)->comment('Pilsēta / City');
            $table->string('delivery_address', 100)->comment('Adrese (max 100 chars) / Address');
            $table->string('delivery_postal_code', 20)->nullable()->comment('Pasta indekss / Postal code');
            $table->decimal('subtotal', 10, 2)->comment('Summa bez piegādes / Subtotal');
            $table->decimal('shipping_cost', 10, 2)->default(0)->comment('Piegādes izmaksas / Shipping cost');
            $table->decimal('discount_amount', 10, 2)->default(0)->comment('Kupona atlaide EUR / Coupon discount EUR');
            $table->string('coupon_code', 64)->nullable()->comment('Izmantotais kupona kods / Used coupon code');
            $table->decimal('total_amount', 10, 2)->comment('Kopējā summa / Total amount');
            $table->enum('status', [
                'pending', 'confirmed', 'processing', 'packed',
                'shipped', 'in_transit', 'delivered', 'cancelled', 'refunded',
            ])->default('pending')->comment('Pasūtījuma statuss / Order status');
            $table->text('notes')->nullable()->comment('Klienta piezīmes / Customer notes');
            $table->string('tracking_number', 100)->nullable()->comment('Sūtījuma izsekošanas numurs / Tracking number');
            $table->timestamp('paid_at')->nullable()->comment('Apmaksas datums / Payment date');
            $table->timestamp('shipped_at')->nullable()->comment('Nosūtīšanas datums / Shipping date');
            $table->timestamp('delivered_at')->nullable()->comment('Piegādes datums / Delivery date');
            $table->timestamps();
        });

        // ── Pasūtījuma preces ────────────────────────────────────────
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade')
                ->comment('FK to orders');
            $table->foreignId('product_id')->constrained('products')
                ->comment('FK to products');
            $table->string('product_name', 50)->comment('Produkta nosaukums (snapshot) / Product name snapshot');
            $table->unsignedInteger('quantity')->comment('Daudzums / Quantity');
            $table->string('size', 10)->nullable();
            $table->decimal('price', 10, 2)->comment('Cena pirkšanas brīdī / Price at purchase');
            // total = quantity * price (generated) — nav atbalstīts visās DB, aprēķina lietotnē
            $table->timestamp('created_at')->nullable();
        });

        // ── Maksājumi ────────────────────────────────────────────────
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade')
                ->comment('FK to orders');
            $table->string('payment_method', 50)->comment('Maksājuma veids: card, paypal, bank_transfer / Payment method');
            $table->string('card_last4', 4)->nullable()->comment('Last 4 digits of card');
            $table->string('card_brand', 20)->nullable()->comment('Card brand (Visa, Mastercard, etc)');
            $table->string('card_exp_month', 2)->nullable()->comment('Card expiry month (MM)');
            $table->string('card_exp_year', 4)->nullable()->comment('Card expiry year (YYYY)');
            $table->decimal('amount', 10, 2)->comment('Summa / Amount');
            $table->string('currency', 3)->default('EUR');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])
                ->default('pending')->comment('Apmaksas statuss / Payment status');
            $table->string('transaction_id', 100)->nullable()->comment('External payment gateway ID');
            $table->json('gateway_response')->nullable()->comment('Full payment gateway response');
            $table->timestamp('paid_at')->nullable()->comment('Apmaksas datums / Payment date');
            $table->timestamp('refunded_at')->nullable()->comment('Atmaksas datums / Refund date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
