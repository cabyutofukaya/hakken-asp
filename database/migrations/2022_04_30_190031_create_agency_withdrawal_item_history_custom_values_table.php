<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgencyWithdrawalItemHistoryCustomValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agency_withdrawal_item_history_custom_values', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('agency_withdrawal_item_history_id')->comment('(商品毎)出金管理ID');
            $table->unsignedBigInteger('user_custom_item_id')->comment("カスタム項目ID");
            $table->text('val')->nullable()->comment('値');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_withdrawal_item_history_id', 'user_custom_item_id'])->name("agency_withdrawal_item_history_id_user_custom_item_id_unique");

            $table->foreign('agency_withdrawal_item_history_id')->references('id')->on('agency_withdrawal_item_histories')->onDelete('cascade')->name("agency_withdrawal_item_history_id_foreign");

            $table->foreign('user_custom_item_id')->references('id')
            ->on('user_custom_items')->onDelete('cascade')->name("user_custom_item_id_foreign");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agency_withdrawal_item_history_custom_values');
    }
}
