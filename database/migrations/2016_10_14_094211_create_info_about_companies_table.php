<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInfoAboutCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('info_about_companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('site', 255)->index();
            $table->string('google_plus', 255)->nullable()->index();
            $table->string('instagram', 255)->nullable()->index();
            $table->text('phones')->nullable();
            $table->string('twitter', 255)->nullable()->index();
            $table->string('youtube', 255)->nullable()->index();
            $table->string('linkedin', 255)->nullable()->index();
            $table->string('facebook', 255)->nullable()->index();
            $table->integer('count_request')->default(0)->index();
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
        Schema::dropIfExists('info_about_companies');
    }
}
