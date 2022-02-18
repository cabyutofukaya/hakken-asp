<?php

namespace App\Services;

use App\Models\UserCustomValue;
use Illuminate\Support\Arr;
use App\Repositories\UserCustomValue\UserCustomValueRepository;
use App\Repositories\UserCustomItem\UserCustomItemRepository;

class UserCustomValueService
{
    public function __construct(UserCustomValueRepository $userCustomValueRepository, UserCustomItemRepository $userCustomItemRepository)
    {
        $this->userCustomValueRepository = $userCustomValueRepository;
        $this->userCustomItemRepository = $userCustomItemRepository;
    }

    /**
     * カスタム項目値をinsert or update
     *
     * @param array $fields 「項目キー => 値」形式の配列
     * @param int $userId ユーザーID
     * @return bool
     */
    public function upsertCustomFileds(array $fields, int $userId) : bool
    {
        $userCustomItems = $this->userCustomItemRepository->getByKeys(array_keys($fields,), [], ['id','key']);

        foreach ($userCustomItems as $uci) {
            $this->userCustomValueRepository->updateOrCreate(
                ['user_id' => $userId, 'user_custom_item_id' => $uci->id],
                ['val' => Arr::get($fields, $uci->key)]
            );
        }

        return true;
    }

    /**
     * 当該IDに紐づくカスタム項目値を削除
     *
     * @param int $userId ユーザーID
     * @param bool $isSoftDelete 論理削除の場合はtrue
     * @return bool
     */
    public function deleteByUserId(int $userId, bool $isSoftDelete=true) : bool
    {
        return $this->userCustomValueRepository->deleteByUserId($userId, $isSoftDelete);
    }
}
