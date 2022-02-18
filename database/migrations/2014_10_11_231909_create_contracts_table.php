<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractsTable extends Migration
{
    /**
     * 契約情報
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedInteger('parent_id')->default(0)->comment('親契約ID');
            $table->datetime('start_at')->nullable()->comment('契約開始日時');
            $table->datetime('end_at')->nullable()->comment('契約終了日時');
            $table->unsignedTinyInteger('contract_plan_id')->nullable()->comment('プランID');
            $table->date('cancellation_at')->nullable()->comment('解約日');
            $table->boolean('renewal')->default(false)->comment('契約更新フラグ');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agency_id')
            ->references('id')->on('agencies')
            ->onDelete('cascade');

            $table->foreign('contract_plan_id')
            ->references('id')->on('contract_plans')
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
        Schema::dropIfExists('contracts');
    }
}
