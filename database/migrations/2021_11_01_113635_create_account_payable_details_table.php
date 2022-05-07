<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountPayableDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_payable_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedBigInteger('reserve_id')->comment('予約ID');
            $table->unsignedBigInteger('reserve_travel_date_id')->comment('旅行日ID');
            $table->unsignedBigInteger('account_payable_id')->comment('買い掛け金ID');
            $table->unsignedBigInteger('supplier_id')->nullable()->comment('仕入先');
            $table->string("supplier_name")->nullable()->comment("仕入先名");
            $table->string("item_name")->nullable()->comment("商品名");
            $table->string("item_code")->nullable()->comment("商品コード");
            $table->date("use_date")->nullable()->comment("利用日");
            $table->date("payment_date")->nullable()->comment("支払日");
            $table->string("saleable_type")->comment("料金テーブル");
            $table->string("saleable_id")->comment("料金テーブルID");
            $table->integer('amount_billed')->default(0)->comment('請求金額(NET)');
            $table->integer('amount_payment')->default(0)->comment('仕入金額'); // 使ってない？
            $table->integer('unpaid_balance')->default(0)->comment('未払金額');
            $table->boolean('official')->default(false)->comment('正式版フラグ');
            $table->unsignedBigInteger('last_manager_id')->nullable()->comment("担当者(最終更新値)");
            $table->text("last_note")->nullable()->comment("備考(最終更新値)");
            $table->tinyInteger('status')->default(config('consts.account_payable_details.STATUS_UNPAID'))->comment('ステータス'); // 支払いナシ,未払,支払済み
            $table->softDeletes();
            $table->timestamps();
            

            $table->foreign('agency_id')
                ->references('id')->on('agencies')
                ->onDelete('cascade');

            $table->foreign('reserve_id')
                ->references('id')->on('reserves')
                ->onDelete('cascade');

            $table->foreign('reserve_travel_date_id')
                ->references('id')->on('reserve_travel_dates')
                ->onDelete('cascade');

            $table->foreign('account_payable_id')
                ->references('id')->on('account_payables')
                ->onDelete('cascade');

            $table->foreign('supplier_id')
                ->references('id')->on('suppliers')
                ->onDelete('set null');

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
        Schema::dropIfExists('account_payable_details');
    }
}
