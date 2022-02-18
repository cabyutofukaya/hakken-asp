<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservePurchasingSubjectAirplaneCustomValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserve_purchasing_subject_airplane_custom_values', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reserve_purchasing_subject_airplane_id')->comment('仕入航空券科目ID');
            $table->unsignedBigInteger('user_custom_item_id')->comment("カスタム項目ID");
            $table->text('val')->nullable()->comment('値');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['reserve_purchasing_subject_airplane_id', 'user_custom_item_id'])->name('user_custom_item_unique');

            $table->foreign('reserve_purchasing_subject_airplane_id')
                ->references('id')->on('reserve_purchasing_subject_airplanes')
                ->onDelete('cascade')->name('purchasing_subject_airplane_foreign');
            
            $table->foreign('user_custom_item_id')
                ->references('id')->on('user_custom_items')
                ->onDelete('cascade')->name('user_custom_item_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reserve_purchasing_subject_airplane_custom_values');
    }
}
