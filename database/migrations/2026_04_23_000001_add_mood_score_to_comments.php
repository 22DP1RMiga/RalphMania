<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Noskaņojuma vērtējums 0–100 (null = nav novērtēts)
            $table->unsignedTinyInteger('mood_score')
                ->nullable()
                ->default(null)
                ->after('is_approved')
                ->comment('Emoji slider score 0-100, null = not rated');
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('mood_score');
        });
    }
};
