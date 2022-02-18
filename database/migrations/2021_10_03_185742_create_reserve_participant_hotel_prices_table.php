<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReserveParticipantHotelPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserve_participant_hotel_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reserve_itinerary_id')->comment('行程ID');
            $table->unsignedBigInteger('reserve_purchasing_subject_hotel_id')->comment('ホテル科目ID');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedBigInteger('participant_id')->nullable()->comment('参加者'); // 参加者を消した時の動作に関わってくるのでnullableで良いか要検討
            $table->boolean('valid')->default(true)->comment('有効');
            $table->string('room_number')->nullable()->comment('部屋番号');
            $table->integer('gross_ex')->default(0)->comment('GROSS(税抜)');
            $table->string('zei_kbn', 8)->nullable()->comment('税区分');
            $table->integer('gross')->default(0)->comment('GROSS単価');
            $table->integer('cost')->default(0)->comment('仕入れ値');
            $table->smallInteger('commission_rate')->default(0)->comment('手数料率');
            $table->integer('net')->default(0)->comment('NET単価');
            $table->integer('gross_profit')->default(0)->comment('粗利');
            $table->softDeletes();
            $table->timestamps();


            $table->foreign('reserve_itinerary_id')
                ->references('id')->on('reserve_itineraries')
                ->onDelete('cascade');
                
            $table->foreign('reserve_purchasing_subject_hotel_id')
                ->references('id')->on('reserve_purchasing_subject_hotels')
                ->onDelete('cascade')->name('reserve_purchasing_subject_hotel_foreign');

            $table->foreign('agency_id')
                ->references('id')->on('agencies')
                ->onDelete('cascade');

            $table->foreign('participant_id')
                ->references('id')->on('participants')
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
        Schema::dropIfExists('reserve_participant_hotel_prices');
    }
}
