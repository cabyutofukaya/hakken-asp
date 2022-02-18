<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierAccountPayablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_account_payables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedBigInteger('supplier_id')->comment('仕入先ID');
            $table->string('kinyu_code', 4)->nullable()->comment('金融機関コード');
            $table->string('tenpo_code', 3)->nullable()->comment('店舗コード');
            $table->string('kinyu_name')->nullable()->comment('金融機関名');
            $table->string('tenpo_name')->nullable()->comment('店舗名');
            $table->tinyInteger('account_type')->nullable()->comment('口座種別'); // 1:普通、2:当座
            $table->string('account_number', 32)->nullable()->comment('口座番号');
            $table->string('account_name')->nullable()->comment('口座名義');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agency_id')
            ->references('id')->on('agencies')
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
        Schema::dropIfExists('supplier_account_payables');
    }
}
