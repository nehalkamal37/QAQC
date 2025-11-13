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
        $table->string('severity')->default('medium'); // low / medium / high / critical
    });
}

public function down()
{
    Schema::table('qa_items', function (Blueprint $table) {
        $table->dropColumn('severity');
    });
}

};
