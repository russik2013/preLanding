<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFlagForWhiteSite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('side_bar_groops', function (Blueprint $table) {
            $table->boolean('white_site_flag')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('side_bar_groops', function (Blueprint $table) {
            $table->boolean('white_site_flag')->default(true);
        });
    }
}
