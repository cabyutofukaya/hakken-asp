<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUniqueReserveIdColumnOnAccountPayableReserves extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_payable_reserves', function (Blueprint $table) {
            $table->unique(['reserve_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_payable_reserves', function (Blueprint $table) {
            $table->dropUnique(['reserve_id']);
        });
    }
}
