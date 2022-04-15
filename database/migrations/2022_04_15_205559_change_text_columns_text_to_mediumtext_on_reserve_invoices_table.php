<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTextColumnsTextToMediumtextOnReserveInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reserve_invoices', function (Blueprint $table) {
            $table->mediumText('option_prices')->change();
            $table->mediumText('airticket_prices')->change();
            $table->mediumText('hotel_prices')->change();
            $table->mediumText('hotel_info')->change();
            $table->mediumText('hotel_contacts')->change();
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
            $table->text('option_prices')->change();
            $table->text('airticket_prices')->change();
            $table->text('hotel_prices')->change();
            $table->text('hotel_info')->change();
            $table->text('hotel_contacts')->change();
        });
    }
}
