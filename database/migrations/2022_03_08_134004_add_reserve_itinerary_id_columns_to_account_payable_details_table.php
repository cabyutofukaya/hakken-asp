<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReserveItineraryIdColumnsToAccountPayableDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_payable_details', function (Blueprint $table) {
            $table->unsignedBigInteger('reserve_itinerary_id')->after('reserve_id')->comment('行程ID'); // 行程ごとに集計できるのが便利なので設置

            $table->foreign('reserve_itinerary_id')
                ->references('id')->on('reserve_itineraries')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_payable_details', function (Blueprint $table) {
            $table->dropForeign('account_payable_details_reserve_itinerary_id_foreign');
            $table->dropColumn('reserve_itinerary_id');
        });
    }
}
