<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataComparationInCheckEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('google_check_emails', function (Blueprint $table) {
            $table->integer('data_comparasion_id')->unsigned();
            $table->foreign('data_comparasion_id')->references('id')->on('data_comparisons');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('google_check_emails', function (Blueprint $table) {
            $table->dropColumn('data_comparasion_id');
        });
    }
}
