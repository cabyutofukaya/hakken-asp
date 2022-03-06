<?php

namespace App\Services;

use App\Events\ChangePaymentAmountEvent;
use App\Exceptions\ExclusiveLockException;
use App\Models\AccountPayable;
use App\Models\AccountPayableDetail;
use App\Models\ParticipantPriceInterface;
use App\Models\Reserve;
use App\Models\ReserveItinerary;
use App\Models\Supplier;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\Reserve\ReserveRepository;
use App\Repositories\ReserveItinerary\ReserveItineraryRepository;
use App\Repositories\Supplier\SupplierRepository;
use App\Services\AccountPayableDetailService;
use App\Services\AccountPayableService;
use App\Services\ReservePurchasingSubjectAirplaneCustomValueService;
use App\Services\ReservePurchasingSubjectAirplaneService;
use App\Services\ReservePurchasingSubjectHotelCustomValueService;
use App\Services\ReservePurchasingSubjectHotelService;
use App\Services\ReservePurchasingSubjectOptionCustomValueService;
use App\Services\ReservePurchasingSubjectOptionService;
use App\Services\ReservePurchasingSubjectService;
use App\Services\ReserveSchedulePhotoService;
use App\Services\ReserveScheduleService;
use App\Services\ReserveService;
use App\Services\ReserveTravelDateService;
use App\Services\SupplierService;
use App\Traits\UserCustomItemTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;


class ReserveItineraryService
{
    use UserCustomItemTrait;

    public function __construct(
        AgencyRepository $agencyRepository,
        ReserveItineraryRepository $reserveItineraryRepository,
        ReservePurchasingSubjectAirplaneCustomValueService $reservePurchasingSubjectAirplaneCustomValueService,
        ReservePurchasingSubjectAirplaneService $reservePurchasingSubjectAirplaneService,
        ReservePurchasingSubjectHotelCustomValueService $reservePurchasingSubjectHotelCustomValueService,
        ReservePurchasingSubjectHotelService $reservePurchasingSubjectHotelService,
        ReservePurchasingSubjectOptionCustomValueService $reservePurchasingSubjectOptionCustomValueService,
        ReservePurchasingSubjectOptionService $reservePurchasingSubjectOptionService,
        ReservePurchasingSubjectService $reservePurchasingSubjectService,
        ReserveSchedulePhotoService $reserveSchedulePhotoService,
        ReserveScheduleService $reserveScheduleService,
        ReserveService $reserveService,
        ReserveTravelDateService $reserveTravelDateService,
        AccountPayableService $accountPayableService,
        SupplierRepository $supplierRepository,
        AccountPayableDetailService $accountPayableDetailService,
        SupplierService $supplierService,
        ReserveRepository $reserveRepository
    ) {
        $this->accountPayableDetailService = $accountPayableDetailService;
        $this->accountPayableService = $accountPayableService;
        $this->agencyRepository = $agencyRepository;
        $this->reserveItineraryRepository = $reserveItineraryRepository;
        $this->reservePurchasingSubjectAirplaneCustomValueService = $reservePurchasingSubjectAirplaneCustomValueService;
        $this->reservePurchasingSubjectAirplaneService = $reservePurchasingSubjectAirplaneService;
        $this->reservePurchasingSubjectHotelCustomValueService = $reservePurchasingSubjectHotelCustomValueService;
        $this->reservePurchasingSubjectHotelService = $reservePurchasingSubjectHotelService;
        $this->reservePurchasingSubjectOptionCustomValueService = $reservePurchasingSubjectOptionCustomValueService;
        $this->reservePurchasingSubjectOptionService = $reservePurchasingSubjectOptionService;
        $this->reservePurchasingSubjectService = $reservePurchasingSubjectService;
        $this->reserveRepository = $reserveRepository;
        $this->reserveSchedulePhotoService = $reserveSchedulePhotoService;
        $this->reserveScheduleService = $reserveScheduleService;
        $this->reserveService = $reserveService;
        $this->reserveTravelDateService = $reserveTravelDateService;
        $this->supplierRepository = $supplierRepository;
        $this->supplierService = $supplierService;
    }

    /**
     * 予約情報と行程番号から行程データを1件取得
     *
     * @param string $estimateNumber 見積番号
     * @param string $itineraryNumber 行程番号
     * @param string $agencyAccount 会社ID
     */
    public function findByItineraryNumber(int $reserveId, string $itineraryNumber, int $agencyId, array $with = [], array $select=[], bool $getDeleted = false) : ?ReserveItinerary
    {
        return $this->reserveItineraryRepository->findByReserveItineraryNumber($itineraryNumber, $reserveId, $agencyId, $with, $select, $getDeleted);
    }

    /**
     * 作成
     *
     * @param string $agencyAccount 会社アカウント
     * @param string $reserveNumber 予約番号
     * @param array $input 入力データ
     */
    public function create(Reserve $reserve, array $input) : ReserveItinerary
    {
        ///////////// 工程表管理レコードを作成 //////////

        $agencyId = $reserve->agency_id;

        $createData = array();
        $createData['reserve_id'] = $reserve->id;
        $createData['agency_id'] = $agencyId;
        $createData['control_number'] = $this->createUserNumber($reserve->id); // 管理番号を発行
        $createData['note'] = Arr::get($input, "note"); // 備考
        $createData['enabled'] = $reserve->reserve_itineraries->isEmpty(); // 最初に作成した旅程は自動で有効化

        $reserveItinerary = $this->reserveItineraryRepository->create($createData);

        $this->editCommon($reserveItinerary, $input);

        return $reserveItinerary;
    }

    /**
     * 買い掛け金詳細upsert処理
     * 当該科目が”無効”で出金登録がされていない場合はaccount_payable_detailsテーブル削除
     *
     * @param int $agencyId 会社ID
     * @param int $reserveId 予約ID
     * @param int $reserveTravelDateId 旅行日ID
     * @param bool $valid 科目の有効・無効フラグ
     * @param string $useDate 利用日
     * @param string $paymentDate 支払日
     */
    private function accountPayableDetailCommon(int $agencyId, int $reserveId, int $reserveTravelDateId, bool $valid, int $accountPayableId, ParticipantPriceInterface $participantPrice, Supplier $supplier, ?string $itemCode, ?string $itemName, string $useDate, ?string $paymentDate) : ?AccountPayableDetail
    {
        // 検索条件
        $attributes = [
            'account_payable_id' => $accountPayableId,
            'saleable_type' => get_class($participantPrice),
            'saleable_id' => $participantPrice->id,
        ];

        if (!$valid) { // 無効科目 → 出金登録がなければ削除
            $accountPayableDetail = $this->accountPayableDetailService->findWhere($attributes);
            if ($accountPayableDetail && $accountPayableDetail->agency_withdrawals->isEmpty()) {
                // 論理削除
                $this->accountPayableDetailService->delete($accountPayableDetail->id, true);
            }
            return null;
        }

        /////////// 更新or登録が必要な場合は以下の処理

        $accountPayableDetail = $this->accountPayableDetailService->updateOrCreate(
            $attributes,
            [
                'agency_id' => $agencyId,
                'reserve_id' => $reserveId,
                'reserve_travel_date_id' => $reserveTravelDateId,
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
                'item_code' => $itemCode,
                'item_name' => $itemName,
                'amount_billed' => $participantPrice->net ?? 0, // 数字なのでnullの場合は0で初期化
                'amount_payment' => $participantPrice->cost ?? 0, // 数字なのでnullの場合は0で初期化
                'use_date' => $useDate,
                'payment_date' => $paymentDate,
            ]
        );

        event(new ChangePaymentAmountEvent($accountPayableDetail->id));
    
        return $accountPayableDetail;
    }

    /**
     * 登録、編集の共通処理
     *
     */
    private function editCommon(ReserveItinerary $reserveItinerary, array $input)
    {
        $agencyId = $reserveItinerary->agency_id;

        ///////////// 旅行日管理レコードを作成 //////////
        $dates = array_keys(Arr::get($input, 'dates', []));

        $editTravelDateIds = []; // 編集、新規登録されたID一覧。削除処理に使用

        // 支払い先情報は、仕入先名の取得や支払日計算に使うので何度もDBから取得しないように配列に保持
        $suppliers = [];

        if ($dates) {
            foreach ($dates as $date) {
                $reserveTravelDate = $reserveItinerary->reserve_travel_dates()->updateOrCreate(
                    [
                        'reserve_itinerary_id' => $reserveItinerary->id,
                        'travel_date' => $date
                    ],
                    [
                        'agency_id' => $agencyId,
                        'reserve_id' => $reserveItinerary->reserve_id,
                        'travel_date' => $date,
                    ]
                );
                $editTravelDateIds[] = $reserveTravelDate->id;

                // ///////////// 旅行スケジュールレコードを作成 //////////

                $schedules = Arr::get($input, "dates.{$date}", []);
                $editScheduleIds = []; // 編集、新規登録されたID一覧。削除処理に使用
                if ($schedules) {
                    foreach ($schedules as $schedule) {
                        $reserveSchedule = $reserveTravelDate->reserve_schedules()->updateOrCreate(
                            ['id' => Arr::get($schedule, "id")],
                            array_merge($schedule, ['agency_id' => $agencyId])
                        );
                        $editScheduleIds[] = $reserveSchedule->id;

                        ///////////// 写真情報をセット //////////
                        if ($schedule['type'] === config('consts.reserve_itineraries.ITINERARY_TYPE_WAYPOINT_IMAGE')) {
                            $photos = Arr::get($schedule, "photos", []);

                            foreach ($photos as $photo) {
                                // upload画像がある場合は、公開状態をprivateからpublicに変更（オリジナル画像とサムネイル画像）して保存用カラムをupload_file_nameからfile_nameに切り替える
                                if (Arr::get($photo, 'upload_file_name')) {
                                    foreach ([config('consts.const.UPLOAD_IMAGE_DIR'),
                                    config('consts.const.UPLOAD_THUMB_M_DIR'),
                                    config('consts.const.UPLOAD_THUMB_S_DIR')] as $dir) {
                                        \Storage::disk('s3')->setVisibility($dir.$photo['upload_file_name'], 'public');
                                    }
                                    $photo['file_name'] = $photo['upload_file_name'];
                                }

                                // idあり=更新時 の場合は更新前のfile_nameを取得しておき、file_nameが変わっている場合は古いファイルは削除する
                                $oldFileName = null;
                                if (($rspId = Arr::get($photo, "id"))) {
                                    $oldPhoto = $this->reserveSchedulePhotoService->find($rspId, [], ['file_name']);
                                    $oldFileName = $oldPhoto->getOriginal("file_name");
                                }

                                // reserve_schedule_photos更新or新規
                                $reserveSchedulePhoto = $reserveSchedule->reserve_schedule_photos()->updateOrCreate(
                                    ['id' => Arr::get($photo, "id")],
                                    array_merge($photo, ['agency_id' => $agencyId])
                                );

                                if (!$reserveSchedulePhoto->wasRecentlyCreated) {
                                    if ($oldFileName && $reserveSchedulePhoto->wasChanged("file_name")) { // ファイル名が変更されている場合は、古いファイルは削除
                                        $this->reserveSchedulePhotoService->deleteFile($oldFileName, false); // 物理削除
                                    }
                                }
                            }
                        }

                        ///////////// 仕入科目レコードを作成 //////////

                        $purchasingSubjects = Arr::get($schedule, "reserve_purchasing_subjects", []);
                        
                        $editSubjectOptionIds = []; // 編集、新規登録されたオプション科目ID一覧。削除処理に使用
                        $editSubjectAirplaneIds = []; // 編集、新規登録された航空券科目ID一覧。削除処理に使用
                        $editSubjectHotelIds = []; // 編集、新規登録されたホテル科目ID一覧。削除処理に使用

                        if ($purchasingSubjects) {
                            foreach ($purchasingSubjects as $purchasingSubject) {
                                $currentSupplier = null;
                                if (!isset($suppliers[$purchasingSubject['supplier_id']])) {
                                    $suppliers[$purchasingSubject['supplier_id']] = $this->supplierService->find($purchasingSubject['supplier_id'], [], true);//論理削除も取得
                                }
                                $currentSupplier = $suppliers[$purchasingSubject['supplier_id']];
                                

                                $payableControlNumber = $this->accountPayableService->createUserNumber($reserveItinerary->reserve_id, $reserveItinerary->id, $currentSupplier->id); //買い掛け金番号


                                $accountPayable =  $this->accountPayableService->updateOrCreate(
                                    [
                                        'reserve_itinerary_id' => $reserveItinerary->id,
                                        'supplier_id' => $purchasingSubject['supplier_id']
                                    ],
                                    [
                                        'agency_id' => $agencyId,
                                        'reserve_id' => $reserveItinerary->reserve_id,
                                        'payable_number' => $payableControlNumber,
                                        'supplier_name' => $currentSupplier->name,
                                    ]
                                );


                                // 仕入業者に対する支払日を算出
                                $paymentDate = $this->accountPayableService->calcPaymentDate($currentSupplier, $reserveItinerary->reserve);


                                if ($purchasingSubject['subject'] === config('consts.subject_categories.SUBJECT_CATEGORY_OPTION')) { // オプション科目

                                    $subject = $this->reservePurchasingSubjectOptionService->updateOrCreate(
                                        ['id' => Arr::get($purchasingSubject, "id")],
                                        array_merge($purchasingSubject, ['agency_id' => $agencyId, 'reserve_schedule_id' => $reserveSchedule->id])
                                    );
                                    $editSubjectOptionIds[] = $subject->id;

                                    $customFields = $this->customFieldsExtraction($purchasingSubject); // 入力データからカスタムフィールドを抽出
                                    if ($customFields) {
                                        $this->reservePurchasingSubjectOptionCustomValueService->upsertCustomFileds($customFields, $subject->id); // カスタムフィールド保存
                                    }

                                    // 参加者の料金テーブル保存
                                    $participantPrices = Arr::get($purchasingSubject, "participants", []);
                                    if ($participantPrices) { // 参加者料金
                                        foreach ($participantPrices as $participantPrice) {
                                            $price = $subject->reserve_participant_prices()->updateOrCreate(
                                                ['id' => Arr::get($participantPrice, 'id')],
                                                array_merge(
                                                    $participantPrice,
                                                    ['agency_id' => $agencyId, 'reserve_itinerary_id' => $reserveItinerary->id, 'reserve_id' => $reserveItinerary->reserve_id]
                                                )
                                            );

                                            // 買い掛け金明細管理
                                            $this->accountPayableDetailCommon(
                                                $agencyId,
                                                $reserveItinerary->reserve_id,
                                                $reserveTravelDate->id,
                                                Arr::get($participantPrice, 'valid') == 1,
                                                $accountPayable->id,
                                                $price,
                                                $currentSupplier,
                                                Arr::get($purchasingSubject, 'code'),
                                                Arr::get($purchasingSubject, 'name'),
                                                $date,
                                                $paymentDate
                                            );
                                        }
                                    }
                                } elseif ($purchasingSubject['subject'] === config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE')) { // 航空科目

                                    $subject = $this->reservePurchasingSubjectAirplaneService->updateOrCreate(
                                        ['id' => Arr::get($purchasingSubject, "id")],
                                        array_merge($purchasingSubject, ['agency_id' => $agencyId, 'reserve_schedule_id' => $reserveSchedule->id])
                                    );
                                    $editSubjectAirplaneIds[] = $subject->id;

                                    $customFields = $this->customFieldsExtraction($purchasingSubject); // 入力データからカスタムフィールドを抽出
                                    if ($customFields) {
                                        $this->reservePurchasingSubjectAirplaneCustomValueService->upsertCustomFileds($customFields, $subject->id); // カスタムフィールド保存
                                    }

                                    // 参加者の料金テーブル保存
                                    $participantPrices = Arr::get($purchasingSubject, "participants", []);
                                    if ($participantPrices) { // 参加者料金
                                        foreach ($participantPrices as $participantPrice) {
                                            $price = $subject->reserve_participant_prices()->updateOrCreate(
                                                ['id' => Arr::get($participantPrice, 'id')],
                                                array_merge(
                                                    $participantPrice,
                                                    ['agency_id' => $agencyId, 'reserve_itinerary_id' => $reserveItinerary->id, 'reserve_id' => $reserveItinerary->reserve_id]
                                                )
                                            );

                                            // 買い掛け金明細管理
                                            $this->accountPayableDetailCommon(
                                                $agencyId,
                                                $reserveItinerary->reserve_id,
                                                $reserveTravelDate->id,
                                                Arr::get($participantPrice, 'valid') == 1,
                                                $accountPayable->id,
                                                $price,
                                                $currentSupplier,
                                                Arr::get($purchasingSubject, 'code'),
                                                Arr::get($purchasingSubject, 'name'),
                                                $date,
                                                $paymentDate
                                            );
                                        }
                                    }
                                } elseif ($purchasingSubject['subject'] === config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL')) { // ホテル科目

                                    $subject = $this->reservePurchasingSubjectHotelService->updateOrCreate(
                                        ['id' => Arr::get($purchasingSubject, "id")],
                                        array_merge($purchasingSubject, ['agency_id' => $agencyId, 'reserve_schedule_id' => $reserveSchedule->id])
                                    );
                                    $editSubjectHotelIds[] = $subject->id;

                                    $customFields = $this->customFieldsExtraction($purchasingSubject); // 入力データからカスタムフィールドを抽出
                                    if ($customFields) {
                                        $this->reservePurchasingSubjectHotelCustomValueService->upsertCustomFileds($customFields, $subject->id); // カスタムフィールド保存
                                    }

                                    // 参加者の料金テーブル保存
                                    $participantPrices = Arr::get($purchasingSubject, "participants", []);
                                    if ($participantPrices) { // 参加者料金
                                        foreach ($participantPrices as $participantPrice) {
                                            $price = $subject->reserve_participant_prices()->updateOrCreate(
                                                ['id' => Arr::get($participantPrice, 'id')],
                                                array_merge(
                                                    $participantPrice,
                                                    ['agency_id' => $agencyId, 'reserve_itinerary_id' => $reserveItinerary->id, 'reserve_id' => $reserveItinerary->reserve_id]
                                                )
                                            );

                                            // 買い掛け金明細管理
                                            $this->accountPayableDetailCommon(
                                                $agencyId,
                                                $reserveItinerary->reserve_id,
                                                $reserveTravelDate->id,
                                                Arr::get($participantPrice, 'valid') == 1,
                                                $accountPayable->id,
                                                $price,
                                                $currentSupplier,
                                                Arr::get($purchasingSubject, 'code'),
                                                Arr::get($purchasingSubject, 'name'),
                                                $date,
                                                $paymentDate
                                            );
                                        }
                                    }
                                } else { // 未定義科目
                                    throw new Exception("未定義の科目です");
                                }

                                // ポリモーフィックリレーションを作成or更新
                                $this->reservePurchasingSubjectService->updateOrCreate(
                                    [
                                        'reserve_schedule_id' => $reserveSchedule->id,
                                        'subjectable_type' => get_class($subject),
                                        'subjectable_id' => $subject->id
                                    ],
                                    [
                                        'reserve_schedule_id' => $reserveSchedule->id,
                                        'subjectable_type' => get_class($subject),
                                        'subjectable_id' => $subject->id
                                    ],
                                );
                            }
                        }
                        ///////// 編集対象にならなかったオプション科目の削除処理（科目レコード＆オプション科目レコード）。reserve_participant_option_pricesはreserve_purchasing_subject_optionsレコードの削除と合わせて削除されるので特に手動で削除するような処理は不要 /////////

                        // 編集対象にならなかった科目レコードを削除
                        $this->reservePurchasingSubjectService->deleteOtherSubjectableIdsForSchedule($reserveSchedule->id, 'App\Models\ReservePurchasingSubjectOption', $editSubjectOptionIds, true);// 論理削除

                        // 編集対象にならなかったオプション科目レコードを削除
                        $deleteSubjectIds = $this->reservePurchasingSubjectOptionService->deleteOtherIdsForSchedule($reserveSchedule->id, $editSubjectOptionIds, true);// 論理削除

                        ///////// 編集対象にならなかった航空券科目の削除処理（科目レコード＆航空券科目レコード） /////////

                        // 編集対象にならなかった科目レコードを削除
                        $this->reservePurchasingSubjectService->deleteOtherSubjectableIdsForSchedule($reserveSchedule->id, 'App\Models\ReservePurchasingSubjectAirplane', $editSubjectAirplaneIds, true);// 論理削除

                        // 編集対象にならなかった航空券科目レコードを削除
                        $deleteSubjectIds = $this->reservePurchasingSubjectAirplaneService->deleteOtherIdsForSchedule($reserveSchedule->id, $editSubjectAirplaneIds, true);// 論理削除

                        ///////// 編集対象にならなかったホテル科目の削除処理（科目レコード＆ホテル科目レコード） /////////

                        // 編集対象にならなかった科目レコードを削除
                        $this->reservePurchasingSubjectService->deleteOtherSubjectableIdsForSchedule($reserveSchedule->id, 'App\Models\ReservePurchasingSubjectHotel', $editSubjectHotelIds, true);// 論理削除

                        // 編集対象にならなかったホテル科目レコードを削除
                        $deleteSubjectIds = $this->reservePurchasingSubjectHotelService->deleteOtherIdsForSchedule($reserveSchedule->id, $editSubjectHotelIds, true);// 論理削除
                    }
                }
                // 編集対象にならなかったスケジュールレコードを削除
                $this->reserveScheduleService->deleteOtherIdsForTravelDate($reserveTravelDate->id, $editScheduleIds, true); // 論理削除
            }
        }
        // 編集対象にならなかった日付レコードを削除
        $this->reserveTravelDateService->deleteOtherIdsForReserveItinerary($reserveItinerary->id, $editTravelDateIds, true); // 論理削除

        ///////// 買い掛け金データの整理 ////////
        $supplierIds = []; // 当行程に関する仕入先ID一覧
        foreach ($suppliers as $supplier) {
            $supplierIds[] = $supplier->id;
        }
        // 買掛金明細がなくなった買掛金レコードを削除（親レコード、子レコードとも）
        $this->accountPayableService->deleteLostPurchaseData($reserveItinerary->id, $supplierIds, true);
    }

    /**
     * 更新
     *
     * @param int $id 行程ID
     * @param array $data 編集データ
     * @return ReserveItinerary
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function update(int $id, array $input) : ReserveItinerary
    {
        $reserveItinerary = $this->reserveItineraryRepository->find($id);
        if ($reserveItinerary->updated_at != Arr::get($input, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        $this->reserveItineraryRepository->updateField($id, [
            'note' => Arr::get($input, 'note')
        ]); // 備考

        $this->editCommon($reserveItinerary, $input);

        return $this->reserveItineraryRepository->find($id);
    }

    /**
     * 全取得
     *
     * @param int $reserveId
     */
    public function getByReserveId(int $reserveId, array $with=[], array $select=[]) : Collection
    {
        return $this->reserveItineraryRepository->getByReserveId($reserveId, $with, $select);
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        if ($isSoftDelete) { // 論理削除の場合は一応、有効フラグを外しておく
            $this->reserveItineraryRepository->updateField($id, ['enabled' => false]);
        }
        return $this->reserveItineraryRepository->delete($id, $isSoftDelete);
    }

    /**
     * 当該予約において、現在有効の行程IDを取得
     *
     * @param int $reserveId 予約ID
     * @return ReserveItinerary
     */
    public function getEnableItineraryByReserveId(int $reserveId) : ?ReserveItinerary
    {
        $this->reserveItineraryRepository->findWhere([
            'reserve_id' => $reserveId,
            'enabled' => true
        ]);
    }

    /**
     * 有効を設定
     *
     * @param int $id 行程ID
     * @param int $reserveId 予約ID
     */
    public function setEnabled(int $id, int $reserveId) : ReserveItinerary
    {
        /**
         * ①当該予約の有効フラグを一旦全てOff
         * ↓
         * ②当該行程の有効フラグをOn
         */

        // ①
        $this->reserveItineraryRepository->updateWhere(
            ['reserve_id' => $reserveId],
            ['enabled' => false]
        );

        // ②
        return $this->reserveItineraryRepository->updateField($id, ['enabled' => true]);
    }

    /**
     * 管理番号を生成
     *
     * フォーマット: K + 3桁連番（予約IDに対する連番）
     *
     * @param int $reserveId 予約ID
     * @return string
     */
    public function createUserNumber(int $reserveId) : string
    {
        // 論理削除も含めた予約IDに対するレコード数を集計
        $count = $this->reserveItineraryRepository->getCountByReserveId($reserveId, true);

        return sprintf("K-%03d", $count + 1);
    }
}
