<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVAreasTable extends Migration
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
        CREATE VIEW v_areas AS 
            SELECT
                uuid, master_direction_uuid AS v_direction_uuid, code, name, name_en, {$masterAgencyId} AS agency_id, 1 AS master, created_at, null AS deleted_at
            FROM 
                master_areas
        UNION 
            SELECT
                uuid, v_direction_uuid, code, name, name_en, agency_id, 0 AS master, created_at, deleted_at
            FROM 
                areas
        ");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS v_areas');
    }
}
