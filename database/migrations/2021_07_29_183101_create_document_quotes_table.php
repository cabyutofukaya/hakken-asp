<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentQuotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_quotes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedTinyInteger('document_category_id')->comment('帳票カテゴリID');
            $table->string('code', 32)->nullable()->comment('管理コード');
            $table->string('name')->comment('名前');
            $table->string('title')->comment('表題');
            $table->string('management_name')->nullable()->comment('管理名称');
            $table->unsignedBigInteger('document_common_id')->nullable()->comment('宛名/自社情報共通設定');
            $table->string('description')->nullable()->comment('説明');
            $table->text('setting')->nullable()->comment('出力設定項目');
            $table->boolean('seal')->default(false)->comment('検印欄表示');
            $table->unsignedTinyInteger('seal_number')->default(0)->comment('検印欄表示数');
            $table->text('seal_items')->nullable()->comment('検印項目名');
            $table->string('seal_wording')->nullable()->comment('枠下文言');
            $table->text('information')->nullable()->comment('案内文');
            $table->text('note')->nullable()->comment('備考');
            $table->boolean('undelete_item')->default(false)->comment('削除不可フラグ');
            $table->unsignedSmallInteger('seq')->default(0)->comment('順番');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agency_id')
            ->references('id')->on('agencies')
            ->onDelete('cascade');

            $table->foreign('document_common_id')
            ->references('id')->on('document_commons')
            ->onDelete('set null');

            $table->foreign('document_category_id')
            ->references('id')->on('document_categories')
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
        Schema::dropIfExists('document_quotes');
    }
}
