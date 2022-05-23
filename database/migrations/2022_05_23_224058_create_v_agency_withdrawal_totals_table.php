<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVAgencyWithdrawalTotalsTable extends Migration
{
    /**
     * Run the migrations.
     * account_payable_details毎の出金合計金額を集計したview。論理削除行は集計対象外
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP VIEW IF EXISTS v_agency_withdrawal_totals');
        DB::statement("
        CREATE VIEW v_agency_withdrawal_totals AS 
        SELECT
            UUID() AS id,
            account_payable_detail_id,
            sum(amount) AS total_amount
        FROM
            agency_withdrawals
        WHERE
            deleted_at is null
        GROUP BY
            account_payable_detail_id
        ");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS v_agency_withdrawal_totals');
    }
}
