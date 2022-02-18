<?php

namespace App\Services;

use App\Models\ReservePurchasingSubjectHotelCustomValue;
use Illuminate\Support\Arr;
use App\Repositories\ReservePurchasingSubjectHotelCustomValue\ReservePurchasingSubjectHotelCustomValueRepository;
use App\Repositories\UserCustomItem\UserCustomItemRepository;
use Illuminate\Database\Eloquent\Model;

class ReservePurchasingSubjectHotelCustomValueService
{
    public function __construct(
        ReservePurchasingSubjectHotelCustomValueRepository $reservePurchasingSubjectHotelCustomValueRepository, UserCustomItemRepository $userCustomItemRepository)
    {
        $this->reservePurchasingSubjectHotelCustomValueRepository = $reservePurchasingSubjectHotelCustomValueRepository;
        $this->userCustomItemRepository = $userCustomItemRepository;
    }

    /**
     * カスタム項目値をinsert or update
     *
     * @param array $fields 「項目キー => 値」形式の配列
     * @param int $reservePurchasingSubjectHotelId 仕入科目ホテルID
     * @return bool
     */
    public function upsertCustomFileds(array $fields, int $reservePurchasingSubjectHotelId) : bool
    {
        $userCustomItems = $this->userCustomItemRepository->getByKeys(array_keys($fields,), [], ['id','key']);

        foreach ($userCustomItems as $uci) {
            $this->reservePurchasingSubjectHotelCustomValueRepository->updateOrCreate(
                ['reserve_purchasing_subject_hotel_id' => $reservePurchasingSubjectHotelId, 'user_custom_item_id' => $uci->id],
                ['val' => Arr::get($fields, $uci->key)]
            );
        }

        return true;
    }

    /**
     * 項目更新
     */
    public function updateField(int $reservePurchasingSubjectHotelId, array $params) : Model
    {
        return $this->reservePurchasingSubjectHotelCustomValueRepository->updateField($reservePurchasingSubjectHotelId, $params);
    }

    /**
     * 当該IDに紐づくカスタム項目値を削除
     *
     * @param int $reserveId 法人顧客ID
     * @param bool $isSoftDelete 論理削除の場合はtrue
     * @return bool
     */
    public function deleteByUserId(int $reserveId, bool $isSoftDelete=true) : bool
    {
        return $this->reservePurchasingSubjectHotelCustomValueRepository->deleteByUserId($reserveId, $isSoftDelete);
    }
}
