<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFewColumnsOnWebConsults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('web_consults', function (Blueprint $table) {
            $table->smallInteger('status')->change();
            $table->string('stays')->nullable(true)->default(null)->change();
            $table->string('adult')->nullable(true)->change();
            $table->string('child')->nullable(true)->change();
            $table->string('infant')->nullable(true)->change();
            $table->text('purpose')->nullable(true)->change();
            $table->string('amount')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('web_consults', function (Blueprint $table) {
            $table->boolean('status')->change();
            $table->smallInteger('stays')->change();
            $table->smallInteger('adult')->change();
            $table->smallInteger('child')->change();
            $table->smallInteger('infant')->change();
            $table->string('purpose')->change();
            $table->integer('amount')->change();
        });
    }
}
