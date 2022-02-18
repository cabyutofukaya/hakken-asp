<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staffs', function (Blueprint $table) {
            $table->bigIncrements('id');
            // $table->string('hash_id')->nullable()->comment('ハッシュID');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->string('name')->nullable()->comment('名前');
            $table->string('account', 32)->comment('アカウント');
            $table->string('email')->nullable()->comment('email');
            $table->string('password')->comment('パスワード');
            $table->rememberToken();
            $table->boolean('master')->default(false)->comment('マスター管理者フラグ');
            $table->unsignedInteger('agency_role_id')->nullable()->comment('権限');
            $table->boolean('status')->default(1)->comment('状態');
            $table->boolean('web_valid')->default(false)->comment('Hakken機能有効フラグ');
            $table->datetime('last_login_at')->nullable()->comment('最終ログイン日時');
            $table->unsignedTinyInteger('number_of_plan')->default(0)->comment('有効保有プラン数'); // HAKKEN用
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agency_id')
            ->references('id')->on('agencies')
            ->onDelete('cascade');

            $table->foreign('agency_role_id')
            ->references('id')->on('agency_roles')
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
        Schema::dropIfExists('staffs');
    }
}
