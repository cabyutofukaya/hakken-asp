<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameUnpaidBalanceToTotalAmountAccruedOnAccountPayableReservesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_payable_reserves', function (Blueprint $table) {
            $table->renameColumn('unpaid_balance', 'total_amount_accrued');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_payable_reserves', function (Blueprint $table) {
            $table->renameColumn('total_amount_accrued', 'unpaid_balance');
        });
    }
}
