<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->decimal('distance_km', 5, 1)->default(0);
            $table->boolean('bio_certified')->default(false);
            $table->string('avatar')->nullable();
            $table->string('cover_image')->nullable();
            $table->text('description')->nullable();
            $table->decimal('fair_share_percent', 5, 1)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producers');
    }
};
