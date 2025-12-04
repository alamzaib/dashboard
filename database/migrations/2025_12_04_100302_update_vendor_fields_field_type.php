<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the enum constraint and change to mediumText
        DB::statement('ALTER TABLE vendor_fields MODIFY field_type MEDIUMTEXT NULL');
        DB::statement('ALTER TABLE prospect_fields MODIFY field_type MEDIUMTEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to enum (if needed)
        DB::statement("ALTER TABLE vendor_fields MODIFY field_type ENUM('text', 'email', 'phone', 'textarea', 'date', 'number', 'select', 'checkbox', 'radio') DEFAULT 'text'");
        DB::statement("ALTER TABLE prospect_fields MODIFY field_type ENUM('text', 'email', 'phone', 'textarea', 'date', 'number', 'select', 'checkbox', 'radio') DEFAULT 'text'");
    }
};

