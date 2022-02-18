<?php

namespace App\Services;

use App\Models\ReservePurchasingSubjectOptionCustomValue;
use Illuminate\Support\Arr;
use App\Repositories\ReservePurchasingSubjectOptionCustomValue\ReservePurchasingSubjectOptionCustomValueRepository;
use App\Repositories\UserCustomItem\UserCustomItemRepository;
use Illuminate\Database\Eloquent\Model;

class ReservePurchasingSubjectOptionCustomValueService
{
    public function __construct(
        ReservePurchasingSubjectOptionCustomValueRepository $reservePurchasingSubjectOptionCustomValueRepository, UserCustomItemRepository $userCustomItemRepository)
    {
        $this->reservePurchasingSubjectOptionCustomValueRepository = $reservePurchasingSubjectOptionCustomValueRepository;
        $this->userCustomItemRepository = $userCustomItemRepository;
    }

    /**
     * カスタム項目値をinsert or update
     *
     * @param array $fields 「項目キー => 値」形式の配列
     * @param int $reservePurchasingSubjectOptionId 仕入科目オプションID
     * @return bool
     */
    public function upsertCustomFileds(array $fields, int $reservePurchasingSubjectOptionId) : bool
    {
        $userCustomItems = $this->userCustomItemRepository->getByKeys(array_keys($fields,), [], ['id','key']);

        foreach ($userCustomItems as $uci) {
            $this->reservePurchasingSubjectOptionCustomValueRepository->updateOrCreate(
                ['reserve_purchasing_subject_option_id' => $reservePurchasingSubjectOptionId, 'user_custom_item_id' => $uci->id],
                ['val' => Arr::get($fields, $uci->key)]
            );
        }

        return true;
    }

    /**
     * 項目更新
     */
    public function updateField(int $reservePurchasingSubjectOptionId, array $params) : Model
    {
        return $this->reservePurchasingSubjectOptionCustomValueRepository->updateField($reservePurchasingSubjectOptionId, $params);
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
        return $this->reservePurchasingSubjectOptionCustomValueRepository->deleteByUserId($reserveId, $isSoftDelete);
    }
}
