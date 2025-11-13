<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up()
{
    Schema::create('qa_item_reviews', function (Blueprint $table) {
        $table->id();
        $table->foreignId('qa_item_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('role')->nullable(); // Reviewer / PM / Engineer
//        $table->string('status', 50);
$table->enum('status', [
    'noted',
    'open',
    'in_progress',
    'resolved',
    'verified',
    'closed'
])->default('open');

    //   $table->enum('status', ['open', 'in_progress', 'resolved', 'verified', 'closed'])->default('open');
        $table->text('comment')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qa_item_reviews');
    }
};
