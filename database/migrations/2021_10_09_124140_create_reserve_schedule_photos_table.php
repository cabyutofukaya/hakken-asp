<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReserveSchedulePhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserve_schedule_photos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('agency_id')->comment('会社ID');
            $table->unsignedBigInteger('reserve_schedule_id')->comment('スケジュールID');
            $table->string("file_name")->nullable()->comment("ファイル名");
            $table->string("original_file_name")->nullable()->comment("オリジナルファイル名");
            $table->text("description")->nullable()->comment("説明");
            $table->string("mime_type")->nullable()->comment("MIMEタイプ");
            $table->unsignedInteger('file_size')->default(0)->comment('ファイルサイズ(B)');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('agency_id')
                ->references('id')->on('agencies')
                ->onDelete('cascade');

            $table->foreign('reserve_schedule_id')
                ->references('id')->on('reserve_schedules')
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
        Schema::dropIfExists('reserve_schedule_photos');
    }
}
