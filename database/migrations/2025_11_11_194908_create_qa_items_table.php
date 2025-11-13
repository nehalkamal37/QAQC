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
        Schema::create('qa_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sheet_id')->constrained()->onDelete('cascade');
            $table->string('item_description');
            $table->string('status')->default('open'); // open, pending, closed
            $table->text('comments')->nullable();
            $table->date('due_date')->nullable();
            $table->string('assigned_to')->nullable();
            $table->timestamps();
        });
    }

   

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qa_items');
    }
};
