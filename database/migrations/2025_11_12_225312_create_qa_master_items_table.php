<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('qa_master_items', function (Blueprint $table) {
            $table->id();
            $table->string('discipline')->default('Electrical'); // لحد ما نضيف ميكانيكال وغيره
            $table->string('type')->nullable();      // من الكولمن Type في الإكسل
            $table->string('category')->nullable();  // من الكولمن Category
            $table->text('item')->nullable();        // نص البند نفسه
            $table->text('notes')->nullable();       // أي ملاحظات إضافية
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qa_master_items');
    }
};
