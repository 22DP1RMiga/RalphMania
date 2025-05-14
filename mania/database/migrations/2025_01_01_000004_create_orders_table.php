<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_ID');
            $table->foreignId('user_ID')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['new', 'approved', 'in processing', 'sent', 'delivered', 'canceled', 'repaid']);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('orders');
    }
};
