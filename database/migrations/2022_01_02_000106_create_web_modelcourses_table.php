<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebModelcoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_modelcourses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedBigInteger('author_id')->nullable()->comment('スタッフID');
            $table->string('course_no', 6)->comment("コースNo");
            $table->string('name', 64)->nullable()->comment("コース名");
            $table->text('description')->nullable()->comment('説明文');
            $table->unsignedTinyInteger('stays')->default(0)->comment("日数");
            $table->string('price_per_ad', 64)->nullable()->comment("大人料金");
            $table->string('price_per_ch', 64)->nullable()->comment("子供料金");
            $table->string('price_per_inf', 64)->nullable()->comment("幼児料金");
            $table->uuid('departure_id')->nullable()->comment('国・地域（出発地）');
            $table->string('departure_place')->nullable()->comment('住所・名称（出発地）');
            $table->uuid('destination_id')->nullable()->comment('国・地域（目的地）');
            $table->string('destination_place')->nullable()->comment('住所・名称（目的地）');
            $table->boolean('show')->default(true)->comment('表示フラグ');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['course_no', 'agency_id']);


            $table->foreign('agency_id')
                ->references('id')
                ->on('agencies')
                ->onDelete('cascade');

            $table->foreign('author_id')
                ->references('id')
                ->on('staffs')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('web_modelcourses');
    }
}
