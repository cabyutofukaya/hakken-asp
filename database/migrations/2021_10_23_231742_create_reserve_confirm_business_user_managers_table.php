<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReserveConfirmBusinessUserManagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserve_confirm_business_user_managers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('company_name', 100)->nullable()->comment('法人名');
            $table->string('department_name', 64)->nullable()->comment('部署名');
            $table->string('name', 64)->nullable()->comment('氏名');
            $table->tinyInteger('honorific')->nullable()->comment('敬称');
            $table->string('zip_code', 7)->nullable()->comment('郵便番号');
            $table->string('prefecture', 32)->nullable()->comment('都道府県');
            $table->string('address1')->nullable()->comment('住所1');
            $table->string('address2')->nullable()->comment('住所2');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reserve_confirm_business_user_managers');
    }
}
