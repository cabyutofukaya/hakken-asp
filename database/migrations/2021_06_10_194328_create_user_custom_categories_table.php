<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCustomCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_custom_categories', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name', 24)->comment('カテゴリ名');
            $table->string('code', 24)->comment('管理コード');
            $table->unsignedTinyInteger('seq')->default(0)->comment("並び順");
            $table->timestamps();

            $table->unique('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_custom_categories');
    }
}
