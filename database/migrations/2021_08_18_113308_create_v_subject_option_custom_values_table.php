<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * カスタム項目値に親テーブルの
 * 有効フラグや管理コード情報などを付与し、
 * データを取得しやすくするためのview
 */
class CreateVSubjectOptionCustomValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
        CREATE VIEW v_subject_option_custom_values AS 
        SELECT
            l.id, l.subject_option_id, l.val, 
            r.key, r.type, r.agency_id, r.display_position, r.name, r.code, r.input_type, r.list, r.flg, r.seq 
        FROM 
            subject_option_custom_values AS l 
        LEFT JOIN 
            user_custom_items AS r 
        ON l.user_custom_item_id = r.id
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS v_subject_option_custom_values');
    }
}
