<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWebSearchHashColumnsToWebConsultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('web_consults', function (Blueprint $table) {
            $table->string('web_search_hash')->after('consult_kind')->comment("検索ハッシュ値"); // web_searchesテーブルのsearch_hashと同じ値。web_searchesレコードと連携して相談レコードが作成済みかどうかなどを調べるのに使用
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
            $table->dropColumn('web_search_hash');
        });
    }
}
