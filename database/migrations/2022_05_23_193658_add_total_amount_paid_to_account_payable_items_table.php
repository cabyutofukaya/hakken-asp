<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalAmountPaidToAccountPayableItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_payable_items', function (Blueprint $table) {
            $table->integer("total_amount_paid")->default(0)->after('total_purchase_amount')->comment("総支払額");
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
            $table->dropColumn('total_amount_paid');
        });
    }
}
