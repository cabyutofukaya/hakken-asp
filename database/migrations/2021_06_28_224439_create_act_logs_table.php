<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('act_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->unsigned()->nullable()->comment('ユーザーID');
            $table->string('guard',12)->nullable()->comment('操作Role');
            $table->string('route')->nullable()->comment('ルート名称');
            $table->string('url')->nullable()->comment('要求Path');
            $table->string('method')->nullable()->comment('要求メソッド');
            $table->integer('status')->unsigned()->nullable();
            $table->text('message')->nullable();
            $table->string('remote_addr')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            // $table->index(['created_at']);
            // $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('act_logs');
    }
}
