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
        Schema::create('qa_daily_stats', function (Blueprint $table) {
    $table->id();
    $table->date('date');
    $table->unsignedBigInteger('project_id')->nullable();
    $table->integer('open_count')->default(0);
    $table->integer('in_progress_count')->default(0);
    $table->integer('resolved_count')->default(0);
    $table->integer('verified_count')->default(0);
    $table->integer('closed_count')->default(0);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
