<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsAliveCancelColumnsToReserveParticipantAirplanePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reserve_participant_airplane_prices', function (Blueprint $table) {
            $table->boolean('is_alive_cancel')->default(false)->after('gross_profit')->comment('キャンセル設定'); // キャンセルページ経由でキャンセルした場合にtrueをセット。falseの場合はキャンセルページ経由ではなく行程編集ページで追加した仕入行の数合わせのためのキャンセルレコード
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reserve_participant_airplane_prices', function (Blueprint $table) {
            $table->dropColumn('is_alive_cancel');
        });
    }
}
