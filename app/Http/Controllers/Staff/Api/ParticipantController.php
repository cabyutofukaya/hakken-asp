<?php

namespace App\Http\Controllers\Staff\Api;

use App\Events\UpdateBillingAmountEvent;
use App\Traits\CancelChargeTrait;
use App\Traits\PaymentTrait;
use App\Events\ReserveChangeHeadcountEvent;
use App\Events\ReserveChangeRepresentativeEvent;
use App\Events\PriceRelatedChangeEvent;
use App\Events\ReserveChangeSumGrossEvent;
use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\CustomerSearchRequest;
use App\Http\Requests\Staff\ParticipantCancelRequest;
use App\Http\Requests\Staff\ParticipantDeleteRequest;
use App\Http\Requests\Staff\ParticipantStoreRequest;
use App\Http\Requests\Staff\ParticipantUpdateRequest;
use App\Http\Requests\Staff\RepresentativeRequest;
use App\Http\Requests\Staff\ParticipantCancelChargeUpdateRequest;
use App\Http\Resources\Staff\Participant\IndexResource;
use App\Http\Resources\Staff\Participant\StoreResource;
use App\Http\Resources\Staff\Participant\UpdateResource;
use App\Http\Resources\Staff\Reserve\BusinessCustomerResource;
use App\Http\Resources\Staff\Reserve\PersonCustomerResource;
use App\Models\BusinessUserManager;
use App\Models\Participant;
use App\Models\User;
use App\Services\BusinessUserManagerService;
use App\Services\EstimateService;
use App\Services\ParticipantService;
use App\Services\ReserveEstimateService;
use App\Services\ReserveService;
use App\Services\UserService;
use App\Services\WebEstimateService;
use App\Services\WebReserveEstimateService;
use App\Services\WebReserveService;
use App\Services\ReserveParticipantOptionPriceService;
use App\Services\ReserveParticipantAirplanePriceService;
use App\Services\ReserveParticipantHotelPriceService;
use App\Services\ReserveParticipantPriceService;
use App\Services\ReserveItineraryService;
use App\Services\AccountPayableDetailService;
use App\Services\AccountPayableReserveService;
use App\Services\AgencyWithdrawalService;
use App\Traits\ReserveTrait;
use DB;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Log;

class ParticipantController extends Controller
{
    use ReserveTrait, CancelChargeTrait, PaymentTrait;

    public function __construct(
        BusinessUserManagerService $businessUserManagerService,
        EstimateService $estimateService,
        ParticipantService $participantService,
        ReserveEstimateService $reserveEstimateService,
        ReserveService $reserveService,
        UserService $userService,
        WebEstimateService $webEstimateService,
        WebReserveEstimateService $webReserveEstimateService,
        WebReserveService $webReserveService,
        ReserveParticipantOptionPriceService $reserveParticipantOptionPriceService,
        ReserveParticipantAirplanePriceService $reserveParticipantAirplanePriceService,
        ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService,
        ReserveParticipantPriceService $reserveParticipantPriceService,
        ReserveItineraryService $reserveItineraryService,
        AccountPayableDetailService $accountPayableDetailService, 
        AccountPayableReserveService $accountPayableReserveService,
        AgencyWithdrawalService $agencyWithdrawalService
    ) {
        $this->businessUserManagerService = $businessUserManagerService;
        $this->estimateService = $estimateService;
        $this->participantService = $participantService;
        $this->reserveEstimateService = $reserveEstimateService;
        $this->reserveService = $reserveService;
        $this->userService = $userService;
        $this->webEstimateService = $webEstimateService;
        $this->webReserveEstimateService = $webReserveEstimateService;
        $this->webReserveService = $webReserveService;
        $this->reserveParticipantOptionPriceService = $reserveParticipantOptionPriceService;
        $this->reserveParticipantAirplanePriceService = $reserveParticipantAirplanePriceService;
        $this->reserveParticipantHotelPriceService = $reserveParticipantHotelPriceService;
        $this->reserveParticipantPriceService = $reserveParticipantPriceService;
        // traitで使用
        $this->reserveItineraryService = $reserveItineraryService;
        $this->accountPayableDetailService = $accountPayableDetailService;
        $this->accountPayableReserveService = $accountPayableReserveService;
        $this->agencyWithdrawalService = $agencyWithdrawalService;
    }

    /**
     * 参加者情報を取得
     *
     * @param string $reception 受付種別(WEB or ASP)
     * @param string $applicationStep 申込段階（見積or予約）
     * @param string $controlNumber 管理番号（見積番号or予約番号）
     */
    public function index(string $agencyAccount, string $reception, string $applicationStep, string $controlNumber)
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', new Participant);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 受付種別で分ける
        if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
            // 見積or予約で処理を分ける
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
                $reserve = $this->estimateService->findByEstimateNumber($controlNumber, $agencyAccount, [], ['id','updated_at']);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount, [], ['id','updated_at']);
            } else {
                abort(404);
            }

            if (!$reserve) {
                abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
            }
    
            return IndexResource::collection($this->reserveEstimateService->getParticipants($reserve->id))
                ->additional([
                    'reserve' => [
                        'updated_at' => $reserve->updated_at->format('Y-m-d H:i:s')
                    ]
                ]);
        } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
            // 見積or予約で処理を分ける
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
                $reserve = $this->webEstimateService->findByEstimateNumber($controlNumber, $agencyAccount, [], ['id','updated_at']);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount, [], ['id','updated_at']);
            } else {
                abort(404);
            }

            if (!$reserve) {
                abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
            }

            return IndexResource::collection($this->webReserveEstimateService->getParticipants($reserve->id))
                ->additional([
                    'reserve' => [
                        'updated_at' => optional($reserve->updated_at)->format('Y-m-d H:i:s')
                    ]
                ]);
        } else {
            abort(404);
        }
    }
    
    /**
     * 参加者情報を作成
     *
     * @param string $reception 受付種別(WEB or ASP)
     * @param string $applicationStep 申込段階（見積or予約）
     * @param string $controlNumber 管理番号（見積番号or予約番号）
     */
    public function store(ParticipantStoreRequest $request, string $agencyAccount, string $reception, string $applicationStep, string $controlNumber)
    {
        // 受付種別で分ける
        if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
            // 見積or予約で処理を分ける
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
                $reserve = $this->estimateService->findByEstimateNumber($controlNumber, $agencyAccount);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);
            } else {
                abort(404);
            }
        } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
            // 見積or予約で処理を分ける
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
                $reserve = $this->webEstimateService->findByEstimateNumber($controlNumber, $agencyAccount);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);
            } else {
                abort(404);
            }
        } else {
            abort(404);
        }

        if (!$reserve) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('create', [new Participant, $reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $input = $request->all();

            $result = DB::transaction(function () use ($reserve, $input) {
                $agencyId = auth('staff')->user()->agency_id;

                // 料金区分ごとにバルクインサート
                foreach ([
                        'ad_number' => config('consts.users.AGE_KBN_AD'),
                        'ch_number' => config('consts.users.AGE_KBN_CH'),
                        'inf_number' => config('consts.users.AGE_KBN_INF'),
                    ] as $numberField => $ageKbn) {
                    if (($number = Arr::get($input, $numberField))) {
                        // postで送られてくる人数は総計なので、現在の人数を引いて新規作成人数を求める
                        if ($ageKbn == config('consts.users.AGE_KBN_AD')) { // 大人
                            $num = $number - $reserve->ad_participant_count;
                        } elseif ($ageKbn == config('consts.users.AGE_KBN_CH')) { // 子供
                            $num = $number - $reserve->ch_participant_count;
                        } elseif ($ageKbn == config('consts.users.AGE_KBN_INF')) { // 幼児
                            $num = $number - $reserve->inf_participant_count;
                        } else {
                            continue;
                        }
                        $this->participantService->bulkCreate($agencyId, $reserve, $num, $ageKbn, config('consts.users.STATUS_VALID')); // ステータスは「有効」で初期化
                    }
                }

                event(new ReserveChangeHeadcountEvent($reserve)); // 参加者人数変更イベント
                
                event(new PriceRelatedChangeEvent($reserve->id, date('Y-m-d H:i:s'))); // 料金変更に関わるイベント

                return true;
            });
            if ($result) {
                // 受付種別で分ける
                if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付

                    // 最新の予約情報を取得
                    $reserve = $this->reserveService->find($reserve->id);

                    return IndexResource::collection($this->reserveEstimateService->getParticipants($reserve->id))->additional([
                        'reserve' => [
                            'reserve_itinerary_exists' => $reserve->reserve_itinerary_exists ? 1 : 0, // 当該予約に紐づく行程情報があるか否か。参加者を追加した際に行程の更新が必要か否かのメッセージを出す際に使用
                            'updated_at' => $reserve->updated_at->format('Y-m-d H:i:s')
                        ]
                    ]);
                } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付

                    // 最新の予約情報を取得
                    $reserve = $this->webReserveEstimateService->find($reserve->id);

                    return IndexResource::collection($this->webReserveEstimateService->getParticipants($reserve->id))->additional([
                        'reserve' => [
                            'reserve_itinerary_exists' => $reserve->reserve_itinerary_exists ? 1 : 0, // 当該予約に紐づく行程情報があるか否か。参加者を追加した際に行程の更新が必要か否かのメッセージを出す際に使用
                            'updated_at' => $reserve->updated_at->format('Y-m-d H:i:s')
                        ]
                    ]);
                }
            }

            // $input = $request->all();
            // $input['agency_id'] = auth('staff')->user()->agency_id; // 会社IDをセット
            // $input['reserve_id'] = $reserve->id; // reserve IDをセット

            // $participant = DB::transaction(function () use ($reserve, $input) {
            //     $participant = $this->participantService->create($reserve, $input);
    
            //     event(new ReserveChangeHeadcountEvent($reserve)); // 参加者人数変更イベント
                
            //     event(new PriceRelatedChangeEvent($reserve->id, date('Y-m-d H:i:s'))); // 料金変更に関わるイベント

            //     return $participant;
            // });
            // if ($participant) {
            //     return new StoreResource($participant, 201);
            // }
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }

    /**
     * 更新
     *
     * @param string $reception 受付種別(WEB or ASP)
     * @param string $applicationStep 申込段階（見積or予約）。処理には特に使っていない
     * @param string $controlNumber 管理番号（見積番号or予約番号）。処理には特に使っていない
     * @param string $id 参加者ID
     */
    public function update(ParticipantUpdateRequest $request, string $agencyAccount, string $reception, string $applicationStep, string $controlNumber, int $id)
    {
        $participant = $this->participantService->find($id);
        if (!$participant) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('update', [$participant]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $input = $request->all();

            if ($participant->reserve->is_canceled) { //キャンセル予約の場合は年齢区分だけ更新対象カラムから外しておく。料金に関係する箇所なので念の為
                $input = collect($input)->except(['age_kbn'])->all();
            }

            $result = DB::transaction(function () use ($participant, $input, $reception) {
                if (!$this->participantService->update($participant->id, $input)) {
                    throw new Exception("participantService update error.");
                }
                $res = $this->participantService->find($participant->id);

                // 参加者情報を更新してもマスターとなるusersレコードとは連動させないが、ageカラムがusersで編集できる箇所がないので例外的に更新
                $this->userService->upsertAgeByUserId(Arr::get($input, 'age', null), $res->user_id, $res->agency_id);

                if ($participant->representative) { // 当該参加者が代表者の場合

                    // 受付種別で分ける
                    if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
                        $reserve = $this->reserveEstimateService->find($participant->reserve_id);
                    } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
                        $reserve = $this->webReserveEstimateService->find($participant->reserve_id);
                    } else {
                        abort(500);
                    }

                    event(new ReserveChangeRepresentativeEvent($reserve)); // 代表者更新イベント
                }

                return $res;
            });
            if ($result) {
                return new UpdateResource($result, 200);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }

        abort(500);
    }

    /**
     * 代表を設定
     *
     * @param string $applicationStep 申込段階（見積or予約）。処理には特に使っていない
     * @param string $reception 受付種別(WEB or ASP)
     * @param string $controlNumber 管理番号（見積番号or予約番号）。処理には特に使っていない
     */
    public function setRepresentative(RepresentativeRequest $request, string $agencyAccount, string $reception, string $applicationStep, string $controlNumber)
    {
        $participant = $this->participantService->find($request->participant_id);
        if (!$participant) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('representative', [$participant]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $newParticipant = DB::transaction(function () use ($participant, $reception) {
                $newParticipant = $this->participantService->setRepresentative($participant->id, $participant->reserve_id);
                
                // 受付種別で分ける
                if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
                    $reserve = $this->reserveEstimateService->find($newParticipant->reserve_id);
                } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
                    $reserve = $this->webReserveEstimateService->find($newParticipant->reserve_id);
                } else {
                    abort(500);
                }
                
                event(new ReserveChangeRepresentativeEvent($reserve)); // 代表者更新イベント
    
                return $newParticipant;
            });
            if ($newParticipant) {
                return new UpdateResource($newParticipant, 200);
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }

    /**
     * 当該参加者に対する仕入データが存在するか否か
     *
     * @param string $agencyAccount 会社アカウント
     * @param int $id 参加者ID
     */
    public function isExistsPurchaseData(string $agencyAccount, int $id)
    {
        $result = $this->reserveParticipantPriceService->isExistsPurchaseDataByParticipantId($id, true);

        return ['result' => $result ? 'yes' : 'no']; // 仕入データがある場合はyes
    }

    /**
     * ノンチャージキャンセル
     *
     * @param string $reception 受付種別(WEB or ASP)
     * @param string $id 参加者ID
     */
    public function noCancelChargeCancel(ParticipantCancelRequest $request, string $agencyAccount, string $reception, int $id)
    {
        $oldParticipant = $this->participantService->find($id);
        if (!$oldParticipant) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('cancel', [$oldParticipant]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 受付種別で分ける
        if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
            $reserve = $this->reserveEstimateService->find($oldParticipant->reserve_id);
        } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
            $reserve = $this->webReserveEstimateService->find($oldParticipant->reserve_id);
        } else {
            abort(500);
        }

        try {
            $input = $request->all();

            if ($reserve->updated_at != Arr::get($input, "reserve.updated_at")) {
                throw new ExclusiveLockException;
            }

            $newParticipant = DB::transaction(function () use ($oldParticipant, $reserve, $input) {
                $newParticipant = $this->participantService->setCancel($oldParticipant->id);
                
                $this->reserveParticipantPriceService->participantNoCancelCharge($reserve, $oldParticipant->id); // 当該参加者をキャンセルチャージナシで更新

                $this->reserveParticipantPriceService->setIsAliveCancelByParticipantIdForPurchaseCancel($oldParticipant->id); // 全仕入行に対し、is_alive_cancelフラグをONにする。valid=true/falseに関係なくis_alive_cancelをonに

                if ($reserve->enabled_reserve_itinerary->id) {
                    $this->refreshItineraryTotalAmount($reserve->enabled_reserve_itinerary); // 有効行程の合計金額更新
                }

                if ($reserve->enabled_reserve_itinerary->id) {
                    event(new ReserveChangeSumGrossEvent($reserve->enabled_reserve_itinerary)); // 旅行代金変更イベント
                }

                // キャンセル時も特にoffにする必要もない気がするので一旦無効化
                // if ($oldParticipant->representative) { // 当該参加者が代表者"だった"場合
                //     event(new ReserveChangeRepresentativeEvent($reserve)); // 代表者更新イベント
                // }
    
                event(new ReserveChangeHeadcountEvent($reserve)); // 参加者人数変更イベント

                event(new UpdateBillingAmountEvent($reserve)); // 請求金額変更イベント

                event(new PriceRelatedChangeEvent($reserve->id, date('Y-m-d H:i:s'))); // 料金変更に関わるイベント

                return $newParticipant;
            });
            if ($newParticipant) {
                return new UpdateResource($newParticipant, 200);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }

    /**
     * キャンセルチャージ処理
     *
     * @param string $id 参加者ID
     */
    public function cancelChargeUpdate(ParticipantCancelChargeUpdateRequest $request, string $agencyAccount, string $reception, int $id)
    {
        $participant = $this->participantService->find($id);
        if (!$participant) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = \Gate::inspect('cancel', [$participant]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $input = $request->validated();

            if (!Arr::get($input, "rows")) {
                abort(400, "仕入データがありません。");
            }

            // 受付種別で分ける
            if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
                $reserve = $this->reserveEstimateService->find($participant->reserve_id);
            } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
                $reserve = $this->webReserveEstimateService->find($participant->reserve_id);
            } else {
                abort(500);
            }

            if ($reserve->updated_at != Arr::get($input, "reserve.updated_at")) {
                throw new ExclusiveLockException; // 同時編集エラー
            }

            \DB::transaction(function () use ($input, $participant, $reserve, $reception) {
                $newParticipant = $this->participantService->setCancel($participant->id);

                $this->reserveParticipantPriceService->setCancelDataByParticipantId($participant->id, 0, 0, 0, false); // 全ての仕入情報をキャンセルチャージ0円で初期化。valid=0の仕入行もこの処理でリセットされる

                // キャンセルチャージ料金を保存
                list($optionIds, $airplaneIds, $hotelIds) = $this->setReserveCancelCharge($input, $reserve);

                $this->reserveParticipantPriceService->setIsAliveCancelByReserveParticipantPriceIds($optionIds, $airplaneIds, $hotelIds); // 対象参加者商品仕入IDに対し、is_alive_cancelフラグをONに。

                if ($reserve->enabled_reserve_itinerary->id) {
                    $this->refreshItineraryTotalAmount($reserve->enabled_reserve_itinerary); // 有効行程の合計金額更新
                }

                if ($reserve->enabled_reserve_itinerary->id) {
                    event(new ReserveChangeSumGrossEvent($reserve->enabled_reserve_itinerary)); // 旅行代金変更イベント
                }

                // キャンセル時に代表者をOFFにする必要もない気がするので一旦無効化
                // if ($participant->representative) { // 当該参加者が代表者"だった"場合
                //     event(new ReserveChangeRepresentativeEvent($reserve)); // 代表者更新イベント
                // }
                
                event(new ReserveChangeHeadcountEvent($reserve)); // 参加者人数変更イベント


                // UpdateBillingAmountEventを実行するために再度findする必要があるのかちょっと不明。いらなければカット
                if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
                    $reserve = $this->reserveEstimateService->find($newParticipant->reserve_id);
                } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
                    $reserve = $this->webReserveEstimateService->find($newParticipant->reserve_id);
                }
                event(new UpdateBillingAmountEvent($reserve)); // 請求金額変更イベント
                

                event(new PriceRelatedChangeEvent($reserve->id, date('Y-m-d H:i:s'))); // 料金変更に関わるイベント
            });

            if ($request->input("set_message")) {
                $request->session()->flash('success_message', "「{$participant->name}」様のキャンセル処理が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
            }
            return ['result' => 'ok'];
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }

    /**
     * 参加者削除
     *
     * @param string agencyAccount 会社アカウント
     * @param string $reception 受付種別(WEB or ASP)
     * @param string $controlNumber 予約番号
     * @param string $id 参加者ID
     */
    public function destroy(ParticipantDeleteRequest $request, string $agencyAccount, string $reception, string $applicationStep, string $controlNumber, int $id)
    {
        $participant = $this->participantService->find($id);

        if (!$participant) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('delete', [$participant]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }
        
        try {
            $input = $request->all();

            if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
                $reserve = $this->reserveEstimateService->find($participant->reserve_id);
            } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
                $reserve = $this->webReserveEstimateService->find($participant->reserve_id);
            }

            if ($reserve->updated_at != Arr::get($input, "reserve.updated_at")) {
                throw new ExclusiveLockException;
            }

            // 予約データからの紐付け解除と参加者データ・参加者に紐づく料金情報の削除
            $result = DB::transaction(function () use ($participant, $reception) {

                // 受付種別で分ける
                if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
                    $result = $this->detachParticipant(
                        $participant->reserve_id,
                        $participant->id,
                        $this->reserveParticipantOptionPriceService,
                        $this->reserveParticipantAirplanePriceService,
                        $this->reserveParticipantHotelPriceService,
                        $this->participantService
                    );
                    $reserve = $this->reserveEstimateService->find($participant->reserve_id);
                } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
                    $result = $this->detachParticipant(
                        $participant->reserve_id,
                        $participant->id,
                        $this->reserveParticipantOptionPriceService,
                        $this->reserveParticipantAirplanePriceService,
                        $this->reserveParticipantHotelPriceService,
                        $this->participantService
                    );
                    $reserve = $this->webReserveEstimateService->find($participant->reserve_id);
                } else {
                    abort(404);
                }

                if ($participant->representative) { // 当該参加者が代表者"だった"場合
                    event(new ReserveChangeRepresentativeEvent($reserve)); // 代表者更新イベント
                }

                event(new ReserveChangeHeadcountEvent($reserve)); // 参加者人数変更イベント

                event(new PriceRelatedChangeEvent($reserve->id, date('Y-m-d H:i:s')));

                return $result;
            });
            if ($result) {

                // 最新予約情報を取得
                if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
                    $reserve = $this->reserveEstimateService->find($participant->reserve_id);
                } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
                    $reserve = $this->webReserveEstimateService->find($participant->reserve_id);
                }

                return [
                    'data' => [
                        'reserve' => [
                            'reserve_itinerary_exists' => $reserve->reserve_itinerary_exists ? 1 : 0,
                            'updated_at' => $reserve->updated_at->format('Y-m-d H:i:s')
                            ]
                        ]
                    ];
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }

    /**
     * 顧客検索API
     */
    public function participantSearch(CustomerSearchRequest $request, $agencyAccount)
    {
        $agencyId = auth('staff')->user()->agency_id;

        if ($request->participant_type === config('consts.reserves.PARTICIPANT_TYPE_PERSON')) {

            // 認可チェック。usersの閲覧権限でチェック
            $response = Gate::authorize('viewAny', new User);
            if (!$response->allowed()) {
                abort(403, $response->message());
            }

            // 念の為、取得件数を50件に制限
            return PersonCustomerResource::collection(
                $this->userService->applicantSearch(
                    $agencyId,
                    $request->name,
                    $request->user_number,
                    ['userable.user','userable.user_ext'],
                    [],
                    50,
                    $request->get_deleted === 'true'
                )
            );
        } elseif ($request->participant_type === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')) {

            // 認可チェック。business_user_managersの閲覧権限でチェック
            $response = Gate::authorize('viewAny', new BusinessUserManager);
            if (!$response->allowed()) {
                abort(403, $response->message());
            }

            // 念の為、取得件数を100件に制限
            return
            BusinessCustomerResource::collection(
                $this->businessUserManagerService->applicantSearch(
                    $agencyId,
                    $request->name,
                    $request->user_number,
                    ['business_user.kbns'],
                    [],
                    100,
                    $request->get_deleted === 'true'
                )
            );
        }
        abort(400);
    }
}
