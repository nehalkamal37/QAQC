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
        Schema::table('project_qa_item_statuses', function (Blueprint $table) {

        $table->string('category')->nullable()->after('qa_item_id');
        $table->string('title')->nullable()->after('category');
    


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_qa_item_statuses', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->dropColumn('title');
        });
    }
};
