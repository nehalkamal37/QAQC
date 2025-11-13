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
    Schema::create('sheets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('phase_id')->constrained()->onDelete('cascade');
        $table->string('discipline')->nullable(); // مثلاً Architectural / Structural
        $table->string('number')->nullable();     // كود الشيت (E-01, L-02 ...)
        $table->string('title')->nullable();      // اسم الشيت
        $table->string('version')->nullable();    // الإصدار أو المراجعة
        $table->string('status')->default('Pending');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sheets');
    }
};
