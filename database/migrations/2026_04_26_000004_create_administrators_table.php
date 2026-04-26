<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administrators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade')
                ->comment('FK to users (admin account)');
            $table->string('full_name', 100)->comment('Administratora vārds / Administrator name');
            $table->json('permissions')->nullable()
                ->comment('Piekļuves tiesības / Access permissions');
            $table->boolean('is_super_admin')->default(false)
                ->comment('Vai ir super admin / Is super admin');
            $table->timestamp('last_login_at')->nullable()
                ->comment('Pēdējā pieslēgšanās / Last login');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administrators');
    }
};
