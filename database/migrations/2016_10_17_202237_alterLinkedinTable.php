<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterLinkedinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('linkedin_parser_sessions', function (Blueprint $table) {
            $table->integer('total_results')->default(0
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('linkedin_parser_sessions', function (Blueprint $table) {
            $table->dropColumn('total_results');
        });
    }
}
