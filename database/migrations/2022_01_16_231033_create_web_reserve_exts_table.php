<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebReserveExtsTable extends Migration
{
    /**
     * Run the migrations.
     * 
     * reservesテーブルにHAKKEN予約情報を追加するための拡張テーブル
     * 
     * @return void
     */
    public function up()
    {
        Schema::create('web_reserve_exts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedBigInteger('reserve_id')->comment("予約ID");
            $table->unsignedBigInteger('web_consult_id')->comment('Web相談ID'); // Web相談情報と紐づけるためのカラム
            $table->unsignedBigInteger('manager_id')->nullable()->comment("マイスターID");
            $table->uuid('room_key')->unique()->comment('Roomキー'); // マイスターとユーザーのオンラインチャット等で使用するルーム番号(UUID v4)
            $table->unsignedSmallInteger('agency_unread_count')->default(0)->comment('相談未読件数(会社側)');
            $table->unsignedSmallInteger('user_unread_count')->default(0)->comment('相談未読件数(ユーザー側)');
            $table->tinyInteger('estimate_status')->defautl(1)->comment('見積ステータス');
            $table->dateTime('consent_at')->nullable()->comment("承諾日時");
            $table->dateTime('rejection_at')->nullable()->comment("拒否日時");
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agency_id')
                ->references('id')
                ->on('agencies')
                ->onDelete('cascade');

            $table->foreign('reserve_id')
                ->references('id')
                ->on('reserves')
                ->onDelete('cascade');

            $table->foreign('web_consult_id')
                ->references('id')
                ->on('web_consults')
                ->onDelete('cascade');

            $table->foreign('manager_id')
                ->references('id')
                ->on('staffs')
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
        Schema::dropIfExists('web_reserves');
    }
}
