<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 30)->unique()->comment('Lietotājvārds (max 30 chars)');
            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            $table->string('email', 100)->unique()->comment('E-pasta adrese (max 100 chars)');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->comment('Hashed password (min 8 chars, digits + uppercase)');
            $table->string('phone', 20)->nullable()->comment('Tālruņa numurs / Phone number');
            $table->date('birth_date')->nullable()->comment('Dzimšanas datums / Birth date');
            $table->string('country', 50)->default('Latvia')->comment('Valsts / Country');
            $table->string('city', 50)->nullable()->comment('Pilsēta / City');
            $table->string('address', 100)->nullable()->comment('Adrese (max 100 chars) / Address');
            $table->string('postal_code', 20)->nullable()->comment('Pasta indekss / Postal code');
            $table->string('profile_picture')->nullable()->comment('Profila bilde (.jpg, .png) / Profile picture');
            $table->foreignId('role_id')->default(2)->constrained('roles')->comment('FK to roles (1=guest, 2=user, 3=admin, 4=courier)');
            $table->boolean('is_active')->default(true)->comment('Vai konts ir aktīvs / Is account active');
            $table->boolean('is_public')->default(true)->comment('Vai profils ir publiski redzams / Is profile publicly visible');
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
