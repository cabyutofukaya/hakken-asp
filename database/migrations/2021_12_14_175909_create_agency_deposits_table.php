<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgencyDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agency_deposits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('identifier_id')->comment('入金識別ID'); // agency_bundle_depositsにも同じIDを登録することでお互いの入金情報が同じであることをチェックできるようにする
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedBigInteger('reserve_id')->comment('予約ID');
            $table->unsignedBigInteger('reserve_invoice_id')->comment('請求管理ID');
            $table->integer('amount')->default(0)->comment('出金額');
            $table->date('deposit_date')->nullable()->comment('入金日');
            $table->date('record_date')->nullable()->comment('登録日');
            $table->unsignedBigInteger('manager_id')->nullable()->comment('自社担当');
            $table->text('note')->nullable()->comment('備考');
            $table->softDeletes();
            $table->timestamps();


            $table->foreign('agency_id')
            ->references('id')->on('agencies')
            ->onDelete('cascade');

            $table->foreign('reserve_id')
                ->references('id')->on('reserves')
                ->onDelete('cascade');

            $table->foreign('reserve_invoice_id')
                ->references('id')->on('reserve_invoices')
                ->onDelete('cascade');

            $table->foreign('manager_id')
                ->references('id')
                ->on('staffs')
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
        Schema::dropIfExists('agency_deposits');
    }
}
