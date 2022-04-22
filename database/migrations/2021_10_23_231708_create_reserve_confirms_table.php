<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReserveConfirmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserve_confirms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedBigInteger('reserve_id')->comment('予約ID');
            $table->unsignedBigInteger('reserve_itinerary_id')->comment("行程管理ID");
            $table->string('confirm_number')->nullable()->comment('予約確認書番号');
            $table->string('control_number')->nullable()->comment('予約番号');
            $table->date('issue_date')->nullable()->comment('発行日');
            $table->unsignedBigInteger('document_quote_id')->nullable()->comment("テンプレートID");
            $table->unsignedBigInteger('document_common_id')->nullable()->comment("共通設定ID");
            // ポリモーフィックリレーション
            $table->text('document_address')->nullable()->comment('宛名情報');
            $table->string('name')->nullable()->comment('旅行名');
            $table->date('departure_date')->nullable()->comment('出発日');
            $table->date('return_date')->nullable()->comment('帰着日');
            $table->string('manager', 64)->nullable()->comment('担当者');
            $table->text('representative')->nullable()->comment('代表者情報');
            $table->text('participant_ids')->nullable()->comment('チェック済み参加者ID');
            $table->text('document_setting')->nullable()->comment('書類設定');
            $table->text('document_common_setting')->nullable()->comment('共通設定');
            $table->text('option_prices')->nullable()->comment('オプション科目情報');
            $table->text('airticket_prices')->nullable()->comment('航空券科目情報');
            $table->text('hotel_prices')->nullable()->comment('ホテル科目情報');
            $table->text('hotel_info')->nullable()->comment('宿泊施設情報');
            $table->text('hotel_contacts')->nullable()->comment('宿泊施設連絡先情報');
            $table->integer('amount_total')->default(0)->comment('合計金額');
            $table->tinyInteger('status')->nullable()->comment('状態');
            $table->softDeletes();
            $table->timestamps();


            $table->unique(['reserve_itinerary_id', 'confirm_number']); // 行程管理IDとの組み合わせでユニーク

            $table->foreign('agency_id')
                ->references('id')
                ->on('agencies')
                ->onDelete('cascade');

            $table->foreign('reserve_id')
                ->references('id')
                ->on('reserves')
                ->onDelete('cascade');

            $table->foreign('reserve_itinerary_id')
                ->references('id')
                ->on('reserve_itineraries')
                ->onDelete('cascade');

            $table->foreign('document_quote_id')
                ->references('id')
                ->on('document_quotes')
                ->onDelete('set null');

            $table->foreign('document_common_id')
                ->references('id')
                ->on('document_commons')
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
        Schema::dropIfExists('reserve_confirms');
    }
}
