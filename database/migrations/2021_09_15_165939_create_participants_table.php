<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedBigInteger('reserve_id')->comment('予約ID');
            $table->unsignedBigInteger('user_id')->comment('ユーザーID');
            //
            $table->string('name', 64)->nullable()->comment('氏名');
            $table->string('name_kana', 64)->nullable()->comment('氏名(カナ)');
            $table->string('name_roman')->nullable()->comment('氏名(ローマ字)');
            $table->string('sex', 1)->nullable()->comment('性別');
            $table->smallInteger('birthday_y')->nullable()->comment('生年月日(年)');
            $table->tinyInteger('birthday_m')->nullable()->comment('生年月日(月)');
            $table->tinyInteger('birthday_d')->nullable()->comment('生年月日(日)');
            $table->tinyInteger('age')->nullable()->comment('年齢');
            $table->string('age_kbn', 3)->nullable()->comment('年齢区分');
            $table->string('mobile_phone')->nullable()->comment('携帯電話');
            $table->string('passport_number')->nullable()->comment('旅券番号');
            $table->date('passport_issue_date')->nullable()->comment('旅券発行日');
            $table->date('passport_expiration_date')->nullable()->comment('旅券有効期限');
            $table->string('passport_issue_country_code')->nullable()->comment('旅券発行国');
            $table->string('citizenship_code')->nullable()->comment('国籍');
            ///
            $table->boolean('representative')->default(false)->comment('代表者');
            $table->boolean('cancel')->default(false)->comment('取消');
            $table->text('note')->nullable()->comment("備考");
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agency_id')
            ->references('id')
            ->on('agencies')
            ->onDelete('cascade');

            $table->foreign('reserve_id')
            ->references('id')
            ->on('reserves')
            ->onDelete('cascade');

            $table->foreign('user_id')
            ->references('id')
            ->on('users')
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
        Schema::dropIfExists('participants');
    }
}
