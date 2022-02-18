<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgencyRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agency_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->string('name')->comment('役割名');
            $table->string('description')->nullable()->comment('説明');
            $table->text('authority')->nullable()->commen('権限詳細');
            $table->boolean('master')->default(false)->comment('マスター権限フラグ');
            // $table->softDeletes();
            // $table->timestamps();

            $table->foreign('agency_id')
            ->references('id')->on('agencies')
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
        Schema::dropIfExists('agency_roles');
    }
}
