<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePriceRelatedChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_related_changes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reserve_id')->comment('予約ID');
            $table->dateTime('change_at')->comment('変更日時');

            $table->unique('reserve_id');

            $table->foreign('reserve_id')
                ->references('id')
                ->on('reserves')
                ->onDelete('cascade');
            
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price_related_changes');
    }
}
