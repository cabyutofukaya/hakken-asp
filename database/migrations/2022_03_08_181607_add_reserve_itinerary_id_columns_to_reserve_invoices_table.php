<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReserveItineraryIdColumnsToReserveInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reserve_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('reserve_itinerary_id')->after('reserve_id')->comment('行程ID'); // 現状は特に使わないが、行程ごとに請求書を管理する時に必要なので設置しておく

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
        Schema::table('reserve_invoices', function (Blueprint $table) {
            $table->dropForeign('reserve_invoices_reserve_itinerary_id_foreign');
            $table->dropColumn('reserve_itinerary_id');
        });
    }
}
