<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddItemColumnsToAccountPayableDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_payable_details', function (Blueprint $table) {
            $table->unsignedBigInteger("item_id")->nullable()->after('item_name')->comment("商品ID"); // account_payable_itemsとの連携に使用。外部キーとしては使っておらず商品をまとめる基準に使用
            $table->string("subject")->nullable()->after('item_code')->comment("科目"); // account_payable_itemsとの連携に使用。
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
            $table->dropColumn('item_id');
            $table->dropColumn('subject');
        });
    }
}
