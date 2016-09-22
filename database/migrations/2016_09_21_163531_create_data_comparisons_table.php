<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataComparisonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_comparisons', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('import_id')->unsigned();
            $table->foreign('import_id')->references('id')->on('import_infos');

            $table->string('name', 255);
            $table->string('site', 255);

            $table->text('row_data');

            $table->string('email', 255);
            $table->float('score');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_comparisons');
    }
}
