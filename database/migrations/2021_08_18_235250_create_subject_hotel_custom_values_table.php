<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubjectHotelCustomValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subject_hotel_custom_values', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('subject_hotel_id')->comment('ホテル科目ID');
            $table->unsignedBigInteger('user_custom_item_id')->comment("カスタム項目ID");
            $table->text('val')->nullable()->comment('値');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['subject_hotel_id', 'user_custom_item_id'])->name("subject_hotel_id_user_custom_item_id_unique");

            $table->foreign('subject_hotel_id')->references('id')->on('subject_hotels')->onDelete('cascade');
            $table->foreign('user_custom_item_id')->references('id')
            ->on('user_custom_items')->onDelete('cascade');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subject_hotel_custom_values');
    }
}
