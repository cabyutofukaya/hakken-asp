<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgencyBundleDepositCustomValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agency_bundle_deposit_custom_values', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('agency_bundle_deposit_id')->comment('一括入金管理ID');
            $table->unsignedBigInteger('user_custom_item_id')->comment("カスタム項目ID");
            $table->text('val')->nullable()->comment('値');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_bundle_deposit_id', 'user_custom_item_id'])->name("agency_bundle_deposit_id_user_custom_item_id_unique");

            $table->foreign('agency_bundle_deposit_id')->references('id')->on('agency_bundle_deposits')->onDelete('cascade')->name('agency_bundle_deposit_id_foreign');
            
            $table->foreign('user_custom_item_id')->references('id')
            ->on('user_custom_items')->onDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agency_bundle_deposit_custom_values');
    }
}
