<?php

namespace App\Services;

use App\Models\ReservePurchasingSubjectAirplaneCustomValue;
use Illuminate\Support\Arr;
use App\Repositories\ReservePurchasingSubjectAirplaneCustomValue\ReservePurchasingSubjectAirplaneCustomValueRepository;
use App\Repositories\UserCustomItem\UserCustomItemRepository;
use Illuminate\Database\Eloquent\Model;

class ReservePurchasingSubjectAirplaneCustomValueService
{
    public function __construct(
        ReservePurchasingSubjectAirplaneCustomValueRepository $reservePurchasingSubjectAirplaneCustomValueRepository, UserCustomItemRepository $userCustomItemRepository)
    {
        $this->reservePurchasingSubjectAirplaneCustomValueRepository = $reservePurchasingSubjectAirplaneCustomValueRepository;
        $this->userCustomItemRepository = $userCustomItemRepository;
    }

    /**
     * カスタム項目値をinsert or update
     *
     * @param array $fields 「項目キー => 値」形式の配列
     * @param int $reservePurchasingSubjectAirplaneId 仕入科目航空券ID
     * @return bool
     */
    public function upsertCustomFileds(array $fields, int $reservePurchasingSubjectAirplaneId) : bool
    {
        $userCustomItems = $this->userCustomItemRepository->getByKeys(array_keys($fields,), [], ['id','key']);

        foreach ($userCustomItems as $uci) {
            $this->reservePurchasingSubjectAirplaneCustomValueRepository->updateOrCreate(
                ['reserve_purchasing_subject_airplane_id' => $reservePurchasingSubjectAirplaneId, 'user_custom_item_id' => $uci->id],
                ['val' => Arr::get($fields, $uci->key)]
            );
        }

        return true;
    }

    /**
     * 項目更新
     */
    public function updateField(int $reservePurchasingSubjectAirplaneId, array $params) : Model
    {
        return $this->reservePurchasingSubjectAirplaneCustomValueRepository->updateField($reservePurchasingSubjectAirplaneId, $params);
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
        return $this->reservePurchasingSubjectAirplaneCustomValueRepository->deleteByUserId($reserveId, $isSoftDelete);
    }
}
