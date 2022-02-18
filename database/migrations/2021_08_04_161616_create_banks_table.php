<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('kinyu_code', 4)->comment('金融期間コード');
            $table->string('tenpo_code', 3)->comment('店舗コード');
            $table->string('kinyu_kana')->comment('金融機関名(カナ)');
            $table->string('kinyu_name')->comment('金融機関名');
            $table->string('tenpo_kana')->comment('店舗名(カナ)');
            $table->string('tenpo_name')->comment('店舗名');
            $table->string('zip_code', 8)->comment('郵便番号');
            $table->string('address')->comment('店舗所在地');
            $table->string('tel', 17)->comment('電話番号');
            $table->string('tegata_kokanjyo_no')->comment('手形交換所番号');
            $table->unsignedTinyInteger('narabi_code')->comment('並びコード');
            $table->boolean('kamei')->comment('内国為替制度加盟');
            $table->timestamps();

            $table->unique(['kinyu_code', 'tenpo_code', 'narabi_code']);// 金融機関コード＆店舗コードでユニーク
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banks');
    }
}
