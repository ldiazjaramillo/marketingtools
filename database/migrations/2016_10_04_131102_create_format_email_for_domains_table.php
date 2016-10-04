<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormatEmailForDomainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('format_email_for_domains', function (Blueprint $table) {
            $table->increments('id');

            $table->string('domain', 255);
            $table->unique('domain', 'domain_index');

            $table->string('provider', 255);

            $table->integer('firstInitial_lastName')->default(0);
            $table->index('firstInitial_lastName');

            $table->integer('firstInitial_lastInitial')->default(0);
            $table->index('firstInitial_lastInitial');

            $table->integer('firstName')->default(0);
            $table->index('firstName');

            $table->integer('lastName')->default(0);
            $table->index('lastName');

            $table->integer('lastName_firstInitial')->default(0);
            $table->index('lastName_firstInitial');

            $table->integer('firstName_lastInitial')->default(0);
            $table->index('firstName_lastInitial');

            $table->integer('firstName_dot_lastName')->default(0);
            $table->index('firstName_dot_lastName');

            $table->integer('firstName-lastName')->default(0);
            $table->index('firstName-lastName');

            $table->string('note', 255)->nullable();

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
        Schema::dropIfExists('format_email_for_domains');
    }
}
