<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('message_id')->comment('メッセージID');
            $table->string('suggestion_id')->nullable()->comment('提案ID');
            $table->unsignedBigInteger('user_id')->nullable()->comment('ユーザーID');
            $table->unsignedBigInteger('staff_id')->nullable()->comment('スタッフID');
            $table->text('message')->nullable()->comment('メッセージ');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('message_id');

            // 親テーブルが消えてもチャットログが残るようにしたいのでonDeleteは「set null」
            $table->foreign('suggestion_id')->references('id')->on('suggestions')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('staff_id')->references('id')->on('staffs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chats');
    }
}
