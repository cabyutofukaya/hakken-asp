<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCustomItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_custom_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key')->unique();
            $table->unsignedTinyInteger('user_custom_category_id')->comment('所属カテゴリ');
            $table->unsignedTinyInteger('user_custom_category_item_id')->comment('所属項目');
            $table->string('type', 8)->comment('項目タイプ');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->string('display_position', 24)->nullable()->comment('表示位置');
            $table->string('name')->nullable()->comment('名称');
            $table->string('code')->nullable()->comment('管理コード');
            $table->boolean('fixed_item')->default(false)->comment('固定フラグ');
            $table->boolean('undelete_item')->default(false)->comment('削除不可フラグ');
            $table->boolean('unedit_item')->default(false)->comment('編集不可フラグ');
            $table->string('input_type')->nullable()->comment('入力形式');
            $table->text('list')->nullable()->comment('リスト');
            $table->text('protect_list')->nullable()->comment('編集・削除不可リスト');
            $table->boolean('flg')->default(true)->comment('有効フラグ');
            $table->unsignedTinyInteger('seq')->default(0)->comment('順番');
            $table->timestamps();
            $table->softDeletes();
            

            $table->unique(['agency_id', 'code']); // 会社IDと管理コードの組み合わせでユニーク

            $table->foreign('agency_id')
            ->references('id')->on('agencies')
            ->onDelete('cascade');

            $table->foreign('user_custom_category_id')
            ->references('id')->on('user_custom_categories')
            ->onDelete('cascade');

            $table->foreign('user_custom_category_item_id')
            ->references('id')->on('user_custom_category_items')
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
        Schema::dropIfExists('user_custom_items');
    }
}
