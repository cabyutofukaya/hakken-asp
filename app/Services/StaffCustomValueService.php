<?php

namespace App\Services;

use Lang;
use Illuminate\Support\Str;
use App\Models\StaffCustomValue;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Repositories\StaffCustomValue\StaffCustomValueRepository;
use App\Repositories\UserCustomItem\UserCustomItemRepository;

class StaffCustomValueService
{
    public function __construct(StaffCustomValueRepository $staffCustomValueRepository, UserCustomItemRepository $userCustomItemRepository)
    {
        $this->staffCustomValueRepository = $staffCustomValueRepository;
        $this->userCustomItemRepository = $userCustomItemRepository;
    }

    /**
     * カスタム項目値をinsert or update
     * 
     * @param array $fields 「項目キー => 値」形式の配列
     * @param int $staffId スタッフID
     * @return bool
     */
    public function upsertCustomFileds(array $fields, int $staffId) : bool
    {
        $userCustomItems = $this->userCustomItemRepository->getByKeys(array_keys($fields,), [], ['id','key']);

        foreach ($userCustomItems as $uci) {
            $this->staffCustomValueRepository->updateOrCreate(
                ['staff_id' => $staffId, 'user_custom_item_id' => $uci->id],
                ['val' => Arr::get($fields, $uci->key)]
            );
        }

        return true;
    }
}
