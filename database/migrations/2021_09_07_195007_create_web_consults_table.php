<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebConsultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_consults', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('receipt_number')->comment('受付番号');
            $table->unsignedBigInteger('web_user_id')->comment('webユーザーID');
            $table->tinyInteger('departure_kbn')->default(0)->comment('旅行日区分');
            $table->date('departure_date')->nullable()->comment('出発日');
            $table->date('return_date')->nullable()->comment('帰着日');
            $table->string('departure_season')->nullable()->comment('出発時期');
            $table->unsignedTinyInteger('stays')->default(1)->comment('宿泊数');
            $table->uuid('departure_id')->nullable()->comment('国・地域（出発地）');
            $table->uuid('dest_direction_id')->nullable()->comment('方面（目的地）');
            $table->uuid('destination_id')->nullable()->comment('国・地域（目的地）');
            $table->string('destination_place')->nullable()->comment('住所・名称（目的地）');
            $table->unsignedTinyInteger('adult')->default(0)->comment('大人人数');
            $table->unsignedTinyInteger('child')->default(0)->comment('子供人数');
            $table->unsignedTinyInteger('infant')->default(0)->comment('幼児人数');
            $table->string('purpose')->nullable()->comment('旅の目的');
            $table->tinyInteger('budget_kbn')->default(1)->comment('予算区分');
            $table->integer('amount')->default(0)->comment('予算');
            $table->text('interest')->nullable()->comment('興味');
            $table->text('request')->nullable()->comment('要望');
            $table->boolean('status')->defautl(true)->comment('有効フラグ'); // TODO このカラム不要かも
            $table->timestamp('cancel_at')->nullable()->comment('取消日時'); // ユーザーからの相談取消
            $table->tinyInteger('consult_kind')->comment('相談種別'); // 個別or一括
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique('receipt_number');

            $table->foreign('web_user_id')
                ->references('id')
                ->on('web_users')
                ->onDelete('cascade');

            $table->foreign('departure_id')
                ->references('uuid')
                ->on('master_areas')
                ->onDelete('set null');

            $table->foreign('dest_direction_id')
                ->references('uuid')
                ->on('master_directions')
                ->onDelete('set null');

            $table->foreign('destination_id')
                ->references('uuid')
                ->on('master_areas')
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
        Schema::dropIfExists('web_consults');
    }
}
