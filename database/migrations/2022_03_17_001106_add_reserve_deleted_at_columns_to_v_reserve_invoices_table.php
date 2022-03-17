<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// reserve_deleted_atカラムを追加
class AddReserveDeletedAtColumnsToVReserveInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * 一括請求テーブルと通常請求テーブルを合体したview
         * 
         * 上 -> 一括請求テーブル。reserve_bundle_invoicesが削除済みのレコードは取得対象外
         * 下 -> 通常請求テーブルから一括請求レコードを除いたもの。reserve_invoicesが削除済みのレコードは取得対象外。
         * 
         * プログラムでIDを使うので、テーブル名の頭文字と当該テーブルのIDを組み合わせた一意となる文字列でIDを設定
         */

        DB::statement('DROP VIEW IF EXISTS v_reserve_invoices');
        DB::statement("
        CREATE VIEW v_reserve_invoices AS 
                SELECT
                    concat('rbi',reserve_bundle_invoices.id) AS id,
                    reserve_bundle_invoices.id AS reserve_bundle_invoice_id,
                    NULL AS reserve_invoice_id,
                    reserve_bundle_invoices.agency_id, 
                    reserve_bundle_invoices.business_user_id, 
                    NULL AS reserve_id,
                    NULL AS reserve_deleted_at,
                    reserve_bundle_invoices.bundle_invoice_number,
                    NULL AS applicant_name,
                    reserve_bundle_invoices.billing_address_name,
                    reserve_bundle_invoices.amount_total, 
                    reserve_bundle_invoices.deposit_amount, 
                    reserve_bundle_invoices.not_deposit_amount, 
                    reserve_bundle_invoices.issue_date,
                    reserve_bundle_invoices.payment_deadline,
                    NULL AS departure_date,
                    reserve_bundle_invoices.last_manager_id,
                    reserve_bundle_invoices.created_at,
                    reserve_bundle_invoices.updated_at,
                    reserve_bundle_invoices.cutoff_date, 
                    (CASE WHEN business_users.pay_altogether = " . config('consts.business_users.PAY_ALTOGETHER_YES') . " THEN 1 ELSE 0 END) as is_pay_altogether,
                    reserve_bundle_invoices.status,
                    reserve_bundle_invoices.last_note,
                    business_users.deleted_at AS business_user_deleted_at
                FROM 
                    reserve_bundle_invoices INNER JOIN business_users
                    ON reserve_bundle_invoices.business_user_id = business_users.id 
                WHERE 
                    pay_altogether = " . config('consts.business_users.PAY_ALTOGETHER_YES') ." AND reserve_bundle_invoices.deleted_at IS NULL
                GROUP BY business_user_id, cutoff_date
            UNION 
                SELECT
                    concat('ri',reserve_invoices.id) AS id,
                    NULL AS reserve_bundle_invoice_id,
                    reserve_invoices.id AS reserve_invoice_id,
                    reserve_invoices.agency_id, 
                    reserve_invoices.business_user_id, 
                    reserve_invoices.reserve_id,
                    reserves.deleted_at AS reserve_deleted_at,
                    NULL AS bundle_invoice_number,
                    reserve_invoices.applicant_name,
                    reserve_invoices.billing_address_name,
                    reserve_invoices.amount_total, 
                    reserve_invoices.deposit_amount, 
                    reserve_invoices.not_deposit_amount, 
                    reserve_invoices.issue_date,
                    reserve_invoices.payment_deadline,
                    reserve_invoices.departure_date,
                    reserve_invoices.last_manager_id,
                    reserve_invoices.created_at,
                    reserve_invoices.updated_at,
                    NULL AS cutoff_date, 
                    (CASE WHEN business_users.pay_altogether = " . config('consts.business_users.PAY_ALTOGETHER_YES') . " THEN 1 ELSE 0 END) as is_pay_altogether,
                    reserve_invoices.status,
                    reserve_invoices.last_note,
                    business_users.deleted_at AS business_user_deleted_at
                FROM reserve_invoices LEFT JOIN business_users
                    ON reserve_invoices.business_user_id = business_users.id LEFT JOIN reserves ON reserve_invoices.reserve_id = reserves.id
                WHERE 
                    (pay_altogether != " . config('consts.business_users.PAY_ALTOGETHER_YES') . " OR pay_altogether IS NULL) AND reserve_invoices.deleted_at IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS v_reserve_invoices');
        DB::statement("
        CREATE VIEW v_reserve_invoices AS 
                SELECT
                    concat('rbi',reserve_bundle_invoices.id) AS id,
                    reserve_bundle_invoices.id AS reserve_bundle_invoice_id,
                    NULL AS reserve_invoice_id,
                    reserve_bundle_invoices.agency_id, 
                    reserve_bundle_invoices.business_user_id, 
                    NULL AS reserve_id,
                    reserve_bundle_invoices.bundle_invoice_number,
                    NULL AS applicant_name,
                    reserve_bundle_invoices.billing_address_name,
                    reserve_bundle_invoices.amount_total, 
                    reserve_bundle_invoices.deposit_amount, 
                    reserve_bundle_invoices.not_deposit_amount, 
                    reserve_bundle_invoices.issue_date,
                    reserve_bundle_invoices.payment_deadline,
                    NULL AS departure_date,
                    reserve_bundle_invoices.last_manager_id,
                    reserve_bundle_invoices.created_at,
                    reserve_bundle_invoices.updated_at,
                    reserve_bundle_invoices.cutoff_date, 
                    (CASE WHEN business_users.pay_altogether = " . config('consts.business_users.PAY_ALTOGETHER_YES') . " THEN 1 ELSE 0 END) as is_pay_altogether,
                    reserve_bundle_invoices.status,
                    reserve_bundle_invoices.last_note,
                    business_users.deleted_at AS business_user_deleted_at
                FROM 
                    reserve_bundle_invoices INNER JOIN business_users
                    ON reserve_bundle_invoices.business_user_id = business_users.id 
                WHERE 
                    pay_altogether = " . config('consts.business_users.PAY_ALTOGETHER_YES') ." AND reserve_bundle_invoices.deleted_at IS NULL
                GROUP BY business_user_id, cutoff_date
            UNION 
                SELECT
                    concat('ri',reserve_invoices.id) AS id,
                    NULL AS reserve_bundle_invoice_id,
                    reserve_invoices.id AS reserve_invoice_id,
                    reserve_invoices.agency_id, 
                    reserve_invoices.business_user_id, 
                    reserve_invoices.reserve_id,
                    NULL AS bundle_invoice_number,
                    reserve_invoices.applicant_name,
                    reserve_invoices.billing_address_name,
                    reserve_invoices.amount_total, 
                    reserve_invoices.deposit_amount, 
                    reserve_invoices.not_deposit_amount, 
                    reserve_invoices.issue_date,
                    reserve_invoices.payment_deadline,
                    reserve_invoices.departure_date,
                    reserve_invoices.last_manager_id,
                    reserve_invoices.created_at,
                    reserve_invoices.updated_at,
                    NULL AS cutoff_date, 
                    (CASE WHEN business_users.pay_altogether = " . config('consts.business_users.PAY_ALTOGETHER_YES') . " THEN 1 ELSE 0 END) as is_pay_altogether,
                    reserve_invoices.status,
                    reserve_invoices.last_note,
                    business_users.deleted_at AS business_user_deleted_at
                FROM reserve_invoices LEFT JOIN business_users
                    ON reserve_invoices.business_user_id = business_users.id 
                WHERE 
                    (pay_altogether != " . config('consts.business_users.PAY_ALTOGETHER_YES') . " OR pay_altogether IS NULL) AND reserve_invoices.deleted_at IS NULL
        ");
    }
}
