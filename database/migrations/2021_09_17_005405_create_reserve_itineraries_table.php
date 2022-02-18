<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReserveItinerariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserve_itineraries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reserve_id')->comment('旅行ID');
            $table->unsignedInteger('agency_id')->comment('旅行会社');
            $table->string('control_number')->comment('管理番号');
            $table->boolean('enabled')->default(false)->comment('有効フラグ');
            $table->text('note')->nullable()->comment('備考');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agency_id')
                ->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('reserve_id')
                ->references('id')->on('reserves')->onDelete('cascade');

            $table->unique(['reserve_id', 'control_number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reserve_itineraries');
    }
}
