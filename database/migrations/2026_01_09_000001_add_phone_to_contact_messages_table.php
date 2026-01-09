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
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email')->comment('Tālruņa numurs ar valsts kodu / Phone number with country code');
            $table->string('country_code', 5)->nullable()->after('phone')->comment('Valsts kods / Country code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropColumn(['phone', 'country_code']);
        });
    }
};
