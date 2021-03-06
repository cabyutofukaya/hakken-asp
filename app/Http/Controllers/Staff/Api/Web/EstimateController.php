<?php

namespace App\Http\Controllers\Staff\Api\Web;

use Hashids;
use App\Exceptions\ExclusiveLockException;
use App\Events\ReserveUpdateStatusEvent;
use App\Events\WebMessageSendEvent;
use App\Events\CreateItineraryEvent;
use App\Events\ReserveChangeRepresentativeEvent;
use App\Events\ReserveChangeHeadcountEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\EstimateConsentRequest;
use App\Http\Requests\Staff\EstimateDetermineRequest;
use App\Http\Requests\Staff\EstimateStatusUpdateRequest;
use App\Http\Resources\Staff\WebEstimate\IndexResource;
use App\Http\Resources\Staff\WebEstimate\ShowResource;
use App\Http\Resources\Staff\WebEstimate\StatusResource;
use App\Http\Resources\Staff\WebReserveExt\ShowResource as WebReserveExtResource;
use App\Models\BusinessUserManager;
use App\Models\Reserve;
use App\Models\User;
use App\Services\WebReserveEstimateService;
use App\Services\WebReserveExtService;
use App\Services\WebEstimateService;
use App\Services\ReserveInvoiceService;
use App\Services\ReserveCustomValueService;
use App\Services\UserCustomItemService;
use App\Services\UserService;
use App\Services\VAreaService;
use App\Services\WebMessageService;
use DB;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Log;
use Illuminate\Support\Arr;

class EstimateController extends Controller
{
    public function __construct(UserService $userService, VAreaService $vAreaService, WebReserveEstimateService $webReserveEstimateService, WebEstimateService $webEstimateService, ReserveCustomValueService $reserveCustomValueService, UserCustomItemService $userCustomItemService, ReserveInvoiceService $reserveInvoiceService, WebReserveExtService $webReserveExtService, WebMessageService $webMessageService)
    {
        $this->webReserveEstimateService = $webReserveEstimateService;
        $this->webEstimateService = $webEstimateService;
        $this->userService = $userService;
        $this->vAreaService = $vAreaService;
        $this->reserveCustomValueService = $reserveCustomValueService;
        $this->userCustomItemService = $userCustomItemService;
        $this->reserveInvoiceService = $reserveInvoiceService;
        $this->webReserveExtService = $webReserveExtService;
        $this->webMessageService = $webMessageService;
    }

    // ????????????
    public function show(string $agencyAccount, string $estimateNumber)
    {
        $estimate = $this->webEstimateService->findByEstimateNumber($estimateNumber, $agencyAccount);

        if (!$estimate) {
            abort(404, "??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????");
        }

        // ??????????????????
        $response = Gate::inspect('view', [$estimate]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return new ShowResource($estimate);
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
            if (in_array($key, ['record_number', 'departure_date', 'return_date', 'departure', 'destination', 'applicant', 'representative']) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // ?????????????????????????????????????????????????????????

                if (in_array($key, ['departure_date','return_date'], true) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_CALENDAR_PREFIX')) === 0) { // ?????????????????????????????????????????????YYYY/MM/DD ??? YYYY-MM-DD????????????
                    $params[$key] = !is_empty($val) ? date('Y-m-d', strtotime($val)) : null;
                } else {
                    $params[$key] = $val;
                }
            }
        }

        return IndexResource::collection(
            $this->webEstimateService->paginateByAgencyAccount(
                $agencyAccount,
                $params,
                request()->get("per_page", 10),
                [
                    'web_reserve_ext.web_online_schedule',
                    'manager',
                    'departure',
                    'destination',
                    'travel_type',
                    'application_type',
                    'statuses',
                    'application_date',
                    'applicantable',
                    'representatives.user'
                ]
            )
        );
    }

    // /**
    //  * Http/Controllers/Staff/Api/EstimateController@determine?????????
    //  * ??????????????????
    //  * ????????????????????????????????????
    //  *
    //  * @param string $agencyAccount ?????????????????????
    //  */
    // public function determine(EstimateDetermineRequest $request, $agencyAccount, $estimateNumber)
    // {
    //     $estimate = $this->webEstimateService->findByEstimateNumber($estimateNumber, $agencyAccount);

    //     if (!$estimate) {
    //         abort(404, "??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????");
    //     }

    //     // ??????????????????
    //     $response = Gate::inspect('update', [$estimate]);
    //     if (!$response->allowed()) {
    //         abort(403, $response->message());
    //     }

    //     // TODO ???????????????????????????????????????

    //     $input = $request->only('updated_at');
    //     try {
    //         $reserve = DB::transaction(function () use ($estimate, $input) {
    //             if ($this->webEstimateService->determine(
    //                 $estimate,
    //                 $input,
    //                 $this->userCustomItemService,
    //                 $this->reserveCustomValueService,
    //                 $this->webReserveEstimateService
    //             )) {
    //                 // ?????????????????????????????????????????????????????????????????????????????????????????????????????????
    //                 $reserve = $this->webReserveEstimateService->find($estimate->id);

    //                 if ($reserve->enabled_reserve_itinerary->id) {
    //                     event(new CreateItineraryEvent($reserve->enabled_reserve_itinerary));
    //                 }

    //                 // ?????????????????????????????????
    //                 event(new ReserveUpdateStatusEvent($reserve));
                    
    //                 return $reserve;
    //             }
    //         });

    //         if ($reserve) {
    //             if ($request->input("set_message")) {
    //                 $request->session()->flash('success_message', "??????????????????????????????????????????{$reserve->control_number}??????"); // set_message??????????????????????????????????????????????????????????????????????????????????????????
    //             }
    //             return response('', 200);
    //         }

    //     } catch (ExclusiveLockException $e) { // ?????????????????????
    //         abort(409, "??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????");

    //     } catch (Exception $e) {
    //         Log::error($e);
    //     }
    //     abort(500);
    // }

    /**
     * ?????????????????????
     */
    public function statusUpdate(EstimateStatusUpdateRequest $request, $agencyAccount, $estimateNumber)
    {
        $estimate = $this->webEstimateService->findByEstimateNumber($estimateNumber, $agencyAccount);

        if (!$estimate) {
            abort(404, "??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????");
        }

        $response = Gate::authorize('updateStatus', $estimate);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $input = $request->all();

            // ???????????????????????????????????????????????????????????????????????????
            // // ??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????
            // if ($estimate->updated_at != Arr::get($input, 'updated_at')) {
            //     throw new ExclusiveLockException;
            // }

            // ?????????????????????????????????????????????
            $customStatus = $this->userCustomItemService->findByCodeForAgency($estimate->agency_id, config('consts.user_custom_items.CODE_APPLICATION_ESTIMATE_STATUS'), ['key'], null);
    
            if ($customStatus) {
                $this->reserveCustomValueService->upsertCustomFileds([$customStatus->key => $input['status']], $estimate->id); // ?????????????????????????????????
    
                // ?????????????????????????????????
                $newEstimate = $this->webEstimateService->find($estimate->id);
                event(new ReserveUpdateStatusEvent($newEstimate));
                
                return new StatusResource($newEstimate);
            }
        } catch (ExclusiveLockException $e) { // ?????????????????????
            abort(409, "??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????");
        } catch (Exception $e) {
            Log::error($e);
        }
        return abort(500);
    }

    /**
     * ????????????
     *
     * @param string $hashId ????????????ID
     */
    public function destroy(Request $request, $agencyAccount, string $hashId)
    {
        $id = Hashids::decode($hashId)[0] ?? 0;
        $reserve = $this->webEstimateService->find((int)$id);

        if (!$reserve) {
            abort(404, "??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????");
        }

        // ??????????????????
        $response = Gate::inspect('delete', [$reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        if ($this->webEstimateService->delete($reserve->id, true)) { // ????????????

            if ($request->input("set_message")) {
                $request->session()->flash('decline_message', "{$reserve->estimate_number}????????????????????????????????????"); // set_message??????????????????????????????????????????????????????????????????????????????????????????
            }

            return response('', 200);
        }
        abort(500);
    }

    /**
     * ????????????
     * ???????????????????????????store????????????????????????
     *
     * @param string $requestNumber ????????????
     */
    public function consent(EstimateConsentRequest $request, string $agencyAccount, string $requestNumber)
    {
        $reserve = $this->webEstimateService->findByRequestNumber($requestNumber, $agencyAccount, ['web_reserve_ext']);

        if (!data_get($reserve, 'web_reserve_ext')) {
            abort(404, "??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????");
        }

        // ??????????????????(reserves???web_reserve_exts)
        $response = Gate::inspect('update', [$reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }
        $response = Gate::inspect('consent', [$reserve->web_reserve_ext]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $webUser = $reserve->web_reserve_ext->web_consult->web_user; // ?????????

        $message = $request->input("message");
        try {
            $reserve = \DB::transaction(function () use ($agencyAccount, $reserve, $message, $webUser) {
                // ????????????????????????????????????????????????
                $this->webReserveExtService->consent($reserve->web_reserve_ext->id);

                // ?????????????????????????????????
                if ($message) {
                    $staff = auth("staff")->user();

                    $webMessage = $this->webMessageService->create([
                        'agency_id' => $reserve->agency_id,
                        'reserve_id' => $reserve->id,
                        'senderable_type' => get_class($staff),
                        'senderable_id' => $staff->id,
                        'message' => $message,
                        'send_at' => date('Y-m-d H:i:s'),
                    ]);

                    //????????????????????????????????????(??????????????????????????????????????????)
                    event(new WebMessageSendEvent($webMessage));
                }

                // ???????????????????????????????????????????????????
                if (!($user = $this->userService->findByWebUserId($webUser->id, $reserve->agency_id, false))) {
                    // Web???????????????????????????????????????????????????
                    $user = $this->userService->createFromWebUser($webUser, ['agency_id' => $reserve->agency_id], true);
                }

                // ??????reserves???????????????????????????????????????????????????????????????????????????????????????????????????
                $this->webEstimateService->consent($agencyAccount, $reserve->id, $reserve->agency_id, $user);

                //?????????????????????????????????????????????????????????????????????
                $this->reserveCustomValueService->setValuesForCodes(
                    [
                        config('consts.user_custom_items.CODE_APPLICATION_ESTIMATE_STATUS') => config('consts.reserves.ESTIMATE_DEFAULT_STATUS')
                    ],
                    $reserve->agency_id,
                    $reserve->id
                );

                $reserve = $this->webEstimateService->find($reserve->id);

                // ?????????????????????????????????
                event(new ReserveUpdateStatusEvent($reserve));
                
                event(new ReserveChangeRepresentativeEvent($reserve)); // ???????????????????????????

                event(new ReserveChangeHeadcountEvent($reserve)); // ?????????????????????????????????

                return $reserve;
            });
            
            if ($reserve) {
                if ($request->input("set_message")) {
                    $request->session()->flash('success_message', "???????????????{$requestNumber}???????????????????????????????????????"); // set_message??????????????????????????????????????????????????????????????????????????????????????????
                }
                return new ShowResource($reserve);
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * ??????????????????
     *
     * @param string $requestNumber ????????????
     */
    public function reject(string $agencyAccount, string $requestNumber)
    {
        $reserve = $this->webEstimateService->findByRequestNumber($requestNumber, $agencyAccount, ['web_reserve_ext']);

        if (!data_get($reserve, 'web_reserve_ext')) {
            abort(404, "??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????");
        }

        // ??????????????????
        $response = Gate::inspect('reject', [$reserve->web_reserve_ext]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $result = \DB::transaction(function () use ($reserve) {
                return $this->webReserveExtService->reject($reserve->web_reserve_ext->id);
            });

            if ($result) {
                return new WebReserveExtResource($this->webReserveExtService->find($reserve->web_reserve_ext->id));
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }
}
