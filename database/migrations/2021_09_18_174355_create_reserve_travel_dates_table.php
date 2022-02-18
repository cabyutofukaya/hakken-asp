<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReserveTravelDatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserve_travel_dates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reserve_id')->comment('旅行ID');
            $table->unsignedBigInteger('reserve_itinerary_id')->comment('行程ID');
            $table->unsignedInteger('agency_id')->comment('旅行会社');
            $table->date('travel_date')->comment('旅行日');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agency_id')
                ->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('reserve_id')
                ->references('id')->on('reserves')->onDelete('cascade');
            $table->foreign('reserve_itinerary_id')
                ->references('id')->on('reserve_itineraries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reserve_travel_dates');
    }
}
