<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountPayableItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_payable_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedBigInteger('reserve_id')->comment('予約ID');
            $table->integer('amount_billed')->default(0)->comment('請求金額(NET)');
            $table->integer('unpaid_balance')->default(0)->comment('未払金額');
            $table->unsignedBigInteger('last_manager_id')->nullable()->comment("担当者(最終更新値)");
            $table->text("last_note")->nullable()->comment("備考(最終更新値)");
            $table->tinyInteger('status')->default(config('consts.account_payable_reserves.STATUS_UNPAID'))->comment('ステータス'); // 支払いナシ,未払,支払済み
            $table->softDeletes();
            $table->timestamps();


            $table->foreign('agency_id')
                ->references('id')->on('agencies')
                ->onDelete('cascade');

            $table->foreign('reserve_id')
                ->references('id')->on('reserves')
                ->onDelete('cascade');

            $table->foreign('last_manager_id')
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
        Schema::dropIfExists('account_payable_items');
    }
}
