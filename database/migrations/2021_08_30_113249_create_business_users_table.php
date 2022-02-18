<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->string('user_number')->comment('顧客番号');
            $table->string('name', 100)->nullable()->comment('法人名');
            $table->string('name_kana')->nullable()->comment('法人名(カナ)');
            $table->string('name_roman')->nullable()->comment('法人名(英語表記)');
            $table->string('tel')->nullable()->comment('電話番号');
            $table->string('fax')->nullable()->comment('FAX');
            $table->string('zip_code', 7)->nullable()->comment('郵便番号');
            $table->string('prefecture_code', 2)->nullable()->comment('都道府県');
            $table->string('address1')->nullable()->comment('住所1');
            $table->string('address2')->nullable()->comment('住所2');
            $table->unsignedBigInteger('manager_id')->nullable()->comment("自社担当");
            $table->tinyInteger('pay_altogether')->nullable()->comment('一括支払契約');
            $table->text('note')->nullable()->comment('備考');
            $table->tinyInteger('status')->default(1)->comment('状態');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agency_id')
            ->references('id')->on('agencies')
            ->onDelete('cascade');

            $table->foreign('prefecture_code')
            ->references('code')->on('prefectures')
            ->onDelete('set null');

            $table->foreign('manager_id')
            ->references('id')
            ->on('staffs')
            ->onDelete('set null');

            $table->unique(['agency_id', 'user_number']);
        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_users');
    }
}
