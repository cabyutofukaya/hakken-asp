<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessUserManagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_user_managers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedBigInteger('business_user_id')->comment('法人顧客ID');
            $table->string('user_number')->comment('顧客番号');
            $table->string('name', 64)->nullable()->comment('担当者名');
            $table->string('name_kana', 64)->nullable()->comment('担当者名(カナ)'); // inputフィールドは現状未使用。申込者検索の際に個人顧客の検索SQL SQLと合わせるためにひとまず設置
            $table->string('name_roman')->nullable()->comment('担当者名(ローマ字)');
            $table->string('sex', 1)->nullable()->comment('性別');
            $table->string('department_name', 64)->nullable()->comment('部署名');
            $table->string('email')->nullable()->comment('メールアドレス');
            $table->string('tel')->nullable()->comment('電話番号');
            $table->tinyInteger('dm')->nullable()->comment('DM');
            $table->text('note')->nullable()->comment('備考');
            $table->string('gen_key')->comment('世代キー'); // リレーション更新時の削除制御に使用
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agency_id')
                ->references('id')->on('agencies')
                ->onDelete('cascade');

            $table->foreign('business_user_id')->references('id')->on('business_users')->onDelete('cascade');

            $table->unique(['agency_id', 'user_number']);//本来はbusiness_usersとの組み合わせでユニークとすべきだが、そうすると同じIDが同一agency内に存在してしまうためagency_idごとにユニークに
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_user_managers');
    }
}
