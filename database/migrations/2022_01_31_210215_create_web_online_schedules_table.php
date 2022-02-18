<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebOnlineSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_online_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->nullable()->comment('会社ID');
            $table->unsignedBigInteger('reserve_id')->comment('予約ID');
            $table->unsignedBigInteger('web_reserve_ext_id')->comment('WEB予約ID');
            $table->dateTime('consult_date')->comment("日時");
            // ポリモーフィックリレーション
            $table->string('requesterable_type')->comment('リクエスト者種別'); 
            $table->unsignedBigInteger('requesterable_id')->comment('リクエスト者ID');
            //
            $table->text('zoom_start_url')->nullable()->comment('zoom start URL'); // ホスト用URL
            $table->text('zoom_join_url')->nullable()->comment('zoom join URL'); // 参加者用URL
            $table->text('zoom_response')->nullable()->comment('zoomレスポンス'); // zoom作成時のレスポンス
            $table->unsignedInteger('zoom_api_key_id')->nullable()->comment('zoom APIキー情報');

            $table->tinyInteger('request_status')->default(1)->comment('リクエストステータス');
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

            $table->foreign('web_reserve_ext_id')
                ->references('id')
                ->on('web_reserve_exts')
                ->onDelete('cascade');

            $table->foreign('zoom_api_key_id')
                ->references('id')
                ->on('zoom_api_keys')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('web_online_schedules');
    }
}
