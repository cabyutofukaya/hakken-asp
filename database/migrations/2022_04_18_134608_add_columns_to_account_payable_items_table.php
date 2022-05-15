<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToAccountPayableItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_payable_items', function (Blueprint $table) {
            $table->string("payable_number")->after('id')->comment("買い掛け金番号");
            $table->unsignedBigInteger('reserve_itinerary_id')->after('payable_number')->comment('行程ID');
            $table->unsignedBigInteger('supplier_id')->nullable()->after('reserve_itinerary_id')->comment('仕入先');
            $table->string("supplier_name")->nullable()->after('supplier_id')->comment("仕入先名");
            $table->unsignedBigInteger("item_id")->after('supplier_name')->comment("商品ID"); // account_payable_detailsとの連携に使用。外部キーとしては使っておらず商品をまとめる基準に使用
            $table->string("item_code")->nullable()->after('item_id')->comment("商品コード");
            $table->string("item_name")->nullable()->after('item_code')->comment("商品名");
            $table->string("subject")->after('item_name')->comment("科目"); // account_payable_itemsとの連携に使用。
            $table->date("payment_date")->nullable()->after('unpaid_balance')->comment("支払日");

            $table->unique('payable_number');

            $table->foreign('reserve_itinerary_id')
                ->references('id')->on('reserve_itineraries')
                ->onDelete('cascade');

            $table->foreign('supplier_id')
                ->references('id')->on('suppliers')
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
        Schema::table('account_payable_items', function (Blueprint $table) {
            $table->dropUnique('account_payable_items_payable_number_unique');
            $table->dropForeign('account_payable_items_reserve_itinerary_id_foreign');
            $table->dropForeign('account_payable_items_supplier_id_foreign');
            $table->dropColumn('reserve_itinerary_id');
            $table->dropColumn('payable_number');
            $table->dropColumn('supplier_id');
            $table->dropColumn('supplier_name');
            $table->dropColumn('item_code');
            $table->dropColumn('item_name');
        });
    }
}
