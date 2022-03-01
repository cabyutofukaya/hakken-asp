<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCancelChargeColumnsToReserveParticipantAirplanePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reserve_participant_airplane_prices', function (Blueprint $table) {
            $table->unsignedBigInteger('reserve_id')->nullable()->after('id')->comment('旅行ID');
            $table->boolean('is_cancel')->default(false)->after('gross_profit')->comment('キャンセルフラグ');
            $table->integer('cancel_charge')->default(0)->after('is_cancel')->comment('キャンセルチャージ');

            $table->foreign('reserve_id')
                ->references('id')->on('reserves')->onDelete('set null');

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
            $table->dropForeign('reserve_participant_airplane_prices_reserve_id_foreign');
            $table->dropColumn('reserve_id');
            $table->dropColumn('is_cancel');
            $table->dropColumn('cancel_charge');

        });
    }
}
