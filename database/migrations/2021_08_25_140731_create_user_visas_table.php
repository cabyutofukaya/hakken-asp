<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserVisasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_visas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('ユーザーID');
            $table->string('number')->nullable()->comment('ビザ番号');
            $table->string('country_code')->nullable()->comment('国');
            $table->string('kind')->nullable()->comment('種別');
            $table->string('issue_place_code')->nullable()->comment('発行地');
            $table->date('issue_date')->nullable()->comment('発行日');
            $table->date('expiration_date')->nullable()->comment('有効期限');
            $table->text('note')->nullable()->comment('備考');
            $table->string('gen_key')->comment('世代キー'); // リレーション更新時の削除制御に使用
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('country_code')->references('code')->on('countries')->onDelete('set null');
            $table->foreign('issue_place_code')->references('code')->on('countries')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_visas');
    }
}
