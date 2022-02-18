<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_companies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->text('explanation')->nullable()->comment('会社説明');
            $table->string('logo_image')->nullable()->comment('会社ロゴ');
            $table->text('images')->nullable()->comment('イメージ写真');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('agency_id')
            ->references('id')
            ->on('agencies')
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
        Schema::dropIfExists('web_companies');
    }
}
