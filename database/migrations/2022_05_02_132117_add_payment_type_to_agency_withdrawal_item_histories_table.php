<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentTypeToAgencyWithdrawalItemHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agency_withdrawal_item_histories', function (Blueprint $table) {
            $table->tinyInteger("payment_type")->default(config('consts.agency_withdrawal_item_histories.PAYMENT_TYPE_BULK'))->after('reserve_id')->comment("出金種別"); // 一括出金、個別出金
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agency_withdrawal_item_histories', function (Blueprint $table) {
            $table->dropColumn('payment_type');
        });
    }
}
