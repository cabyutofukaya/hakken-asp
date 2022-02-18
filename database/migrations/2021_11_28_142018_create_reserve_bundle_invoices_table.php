<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReserveBundleInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserve_bundle_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedBigInteger('business_user_id')->nullable()->comment('法人顧客ID');
            $table->string('bundle_invoice_number')->comment('システム発行の請求番号');
            $table->string('user_bundle_invoice_number')->nullable()->comment('請求番号(ユーザー入力)');
            $table->date('issue_date')->nullable()->comment('発行日');
            $table->date('payment_deadline')->nullable()->comment('支払い期限');
            $table->date('period_from')->nullable()->comment('期間(開始)');
            $table->date('period_to')->nullable()->comment('期間(終了)');
            $table->unsignedBigInteger('document_request_all_id')->nullable()->comment("テンプレートID");
            $table->unsignedBigInteger('document_common_id')->nullable()->comment("共通設定ID");
            $table->string('billing_address_name')->nullable()->comment('請求先名');
            $table->text('document_address')->nullable()->comment('宛名情報');
            $table->string('name')->nullable()->comment('案件名');
            $table->date('cutoff_date')->nullable()->comment('請求締日'); // 帰着日の月末
            $table->unsignedBigInteger('last_manager_id')->nullable()->comment("担当者(最終更新値)");
            $table->text("last_note")->nullable()->comment("備考(最終更新値)");
            $table->text('partner_manager_ids')->nullable()->comment('チェック済み担当者ID');
            $table->text('document_setting')->nullable()->comment('項目表示・非表示設定');
            $table->text('document_common_setting')->nullable()->comment('共通設定');
            $table->text('reserve_prices')->nullable()->comment('オプション科目情報');
            $table->integer('amount_total')->default(0)->comment("合計金額");
            $table->integer('deposit_amount')->default(0)->comment("入金済額");
            $table->integer('not_deposit_amount')->default(0)->comment("未入金額");
            $table->tinyInteger('status')->nullable()->comment('状態');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('agency_id')
                ->references('id')
                ->on('agencies')
                ->onDelete('cascade');

            $table->foreign('business_user_id')
                ->references('id')
                ->on('business_users')
                ->onDelete('set null');

            $table->foreign('document_request_all_id')
                ->references('id')
                ->on('document_request_alls')
                ->onDelete('set null');

            $table->foreign('document_common_id')
                ->references('id')
                ->on('document_commons')
                ->onDelete('set null');

            $table->foreign('last_manager_id')
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
        Schema::dropIfExists('reserve_bundle_invoices');
    }
}
