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
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable'); // polymorphic relation (user_id, user_type)
            $table->string('token', 128)->unique(); // hashed refresh token
            $table->timestamp('expires_at');
            $table->string('ip_address', 45)->nullable(); // IPv4 or IPv6
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('revoked_at')->nullable(); // for token rotation revocation
            $table->timestamps();
            
            $table->index(['tokenable_id', 'tokenable_type']);
            $table->index('expires_at'); // for cleanup of expired tokens
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};
