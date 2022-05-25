<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalOverpaymentColumnToAccountPayableItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_payable_items', function (Blueprint $table) {
            $table->integer('total_overpayment')->default(0)->after('total_amount_accrued')->comment('過払金合計');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_payable_items', function (Blueprint $table) {
            $table->dropColumn('total_overpayment');
        });
    }
}
