<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgencyWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agency_withdrawals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedBigInteger('reserve_id')->comment('予約ID');
            $table->unsignedBigInteger('reserve_travel_date_id')->comment('旅行日ID');
            $table->unsignedBigInteger('account_payable_detail_id')->comment('買い掛け金明細ID');
            $table->integer('amount')->default(0)->comment('出金額');
            $table->date('withdrawal_date')->nullable()->comment('出金日');
            $table->date('record_date')->nullable()->comment('登録日');
            $table->unsignedBigInteger('manager_id')->nullable()->comment('自社担当');
            $table->text('note')->nullable()->comment('備考');
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

            $table->foreign('account_payable_detail_id')
                ->references('id')->on('account_payable_details')
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
        Schema::dropIfExists('agency_withdrawals');
    }
}
