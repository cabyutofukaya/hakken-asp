<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubjectAirplanesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subject_airplanes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedTinyInteger('subject_category_id')->comment('科目カテゴリID');
            $table->string('code')->comment('商品コード');
            $table->string('name')->nullable()->comment('商品名');
            $table->string('booking_class')->nullable()->comment('予約クラス');
            $table->unsignedBigInteger('departure_id')->nullable()->comment('出発地');
            $table->unsignedBigInteger('destination_id')->nullable()->comment('目的地');
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

            $table->unique(['agency_id', 'code']); // codeはIDとして使っているので論理削除含め絶対に重複不可

            $table->foreign('agency_id')
            ->references('id')->on('agencies')
            ->onDelete('cascade');

            $table->foreign('subject_category_id')
            ->references('id')->on('subject_categories')
            ->onDelete('cascade');

            $table->foreign('departure_id')
            ->references('id')->on('cities')
            ->onDelete('set null');

            $table->foreign('destination_id')
            ->references('id')->on('cities')
            ->onDelete('set null');

            $table->foreign('supplier_id')
            ->references('id')->on('suppliers')
            ->onDelete('set null');
        });

        DB::statement('ALTER TABLE subject_airplanes MODIFY code varchar(256) BINARY'); // 商品コードは、大文字小文字を区別させる。
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subject_airplanes');
    }
}
