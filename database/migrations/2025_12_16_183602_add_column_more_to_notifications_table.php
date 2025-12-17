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
        
        Schema::table('notifications', function (Blueprint $table) {
    $table->foreignId('actor_id')->nullable()->constrained('users');
    $table->string('subject_type')->nullable(); // QAItem, Project, Phase
    $table->unsignedBigInteger('subject_id')->nullable();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
    $table->dropForeign(['actor_id']);
    $table->dropColumn(['actor_id', 'subject_type', 'subject_id']);
        });
    }
};
