<?php

namespace App\Http\Resources\Staff\WebReserve;

use App\Models\ReserveConfirm;
use App\Models\ReserveInvoice;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class ShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // 催行済を表すパラメータ
        $departedQuery = $this->is_departed ? sprintf('?%s=1', config('consts.const.DEPARTED_QUERY')) : '';

        // 有効行程
        $enabledReserveItinerary = $this->enabled_reserve_itinerary->id ? $this->enabled_reserve_itinerary : null;

        // 予約確認書
        $reserveConfirm = null;
        if ($enabledReserveItinerary) {
            // 予約確認書
            $reserveConfirm = ReserveConfirm::select(['document_setting','confirm_number'])->where('reserve_itinerary_id', $enabledReserveItinerary->id)->whereHas('document_quote', function ($q) {
                $q->where('code', config('consts.document_categories.CODE_RESERVE_CONFIRM_DEFAULT'));
            })->first();
        }

        // 請求書
        $reserveInvoice = ReserveInvoice::where('reserve_id', $this->id)->first();
        // 領収書
        $reserveReceipt = $reserveInvoice ? $reserveInvoice->reserve_receipt : null;


        // 申込者
        $applicant = null;

        $applicant['applicant_type_label'] = $this->applicantable->applicant_type_label;
        $applicant['applicant_type'] = $this->applicantable->applicant_type;

        if ($this->applicantable_type === 'App\Models\User') {
            $applicant['user_number'] = optional($this->applicantable)->user_number;
            $applicant['name'] = optional($this->applicantable->userable)->name;
            $applicant['org_name'] = optional($this->applicantable->userable)->org_name;
            $applicant['name_kana'] = optional($this->applicantable->userable)->name_kana;
            $applicant['is_deleted'] = $this->applicantable ? $this->applicantable->trashed() : false;
            $applicant['detail_url'] = route('staff.client.person.show', [$request->agencyAccount, optional($this->applicantable)->user_number]);
        } elseif ($this->applicantable_type === 'App\Models\BusinessUserManager') {
            $applicant['user_number'] = optional($this->applicantable->business_user)->user_number;
            $applicant['name'] = optional($this->applicantable->business_user)->name;
            $applicant['org_name'] = optional($this->applicantable->business_user)->org_name;
            $applicant['name_kana'] = optional($this->applicantable->business_user)->name_kana;
            $applicant['is_deleted'] = $this->applicantable->business_user ? $this->applicantable->business_user->trashed() : false;
            $applicant['detail_url'] = route('staff.client.business.show', [$request->agencyAccount, optional($this->applicantable->business_user)->user_number]);
        }

        return [
            "control_number" => $this->control_number,
            "estimate_number" => $this->estimate_number,
            "request_number" => $this->request_number,
            "name" => $this->name,
            "departure_date" => $this->departure_date,
            "return_date" => $this->return_date,
            "participant_type" => $this->participant_type,
            "note" => $this->note,
            "departure" => $this->departure->name . $this->departure_place, // 出発地
            "destination" => $this->destination->name . $this->destination_place, // 目的地
            /////// 金額計算 ///////
            "sum_invoice_amount" => $this->sum_invoice_amount, // 請求金額合計
            "sum_withdrawal" => $this->sum_withdrawal, // 出金額合計
            "sum_unpaid" => $this->sum_unpaid, // 未出金額合計
            "sum_deposit" => $this->sum_deposit, // 入金合計
            "sum_not_deposit" => $this->sum_not_deposit, // 未入金合計
            "updated_at" => $this->updated_at->format('Y-m-d H:i:s'),
            // 以下、リレーション項目
            "manager" => [ // 自社担当
                'name' => $this->manager->name,
                'is_deleted' => $this->manager->trashed()
            ],
            "travel_type" => [ // 旅行種別
                'val' => $this->travel_types->isNotEmpty() ? $this->travel_types[0]->val : null
            ],
            "status" => [ // ステータス
                'val' => $this->status ? $this->status->val : null
            ],
            "application_date" => [ // 申込日
                'val' => $this->application_dates->isNotEmpty() ? $this->application_dates[0]->val : null
            ],
            "applicant" => $applicant,
            // 有効な行程
            'enabled_reserve_itinerary' => [
                'sum_gross' => $enabledReserveItinerary ? $enabledReserveItinerary->sum_gross : 0,
                'sum_net' => $enabledReserveItinerary ? $enabledReserveItinerary->sum_net : 0,
                'sum_gross_profit' => $enabledReserveItinerary ? $enabledReserveItinerary->sum_gross_profit : 0,
            ],
            //////// 各種URL ////////
            // 予約確認書
            'reserve_confirm' => [
                'label' => $reserveConfirm ? Arr::get($reserveConfirm->document_setting, 'title') : '予約確認書',
                'url' => $reserveConfirm ? route('staff.web.estimates.reserve_confirm.edit', [
                    $request->agencyAccount,
                    config('consts.reserves.APPLICATION_STEP_RESERVE'),
                    $this->control_number,
                    $enabledReserveItinerary->control_number,
                    $reserveConfirm->confirm_number
                ]) . $departedQuery: null,
            ],
            // 行程表。有効な行程がない場合は新規作成ページ、ある場合は編集ページ
            'itinerary' => [
                'label' => '行程表',
                'url' => $enabledReserveItinerary ? route('staff.web.estimates.itinerary.edit', [
                    $request->agencyAccount,
                    config('consts.reserves.APPLICATION_STEP_RESERVE'),
                    $this->control_number,
                    $enabledReserveItinerary->control_number
                ]) . $departedQuery : route('staff.web.estimates.itinerary.create', [
                    $request->agencyAccount,
                    config('consts.reserves.APPLICATION_STEP_RESERVE'),
                    $this->control_number
                ]) . $departedQuery,
            ],
            // 請求書
            'invoice' => [
                'label' => $reserveInvoice ? Arr::get($reserveInvoice->document_setting, 'title') : '請求書',
                'url' => $reserveInvoice ? route('staff.web.estimates.reserve.invoice.edit', [
                    $request->agencyAccount,
                    $this->control_number
                ]) . $departedQuery: null,
            ],
            // 領収書(請求書が作成済みであれば作成・編集可)
            'receipt' => [
                'label' => $reserveReceipt && Arr::get($reserveReceipt->document_setting, 'title') ? $reserveReceipt->document_setting['title'] : '領収書',
                'url' => $reserveReceipt ? route('staff.web.estimates.reserve.receipt.edit', [
                    $request->agencyAccount,
                    $this->control_number
                ]) . $departedQuery: null,
            ],
            // HAKKEN項目
            'web_reserve_ext' => [
                'web_consult' => [
                    "receipt_number" => optional($this->web_reserve_ext->web_consult)->receipt_number ?? '',
                    "purpose" => optional($this->web_reserve_ext->web_consult)->purpose ?? '',
                    "adult" => optional($this->web_reserve_ext->web_consult)->adult ?? '0',
                    "child" => optional($this->web_reserve_ext->web_consult)->child ?? '0',
                    "infant" => optional($this->web_reserve_ext->web_consult)->infant ?? '0',
                    "budget_label" => optional($this->web_reserve_ext->web_consult)->budget_label ?? '',
                    "web_consult" => optional($this->web_reserve_ext->web_consult)->interest ? $this->web_reserve_ext->web_consult->interest : [],
                ]
            ]
        ];
    }
}
