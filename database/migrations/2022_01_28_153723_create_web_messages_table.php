<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->nullable()->comment('会社ID');
            $table->unsignedBigInteger('reserve_id')->comment('予約ID');
            // ポリモーフィックリレーション
            $table->string('senderable_type')->comment('送信者種別'); 
            $table->unsignedBigInteger('senderable_id')->comment('送信者ID');
            //
            $table->text('message')->nullable()->comment('メッセージ');
            $table->dateTime('send_at')->comment("送信日時");
            $table->dateTime('read_at')->nullable()->comment("既読日時");
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agency_id')
                ->references('id')
                ->on('agencies')
                ->onDelete('set null');

            $table->foreign('reserve_id')
                ->references('id')
                ->on('reserves')
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
        Schema::dropIfExists('web_messages');
    }
}
