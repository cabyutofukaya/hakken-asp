<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZoomApiKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zoom_api_keys', function (Blueprint $table) {
            $table->increments('id');
            $table->string('api_key')->comment('api_key'); 
            $table->string('api_secret')->comment('api_secret'); 
            $table->string('im_chat_history_token')->nullable()->comment('im_chat_history_token'); 
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zoom_api_keys');
    }
}
