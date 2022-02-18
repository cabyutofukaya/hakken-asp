<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentPdfsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_pdfs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');

            // ポリモーフィックリレーション
            $table->string('documentable_type')->comment('帳票種別');
            $table->unsignedBigInteger('documentable_id')->nullable()->comment('帳票ID');

            $table->string("file_name")->nullable()->comment("ファイル名");
            $table->string("original_file_name")->nullable()->comment("オリジナルファイル名");
            $table->text("description")->nullable()->comment("説明");
            $table->string("mime_type")->nullable()->comment("MIMEタイプ");
            $table->unsignedInteger('file_size')->default(0)->comment('ファイルサイズ(B)');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('agency_id')
                ->references('id')->on('agencies')
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
        Schema::dropIfExists('document_pdfs');
    }
}
