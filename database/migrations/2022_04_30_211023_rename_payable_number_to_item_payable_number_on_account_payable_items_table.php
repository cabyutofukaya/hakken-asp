<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenamePayableNumberToItemPayableNumberOnAccountPayableItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_payable_items', function (Blueprint $table) {
            $table->renameColumn('payable_number', 'item_payable_number');
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
            $table->renameColumn('item_payable_number', 'payable_number');
        });
    }
}
