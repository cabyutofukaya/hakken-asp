<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedBigInteger('staff_id')->comment("スタッフID");
            $table->string('post')->nullable()->comment('役職');
            $table->string('name', 64)->nullable()->comment('氏名');
            $table->string('name_kana', 64)->nullable()->comment('氏名(カナ)');
            $table->string('name_roman')->nullable()->comment('氏名(ローマ字)');
            $table->string('email')->nullable()->comment('メールアドレス');
            $table->string('tel')->nullable()->comment('電話番号');
            $table->string('sex', 1)->nullable()->comment('性別');
            $table->smallInteger('birthday_y')->nullable()->comment('生年月日(年)');
            $table->tinyInteger('birthday_m')->nullable()->comment('生年月日(月)');
            $table->tinyInteger('birthday_d')->nullable()->comment('生年月日(日)');
            $table->text('introduction')->nullable()->comment('自己紹介');
            $table->text('business_area')->nullable()->comment('対応エリア');
            $table->text('purpose')->nullable()->comment('旅行分野');
            $table->text('interest')->nullable()->comment('旅行内容');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('agency_id')
                ->references('id')
                ->on('agencies')
                ->onDelete('cascade');

            $table->foreign('staff_id')
                ->references('id')
                ->on('staffs')
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
        Schema::dropIfExists('web_profiles');
    }
}
