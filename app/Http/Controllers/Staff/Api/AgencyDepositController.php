<?php

namespace App\Http\Controllers\Staff\Api;

use App\Models\AgencyDeposit;
use App\Events\AgencyDepositedEvent;
use App\Events\AgencyDepositChangedEvent;
use App\Events\ChangePaymentAmountEvent;
use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\AgencyDepositStoreRequest;
use App\Http\Resources\Staff\ReserveBundleInvoice\BreakdownResource;
use App\Http\Resources\Staff\VReserveInvoice\IndexResource;
use App\Services\AgencyBundleDepositService;
use App\Services\AgencyDepositService;
use App\Services\ReserveInvoiceService;
use App\Services\VReserveInvoiceService;
use Illuminate\Http\Request;

/**
 * 入金管理
 */
class AgencyDepositController extends Controller
{
    public function __construct(AgencyDepositService $agencyDepositService, ReserveInvoiceService $reserveInvoiceService, AgencyBundleDepositService $agencyBundleDepositService, VReserveInvoiceService $vReserveInvoiceService)
    {
        $this->agencyDepositService = $agencyDepositService;
        $this->agencyBundleDepositService = $agencyBundleDepositService;
        $this->reserveInvoiceService = $reserveInvoiceService;
        $this->vReserveInvoiceService = $vReserveInvoiceService;
    }

    /**
     * 入金登録
     *
     * @param string $agencyAccount 会社アカウント
     * @param int $reserveInvoiceId 請求書ID
     */
    public function store(AgencyDepositStoreRequest $request, string $agencyAccount, int $reserveInvoiceId)
    {
        $reserveInvoice = $this->reserveInvoiceService->find($reserveInvoiceId);

        // 認可チェック
        if (!$reserveInvoice) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // reserve_invoicesを使い、対象請求が操作ユーザー会社所有データであることも確認
        $response = \Gate::authorize('create', [new AgencyDeposit, $reserveInvoice]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $input = $request->all(); //カスタムフィールドがあるのでallで取得

        $input['agency_id'] = auth('staff')->user()->agency_id; // 会社IDをセット
        $input['reserve_id'] = $reserveInvoice->reserve_id; // 予約ID
        $input['reserve_invoice_id'] = $reserveInvoice->id; // 請求書ID

        try {
            $agencyDeposit = \DB::transaction(function () use ($input) {
                $agencyDeposit = $this->agencyDepositService->create($input, true, true);

                // 入金額変更処理。入金済・未入金残高計算等
                event(new AgencyDepositChangedEvent($agencyDeposit->reserve_invoice));

                // agency_bundle_depositsレコードの同時作成等
                event(new AgencyDepositedEvent($agencyDeposit));

                return $agencyDeposit;
            });

            if ($agencyDeposit) {
                // 当該入金データの親レコードとなる請求データを返す。
                // 請求管理トップページから入金登録した場合と一括請求内訳ページから入金した場合とでレスポンスするリストが異なるので、list_typeパラメータに応じて出し分け
                if ($input['list_type'] === config('consts.reserve_invoices.LIST_TYPE_BREAKDOWN')) {
                    return new BreakdownResource(
                        $this->reserveInvoiceService->find($agencyDeposit->reserve_invoice_id, ['reserve','agency_deposits.v_agency_deposit_custom_values']),
                        201
                    );
                } elseif ($input['list_type'] === config('consts.reserve_invoices.LIST_TYPE_INDEX')) {
                    return new IndexResource(
                        $this->vReserveInvoiceService->findByReserveInvoiceId($agencyDeposit->reserve_invoice_id, ['reserve','agency_deposits.v_agency_deposit_custom_values']),
                        201
                    );
                } else {
                    return response("", 201);
                }
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            return response($e->getMessage(), 409);
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * 入金データ削除
     *
     * @param string $agencyAccount 会社アカウント
     * @param int $agencyDepositlId 入金ID
     */
    public function destroy(Request $request, $agencyAccount, $agencyDepositlId)
    {
        $agencyDeposit = $this->agencyDepositService->find($agencyDepositlId);

        if (!$agencyDeposit) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        $response = \Gate::authorize('delete', $agencyDeposit);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $result = \DB::transaction(function () use ($agencyDeposit) {

            // 入金識別ID
            $depositIdentifierId = $agencyDeposit->identifier_id;

            $this->agencyDepositService->delete($agencyDeposit->id, true); // 論理削除

            // 入金額変更処理。入金済・未入金残高計算等
            event(new AgencyDepositChangedEvent(
                    $this->reserveInvoiceService->find($agencyDeposit->reserve_invoice_id)
                )
            );
            ///////

            // 一括請求用に登録している同識別IDの入金も削除する。
            // 対象レコードがない場合は処理しないので予約申込者が法人か個人かは特にチェックしなくても良いと思われる
            $this->agencyBundleDepositService->deleteByIdentifierId($depositIdentifierId, true);

            // TODO 何かしらのイベント処理が必要になるかも
            // event(new ChangePaymentAmountEvent($agencyWithdrawal->account_payable_detail_id));

            return true;
        });

        if ($result) {

            // 当該入金データの親レコードとなる請求データを返す。
            // 請求管理トップページから入金登録した場合と一括請求内訳ページから入金した場合とでレスポンスするリストが異なるので、list_typeパラメータに応じて出し分け
            if ($request->input("list_type") === config('consts.reserve_invoices.LIST_TYPE_BREAKDOWN')) {
                return new BreakdownResource(
                    $this->reserveInvoiceService->find($agencyDeposit->reserve_invoice_id, ['reserve','agency_deposits.v_agency_deposit_custom_values']),
                    200
                );
            } elseif ($request->input("list_type") === config('consts.reserve_invoices.LIST_TYPE_INDEX')) {
                return new IndexResource(
                    $this->vReserveInvoiceService->findByReserveInvoiceId($agencyDeposit->reserve_invoice_id, ['reserve','agency_deposits.v_agency_deposit_custom_values']),
                    201
                );
            } else {
                return response("", 200);
            }
        }
        abort(500);
    }
}
