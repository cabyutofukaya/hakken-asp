<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessUserManagerSequencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_user_manager_sequences', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedInteger('current_number')->default(0);
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->date('updated_at');

            $table->unique('agency_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_user_manager_sequences');
    }
}
