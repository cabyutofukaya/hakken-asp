<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDirectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('directions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->comment('UUID')->unique();
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->string('code')->comment('方面コード');
            $table->string('name')->nullable()->comment('名称');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'code']); // 会社IDと方面コードの組み合わせでユニーク

            $table->foreign('agency_id')
            ->references('id')->on('agencies')
            ->onDelete('cascade');
        });

        DB::statement('ALTER TABLE directions MODIFY code varchar(256) BINARY'); // 方面コードは、大文字小文字を区別させる。
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('directions');
    }
}
