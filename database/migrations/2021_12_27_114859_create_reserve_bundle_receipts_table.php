<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReserveBundleReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserve_bundle_receipts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedBigInteger('reserve_bundle_invoice_id')->comment('一括請求ID');
            $table->string('receipt_number')->nullable()->comment('システム発行の領収番号');
            $table->string('user_receipt_number')->nullable()->comment('領収番号(ユーザー入力)');
            $table->date('issue_date')->nullable()->comment('発行日');
            $table->unsignedBigInteger('document_receipt_id')->nullable()->comment("テンプレートID");
            $table->unsignedBigInteger('document_common_id')->nullable()->comment("共通設定ID");
            $table->text('document_address')->nullable()->comment('宛名情報');
            $table->text('document_setting')->nullable()->comment('項目表示・非表示設定');
            $table->text('document_common_setting')->nullable()->comment('共通設定');
            $table->integer('receipt_amount')->default(0)->comment("領収金額");
            $table->tinyInteger('status')->nullable()->comment('状態');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('agency_id')
                ->references('id')
                ->on('agencies')
                ->onDelete('cascade');

            $table->foreign('reserve_bundle_invoice_id')
                ->references('id')
                ->on('reserve_bundle_invoices')
                ->onDelete('cascade');

            $table->foreign('document_receipt_id')
                ->references('id')
                ->on('document_receipts')
                ->onDelete('set null');

            $table->foreign('document_common_id')
                ->references('id')
                ->on('document_commons')
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
        Schema::dropIfExists('reserve_bundle_receipts');
    }
}
