<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_options', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('contract_id')->comment('契約ID');
            $table->unsignedTinyInteger('option')->comment('オプション');
            $table->unsignedMediumInteger('monthly_sum')->default(0)->comment('月額');
            $table->date('start_at')->comment('契約開始日');
            $table->date('end_at')->comment('契約終了日');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('contract_id')
            ->references('id')->on('contracts')
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
        Schema::dropIfExists('contract_options');
    }
}
