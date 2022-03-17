<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParticipantIdColumnsToAgencyWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agency_withdrawals', function (Blueprint $table) {
            $table->unsignedBigInteger('participant_id')->nullable()->after('record_date')->comment('参加者ID'); // 利用者ID（参加者）

            $table->foreign('participant_id')
                ->references('id')->on('participants')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agency_withdrawals', function (Blueprint $table) {
            //
        });
    }
}
