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
        Schema::create('workorder_fields', function (Blueprint $table) {
            $table->id();
            $table->string('field_name');
            $table->string('field_label');
            $table->mediumText('field_type')->nullable(); // Store actual database column type
            $table->text('field_options')->nullable(); // JSON for select/radio options
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(false);
            $table->integer('display_order')->default(0);
            $table->text('validation_rules')->nullable(); // JSON for custom validation
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workorder_fields');
    }
};

