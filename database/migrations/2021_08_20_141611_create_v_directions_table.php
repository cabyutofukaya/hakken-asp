<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * master_directionsとdirectionsテーブルをUNIONしたview
 * master_directionsとdirectionsで登録した双方の方向コードを利用できるようにする
 */
class CreateVDirectionsTable extends Migration
{
    /**
     * Run the migrations.
     * 
     * @return void
     */
    public function up()
    {
        $masterAgencyId = config('consts.const.MASTER_AGENCY_ID');

        DB::statement("
        CREATE VIEW v_directions AS 
            SELECT
                uuid, code, name, {$masterAgencyId} AS agency_id, 1 AS master, created_at, null AS deleted_at
            FROM 
                master_directions
        UNION 
            SELECT
                uuid, code, name, agency_id, 0 AS master, created_at, deleted_at
            FROM 
                directions
        ");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS v_directions');
    }
}
