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
       // database/migrations/2024_01_01_create_checklist_items_table.php
Schema::create('checklist_items', function (Blueprint $table) {
    $table->id();
    $table->text('item_description');
    $table->boolean('is_checked')->default(false);
    $table->string('category')->nullable();
    $table->string('sub_category')->nullable();
    $table->string('project_name')->nullable();
    $table->string('project_number')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
    }
};
