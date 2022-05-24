<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeBulkWithdrawalKeyToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agency_withdrawal_item_histories', function (Blueprint $table) {
            $table->text('bulk_withdrawal_key')->nullable()->change();
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
            //
        });
    }
}
