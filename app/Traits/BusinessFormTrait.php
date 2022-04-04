<?php

namespace App\Traits;

use App\Models\ApplicantInterface;
use App\Models\BusinessUser;
use App\Models\Reserve;
use App\Models\ReserveBundleInvoice;
use App\Models\ReserveDocumentInterface;
use App\Models\ReserveItinerary;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * 帳票用の共通処理を扱うtrait
 */
trait BusinessFormTrait
{
    // 法人顧客情報から申込者情報を取得
    public function getDocumentAddressByBusinessUser(BusinessUser $businessUser) : array
    {
        $documentAddress = [];
        $documentAddress['type'] = config('consts.reserves.PARTICIPANT_TYPE_BUSINESS'); // 一応、申し込み種別をセットしておく
        $documentAddress['honorific'] = config('consts.documents.HONORIFIC_ONCHU'); // 敬称は「御中」で固定
        // 会社情報
        $documentAddress['company_name'] = data_get($businessUser, 'name');
        $documentAddress['zip_code'] = data_get($businessUser, 'zip_code');
        $documentAddress['prefecture'] = data_get($businessUser, 'prefecture.name');
        $documentAddress['address1'] = data_get($businessUser, 'address1');
        $documentAddress['address2'] = data_get($businessUser, 'address2');

        return $documentAddress;
    }

    /**
     * 予約金額情報と予約キャンセル情報を取得
     *
     * ・担当者ID > 予約番号 > 税区分 > 金額データ の構造でまとめた配列
     * ・予約番号 => キャンセル予約か否かの配列
     *
     * @param Collection $reserveInvoices 請求データ
     * @return array
     */
    public function getReservePriceInfo(Collection $reserveInvoices) : array
    {
        $res = [];
        $cancelInfo = []; // 予約番号とキャンセルフラグの配列

        foreach ($reserveInvoices as $reserveInvoice) {

            // キャンセル情報をセット
            $cancelInfo[$reserveInvoice->reserve->control_number] = [];
            $cancelInfo[$reserveInvoice->reserve->control_number] = $reserveInvoice->reserve->is_canceled;

            $partnerManagerId = $reserveInvoice->reserve->applicantable->id;
            if (!Arr::get($res, $partnerManagerId, false)) {
                $res[$partnerManagerId] = [];
            }

            $reserveNumber = $reserveInvoice->reserve->control_number;
            if (!Arr::get($res[$partnerManagerId], $reserveNumber, false)) {
                $res[$partnerManagerId][$reserveNumber] = [];
            }

            foreach (['option_prices','airticket_prices','hotel_prices'] as $field) {
                if ($reserveInvoice->{$field}) {
                    foreach ($reserveInvoice->{$field} as $row) {
                        
                        // 料金対象の参加者でなければスキップ
                        if (!in_array(Arr::get($row, 'participant_id'), $reserveInvoice->participant_ids, true)) {
                            continue;
                        }

                        $tmp = [];
                        if (Arr::get($row, 'purchase_type') == config('consts.const.PURCHASE_CANCEL')) { // キャンセル仕入行
                            $tmp['purchase_type'] = Arr::get($row, 'purchase_type');
                            $tmp['quantity'] = Arr::get($row, 'quantity', 0);
                            $tmp['zei_kbn'] = null; // 税区分ナシ
                            $tmp['cancel_charge'] = Arr::get($row, 'cancel_charge', 0);
                        } elseif (Arr::get($row, 'purchase_type') == config('consts.const.PURCHASE_NORMAL')) { // 通常仕入行
                            $tmp['purchase_type'] = Arr::get($row, 'purchase_type');
                            $tmp['gross_ex'] = Arr::get($row, 'gross_ex', 0);
                            $tmp['quantity'] = Arr::get($row, 'quantity', 0);
                            $tmp['zei_kbn'] = Arr::get($row, 'zei_kbn', null);
                            $tmp['gross'] = Arr::get($row, 'gross', 0);
                            $tmp['cancel_charge'] = Arr::get($row, 'cancel_charge', 0);
                        }

                        $zeiKbn = $tmp['zei_kbn'];
                        if (!Arr::get($res[$partnerManagerId][$reserveNumber], $zeiKbn, false)) {
                            $res[$partnerManagerId][$reserveNumber][$zeiKbn] = [];
                        }
                        $res[$partnerManagerId][$reserveNumber][$zeiKbn][] = $tmp;
                    }
                }
            }
        }

        return [$res, $cancelInfo];
    }

    /**
     * 予約情報を同じ税区分でまとめて数量を計算した配列を返す
     *
     * @parma array $reservePrices 予約金額情報
     * @param array $partnerManagers IDをキーとした担当者情報配列
     */
    public function getReservePriceBreakdown(?array $reservePrices, array $partnerManagers)
    {
        $partnerManagerIds = array_keys($partnerManagers);
        // 有効な担当者のリストのみ抽出
        $enableRows = collect($reservePrices)->filter(function ($val, $managerId) use ($partnerManagerIds) {
            return in_array($managerId, $partnerManagerIds, true);
        });

        // 予約番号毎に同じ税区分でリストをまとめる（ReserveBreakdownPricePreviewArea.jsでやっている処理と同じ）
        $res = [];
        foreach ($enableRows as $managerId => $reserves) {
            foreach ($reserves as $reserveNumber => $zeiKbns) {
                $res[$reserveNumber] = [];
                foreach ($zeiKbns as $zeiKbn => $rows) {
                    $t = [
                        'partner_manager' => Arr::get($partnerManagers, $managerId) ? $partnerManagers[$managerId]['org_name'] : null,
                        'gross_ex' => 0,
                        'quantity' => 1,
                        'zei_kbn' => $zeiKbn,
                        'gross' => 0,
                        'cancel_charge' => 0
                    ];
                    foreach ($rows as $row) {
                        $t['gross_ex'] += (int)Arr::get($row, 'gross_ex', 0);
                        $t['gross'] += (int)Arr::get($row, 'gross', 0);
                        $t['cancel_charge'] += (int)Arr::get($row, 'cancel_charge', 0);
                    }
                    $res[$reserveNumber][] = $t;
                }
            }
        }
        return $res;
    }

    // 申込者情報を取得
    public function getDocumentAddress(ApplicantInterface $applicantable) : array
    {
        // 申込者種別
        $documentAddress['type'] = $applicantable->applicant_type;

        if ($applicantable->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')) {
            $businessUserManager = $applicantable;

            $documentAddress['name'] = $businessUserManager->name;
            $documentAddress['department_name'] = $businessUserManager->department_name;
            $documentAddress['honorific'] = config('consts.documents.HONORIFIC_DEFAULT');
            // 会社情報
            $businessUser = $applicantable->business_user;
            $documentAddress['company_name'] = $businessUser->name;
            $documentAddress['zip_code'] = $businessUser->zip_code;
            $documentAddress['prefecture'] = $businessUser->prefecture->name;
            $documentAddress['address1'] = $businessUser->address1;
            $documentAddress['address2'] = $businessUser->address2;
        } elseif ($applicantable->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_PERSON')) {
            $user = $applicantable;

            $documentAddress['name'] = $user->userable->name;
            $documentAddress['zip_code'] = $user->userable->zip_code;
            $documentAddress['prefecture'] = $user->userable->prefecture->name;
            $documentAddress['address1'] = $user->userable->address1;
            $documentAddress['address2'] = $user->userable->address2;
            $documentAddress['honorific'] = config('consts.documents.HONORIFIC_DEFAULT');
        }

        return $documentAddress;
    }

    /**
     * 代表者情報を取得
     */
    public function getRepresentativeInfo(Reserve $reserve) : array
    {
        return [
            'name' => $reserve->representatives->isNotEmpty() ? $reserve->representatives[0]->name : null,
            'name_roman' => $reserve->representatives->isNotEmpty() ? $reserve->representatives[0]->name_roman : null,
            'sex' => $reserve->representatives->isNotEmpty() ? $reserve->representatives[0]->sex : null,
        ];
    }

    /**
     * 書類設定を取得。設定がない場合は初期設定を返す
     * 検印欄がある書類用
     */
    public function getDocumentSettingSealOrInitSetting(?array $documentSetting)
    {
        if ($documentSetting) {
            return $documentSetting;
        }
        return [
            'setting' => [],
            'seal_items' => [],
            'seal' => 0,
        ];
    }

    /**
     * 一括請求書書類作成時の担当者チェック状態
     *
     * @param Collection $partnerManagers 論理削除も含めた担当者リスト
     * @return array
     */
    public function getDefaultPartnerManagerCheckIds(Collection $partnerManagers) : array
    {
        // 担当者IDを取得
        $managerIds = [];
        foreach ($partnerManagers as $manager) {
            if (!$manager->trashed()) {// 削除済ユーザーはチェックからは外しておく
                $managerIds[] = $manager->id;
            }
        }
        return $managerIds;
    }

    /**
     * 書類作成時の参加者チェック状態。取り消し者以外は全てチェックON
     *
     * @param Collection $participants 参加者リスト
     * @return array
     */
    public function getDefaultParticipantCheckIds(Collection $participants) : array
    {
        // 参加者IDを取得
        $participantIds = [];
        foreach ($participants as $participant) {
            if (!$participant->cancel) { // // 取り消し者は除外
                $participantIds[] = $participant->id;
            }
        }
        return $participantIds;
    }

    /**
     * 「検印欄」の表示・非表示設定を帳票設定の形式から書類作成時の設定形式に変換する
     */
    public function setSealSetting(array &$setting, string $settingKey)
    {
        // 「検印欄」の表示・非表示は設定がイレギュラーにつき、他の設定項目と形式を合わせる
        if (Arr::get($setting, 'seal') == 1) {
            $setting['setting'][$settingKey] = array_merge($setting['setting'][$settingKey], ["検印欄"]);
        }
    }

    /**
     * 料金、ホテル情報を取得
     * PDF用
     *
     */
    public function getPriceAndHotelInfoPdf(ReserveDocumentInterface $reserveDocument, array $participantIds = [])
    {
        $optionPrices = collect($reserveDocument->option_prices)->filter(function ($row, $key) use ($participantIds) {
            return isset($row['participant_id']) && in_array($row['participant_id'], $participantIds);
        })->toArray(); // オプション価格情報

        $airticketPrices = collect($reserveDocument->airticket_prices)->filter(function ($row, $key) use ($participantIds) {
            return isset($row['participant_id']) && in_array($row['participant_id'], $participantIds);
        })->toArray(); // 航空券価格情報

        $hotelPrices = collect($reserveDocument->hotel_prices)->filter(function ($row, $key) use ($participantIds) {
            return isset($row['participant_id']) && in_array($row['participant_id'], $participantIds);
        })->toArray(); // ホテル価格情報

        $hotelInfo = $reserveDocument->hotel_info ?? []; // 宿泊施設情報
        // 参加者に応じて必要な情報を抽出
        foreach ($hotelInfo as $date => $hotels) {
            foreach ($hotels as $n => $hotel) {
                foreach ($hotel['rooms'] as $room => $rooms) {
                    $filtered = collect($rooms)->filter(function ($row, $key) use ($participantIds) {
                        return isset($row['participant_id']) && in_array($row['participant_id'], $participantIds);
                    });

                    $hotelInfo[$date][$n]['rooms'][$room] = $filtered->toArray();

                    if (!$hotelInfo[$date][$n]['rooms'][$room]) { // 利用者がいない部屋情報は削除
                        unset($hotelInfo[$date][$n]['rooms'][$room]);
                    }
                }
                if (!$hotelInfo[$date][$n]['rooms']) { // 利用者がいないホテル情報は削除
                    unset($hotelInfo[$date][$n]);
                }
            }
            if (!$hotelInfo[$date]) { // 利用者のいない日程は削除
                unset($hotelInfo[$date]);
            }
        }

        $hotelContacts = $reserveDocument->hotel_contacts ?? []; // 宿泊施設連絡先
        // 参加者に応じて必要な情報を抽出
        foreach ($hotelContacts as $n => $hotel) {
            $filtered = collect($hotel['guests'])->filter(function ($row, $key) use ($participantIds) {
                return isset($row['participant_id']) && in_array($row['participant_id'], $participantIds);
            });

            $hotelContacts[$n]['guests'] = $filtered->toArray();

            if (!$hotelContacts[$n]['guests']) {
                unset($hotelContacts[$n]);
            }
        }

        return [
            $optionPrices,
            $airticketPrices,
            $hotelPrices,
            $hotelInfo,
            $hotelContacts,
        ];
    }

    /**
     * 料金内訳、ホテル情報を取得
     *
     * @param ReserveItinerary $reserveItinerary 行程
     * @param bool $getPriceOnly 料金情報のみ取得する場合はtrue。全ての情報を取得する場合はfalse
     */
    public function getPriceAndHotelInfo(?ReserveItinerary $reserveItinerary, bool $getPriceOnly = false)
    {
        $optionPrices = []; // オプション価格情報
        $airticketPrices = []; // 航空券価格情報
        $hotelPrices = []; // ホテル価格情報

        $hotelInfo = []; // 宿泊施設情報
        $hotelContacts = []; // 宿泊施設連絡先

        $retained = []; // ハッシュ値配列（ホテル情報の重複をチェックするのに使用）

        if ($reserveItinerary) {
            foreach ($reserveItinerary->reserve_travel_dates as $travelDate) {
                $hotelInfo[$travelDate->travel_date] = []; //旅行日ごとの配列を作成
    
                foreach ($travelDate->reserve_schedules as $reserveSchedule) {
                    if (!$getPriceOnly) {
                        // ホテル（宿泊施設情報、宿泊施設連絡先）
                        foreach ($reserveSchedule->reserve_purchasing_subject_hotels as $subject) {
                            $rooms = []; // $hotelInfoのデータ作成に使用
                            $guests = []; // $hotelContactsのデータ作成に使用

                            foreach ($subject->reserve_participant_prices as $price) {

                                // キャンセルか否かでチェックするプロパティを変更（キャンセルの場合...is_cancel、非キャンセル...valid）
                                if ($price->purchase_type == config('consts.const.PURCHASE_NORMAL')) {
                                    $checkProperty = 'valid';
                                } elseif ($price->purchase_type == config('consts.const.PURCHASE_CANCEL')) {
                                    $checkProperty = 'is_cancel';
                                } else {
                                    continue;
                                }

                                if ($price->{$checkProperty}) { // 有効(is_cancel or valid)
                                    if (!isset($rooms[$price->room_number])) {
                                        $rooms[$price->room_number] = [];
                                    }
                                    $rooms[$price->room_number][] = [
                                        'room_number' => $price->room_number,
                                        'participant_id' => $price->participant->id,
                                        'user_name' => $price->participant->name,
                                    ]; // 宿泊情報
        
                                    $guests[] = [
                                        'participant_id' => $price->participant->id,
                                        'user_name' => $price->participant->name,
                                    ];
                                }
                            }
    
                            if ($rooms) { // 部屋の利用があれば宿泊施設情報をセット
                                // 部屋タイプ
                                $roomType = $subject->room_types->isNotEmpty() ? $subject->room_types[0]->val : null;
                            
                                // 宿泊施設情報をセット
                                $tmp = array_merge($subject->only(['hotel_name']), ['room_type' => $roomType]);
                                $tmp['rooms'] = $rooms;

                                $hotelInfo[$travelDate->travel_date][] = $tmp;// 1科目レコードごとに配列にまとめる
                            }
    
                            if ($guests) { // 利用者がいればホテル情報をセット
                                // 宿泊施設連絡先
                                $hotel = $subject->only(['hotel_name','address','tel','fax','url']); // 出力に必要な情報のみ抽出
                                $sha1 = sha1(serialize($hotel)); // ハッシュ値を作成
                                if (!in_array($sha1, $retained, true)) {
                                    $hotel['guests'] = $guests;
                                    $hotelContacts[] = $hotel;
                                    $retained[] = $sha1;
                                }
                            }
                        }
                    }

                    // オプション科目
                    foreach ($reserveSchedule->reserve_purchasing_subject_options as $subject) {
                        $tmp = ['name' => $subject->name];
    
                        foreach ($subject->reserve_participant_prices as $price) {

                            // キャンセルか否かでチェックするプロパティを変更（キャンセルの場合...is_cancel、非キャンセル...valid）
                            if ($price->purchase_type == config('consts.const.PURCHASE_NORMAL')) {
                                $checkProperty = 'valid';
                            } elseif ($price->purchase_type == config('consts.const.PURCHASE_CANCEL')) {
                                $checkProperty = 'is_cancel';
                            } else {
                                continue;
                            }
                            
                            if ($price->{$checkProperty}) { // 有効
                                $optionPrices[] = array_merge($tmp, [
                                    'purchase_type' => $price->purchase_type,
                                    'participant_id' => $price->participant->id,
                                    'user_name' => $price->participant->name,
                                    'gross_ex' => $price->gross_ex,
                                    'quantity' => 1,
                                    'zei_kbn' => $price->zei_kbn,
                                    'gross' => $price->gross,
                                    'cancel_charge' => $price->cancel_charge,
                                ]);
                            }
                        }
                    }
    
    
                    // 航空券
                    foreach ($reserveSchedule->reserve_purchasing_subject_airplanes as $subject) {
                        $tmp = [];
    
                        // 航空会社
                        $airlineCompany = $subject->airline_companies->isNotEmpty() ? $subject->airline_companies[0]->val : null;
    
                        // 航空券情報をセット
                        $tmp = array_merge($subject->only(['name','booking_class']), ['airline_company' => $airlineCompany]);
    
                        foreach ($subject->reserve_participant_prices as $price) {

                            // キャンセルか否かでチェックするプロパティを変更（キャンセルの場合...is_cancel、非キャンセル...valid）
                            if ($price->purchase_type == config('consts.const.PURCHASE_NORMAL')) {
                                $checkProperty = 'valid';
                            } elseif ($price->purchase_type == config('consts.const.PURCHASE_CANCEL')) {
                                $checkProperty = 'is_cancel';
                            } else {
                                continue;
                            }

                            if ($price->{$checkProperty}) { // 有効
                                $airticketPrices[] = array_merge($tmp, [
                                    'purchase_type' => $price->purchase_type,
                                    'participant_id' => $price->participant->id,
                                    'user_name' => $price->participant->name,
                                    'seat' => $price->seat,
                                    'reference_number' => $price->reference_number,
                                    'gross_ex' => $price->gross_ex,
                                    'quantity' => 1,
                                    'zei_kbn' => $price->zei_kbn,
                                    'gross' => $price->gross,
                                    'cancel_charge' => $price->cancel_charge,
                                ]);
                            }
                        }
                    }
    
                    // ホテル科目
                    foreach ($reserveSchedule->reserve_purchasing_subject_hotels as $subject) {
                        $tmp = [];
    
                        // 部屋タイプ
                        $roomType = $subject->room_types->isNotEmpty() ? $subject->room_types[0]->val : null;
    
                        // ホテル情報をセット
                        $tmp = array_merge($subject->only(['name']), ['room_type' => $roomType]);
    
                        foreach ($subject->reserve_participant_prices as $price) {

                            // キャンセルか否かでチェックするプロパティを変更（キャンセルの場合...is_cancel、非キャンセル...valid）
                            if ($price->purchase_type == config('consts.const.PURCHASE_NORMAL')) {
                                $checkProperty = 'valid';
                            } elseif ($price->purchase_type == config('consts.const.PURCHASE_CANCEL')) {
                                $checkProperty = 'is_cancel';
                            } else {
                                continue;
                            }

                            if ($price->{$checkProperty}) { // 有効
                                $hotelPrices[] = array_merge($tmp, [
                                    'purchase_type' => $price->purchase_type,
                                    'participant_id' => $price->participant->id,
                                    'room_number'=> $price->room_number,
                                    'user_name' => $price->participant->name,
                                    'gross_ex' => $price->gross_ex,
                                    'quantity' => 1,
                                    'zei_kbn' => $price->zei_kbn,
                                    'gross' => $price->gross,
                                    'cancel_charge' => $price->cancel_charge,
                                ]);
                            }
                        }
                    }
                }
                // 宿泊施設データがない場合はキー削除
                if (!$hotelInfo[$travelDate->travel_date]) {
                    unset($hotelInfo[$travelDate->travel_date]);
                }
            }
        }

        return [
            $optionPrices,
            $airticketPrices,
            $hotelPrices,
            $hotelInfo,
            $hotelContacts
        ];
    }

    /**
     * オプション科目情報を同じ項目でまとめて数量を計算した配列を返す
     */
    public function getOptionPriceBreakdown(array $optionPrices)
    {
        $result = [];
        foreach ($optionPrices as $row) {
            if (Arr::get($row, 'purchase_type') == config('consts.const.PURCHASE_NORMAL') && !Arr::get($row, 'gross') && !Arr::get($row, 'gross_ex')) {
                continue; // 通常仕入で単価・税込単価のいずれも0円の場合は表示ナシ
            }
            if (Arr::get($row, 'purchase_type') == config('consts.const.PURCHASE_CANCEL') && !Arr::get($row, 'cancel_charge')) {
                continue; // キャンセル仕入でキャンセルチャージが0円の場合は表示ナシ
            }

            // 仕入タイプ・名称、単価、税込単価、キャンセルチャージ、税区分で同一性をチェック ※asp/resources/assets/staff/js/components/BusinessForm/BreakdownPricePreviewArea.jsと同じ処理
            $key = md5(sprintf("%s_%s_%s_%s_%s_%s", Arr::get($row, 'purchase_type'), Arr::get($row, 'name'), Arr::get($row, 'gross_ex'), Arr::get($row, 'gross'), Arr::get($row, 'cancel_charge'), Arr::get($row, 'zei_kbn')));
            if (!isset($result[$key])) {
                // 参加者情報は不要
                $result[$key] = collect($row)->except(['participant_id', 'user_name'])->all();
            } else {
                $result[$key]['quantity'] = (int)Arr::get($result, "{$key}.quantity", 0) + 1;
            }
        }

        return $result;
    }

    /**
     * 航空券科目情報を同じ項目でまとめて数量を計算した配列を返す
     */
    public function getAirticketPriceBreakdown(array $airticketPrices)
    {
        $result = [];
        foreach ($airticketPrices as $row) {
            if (Arr::get($row, 'purchase_type') == config('consts.const.PURCHASE_NORMAL') && !Arr::get($row, 'gross') && !Arr::get($row, 'gross_ex')) {
                continue; // 通常仕入で単価・税込単価のいずれも0円の場合は表示ナシ
            }
            if (Arr::get($row, 'purchase_type') == config('consts.const.PURCHASE_CANCEL') && !Arr::get($row, 'cancel_charge')) {
                continue; // キャンセル仕入でキャンセルチャージが0円の場合は表示ナシ
            }

            // 仕入タイプ・名称、単価、税込単価、キャンセルチャージ、税区分で同一性をチェック ※asp/resources/assets/staff/js/components/BusinessForm/BreakdownPricePreviewArea.jsと同じ処理
            $key = md5(sprintf("%s_%s_%s_%s_%s_%s_%s", Arr::get($row, 'purchase_type'), Arr::get($row, 'name'), Arr::get($row, 'seat'), Arr::get($row, 'gross_ex'), Arr::get($row, 'gross'), Arr::get($row, 'cancel_charge'), Arr::get($row, 'zei_kbn')));
            if (!isset($result[$key])) {
                // 参加者情報は不要
                $result[$key] = collect($row)->except(['participant_id', 'user_name'])->all();
            } else {
                $result[$key]['quantity'] = (int)Arr::get($result, "{$key}.quantity", 0) + 1;
            }
        }

        return $result;
    }

    /**
     * ホテル科目情報を同じ項目でまとめて数量を計算した配列を返す
     */
    public function getHotelPriceBreakdown(array $hotelPrices)
    {
        $result = [];
        foreach ($hotelPrices as $row) {
            if (Arr::get($row, 'purchase_type') == config('consts.const.PURCHASE_NORMAL') && !Arr::get($row, 'gross') && !Arr::get($row, 'gross_ex')) {
                continue; // 通常仕入で単価・税込単価のいずれも0円の場合は表示ナシ
            }
            if (Arr::get($row, 'purchase_type') == config('consts.const.PURCHASE_CANCEL') && !Arr::get($row, 'cancel_charge')) {
                continue; // キャンセル仕入でキャンセルチャージが0円の場合は表示ナシ
            }
            
            // 仕入タイプ・名称、単価、税込単価、キャンセルチャージ、税区分で同一性をチェック ※asp/resources/assets/staff/js/components/BusinessForm/BreakdownPricePreviewArea.jsと同じ処理
            $key = md5(sprintf("%s_%s_%s_%s_%s_%s_%s", Arr::get($row, 'purchase_type'), Arr::get($row, 'name'), Arr::get($row, 'room_type'), Arr::get($row, 'gross_ex'), Arr::get($row, 'gross'), Arr::get($row, 'cancel_charge'), Arr::get($row, 'zei_kbn')));
            if (!isset($result[$key])) {
                // 参加者情報は不要
                $result[$key] = collect($row)->except(['participant_id', 'user_name'])->all();
            } else {
                $result[$key]['quantity'] = (int)Arr::get($result, "{$key}.quantity", 0) + 1;
            }
        }

        return $result;
    }

    /**
     * 見積・予約、請求フォームで使用。
     * 書類によっては関係定数もあるが、まとめて定義した方が楽なのでここで設定
     */
    public function getConstDatas()
    {
        return [
            // 予約ページ詳細タブ
            'detailTab' => config('consts.reserves.TAB_RESERVE_DETAIL'),
            // 検印欄表示最大値
            'sealMaxNum' => config('consts.const.QUOTE_SEAL_MAXIMUM'),
            // 個人申し込み
            'person' => config('consts.reserves.PARTICIPANT_TYPE_PERSON'),
            // 法人申し込み
            'business' => config('consts.reserves.PARTICIPANT_TYPE_BUSINESS'),
            'application_step_list' => [
                'application_step_draft' => config('consts.reserves.APPLICATION_STEP_DRAFT'),
                'application_step_reserve' => config('consts.reserves.APPLICATION_STEP_RESERVE'),
            ],
        ];
    }

    /**
     * 設定配列をフラットな構造に変更
     */
    private function structuralChange($rows)
    {
        $res = [];
        foreach ($rows as $parent => $childs) {
            $res[] = ['parent' => null, 'val' => $parent];
            if ($childs) {
                foreach ($childs as $child) {
                    $res[] = ['parent' => $parent, 'val' => $child];
                }
            }
        }
        return $res;
    }
}
