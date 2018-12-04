<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSideBarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('side_bars', function (Blueprint $table) {
            $table->increments('id');
            $table->text('text');
            $table->string('url');
            $table->string('photo');
            $table->unsignedInteger('side_bar_groop_id');
            $table->foreign('side_bar_groop_id')
                ->references('id')
                ->on('side_bar_groops')
                ->onDelete('cascade');
            $table->string('profit');
            $table->string('people');
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
        Schema::dropIfExists('side_bars');
    }
}
