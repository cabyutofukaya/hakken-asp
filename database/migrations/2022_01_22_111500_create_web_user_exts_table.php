<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebUserExtsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_user_exts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedBigInteger('web_user_id')->comment('WEBユーザーID');
            $table->tinyInteger('age')->nullable()->comment('年齢');
            $table->string('age_kbn', 3)->nullable()->comment('年齢区分');
            $table->string('emergency_contact')->nullable()->comment('緊急連絡先');
            $table->string('emergency_contact_column')->nullable()->comment('緊急連絡先カラム');
            $table->unsignedBigInteger('manager_id')->nullable()->comment("自社担当");
            $table->tinyInteger('dm')->nullable()->comment('DM');
            $table->text('note')->nullable()->comment('備考');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agency_id')
                ->references('id')
                ->on('agencies')
                ->onDelete('cascade');

            $table->foreign('web_user_id')
                ->references('id')
                ->on('web_users')
                ->onDelete('cascade');

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
        Schema::dropIfExists('web_user_exts');
    }
}
