<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('phase_id')->nullable();
            $table->unsignedBigInteger('sheet_id')->nullable();
            $table->unsignedBigInteger('qa_item_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            // Event type: status_change / review_added / attachment_uploaded / item_imported
            $table->string('action_type');

            // Old & new values as JSON
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();

            // Optional note (review comment, status comment…etc)
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
