<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebProfileTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_profile_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('web_profile_id')->comment("プロフィールID");
            $table->string('tag')->comment('タグ');
            $table->timestamps();

            $table->foreign('web_profile_id')
                ->references('id')
                ->on('web_profiles')
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
        Schema::dropIfExists('web_profile_tags');
    }
}
