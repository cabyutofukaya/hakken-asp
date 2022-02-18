<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebMessageHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_message_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->nullable()->comment('会社ID');
            $table->unsignedBigInteger('reserve_id')->comment('予約ID');
            $table->mediumText('message_log')->nullable()->comment('メッセージログ');
            $table->dateTime('last_received_at')->nullable()->comment("最終受信日");
            $table->string('reserve_status')->nullable()->comment('予約ステータス');
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
        Schema::dropIfExists('web_message_histories');
    }
}
