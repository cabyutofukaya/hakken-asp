<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_news', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->nullable()->comment('タイトル');
            $table->text('content')->comment('タイトル');
            $table->date('regist_date')->comment('日付');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_news');
    }
}
