<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTempIdToAgencyWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agency_withdrawals', function (Blueprint $table) {
            $table->string("temp_id")->nullable()->after('note')->comment("一時ID");
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
            $table->dropColumn('temp_id');
        });
    }
}
