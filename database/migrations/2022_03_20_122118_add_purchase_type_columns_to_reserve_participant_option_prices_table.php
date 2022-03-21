<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaseTypeColumnsToReserveParticipantOptionPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reserve_participant_option_prices', function (Blueprint $table) {
            $table->tinyInteger('purchase_type')->default(config('consts.const.PURCHASE_NORMAL'))->after('reserve_purchasing_subject_option_id')->comment('仕入種別');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reserve_participant_option_prices', function (Blueprint $table) {
            $table->dropColumn('purchase_type');
        });
    }
}
