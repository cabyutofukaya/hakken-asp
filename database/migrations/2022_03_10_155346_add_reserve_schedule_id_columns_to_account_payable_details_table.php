<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReserveScheduleIdColumnsToAccountPayableDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_payable_details', function (Blueprint $table) {
            $table->unsignedBigInteger('reserve_schedule_id')->after('reserve_id')->comment('スケジュールID'); // 当該仕入が当該スケジュールにおいて新規か更新かを判定するのに必要なため追加

            $table->foreign('reserve_schedule_id')
                ->references('id')->on('reserve_schedules')
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
            //
        });
    }
}
