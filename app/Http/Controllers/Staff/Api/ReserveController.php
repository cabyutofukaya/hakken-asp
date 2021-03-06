<?php

namespace App\Http\Controllers\Staff\Api;

use App\Events\PriceRelatedChangeEvent;
use App\Events\ReserveChangeHeadcountEvent;
use App\Events\ReserveUpdateStatusEvent;
use App\Events\UpdateBillingAmountEvent;
use App\Events\ReserveChangeSumGrossEvent;
use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\CheckReserveScheduleChangeRequest;
use App\Http\Requests\Staff\ReserveCancelChargeUpdateRequest;
use App\Http\Requests\Staff\ReserveNoCancelChargeCancelRequest;
use App\Http\Requests\Staff\ReserveStatusUpdateRequest;
use App\Http\Resources\Staff\Reserve\IndexResource;
use App\Http\Resources\Staff\Reserve\ShowResource;
use App\Http\Resources\Staff\Reserve\StatusResource;
use App\Http\Resources\Staff\Reserve\VAreaResource;
use App\Models\Reserve;
use App\Services\AccountPayableDetailService;
use App\Services\AccountPayableReserveService;
use App\Services\AgencyWithdrawalService;
use App\Services\BusinessUserManagerService;
use App\Services\ParticipantService;
use App\Services\ReserveCustomValueService;
use App\Services\ReserveItineraryService;
use App\Services\ReserveParticipantAirplanePriceService;
use App\Services\ReserveParticipantHotelPriceService;
use App\Services\ReserveParticipantOptionPriceService;
use App\Services\ReserveParticipantPriceService;
use App\Services\ReserveService;
use App\Services\UserCustomItemService;
use App\Services\UserService;
use App\Services\VAreaService;
use App\Traits\CancelChargeTrait;
use App\Traits\PaymentTrait;
use DB;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Log;

class ReserveController extends Controller
{
    use CancelChargeTrait, PaymentTrait;
    
    public function __construct(UserService $userService, BusinessUserManagerService $businessUserManagerService, VAreaService $vAreaService, ReserveService $reserveService, ReserveCustomValueService $reserveCustomValueService, UserCustomItemService $userCustomItemService, ReserveParticipantPriceService $reserveParticipantPriceService, ReserveItineraryService $reserveItineraryService, ParticipantService $participantService, ReserveParticipantOptionPriceService $reserveParticipantOptionPriceService, ReserveParticipantAirplanePriceService $reserveParticipantAirplanePriceService, ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService, AccountPayableDetailService $accountPayableDetailService, AccountPayableReserveService $accountPayableReserveService, AgencyWithdrawalService $agencyWithdrawalService)
    {
        $this->reserveService = $reserveService;
        $this->userService = $userService;
        $this->businessUserManagerService = $businessUserManagerService;
        $this->vAreaService = $vAreaService;
        $this->reserveCustomValueService = $reserveCustomValueService;
        $this->userCustomItemService = $userCustomItemService;
        $this->reserveParticipantPriceService = $reserveParticipantPriceService;
        $this->reserveItineraryService = $reserveItineraryService;
        $this->participantService = $participantService;
        // ?????????????????????
        $this->reserveParticipantOptionPriceService = $reserveParticipantOptionPriceService;
        $this->reserveParticipantAirplanePriceService = $reserveParticipantAirplanePriceService;
        $this->reserveParticipantHotelPriceService = $reserveParticipantHotelPriceService;
        $this->accountPayableDetailService = $accountPayableDetailService;
        $this->accountPayableReserveService = $accountPayableReserveService;
        $this->agencyWithdrawalService = $agencyWithdrawalService;
    }

    // ????????????
    public function show($agencyAccount, $reserveNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($reserveNumber, $agencyAccount);

        if (!$reserve) {
            abort(404, "??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????");
        }

        // ??????????????????
        $response = Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return new ShowResource($reserve);
    }

    // ??????
    public function index($agencyAccount)
    {
        // ??????????????????
        $response = Gate::authorize('viewAny', new Reserve);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // ?????????????????????????????????????????????????????????
        // applicant -> ?????????
        // representative -> ???????????????
        $params = [];
        foreach (request()->all() as $key => $val) {
            if (in_array($key, ['control_number', 'departure_date', 'return_date', 'departure', 'destination', 'applicant', 'representative']) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // ?????????????????????????????????????????????????????????

                if (in_array($key, ['departure_date','return_date'], true) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_CALENDAR_PREFIX')) === 0) { // ?????????????????????????????????????????????YYYY/MM/DD ??? YYYY-MM-DD????????????
                    $params[$key] = !is_empty($val) ? date('Y-m-d', strtotime($val)) : null;
                } else {
                    $params[$key] = $val;
                }
            }
        }

        return IndexResource::collection($this->reserveService->paginateByAgencyAccount(
            $agencyAccount,
            $params,
            request()->get("per_page", 10),
            [
                'manager',
                'departure',
                'destination',
                'travel_types',
                'statuses',
                'application_dates',
                'applicantable',
                'representatives.user'
            ]
        ));
    }

    /**
     * ??????????????????
     */
    public function vAreaSearch(Request $request, $agencyAccount)
    {
        // // ???????????????????????????????????????????????????????????????
        // $response = Gate::authorize('viewAny', new Reserve);
        // if (!$response->allowed()) {
        //     abort(403, $response->message());
        // }
        
        return VAreaResource::collection(
            $this->vAreaService->search(
                $agencyAccount,
                $request->area,
                [],
                ['uuid','code','name'],
                50
            )
        );
    }

    /**
     * ?????????????????????
     */
    public function statusUpdate(ReserveStatusUpdateRequest $request, $agencyAccount, $reserveNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($reserveNumber, $agencyAccount);
        
        if (!$reserve) {
            abort(404, "??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????");
        }

        $response = Gate::authorize('updateStatus', $reserve);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $input = $request->all();

            // ???????????????????????????????????????????????????????????????????????????
            // // ??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????
            // if ($reserve->updated_at != Arr::get($input, 'updated_at')) {
            //     throw new ExclusiveLockException;
            // }

            // ?????????????????????????????????????????????
            $customStatus =$this->userCustomItemService->findByCodeForAgency($reserve->agency_id, config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS'), ['key'], null);
    
            if ($customStatus) {
                $this->reserveCustomValueService->upsertCustomFileds([$customStatus->key => $input['status']], $reserve->id); // ?????????????????????????????????
    
                // ?????????????????????????????????
                $newReserve = $this->reserveService->find($reserve->id);
                event(new ReserveUpdateStatusEvent($newReserve));
                
                return new StatusResource($newReserve);
            }
        } catch (ExclusiveLockException $e) { // ?????????????????????
            abort(409, "??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????");
        } catch (Exception $e) {
            Log::error($e);
        }
        return abort(500);
    }

    /**
     * ???????????????????????????????????????????????????
     * ??????????????????????????????????????????Staff/Api/Web/ReserveController@noCancelChargeCancel ?????????????????????
     *
     * @param string $reserveNumber ????????????
     */
    public function noCancelChargeCancel(ReserveNoCancelChargeCancelRequest $request, $agencyAccount, $reserveNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($reserveNumber, $agencyAccount);

        if (!$reserve) {
            abort(404, "??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????");
        }

        // ??????????????????
        $response = Gate::inspect('cancel', [$reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {

            // ????????????????????????
            if ($reserve->updated_at != $request->updated_at) {
                throw new ExclusiveLockException;
            }

            $result = \DB::transaction(function () use ($reserve) {
                $this->participantService->setCancelByReserveId($reserve->id); // ????????????????????????????????????????????????

                $this->reserveService->cancel($reserve, false); // ????????????????????????????????????????????????ON

                $this->reserveParticipantPriceService->reserveNoCancelCharge($reserve, $reserve->enabled_reserve_itinerary->id); // ???????????????????????????????????????????????????0???????????????

                $this->reserveParticipantPriceService->setIsAliveCancelByReserveId($reserve->id, $reserve->enabled_reserve_itinerary->id); // ????????????????????????is_alive_cancel????????????ON???purchase_type???PURCHASE_CANCEL)?????????

                if ($reserve->enabled_reserve_itinerary->id) {
                    $this->refreshItineraryTotalAmount($reserve->enabled_reserve_itinerary); // ?????????????????????????????????
                }

                if ($reserve->enabled_reserve_itinerary->id) {
                    event(new ReserveChangeSumGrossEvent($reserve->enabled_reserve_itinerary)); // ??????????????????????????????
                }

                event(new ReserveChangeHeadcountEvent($reserve)); // ?????????????????????????????????

                event(new UpdateBillingAmountEvent($this->reserveService->find($reserve->id))); // ??????????????????????????????

                /**???????????????????????????????????????????????????????????? */

                // ?????????????????????????????????????????????
                $customStatus =$this->userCustomItemService->findByCodeForAgency($reserve->agency_id, config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS'), ['key'], null);

                if ($customStatus) {
                    $this->reserveCustomValueService->upsertCustomFileds([$customStatus->key => config('consts.reserves.RESERVE_CANCEL_STATUS')], $reserve->id);

                    // ?????????????????????????????????
                    event(new ReserveUpdateStatusEvent($this->reserveService->find($reserve->id)));
                }

                event(new PriceRelatedChangeEvent($reserve->id, date('Y-m-d H:i:s', strtotime("now +1 seconds")))); // ??????????????????????????????????????????????????????????????????touch?????????????????????????????????????????????????????????????????????????????????????????????1???????????????????????????

                return true;
            });

            if ($result) {
                if ($request->input("set_message")) {
                    $request->session()->flash('success_message', "{$reserve->control_number}???????????????????????????????????????????????????"); // set_message??????????????????????????????????????????????????????????????????????????????????????????
                }

                return ['result' => 'ok'];
            }
        } catch (ExclusiveLockException $e) { // ?????????????????????
            abort(409, "??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????");
        } catch (Exception $e) {
            Log::error($e);
        }
        return abort(500);
    }

    /**
     * ?????????????????????????????????
     * ??????????????????????????????????????????Staff/Api/Web/ReserveController@cancelChargeUpdate ?????????????????????
     */
    public function cancelChargeUpdate(ReserveCancelChargeUpdateRequest $request, string $agencyAccount, string $controlNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

        // ??????????????????
        $response = Gate::inspect('cancel', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        try {
            $input = $request->validated();

            // ????????????????????????
            if ($reserve->updated_at != Arr::get($input, 'reserve.updated_at')) {
                throw new ExclusiveLockException;
            }

            $result = DB::transaction(function () use ($input, $reserve) {
                $this->participantService->setCancelByReserveId($reserve->id); // ????????????????????????????????????????????????

                $this->reserveService->cancel($reserve, true);

                // ??????????????????????????????????????????
                $this->setReserveCancelCharge($input, $reserve);

                $this->reserveParticipantPriceService->setIsAliveCancelByReserveId($reserve->id, $reserve->enabled_reserve_itinerary->id); // ????????????????????????is_alive_cancel????????????ON???purchase_type???PURCHASE_CANCEL)?????????

                if ($reserve->enabled_reserve_itinerary->id) {
                    $this->refreshItineraryTotalAmount($reserve->enabled_reserve_itinerary); // ?????????????????????????????????
                }

                if ($reserve->enabled_reserve_itinerary->id) {
                    event(new ReserveChangeSumGrossEvent($reserve->enabled_reserve_itinerary)); // ??????????????????????????????
                }

                event(new ReserveChangeHeadcountEvent($reserve)); // ?????????????????????????????????

                event(new UpdateBillingAmountEvent($this->reserveService->find($reserve->id))); // ??????????????????????????????

                /**???????????????????????????????????????????????????????????? */

                // ?????????????????????????????????????????????
                $customStatus =$this->userCustomItemService->findByCodeForAgency($reserve->agency_id, config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS'), ['key'], null);

                if ($customStatus) {
                    $this->reserveCustomValueService->upsertCustomFileds([$customStatus->key => config('consts.reserves.RESERVE_CANCEL_STATUS')], $reserve->id);

                    // ?????????????????????????????????
                    event(new ReserveUpdateStatusEvent($this->reserveService->find($reserve->id)));
                }

                event(new PriceRelatedChangeEvent($reserve->id, date('Y-m-d H:i:s', strtotime("now +1 seconds")))); // ????????????????????????????????????????????????????????????touch???????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????1???????????????????????????

                return true;
            });

            if ($result) {
                if ($request->input("set_message")) {
                    $request->session()->flash('success_message', "{$reserve->control_number}???????????????????????????????????????????????????"); // set_message??????????????????????????????????????????????????????????????????????????????????????????
                }
                return ['result' => 'ok'];
            }
        } catch (ExclusiveLockException $e) { // ?????????????????????
            abort(409, "??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????");
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }

    /**
     * ??????????????????????????????????????????
     * 
     * @param stirng $reception ????????????
     */
    public function checkScheduleChange(CheckReserveScheduleChangeRequest $request, string $agencyAccount, string $reception, string $controlNumber)
    {
        return ['result' => 'ok'];
    }


    /**
     * ????????????
     *
     * @param string $reserveNumber ????????????
     */
    public function destroy(Request $request, $agencyAccount, $reserveNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($reserveNumber, $agencyAccount);

        if (!$reserve) {
            abort(404, "??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????");
        }

        // ??????????????????
        $response = Gate::inspect('delete', [$reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        if ($this->reserveService->delete($reserve->id, true)) { // ????????????

            if ($request->input("set_message")) {
                $request->session()->flash('decline_message', "{$reserveNumber}????????????????????????????????????"); // set_message??????????????????????????????????????????????????????????????????????????????????????????
            }

            return response('', 200);
        }
        abort(500);
    }
}
