<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReserveCancelInfoToReserveBundleInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reserve_bundle_invoices', function (Blueprint $table) {
            $table->text('reserve_cancel_info')->nullable()->after('reserve_prices')->comment('キャンセル予約情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reserve_bundle_invoices', function (Blueprint $table) {
            $table->dropColumn('reserve_cancel_info');
        });
    }
}
