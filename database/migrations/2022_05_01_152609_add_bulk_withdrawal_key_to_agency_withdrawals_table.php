<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBulkWithdrawalKeyToAgencyWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agency_withdrawals', function (Blueprint $table) {
            $table->string("bulk_withdrawal_key")->nullable()->after('note')->comment("一括出金識別キー"); // 一括出金時の親レコードを参照するためのキー
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agency_withdrawals', function (Blueprint $table) {
            $table->dropColumn('bulk_withdrawal_key');
        });
    }
}
