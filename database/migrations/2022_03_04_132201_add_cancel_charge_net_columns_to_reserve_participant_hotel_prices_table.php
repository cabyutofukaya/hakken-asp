<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCancelChargeNetColumnsToReserveParticipantHotelPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reserve_participant_hotel_prices', function (Blueprint $table) {
            $table->integer('cancel_charge_net')->default(0)->after('cancel_charge')->comment('仕入先支払料金合計(キャンセル時)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reserve_participant_hotel_prices', function (Blueprint $table) {
            $table->dropColumn('cancel_charge_net');
        });
    }
}
