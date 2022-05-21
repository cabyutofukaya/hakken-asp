<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameWithdrawalKeyToBulkWithdrawalKeyOnAgencyWithdrawalItemHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agency_withdrawal_item_histories', function (Blueprint $table) {
            $table->renameColumn('withdrawal_key', 'bulk_withdrawal_key');
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
            $table->renameColumn('bulk_withdrawal_key', 'withdrawal_key');
        });
    }
}
