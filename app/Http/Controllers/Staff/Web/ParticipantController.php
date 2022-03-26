<?php

namespace App\Http\Controllers\Staff\Web;

use App\Exceptions\ExclusiveLockException;
use App\Events\PriceRelatedChangeEvent;
use App\Events\ReserveChangeHeadcountEvent;
use App\Events\ReserveChangeRepresentativeEvent;
use App\Events\UpdateBillingAmountEvent;
use App\Traits\CancelChargeTrait;
use App\Services\WebReserveService;
use App\Services\ParticipantService;
use App\Services\ReserveParticipantPriceService;
use App\Services\ReserveParticipantAirplanePriceService;
use App\Services\ReserveParticipantHotelPriceService;
use App\Services\ReserveParticipantOptionPriceService;
use App\Services\AccountPayableDetailService;
use App\Services\ReserveItineraryService;
use App\Http\Requests\Staff\ParticipantCancelChargeUpdateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;


class ParticipantController extends Controller
{
    use CancelChargeTrait;

    public function __construct(WebReserveService $webReserveService, ParticipantService $participantService, ReserveParticipantPriceService $reserveParticipantPriceService, ReserveParticipantOptionPriceService $reserveParticipantOptionPriceService, ReserveParticipantAirplanePriceService $reserveParticipantAirplanePriceService, ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService, AccountPayableDetailService $accountPayableDetailService, ReserveItineraryService $reserveItineraryService)
    {
        $this->webReserveService = $webReserveService;
        $this->participantService = $participantService;
        // traitで使用
        $this->reserveParticipantPriceService = $reserveParticipantPriceService;
        $this->reserveParticipantOptionPriceService = $reserveParticipantOptionPriceService;
        $this->reserveParticipantAirplanePriceService = $reserveParticipantAirplanePriceService;
        $this->reserveParticipantHotelPriceService = $reserveParticipantHotelPriceService;
        $this->accountPayableDetailService = $accountPayableDetailService;
        $this->reserveItineraryService = $reserveItineraryService;
    }

    /**
     * キャンセルチャージ設定ページ
     */
    public function cancelCharge(string $agencyAccount, string $controlNumber, int $id)
    {
        $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);

        if (!$reserve) {
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        $participant = $this->participantService->find($id);
        if (!$participant) {
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        // 支払い情報を取得。第二引数は有効行程ID
        $purchasingList = $this->getPurchasingListByParticipant($participant->id, $reserve->enabled_reserve_itinerary->id, true);

        return view('staff.web.participant.cancel_charge', compact('participant', 'reserve', 'purchasingList'));
    }

    /**
     * キャンセルチャージ処理
     */
    public function cancelChargeUpdate(ParticipantCancelChargeUpdateRequest $request, string $agencyAccount, string $controlNumber, int $participantId)
    {
        $participant = $this->participantService->find($participantId);
        if (!$participant) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = \Gate::inspect('cancel', [$participant]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $input = $request->validated();

            if (!Arr::get($input, "rows")) {
                return back()->withInput()->with('error_message', "仕入データがありません。");
            }

            $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);
            if (!$reserve) {
                abort(404);
            }

            if ($reserve->updated_at != Arr::get($input, "reserve.updated_at")) {
                throw new ExclusiveLockException; // 同時編集エラー
            }

            \DB::transaction(function () use ($input, $participant, $reserve) {
                $newParticipant = $this->participantService->setCancel($participant->id);

                $this->reserveParticipantPriceService->setCancelDataByParticipantId($participant->id, 0, 0, 0, false); // 全ての仕入情報をキャンセルチャージ0円で初期化。valid=0の仕入行もこの処理でリセットされる

                // キャンセルチャージ料金を保存
                list($optionIds, $airplaneIds, $hotelIds) = $this->setParticipantCancelCharge($input);

                $this->reserveParticipantPriceService->setIsAliveCancelByReserveParticipantPriceIds($optionIds, $airplaneIds, $hotelIds); // 対象参加者商品仕入IDに対し、is_alive_cancelフラグをONに。

                $this->refreshItineraryTotalAmount($reserve->enabled_reserve_itinerary); // 有効行程の合計金額更新

                if ($participant->representative) { // 当該参加者が代表者"だった"場合
                    event(new ReserveChangeRepresentativeEvent($reserve)); // 代表者更新イベント
                }
                
                event(new ReserveChangeHeadcountEvent($reserve)); // 参加者人数変更イベント

                event(new UpdateBillingAmountEvent($this->webReserveService->find($reserve->id))); // 請求金額変更イベント
                

                event(new PriceRelatedChangeEvent($reserve->id, date('Y-m-d H:i:s', strtotime("now +1 seconds")))); // 料金変更に関わるイベント。参加者情報を更新すると関連する行程レコードもtouchで日時が更新されてしまうので、他のレコードよりも確実に新しい日時で更新されるように1秒後の時間をセット
            });

            if ($reserve->is_departed) { // 催行済の場合は催行ページへ
                // 催行済詳細ページへリダイレクト
                return redirect()->route('staff.estimates.departed.show', ['agencyAccount' => $agencyAccount, 'reserveNumber' => $controlNumber, 'tab' => config('consts.reserves.TAB_RESERVE_DETAIL')])->with('success_message', "「{$controlNumber}」のキャンセルチャージ処理が完了しました");
            } else {
                // 予約詳細ページへリダイレクト
                return redirect()->route('staff.web.estimates.reserve.show', ['agencyAccount' => $agencyAccount, 'reserveNumber' => $controlNumber, 'tab' => config('consts.reserves.TAB_RESERVE_DETAIL')])->with('success_message', "「{$controlNumber}」のキャンセルチャージ処理が完了しました");
            }

        } catch (ExclusiveLockException $e) { // 同時編集エラー
            return back()->withInput()->with('error_message', "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);

    }

}
