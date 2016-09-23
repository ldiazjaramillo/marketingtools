<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogCallApisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_call_apis', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('import_id')->unsigned();
            $table->foreign('import_id')->references('id')->on('import_infos');

            $table->string('email', 255);
            $table->string('did_you_mean', 255)->nullable();
            $table->string('user', 255);
            $table->string('domain', 255);

            $table->boolean('format_valid')->nullable();
            $table->boolean('mx_found')->nullable();
            $table->boolean('smtp_check')->nullable();
            $table->boolean('catch_all')->nullable();
            $table->boolean('role')->nullable();
            $table->boolean('disposable')->nullable();
            $table->boolean('free')->nullable();
            $table->float('score')->nullable();

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
        Schema::dropIfExists('log_call_apis');
    }
}
