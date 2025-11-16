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
    Schema::create('attachments', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('qa_item_id');
        $table->unsignedBigInteger('user_id');
        $table->string('path');
        $table->string('filename');
        $table->string('mime');
        $table->timestamps();

        $table->foreign('qa_item_id')->references('id')->on('qa_items')->onDelete('cascade');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
