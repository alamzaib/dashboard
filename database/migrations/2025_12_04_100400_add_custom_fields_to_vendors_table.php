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
        Schema::table('vendors', function (Blueprint $table) {
            // Add string fields
            for ($i = 1; $i <= 10; $i++) {
                $table->string("string_field{$i}")->nullable();
            }
            
            // Add text fields
            for ($i = 1; $i <= 10; $i++) {
                $table->text("text_field{$i}")->nullable();
            }
            
            // Add number fields
            for ($i = 1; $i <= 10; $i++) {
                $table->decimal("number_field{$i}", 15, 2)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            // Drop string fields
            for ($i = 1; $i <= 10; $i++) {
                $table->dropColumn("string_field{$i}");
            }
            
            // Drop text fields
            for ($i = 1; $i <= 10; $i++) {
                $table->dropColumn("text_field{$i}");
            }
            
            // Drop number fields
            for ($i = 1; $i <= 10; $i++) {
                $table->dropColumn("number_field{$i}");
            }
        });
    }
};

