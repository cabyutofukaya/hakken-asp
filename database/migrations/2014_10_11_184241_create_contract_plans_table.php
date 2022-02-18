<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractPlansTable extends Migration
{
    /**
     * プラン情報テーブル（旅行会社用）
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_plans', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name')->comment('プラン名');
            $table->unsignedMediumInteger('monthly_sum')->default(0)->comment('月額');
            $table->unsignedTinyInteger('period')->default(config('consts.const.AGENCY_CONTRACT_PERIOD_DEFAULT'))->comment('契約期間');
            $table->tinyInteger('number_staff')->default(0)->comment('スタッフ登録許可数');
            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contract_plans');
    }
}
