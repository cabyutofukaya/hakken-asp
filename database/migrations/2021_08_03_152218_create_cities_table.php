<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->string('code')->comment('都市・空港コード');
            $table->uuid('v_area_uuid')->nullable()->comment("国・地域ID");
            $table->string('name')->nullable()->comment('都市・空港名称');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'code']); // 会社IDと方面コードの組み合わせでユニーク

            $table->foreign('agency_id')
            ->references('id')->on('agencies')
            ->onDelete('cascade');

            // $table->foreign('area_id')
            // ->references('id')->on('areas')
            // ->onDelete('set null');
        });

        DB::statement('ALTER TABLE cities MODIFY code varchar(256) BINARY'); // 都市・空港コードは、大文字小文字を区別させる。

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cities');
    }
}
