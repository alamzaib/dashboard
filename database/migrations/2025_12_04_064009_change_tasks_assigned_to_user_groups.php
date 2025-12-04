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
        Schema::table('tasks', function (Blueprint $table) {
            // Drop the old foreign key constraint
            $table->dropForeign(['assigned_to']);
        });
        
        // Use DB facade to rename column (Laravel doesn't have renameColumn in Blueprint)
        \DB::statement('ALTER TABLE tasks CHANGE assigned_to assigned_to_user_group_id BIGINT UNSIGNED NULL');
        
        Schema::table('tasks', function (Blueprint $table) {
            // Add new foreign key constraint to user_groups table
            $table->foreign('assigned_to_user_group_id')
                  ->references('id')
                  ->on('user_groups')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Drop the new foreign key constraint
            $table->dropForeign(['assigned_to_user_group_id']);
        });
        
        // Rename back to assigned_to
        \DB::statement('ALTER TABLE tasks CHANGE assigned_to_user_group_id assigned_to BIGINT UNSIGNED NULL');
        
        Schema::table('tasks', function (Blueprint $table) {
            // Restore the old foreign key constraint
            $table->foreign('assigned_to')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }
};
