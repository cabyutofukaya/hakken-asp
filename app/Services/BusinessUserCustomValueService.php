<?php

namespace App\Services;

use App\Models\BusinessUserCustomValue;
use Illuminate\Support\Arr;
use App\Repositories\BusinessUserCustomValue\BusinessUserCustomValueRepository;
use App\Repositories\UserCustomItem\UserCustomItemRepository;

class BusinessUserCustomValueService
{
    public function __construct(BusinessUserCustomValueRepository $businessUserCustomValueRepository, UserCustomItemRepository $userCustomItemRepository)
    {
        $this->businessUserCustomValueRepository = $businessUserCustomValueRepository;
        $this->userCustomItemRepository = $userCustomItemRepository;
    }

    /**
     * カスタム項目値をinsert or update
     *
     * @param array $fields 「項目キー => 値」形式の配列
     * @param int $businessUserId 法人顧客ID
     * @return bool
     */
    public function upsertCustomFileds(array $fields, int $businessUserId) : bool
    {
        $userCustomItems = $this->userCustomItemRepository->getByKeys(array_keys($fields,), [], ['id','key']);

        foreach ($userCustomItems as $uci) {
            $this->businessUserCustomValueRepository->updateOrCreate(
                ['business_user_id' => $businessUserId, 'user_custom_item_id' => $uci->id],
                ['val' => Arr::get($fields, $uci->key)]
            );
        }

        return true;
    }

    /**
     * 当該IDに紐づくカスタム項目値を削除
     *
     * @param int $businessUserId 法人顧客ID
     * @param bool $isSoftDelete 論理削除の場合はtrue
     * @return bool
     */
    public function deleteByUserId(int $businessUserId, bool $isSoftDelete=true) : bool
    {
        return $this->businessUserCustomValueRepository->deleteByUserId($businessUserId, $isSoftDelete);
    }
}
