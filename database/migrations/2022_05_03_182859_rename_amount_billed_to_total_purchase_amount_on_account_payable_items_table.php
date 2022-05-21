<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameAmountBilledToTotalPurchaseAmountOnAccountPayableItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_payable_items', function (Blueprint $table) {
            $table->renameColumn('amount_billed', 'total_purchase_amount');
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
            $table->renameColumn('total_purchase_amount', 'amount_billed');
        });
    }
}
