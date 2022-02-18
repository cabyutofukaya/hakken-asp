<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCustomCategoryItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_custom_category_items', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->unsignedTinyInteger('user_custom_category_id')->comment("カテゴリID");
            $table->string('name', 32)->comment('カテゴリ名');
            $table->string('type', 8)->comment('項目タイプ');
            $table->string('display_positions')->nullable()->comment('対応表示位置');
            $table->unsignedTinyInteger('seq')->default(0)->comment("並び順");

            $table->unique(['user_custom_category_id', 'type']); // カテゴリIDと項目タイプの組み合わせでユニーク

            $table->foreign('user_custom_category_id')
            ->references('id')->on('user_custom_categories')
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
        Schema::dropIfExists('user_custom_category_items');
    }
}
