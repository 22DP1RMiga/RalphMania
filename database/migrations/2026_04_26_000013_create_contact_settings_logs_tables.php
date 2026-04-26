<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Kontaktu ziņojumi ────────────────────────────────────────
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('FK to users (if registered)');
            $table->string('name', 100);
            $table->string('email', 100);
            $table->string('phone', 20)->nullable()->comment('Tālruņa numurs ar valsts kodu / Phone number with country code');
            $table->string('country_code', 5)->nullable()->comment('Valsts kods / Country code');
            $table->string('subject', 200)->comment('Ziņojuma virsraksts / Message subject');
            $table->text('message')->comment('Ziņojuma saturs / Message content');
            $table->boolean('is_read')->default(false)->comment('Vai ir izlasīts / Is read');
            $table->boolean('is_replied')->default(false)->comment('Vai ir atbildēts / Is replied');
            $table->text('reply_text')->nullable()->comment('Administratora atbilde / Admin reply text');
            $table->timestamp('replied_at')->nullable()->comment('Atbildes datums / Reply date');
            $table->foreignId('replied_by')->nullable()->constrained('administrators')->nullOnDelete()
                ->comment('FK to administrators');
            $table->timestamps();
        });

        // ── Sistēmas iestatījumi ─────────────────────────────────────
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });

        // ── Aktivitāšu žurnāls ───────────────────────────────────────
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('activity_type', 50)
                ->comment('Darbības veids: login, logout, order_created / Activity type');
            $table->text('description')->nullable()->comment('Apraksts / Description');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('contact_messages');
    }
};
