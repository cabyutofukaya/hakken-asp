<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReserveInvoiceSequencesTable extends Migration
{
    /**
     * 請求書連番管理
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserve_invoice_sequences', function (Blueprint $table) {
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
        Schema::dropIfExists('reserve_invoice_sequences');
    }
}
