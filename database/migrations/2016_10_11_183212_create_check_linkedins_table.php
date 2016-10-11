<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckLinkedinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_linkedins', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('import_id')->unsigned();
            $table->foreign('import_id')->references('id')->on('import_infos');

            $table->string('site', 255);
            $table->string('title', 255);
            $table->string('company_name', 255);
            $table->string('provider', 255);
            $table->string('full_name', 255);
            $table->string('link')->nullable();

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
        Schema::dropIfExists('check_linkedins');
    }
}
