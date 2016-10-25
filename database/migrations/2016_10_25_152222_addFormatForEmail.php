<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFormatForEmail extends Migration
{
    /**
     * Run the migrations_dot_
     *
     * @return void
     */
    public function up()
    {

        Schema::table('format_email_for_domains', function (Blueprint $table) {
            
            $table->integer('FirstInitial')->default(0);
            $table->index('FirstInitial');

            $table->integer('FirstInitial_dot_Lastname')->default(0);
            $table->index('FirstInitial_dot_Lastname');

            $table->integer('Firstname_Lastname2')->default(0);
            $table->index('Firstname_Lastname2');

            $table->integer('Firstname_dot_LastInitial')->default(0);
            $table->index('Firstname_dot_LastInitial');

            $table->integer('FirstnameLastInitial')->default(0);
            $table->index('FirstnameLastInitial');

            $table->integer('FirstnameLastname')->default(0);
            $table->index('FirstnameLastname');

            $table->integer('Lastname_Firstname')->default(0);
            $table->index('Lastname_Firstname');

            $table->integer('Lastname_dot_FirstInitial')->default(0);
            $table->index('Lastname_dot_FirstInitial');

            $table->integer('LastnameFirstInitial')->default(0);
            $table->index('LastnameFirstInitial');

        });

    }

    /**
     * Reverse the migrations_dot_
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('format_email_for_domains', function (Blueprint $table) {
            $table->dropColumn('FirstInitial');
            $table->dropColumn('FirstInitial_dot_Lastname');
            $table->dropColumn('Firstname_Lastname');
            $table->dropColumn('Firstname_dot_LastInitial');
            $table->dropColumn('FirstnameLastInitial');
            $table->dropColumn('FirstnameLastname');
            $table->dropColumn('Lastname_Firstname');
            $table->dropColumn('Lastname_dot_FirstInitial');
            $table->dropColumn('LastnameFirstInitial');
        });
    }
}
