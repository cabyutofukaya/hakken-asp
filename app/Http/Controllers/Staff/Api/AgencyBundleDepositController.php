<?php

namespace App\Http\Controllers\Staff\Api;

use App\Models\AgencyBundleDeposit;
use App\Events\AgencyBundleDepositChangedEvent;
use App\Events\AgencyDepositChangedEvent;
use App\Exceptions\DepositAmountException;
use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\AgencyBundleDepositStoreRequest;
use App\Http\Resources\Staff\VReserveInvoice\IndexResource;
use App\Services\AgencyBundleDepositService;
use App\Services\AgencyDepositService;
use App\Services\ReserveBundleInvoiceService;
use App\Services\ReserveInvoiceService;
use App\Services\VReserveInvoiceService;
use Illuminate\Http\Request;

/**
 * 一括入金管理
 */
class AgencyBundleDepositController extends Controller
{
    public function __construct(AgencyDepositService $agencyDepositService, ReserveInvoiceService $reserveInvoiceService, AgencyBundleDepositService $agencyBundleDepositService, VReserveInvoiceService $vReserveInvoiceService, ReserveBundleInvoiceService $reserveBundleInvoiceService)
    {
        $this->agencyDepositService = $agencyDepositService;
        $this->agencyBundleDepositService = $agencyBundleDepositService;
        $this->reserveInvoiceService = $reserveInvoiceService;
        $this->vReserveInvoiceService = $vReserveInvoiceService;
        $this->reserveBundleInvoiceService = $reserveBundleInvoiceService;
    }

    /**
     * 一括請求レコードの入金登録
     */
    public function store(AgencyBundleDepositStoreRequest $request, $agencyAccount, $reserveBundleInvoiceId)
    {
        $reserveBundleInvoice = $this->reserveBundleInvoiceService->find($reserveBundleInvoiceId);

        // 認可チェック
        if (!$reserveBundleInvoice) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // reserve_bundle_invoicesを使い、対象請求が操作ユーザー会社所有データであることも確認
        $response = \Gate::authorize('create', [new AgencyBundleDeposit, $reserveBundleInvoice]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $input = $request->all(); //カスタムフィールドがあるのでallで取得

        $input['agency_id'] = auth('staff')->user()->agency_id; // 会社IDをセット
        $input['reserve_bundle_invoice_id'] = $reserveBundleInvoice->id; // 請求書ID

        try {
            $agencyBundleDeposit = \DB::transaction(function () use ($agencyAccount, $reserveBundleInvoiceId, $input) {

                /**
                 * 一括請求テーブルへの入金登録は一括請求テーブルと通常請求テーブルの両方に行う
                 *
                 * 順番は
                 * 通常請求テーブル → 一括請求テーブルの順に行う
                 */

                // 入金識別IDを発行
                $input['identifier_id'] = $this->agencyBundleDepositService->generateIdentifierId();

                $balance = (int)$input['amount']; // 残高を保持
                unset($input['amount']);

                //　通常請求テーブルに入金処理を実施
                $reserveInvoices = $this->reserveInvoiceService->getByReserveBundleInvoiceId($agencyAccount, $reserveBundleInvoiceId, [], [], false);
                foreach ($reserveInvoices as $reserveInvoice) {

                    if ($reserveInvoice->sum_not_deposit === 0) {
                        continue; // 未入金額0円の場合は処理ナシ
                    }
                    
                    $amount = $reserveInvoice->sum_not_deposit; // 入金額
                    $balance -= $amount;

                    $agencyDeposit = $this->agencyDepositService->create(
                        array_merge(
                            collect($input)->except([
                                'reserve_bundle_invoice_id',
                                'reserve_bundle_invoice'
                            ])->toArray(), // 念の為不要な項目は除去
                            [
                                'reserve_id' => $reserveInvoice->reserve_id,
                                'reserve_invoice_id' => $reserveInvoice->id,
                                'amount' => $amount,
                            ]
                        ),
                        false, // 入金識別IDは作成済みなので生成しない
                        false // このタイミングではagency_depositsテーブルの同時編集チェックはしなくて良いと思われる
                    );

                    // 入金額変更処理。入金済・未入金残高計算等
                    event(new AgencyDepositChangedEvent($agencyDeposit->reserve_invoice));
                    ////////

                    // 同じ入金額を一括請求レコードに登録
                    $agencyBundleDeposit = $this->agencyBundleDepositService->create(
                        array_merge(
                            $input,
                            ['amount' => $amount]
                        ),
                        false, // 入金識別IDは作成済みなので生成しない
                        $reserveInvoice === reset($reserveInvoices) // loop初回時のみagency_bundle_depositsレコードへの同時編集チェックを実施
                    );

                    // 入金額変更処理。入金済・未入金残高計算等
                    event(new AgencyBundleDepositChangedEvent($agencyBundleDeposit->reserve_bundle_invoice));
                    //////////////
                }

                if ($balance !== 0) { // 個別請求の未入金計と入金額が合っていなければエラー
                    throw new DepositAmountException("入金額が正しくありません。請求額をご確認ください。\n請求書を最新情報で保存していない場合は書類の保存状態をご確認の上、再度ご入力お願いします。");
                }

                return $agencyBundleDeposit;
            });

            if ($agencyBundleDeposit) {
                // 当該入金データの親レコードとなる請求データを返す。
                return new IndexResource(
                    $this->vReserveInvoiceService->findByReserveBundleInvoiceId($agencyBundleDeposit->reserve_bundle_invoice_id, [
                        'reserve',
                        'agency_bundle_deposits.v_agency_bundle_deposit_custom_values',
                        'agency_deposits.v_agency_deposit_custom_values',
                    ]),
                    201
                );
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            return response($e->getMessage(), 409);
        } catch (DepositAmountException $e) { // 金額エラー
            return response($e->getMessage(), 400);
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * 一括入金レコードの入金データ削除
     */
    public function destroy(Request $request, $agencyAccount, $agencyBundleDepositlId)
    {
        $agencyBundleDeposit = $this->agencyBundleDepositService->find($agencyBundleDepositlId);

        if (!$agencyBundleDeposit) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        $response = \Gate::authorize('delete', $agencyBundleDeposit);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $result = \DB::transaction(function () use ($agencyBundleDeposit) {

            // 入金識別ID
            $depositIdentifierId = $agencyBundleDeposit->identifier_id;

            $this->agencyBundleDepositService->delete($agencyBundleDeposit->id, true); // 論理削除

            // 入金額変更処理。入金済・未入金残高計算等
            event(new AgencyBundleDepositChangedEvent($agencyBundleDeposit->reserve_bundle_invoice));


            // 通常請求用に登録している同識別IDの入金も削除する。
            // 対象レコードがない場合は処理しないので予約申込者が法人か個人かは特にチェックしなくても良いと思われる
            $this->agencyDepositService->deleteByIdentifierId($depositIdentifierId, true);

            return true;
        });

        if ($result) {
            // 当該入金データの親レコードとなる請求データを返す。
            return new IndexResource(
                $this->vReserveInvoiceService->findByReserveBundleInvoiceId($agencyBundleDeposit->reserve_bundle_invoice_id, [
                    'reserve',
                    'agency_bundle_deposits.v_agency_bundle_deposit_custom_values',
                    'agency_deposits.v_agency_deposit_custom_values',
                ]),
                201
            );
        }
        abort(500);
    }
}
