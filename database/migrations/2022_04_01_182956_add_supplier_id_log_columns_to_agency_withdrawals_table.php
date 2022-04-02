<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupplierIdLogColumnsToAgencyWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agency_withdrawals', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id_log')->after('manager_id')->comment('仕入先ID(ログ扱い)'); // 出金時に指定した仕入先IDを記録。問題があった場合に調査するためのログ的な扱い
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
            $table->dropColumn('supplier_id_log');
        });
    }
}
