<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('model_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('model')->comment('モデル名');
            $table->string('model_id',32)->comment('モデルのID');
            $table->string('guard',12)->nullable()->comment('操作したユーザーの種別');
            $table->unsignedBigInteger('user_id')->nullable()->comment('操作したユーザー');
            $table->string('operation_type',12)->comment('操作のタイプ'); // created, updated, deleted, retrieved
            $table->text('message')->nullable()->comment('操作内容');
            $table->timestamps();
            // $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('model_logs');
    }
}
