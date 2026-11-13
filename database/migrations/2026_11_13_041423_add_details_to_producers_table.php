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
        Schema::table('producers', function (Blueprint $table) {
            $table->string('farmers_name')->nullable()->after('description');
            $table->text('quote')->nullable()->after('farmers_name');
            $table->integer('varieties_count')->default(0)->after('quote');
            $table->decimal('hectares', 5, 1)->default(0)->after('varieties_count');
        });
    }

    public function down(): void
    {
        Schema::table('producers', function (Blueprint $table) {
            $table->dropColumn(['farmers_name', 'quote', 'varieties_count', 'hectares']);
        });
    }
};
