<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->string('code')->comment('仕入先コード');
            $table->string('name')->nullable()->comment('仕入先名称');
            $table->string('reference_date')->nullable()->comment('基準日');
            // $table->string('payday')->nullable()->comment('支払日');
            $table->string('cutoff_date')->nullable()->comment('締日');
            $table->string('payment_month')->nullable()->comment('入金日(月)');
            $table->string('payment_day')->nullable()->comment('入金日(日)');
            $table->text('note')->nullable()->comment('備考');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'code']); // 会社IDと方面コードの組み合わせでユニーク

            $table->foreign('agency_id')
            ->references('id')->on('agencies')
            ->onDelete('cascade');
        });

        DB::statement('ALTER TABLE suppliers MODIFY code varchar(256) BINARY'); // 仕入先コードは、大文字小文字を区別させる。
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suppliers');
    }
}
