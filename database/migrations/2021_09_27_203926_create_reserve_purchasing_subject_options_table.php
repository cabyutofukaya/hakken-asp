<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservePurchasingSubjectOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserve_purchasing_subject_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedBigInteger('reserve_schedule_id')->comment('スケジュールID');
            $table->string('code')->nullable()->comment('商品コード');
            $table->string('name')->nullable()->comment('名称');
            $table->string('name_ex')->nullable()->comment('名称拡張');
            $table->unsignedBigInteger('supplier_id')->nullable()->comment('仕入先ID');
            $table->integer('ad_gross_ex')->default(0)->comment('大人料金GROSS(税抜)');
            $table->string('ad_zei_kbn', 8)->nullable()->comment('大人料金税区分');
            $table->integer('ad_gross')->default(0)->comment('大人料金GROSS単価');
            $table->integer('ad_cost')->default(0)->comment('大人料金仕入れ値');
            $table->smallInteger('ad_commission_rate')->default(0)->comment('大人料金手数料率');
            $table->integer('ad_net')->default(0)->comment('大人料金NET単価');
            $table->integer('ad_gross_profit')->default(0)->comment('大人料金粗利');
            $table->integer('ch_gross_ex')->default(0)->comment('子供料金GROSS(税抜)');
            $table->string('ch_zei_kbn', 8)->nullable()->comment('子供料金税区分');
            $table->integer('ch_gross')->default(0)->comment('子供料金GROSS単価');
            $table->integer('ch_cost')->default(0)->comment('子供料金仕入れ値');
            $table->smallInteger('ch_commission_rate')->default(0)->comment('子供料金手数料率');
            $table->integer('ch_net')->default(0)->comment('子供料金NET単価');
            $table->integer('ch_gross_profit')->default(0)->comment('子供料金粗利');
            $table->integer('inf_gross_ex')->default(0)->comment('幼児料金GROSS(税抜)');
            $table->string('inf_zei_kbn', 8)->nullable()->comment('幼児料金税区分');
            $table->integer('inf_gross')->default(0)->comment('幼児料金GROSS単価');
            $table->integer('inf_cost')->default(0)->comment('幼児料金仕入れ値');
            $table->smallInteger('inf_commission_rate')->default(0)->comment('幼児料金手数料率');
            $table->integer('inf_net')->default(0)->comment('幼児料金NET単価');
            $table->integer('inf_gross_profit')->default(0)->comment('幼児料金粗利');
            $table->text('note')->nullable()->comment('備考');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('supplier_id')
            ->references('id')->on('suppliers')
            ->onDelete('set null');

            $table->foreign('agency_id')
            ->references('id')->on('agencies')
            ->onDelete('cascade');

            $table->foreign('reserve_schedule_id')
                ->references('id')->on('reserve_schedules')
                ->onDelete('cascade'); // プログラム上は直接使わないが（ポリモーフィクリレーションで連携）、親レコードの削除時に本レコードを消すのに設定

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reserve_purchasing_subject_options');
    }
}
