<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterDirectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_directions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->comment('UUID')->unique();
            $table->string('code')->comment('方面コード')->unique();
            $table->string('name')->comment('名称');
            $table->string('gen_key')->comment('世代キー');
            $table->timestamps();
            // $table->softDeletes();
        });

        DB::statement('ALTER TABLE master_directions MODIFY code varchar(256) BINARY'); // 方面コードは、大文字小文字を区別させる。

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_directions');
    }
}
