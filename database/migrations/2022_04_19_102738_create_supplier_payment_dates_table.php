<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierPaymentDatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_payment_dates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reserve_id')->comment('予約ID');
            $table->unsignedBigInteger('supplier_id')->comment('仕入先');
            $table->date("payment_date")->nullable()->comment("支払日");
            $table->timestamps();

            $table->unique(['reserve_id', 'supplier_id']); // 予約IDと仕入先IDの組み合わせでユニーク

            $table->foreign('reserve_id')
                ->references('id')->on('reserves')
                ->onDelete('cascade');

            $table->foreign('supplier_id')
                ->references('id')->on('suppliers')
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
        Schema::dropIfExists('supplier_payment_dates');
    }
}
