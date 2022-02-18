<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agencies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('account', 32)->unique()->comment('アカウント');
            // $table->string('password');
            $table->string('identifier', 4)->unique()->comment('会社識別子');
            // $table->rememberToken();
            // $table->timestamp('email_verified_at')->nullable();
            $table->string('company_name')->comment('会社名');
            $table->string('company_kana')->comment('会社名カナ');
            $table->string('representative_name')->nullable()->comment('代表者名');
            $table->string('representative_kana')->nullable()->comment('代表者名カナ');
            $table->string('person_in_charge_name')->nullable()->comment('担当者名');
            $table->string('person_in_charge_kana')->nullable()->comment('担当者名カナ');
            $table->string('zip_code', 7)->comment('郵便番号');
            $table->string('prefecture_code', 2)->nullable()->comment('都道府県コード');
            $table->string('address1')->comment('住所1');
            $table->string('address2')->nullable()->comment('住所2');
            $table->string('capital')->nullable()->comment('資本金');
            $table->string('email')->comment('メールアドレス');
            $table->string('tel', 13)->nullable()->comment('電話番号');
            $table->string('fax', 13)->nullable()->comment('FAX');
            $table->string('emergency_contact')->nullable()->comment('緊急連絡先');
            $table->date('establishment_at')->nullable()->comment('設立年月日');
            $table->date('travel_agency_registration_at')->nullable()->comment('旅行業登録年月日');
            $table->tinyInteger('business_scope')->comment('業務範囲');
            $table->mediumInteger('employees_number')->nullable()->comment('従業員数');
            $table->string('registered_administrative_agency', 100)->nullable()->comment('登録行政庁名');
            $table->tinyInteger('registration_type')->comment('登録種別');
            $table->string('registration_number', 100)->nullable()->comment('登録番号');
            $table->tinyInteger('travel_agency_association')->comment('旅行業協会');
            $table->boolean('fair_trade_council')->default(false)->comment('旅公取協');
            $table->boolean('iata')->default(false)->comment('IATA加入');
            $table->boolean('etbt')->default(false)->comment('e-TBT加入');
            $table->boolean('bond_guarantee')->default(false)->comment('ボンド保証制度');
            $table->tinyInteger('number_staff_allowed')->default(0)->comment('スタッフ登録許可数');
            $table->tinyInteger('max_storage_capacity')->default(0)->comment('データ保存容量');
            $table->boolean('status')->default(true)->comment('状態');
            $table->date('registration_at')->comment('登録年月日');
            //　契約関連ここから
            $table->boolean('trial')->default(false)->comment('トライアル版');
            $table->date('trial_start_at')->nullable()->comment('トライアル開始日');
            $table->date('trial_end_at')->nullable()->comment('トライアル終了日');
            $table->boolean('definitive')->default(false)->comment('正式版');
            $table->unsignedSmallInteger('contract_count')->default(0)->comment('契約更新回数');
            // 契約関連ここまで
            $table->text('agreement_file')->nullable()->comment('旅行業約款');
            $table->text('terms_file')->nullable()->comment('取引条件説明書面（共通事項）');
            $table->string('manager', 32)->nullable()->comment('自社担当');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('prefecture_code')
            ->references('code')->on('prefectures')
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
        Schema::dropIfExists('agencies');
    }
}
