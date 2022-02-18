<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentCommonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_commons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedTinyInteger('document_category_id')->comment('帳票カテゴリID');
            $table->string('code', 32)->nullable()->comment('管理コード');
            $table->string('name')->comment('名前');
            $table->string('description')->nullable()->comment('説明');
            $table->text('setting')->nullable()->comment('出力設定項目');
            $table->string('company_name')->nullable()->comment('自社名');
            $table->string('supplement1')->nullable()->comment('補足1');
            $table->string('supplement2')->nullable()->comment('補足2');
            $table->string('zip_code',7)->nullable()->comment('郵便番号');
            $table->string('address1')->nullable()->comment('住所1');
            $table->string('address2')->nullable()->comment('住所2');
            $table->string('tel', 64)->nullable()->comment('TEL');
            $table->string('fax', 64)->nullable()->comment('FAX');
            $table->boolean('undelete_item')->default(false)->comment('削除不可フラグ');
            $table->unsignedSmallInteger('seq')->default(0)->comment('順番');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agency_id')
            ->references('id')->on('agencies')
            ->onDelete('cascade');

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
        Schema::dropIfExists('document_commons');
    }
}
