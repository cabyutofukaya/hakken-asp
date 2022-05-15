<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebSearchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * web_user_id、search_hash、statusの組み合わせでユニークになるようにphp側で制御
     * データベースだと論理削除カラム(deleted_at)があるのでユニーク制約が難しい
     * 
     * @return void
     */
    public function up()
    {
        Schema::create('web_searches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('web_user_id')->comment('webユーザーID');
            $table->string('to_direction')->nullable()->comment("方面");
            $table->text('to_area')->nullable()->comment("目的地");
            $table->date('departure_timing')->nullable()->comment("出発時期");
            $table->string('stays')->nullable()->comment("日数");
            $table->string('adult')->nullable()->comment("大人人数");
            $table->string('child')->nullable()->comment("子供人数");
            $table->string('infant')->nullable()->comment("幼児人数");
            $table->tinyInteger('budget_kbn')->nullable()->comment("費用区分");
            $table->string('amount')->nullable()->comment("費用");
            $table->text('purpose')->nullable()->comment("旅の目的");
            $table->text('interest')->nullable()->comment("興味があること");
            $table->string('from_area')->nullable()->comment("出発地");
            $table->string('from_place')->nullable()->comment("出発地場所");
            $table->text('request')->nullable()->comment("要望");
            $table->string('search_hash')->comment("検索ハッシュ値");
            $table->boolean('active')->default(false)->comment('有効フラグ');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('web_user_id')
                ->references('id')
                ->on('web_users')
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
        Schema::dropIfExists('web_searches');
    }
}
