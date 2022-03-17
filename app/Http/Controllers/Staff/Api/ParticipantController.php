<?php

namespace App\Http\Controllers\Staff\Api;

use App\Events\ReserveChangeHeadcountEvent;
use App\Events\ReserveChangeRepresentativeEvent;
use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\CustomerSearchRequest;
use App\Http\Requests\Staff\ParticipantCancelRequest;
use App\Http\Requests\Staff\ParticipantDeleteRequest;
use App\Http\Requests\Staff\ParticipantStoreRequest;
use App\Http\Requests\Staff\ParticipantUpdateRequest;
use App\Http\Requests\Staff\RepresentativeRequest;
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
use App\Traits\ReserveTrait;
use DB;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Log;

class ParticipantController extends Controller
{
    use ReserveTrait;

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
        ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService
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
                $reserve = $this->estimateService->findByEstimateNumber($controlNumber, $agencyAccount, [], ['id']);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount, [], ['id']);
            } else {
                abort(404);
            }

            if (!$reserve) {
                abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
            }
    
            return IndexResource::collection($this->reserveEstimateService->getParticipants($reserve->id));
        } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
            // 見積or予約で処理を分ける
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
                $reserve = $this->webEstimateService->findByEstimateNumber($controlNumber, $agencyAccount, [], ['id']);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount, [], ['id']);
            } else {
                abort(404);
            }

            if (!$reserve) {
                abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
            }
    
            return IndexResource::collection($this->webReserveEstimateService->getParticipants($reserve->id));
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
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('create', [new Participant, $reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $input = $request->all();
            $input['agency_id'] = auth('staff')->user()->agency_id; // 会社IDをセット
            $input['reserve_id'] = $reserve->id; // reserve IDをセット

            $participant = DB::transaction(function () use ($reserve, $input) {
                $participant = $this->participantService->create($reserve, $input);
    
                event(new ReserveChangeHeadcountEvent($reserve)); // 参加者人数変更イベント
    
                return $participant;
            });
            if ($participant) {
                return new StoreResource($participant, 201);
            }
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
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
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
                $res = $this->participantService->update($participant->id, $input);

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
            abort(409, "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
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
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
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
     * 取消
     *
     * @param string $applicationStep 申込段階（見積or予約）。本パラメータは処理には特に使っていない
     * @param string $reception 受付種別(WEB or ASP)
     * @param string $controlNumber 管理番号（見積番号or予約番号）。処理には特に使っていない
     * @param string $id 参加者ID
     */
    public function setCancel(ParticipantCancelRequest $request, string $agencyAccount, string $reception, string $applicationStep, string $controlNumber, int $id)
    {
        $oldParticipant = $this->participantService->find($id);
        if (!$oldParticipant) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('cancel', [$oldParticipant]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $newParticipant = DB::transaction(function () use ($oldParticipant, $reception) {
                $newParticipant = $this->participantService->setCancel($oldParticipant->id);
    
                // 受付種別で分ける
                if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
                    $reserve = $this->reserveEstimateService->find($oldParticipant->reserve_id);
                } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
                    $reserve = $this->webReserveEstimateService->find($oldParticipant->reserve_id);
                } else {
                    abort(500);
                }

                if ($oldParticipant->representative) { // 当該参加者が代表者"だった"場合
                    event(new ReserveChangeRepresentativeEvent($reserve)); // 代表者更新イベント
                }
    
                event(new ReserveChangeHeadcountEvent($reserve)); // 参加者人数変更イベント
    
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
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('delete', [$participant]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
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

                return $result;
            });
            if ($result) {
                return response('', 200);
            }
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

            // 念の為、取得件数を50件に制限
            return
            BusinessCustomerResource::collection(
                $this->businessUserManagerService->applicantSearch(
                    $agencyId,
                    $request->name,
                    $request->user_number,
                    ['business_user.kbns'],
                    [],
                    50,
                    $request->get_deleted === 'true'
                )
            );
        }
        abort(400);
    }
}
