<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebRequestSequencesTable extends Migration
{
    /**
     * Run the migrations.
     * Webからの相談リクエストを1日単位でカウントするレコード(依頼番号生成用)
     * 
     * @return void
     */
    public function up()
    {
        Schema::create('web_request_sequences', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->unsignedInteger('current_number')->default(0); // 1日ごとに連番カウント
            $table->datetime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('web_reserve_sequences');
    }
}
