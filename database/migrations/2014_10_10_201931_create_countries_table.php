<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->smallIncrements('id')->unsigned();
            $table->string('code', 2)->unique()->comment('国コード');
            $table->string('name', 64)->nullable()->comment('国名');
            $table->string('name_en', 64)->nullable()->comment('国名(英語)');
            $table->smallInteger('seq')->default(0)->comment('順番');
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
        Schema::dropIfExists('countries');
    }
}
