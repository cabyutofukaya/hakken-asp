<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_areas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->comment('UUID')->unique();
            $table->string('code')->comment('方面コード')->unique();
            $table->uuid('master_direction_uuid')->nullable()->comment("方面ID");
            $table->string('name')->comment('国・地域名称');
            $table->string('name_en')->comment('国・地域名称(英)');
            $table->string('gen_key')->comment('世代キー');
            $table->boolean('is_default')->default(false)->comment('デフォルト選択');
            $table->timestamps();

            $table->foreign('master_direction_uuid')
            ->references('uuid')->on('master_directions')
            ->onDelete('set null');
        });

        DB::statement('ALTER TABLE master_areas MODIFY code varchar(256) BINARY'); // 方面コードは、大文字小文字を区別させる。
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_areas');
    }
}
