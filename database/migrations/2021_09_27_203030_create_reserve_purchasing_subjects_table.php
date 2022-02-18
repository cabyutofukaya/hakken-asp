<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservePurchasingSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserve_purchasing_subjects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reserve_schedule_id')->comment('スケジュールID');
            $table->string("subjectable_type")->comment("科目タイプ");
            $table->unsignedBigInteger('subjectable_id')->comment('科目ID');
            // $table->smallInteger('seq')->default(0)->comment('順番');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('reserve_schedule_id')
                ->references('id')->on('reserve_schedules')
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
        Schema::dropIfExists('reserve_purchasing_subjects');
    }
}
