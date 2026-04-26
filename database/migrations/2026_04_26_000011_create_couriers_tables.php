<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade')
                ->comment('FK to users (courier account)');
            $table->string('full_name', 100)->comment('Kurjera vārds / Courier name');
            $table->string('vehicle_type', 50)->nullable()->comment('Transporta līdzekļa veids / Vehicle type');
            $table->string('delivery_area', 100)->nullable()->comment('Piegādes rajons / Delivery area');
            $table->string('phone', 20)->comment('Kontakttālrunis / Contact phone');
            $table->boolean('is_active')->default(true)->comment('Vai ir aktīvs / Is active');
            $table->date('hired_at')->nullable()->comment('Nodarbinātības datums / Employment date');
            $table->timestamps();
        });

        Schema::create('courier_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courier_id')->constrained('couriers')->onDelete('cascade')
                ->comment('FK to couriers');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade')
                ->comment('FK to orders');
            $table->timestamp('assigned_at')->nullable()->useCurrent()
                ->comment('Piešķiršanas datums / Assignment date');
            $table->timestamp('completed_at')->nullable()
                ->comment('Pabeigšanas datums / Completion date');
            $table->text('notes')->nullable()->comment('Kurjera piezīmes / Courier notes');
            // Nav timestamps — courier_assignments neizmanto created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_assignments');
        Schema::dropIfExists('couriers');
    }
};
