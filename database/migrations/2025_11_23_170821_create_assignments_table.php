<?php
// database/migrations/2024_01_01_000005_create_assignments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('phase_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('role', ['pm', 'senior_reviewer', 'engineer', 'eit', 'night_vision']);
            $table->boolean('is_active')->default(true);
            $table->timestamp('assigned_at');
            $table->timestamp('removed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicate assignments
            $table->unique(['user_id', 'project_id', 'phase_id', 'role']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('assignments');
    }
};