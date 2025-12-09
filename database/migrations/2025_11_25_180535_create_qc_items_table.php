<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('qc_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_text');          // The checklist description
            $table->boolean('applicable')->default(false);
            $table->boolean('incorporated')->default(false);
            $table->boolean('confirmed')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('qc_items');
    }
};
