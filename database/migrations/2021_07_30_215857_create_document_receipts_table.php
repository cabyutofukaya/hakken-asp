<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_receipts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedTinyInteger('document_category_id')->comment('帳票カテゴリID');
            $table->string('name')->comment('名前');
            $table->unsignedBigInteger('document_common_id')->nullable()->comment('宛名/自社情報共通設定');
            $table->string('title')->comment('表題');
            $table->string('description')->nullable()->comment('説明');
            $table->text('proviso')->nullable()->comment('但し書き');
            $table->text('note')->nullable()->comment('備考');
            $table->string('code', 32)->nullable()->comment('管理コード');
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
        Schema::dropIfExists('document_receipts');
    }
}
