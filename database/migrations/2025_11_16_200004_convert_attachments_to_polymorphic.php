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
    Schema::table('attachments', function (Blueprint $table) {
        // rename qa_item_id → attachable_id
        $table->renameColumn('qa_item_id', 'attachable_id');

        // add new polymorphic type column
        $table->string('attachable_type')->default('App\\Models\\QAItem')->after('attachable_id');
    });
}

public function down()
{
    Schema::table('attachments', function (Blueprint $table) {
        $table->renameColumn('attachable_id', 'qa_item_id');
        $table->dropColumn('attachable_type');
    });
}


    /**
     * Reverse the migrations.
     */
  
};
