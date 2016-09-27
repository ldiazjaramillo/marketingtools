<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoogleCheckPhonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_check_phones', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('import_id')->unsigned();
            $table->foreign('import_id')->references('id')->on('import_infos');

            $table->text('site');
            $table->text('company_name')->nullable();
            $table->text('phone')->nullable();

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
        Schema::dropIfExists('google_check_phones');
    }
}
