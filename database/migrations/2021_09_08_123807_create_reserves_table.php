<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserves', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('control_number')->nullable()->comment('予約番号');
            $table->string('estimate_number')->nullable()->comment('見積番号');
            $table->string('request_number')->nullable()->comment('依頼番号');

            $table->unsignedInteger('agency_id')->comment('会社ID');
            
            // ポリモーフィックリレーション
            $table->string('applicantable_type')->nullable()->comment('顧客種別'); // Web受付の場合、相談段階では本カラムはnullになるためnullも許可
            $table->unsignedBigInteger('applicantable_id')->nullable()->comment('申込者ID');
            // ポリモーフィックリレーション(検索用)
            $table->string('applicant_searchable_type')->nullable()->comment('顧客種別(検索用)'); // Web受付の場合、相談段階では本カラムはnullになるためnullも許可
            $table->unsignedBigInteger('applicant_searchable_id')->nullable()->comment('申込者ID(検索用)');

            $table->string('name')->nullable()->comment('旅行名');
            $table->date('departure_date')->nullable()->comment('出発日');
            $table->date('return_date')->nullable()->comment('帰着日');
            $table->uuid('departure_id')->nullable()->comment('国・地域（出発地）');
            $table->string('departure_place')->nullable()->comment('住所・名称（出発地）');
            $table->uuid('destination_id')->nullable()->comment('国・地域（目的地）');
            $table->string('destination_place')->nullable()->comment('住所・名称（目的地）');
            $table->text('note')->nullable()->comment('備考');
            $table->unsignedBigInteger('manager_id')->nullable()->comment("スタッフID");
            $table->string('application_step', 12)->nullable()->comment("申込段階"); // 見積 or 予約
            $table->string('representative_name',64)->nullable()->comment('代表者名'); //主にソート・検索用
            $table->tinyInteger('reception_type')->default(1)->comment('受付種別');
            $table->unsignedBigInteger('web_consult_id')->nullable()->comment('Web相談ID'); // 予約・見積情報をWeb相談情報と紐づけるためのカラム
            $table->unsignedSmallInteger('headcount')->default(0)->comment("人数"); //主にソート・検索用
            $table->integer('sum_gross')->default(0)->comment('GROSS合計'); //主にソート・検索用
            $table->integer('sum_withdrawal')->default(0)->comment('支払合計');
            $table->dateTime('latest_number_issue_at')->nullable()->comment("最新番号発行日時");
            $table->dateTime('cancel_at')->nullable()->comment("キャンセル日時");
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['control_number', 'agency_id']);
            $table->unique(['estimate_number', 'agency_id']);
            $table->unique(['request_number', 'agency_id']);

            $table->foreign('agency_id')
                ->references('id')
                ->on('agencies')
                ->onDelete('cascade');

            $table->foreign('manager_id')
                ->references('id')
                ->on('staffs')
                ->onDelete('set null');

            $table->foreign('web_consult_id')
                ->references('id')
                ->on('web_consults')
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
        Schema::dropIfExists('reserves');
    }
}
