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
        Schema::table('user_groups', function (Blueprint $table) {
            $table->string('header_color', 20)->nullable()->after('description');
            $table->string('logo_path')->nullable()->after('header_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_groups', function (Blueprint $table) {
            $table->dropColumn(['header_color', 'logo_path']);
        });
    }
};


