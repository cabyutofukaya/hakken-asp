<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAspUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asp_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->string('name', 64)->nullable()->comment('氏名');
            $table->string('name_kana', 64)->nullable()->comment('氏名(カナ)');
            $table->string('name_roman')->nullable()->comment('氏名(ローマ字)');
            $table->string('sex', 1)->nullable()->comment('性別');
            $table->smallInteger('birthday_y')->nullable()->comment('生年月日(年)');
            $table->tinyInteger('birthday_m')->nullable()->comment('生年月日(月)');
            $table->tinyInteger('birthday_d')->nullable()->comment('生年月日(日)');
            $table->string('mobile_phone')->nullable()->comment('携帯電話');
            $table->string('tel')->nullable()->comment('固定電話');
            $table->string('fax')->nullable()->comment('FAX');
            $table->string('email')->nullable()->comment('メールアドレス');
            $table->string('zip_code', 7)->nullable()->comment('郵便番号');
            $table->string('prefecture_code', 2)->nullable()->comment('都道府県');
            $table->string('address1')->nullable()->comment('住所1');
            $table->string('address2')->nullable()->comment('住所2');
            $table->string('passport_number')->nullable()->comment('旅券番号');
            $table->date('passport_issue_date')->nullable()->comment('旅券発行日');
            $table->date('passport_expiration_date')->nullable()->comment('旅券有効期限');
            $table->string('passport_issue_country_code')->nullable()->comment('旅券発行国');
            $table->string('citizenship_code')->nullable()->comment('国籍');
            $table->tinyInteger('registration_type')->nullable()->comment('登録区分'); // 法人・個人
            $table->string('workspace_name')->nullable()->comment('勤務先/学校名');
            $table->string('workspace_address')->nullable()->comment('住所(勤務先/学校)');
            $table->string('workspace_tel')->nullable()->comment('電話番号(勤務先/学校)');
            $table->text('workspace_note')->nullable()->comment('備考(勤務先/学校)');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agency_id')
            ->references('id')->on('agencies')
            ->onDelete('cascade');

            $table->foreign('passport_issue_country_code')->references('code')->on('countries')->onDelete('set null');

            $table->foreign('citizenship_code')->references('code')->on('countries')->onDelete('set null');

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
        Schema::dropIfExists('asp_users');
    }
}
