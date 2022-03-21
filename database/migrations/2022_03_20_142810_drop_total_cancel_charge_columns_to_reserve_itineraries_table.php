<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropTotalCancelChargeColumnsToReserveItinerariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reserve_itineraries', function (Blueprint $table) {
            $table->dropColumn('total_cancel_charge');
            $table->dropColumn('total_cancel_charge_net');
            $table->dropColumn('total_cancel_charge_profit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reserve_itineraries', function (Blueprint $table) {
            $table->integer('total_cancel_charge')->default(0)->after('total_gross_profit')->comment('キャンセルチャージ合計');
            $table->integer('total_cancel_charge_net')->default(0)->after('total_cancel_charge')->comment('キャンセルチャージNET合計');
            $table->integer('total_cancel_charge_profit')->default(0)->after('total_cancel_charge_net')->comment('キャンセルチャージ粗利合計');
        });
    }
}
