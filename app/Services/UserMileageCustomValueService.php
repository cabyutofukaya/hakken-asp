<?php

namespace App\Services;

use App\Models\UserMileageCustomValue;
use Illuminate\Support\Arr;
use App\Repositories\UserMileageCustomValue\UserMileageCustomValueRepository;
use App\Repositories\UserCustomItem\UserCustomItemRepository;

class UserMileageCustomValueService
{
    public function __construct(UserMileageCustomValueRepository $userMileageCustomValueRepository, UserCustomItemRepository $userCustomItemRepository)
    {
        $this->userMileageCustomValueRepository = $userMileageCustomValueRepository;
        $this->userCustomItemRepository = $userCustomItemRepository;
    }

    /**
     * カスタム項目値をinsert or update
     *
     * @param array $fields 「項目キー => 値」形式の配列
     * @param int $userMileageId ユーザーマイレージID
     * @return bool
     */
    public function upsertCustomFileds(array $fields, int $userMileageId) : bool
    {
        $userCustomItems = $this->userCustomItemRepository->getByKeys(array_keys($fields,), [], ['id','key']);

        foreach ($userCustomItems as $uci) {
            $this->userMileageCustomValueRepository->updateOrCreate(
                ['user_mileage_id' => $userMileageId, 'user_custom_item_id' => $uci->id],
                ['val' => Arr::get($fields, $uci->key)]
            );
        }

        return true;
    }

    /**
     * 当該仕入IDに紐づくカスタム項目値を削除
     *
     * @param int $userMileageId ユーザーマイレージID
     * @param bool $isSoftDelete 論理削除の場合はtrue
     * @return bool
     */
    public function deleteBySupplierId(int $userMileageId, bool $isSoftDelete=true) : bool
    {
        return $this->userMileageCustomValueRepository->deleteBySupplierId($userMileageId, $isSoftDelete);
    }
}
