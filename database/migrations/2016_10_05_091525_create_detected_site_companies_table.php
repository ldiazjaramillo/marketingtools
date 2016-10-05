<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetectedSiteCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detected_site_companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('company_name', 255);
            $table->unique('company_name', 'company_name_index');

            $table->string('site', 255)->nullable();
            $table->index('site');

            $table->integer('import_id')->unsigned();
            $table->foreign('import_id')->references('id')->on('import_infos');

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
        Schema::dropIfExists('detected_site_companies');
    }
}
