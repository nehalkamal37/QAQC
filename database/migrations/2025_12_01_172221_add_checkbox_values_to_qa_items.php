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
        $table->json('checkbox_values')->nullable()->after('status');
    });
}

public function down()
{
    Schema::table('qa_items', function (Blueprint $table) {
        $table->dropColumn('checkbox_values');
    });
}

   
};
