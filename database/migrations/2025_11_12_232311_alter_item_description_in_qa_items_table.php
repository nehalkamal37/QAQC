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
    Schema::table('qa_items', function (Blueprint $table) {
        $table->longText('item_description')->change();
    });
}

public function down()
{
    Schema::table('qa_items', function (Blueprint $table) {
        $table->string('item_description', 255)->change();
    });
}

    /**
     * Reverse the migrations.
     */
   
    
};
