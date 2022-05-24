<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAgencyWithdrawalIdToAgencyWithdrawalItemHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agency_withdrawal_item_histories', function (Blueprint $table) {
            $table->unsignedBigInteger("agency_withdrawal_id")->nullable()->after('account_payable_item_id')->comment("個別出金ID");

            $table->foreign('agency_withdrawal_id')
                ->references('id')
                ->on('agency_withdrawals')
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
        Schema::table('agency_withdrawal_item_histories', function (Blueprint $table) {
            // 外部キー制約の削除
            $table->dropForeign('agency_withdrawal_item_histories_agency_withdrawal_id_foreign');
            $table->dropColumn('agency_withdrawal_id');
        });
    }
}
