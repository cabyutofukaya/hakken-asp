<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCancelChargeCancelChargeProfitColumnsToReserveParticipantAirplanePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reserve_participant_airplane_prices', function (Blueprint $table) {
            $table->integer('cancel_charge_profit')->default(0)->after('cancel_charge_net')->comment('キャンセルチャージ粗利');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reserve_participant_airplane_prices', function (Blueprint $table) {
            $table->dropColumn('cancel_charge_profit');
        });
    }
}
