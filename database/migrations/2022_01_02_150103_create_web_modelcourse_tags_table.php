<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebModelcourseTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_modelcourse_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('web_modelcourse_id')->comment("モデルコースID");
            $table->string('tag')->comment('タグ');
            $table->timestamps();

            $table->foreign('web_modelcourse_id')
                ->references('id')
                ->on('web_modelcourses')
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
        Schema::dropIfExists('web_modelcourse_tags');
    }
}
