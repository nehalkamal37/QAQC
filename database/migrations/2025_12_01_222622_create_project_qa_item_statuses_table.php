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
    Schema::create('project_qa_item_statuses', function (Blueprint $table) {
        $table->id();

        // Project association
        $table->foreignId('project_id')->constrained()->onDelete('cascade');

        // QA Item association
        $table->foreignId('qa_item_id')->constrained()->onDelete('cascade');

        // Checkbox states
        $table->boolean('applicable')->default(false);
        $table->boolean('incorporated')->default(false);
        $table->boolean('confirmed')->default(false);

        // Project-specific fields
        $table->text('comments')->nullable();
        $table->date('due_date')->nullable();
        $table->string('assigned_to')->nullable();

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('project_qa_item_statuses');
}

};
