<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgencyConsultationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agency_consultations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('control_number')->comment('管理番号');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->string('taxonomy', 12)->comment("分類");
            $table->unsignedBigInteger('user_id')->nullable()->comment("個人顧客ID");
            $table->unsignedBigInteger('business_user_id')->nullable()->comment("法人顧客ID");
            $table->unsignedBigInteger('reserve_id')->nullable()->comment("予約ID");
            $table->string('title', 32)->nullable()->comment('タイトル');
            $table->unsignedBigInteger('manager_id')->nullable()->comment("スタッフID");
            $table->date('reception_date')->nullable()->comment('受付日');
            $table->unsignedTinyInteger('kind')->nullable()->comment("種別");
            $table->date('deadline')->nullable()->comment('期限');
            $table->unsignedTinyInteger('status')->nullable()->comment("ステータス");
            $table->text('contents')->nullable()->comment("内容");
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'control_number'])->name("control_number_agency_id");

            $table->foreign('agency_id')
                ->references('id')
                ->on('agencies')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('business_user_id')
                ->references('id')
                ->on('business_users')
                ->onDelete('set null');

            $table->foreign('reserve_id')
                ->references('id')
                ->on('reserves')
                ->onDelete('set null');

            $table->foreign('manager_id')
                ->references('id')
                ->on('staffs')
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
        Schema::dropIfExists('agency_consultations');
    }
}
