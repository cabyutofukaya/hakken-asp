<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->comment('UUID')->unique();
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->string('code')->comment('方面コード');
            $table->uuid('v_direction_uuid')->nullable()->comment("方面ID");
            $table->string('name')->nullable()->comment('国・地域名称');
            $table->string('name_en')->nullable()->comment('国・地域名称(英)');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'code']); // 会社IDと方面コードの組み合わせでユニーク

            $table->foreign('agency_id')
            ->references('id')->on('agencies')
            ->onDelete('cascade');

            // $table->foreign('v_direction_uuid')
            // ->references('uuid')->on('directions')
            // ->onDelete('set null');
        });

        DB::statement('ALTER TABLE areas MODIFY code varchar(256) BINARY'); // 方面コードは、大文字小文字を区別させる。
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('areas');
    }
}
