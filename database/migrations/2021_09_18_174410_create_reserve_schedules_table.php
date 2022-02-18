<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReserveSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserve_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reserve_travel_date_id')->comment('旅程ID');
            $table->unsignedInteger('agency_id')->comment('旅行会社');
            $table->string('type')->comment('旅程タイプ');
            $table->string('arrival_time',32)->nullable()->comment('到着時間');
            $table->string('staying_time',32)->nullable()->comment('滞在時間');
            $table->string('departure_time',32)->nullable()->comment('出発時間');
            $table->string('place')->nullable()->comment('場所');
            $table->text('explanation')->nullable()->comment('説明');
            $table->string('transportation', '24')->nullable()->comment('移動手段');
            $table->string('transportation_supplement')->nullable()->comment('移動手段(補足)');
            $table->unsignedSmallInteger('seq')->default(0)->comment("並び順");
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agency_id')
                ->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('reserve_travel_date_id')
                ->references('id')->on('reserve_travel_dates')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reserve_schedules');
    }
}
