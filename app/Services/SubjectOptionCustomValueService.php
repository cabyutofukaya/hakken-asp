<?php

namespace App\Services;

use App\Models\SubjectOptionCustomValue;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Repositories\SubjectOptionCustomValue\SubjectOptionCustomValueRepository;
use App\Repositories\UserCustomItem\UserCustomItemRepository;

class SubjectOptionCustomValueService
{
    public function __construct(SubjectOptionCustomValueRepository $subjectOptionCustomValueRepository, UserCustomItemRepository $userCustomItemRepository)
    {
        $this->subjectOptionCustomValueRepository = $subjectOptionCustomValueRepository;
        $this->userCustomItemRepository = $userCustomItemRepository;
    }

    /**
     * カスタム項目値をinsert or update
     *
     * @param array $fields 「項目キー => 値」形式の配列
     * @param int $subjectOptionId 仕入先ID
     * @return bool
     */
    public function upsertCustomFileds(array $fields, int $subjectOptionId) : bool
    {
        $userCustomItems = $this->userCustomItemRepository->getByKeys(array_keys($fields,), [], ['id','key']);

        foreach ($userCustomItems as $uci) {
            $this->subjectOptionCustomValueRepository->updateOrCreate(
                ['subject_option_id' => $subjectOptionId, 'user_custom_item_id' => $uci->id],
                ['val' => Arr::get($fields, $uci->key)]
            );
        }

        return true;
    }

    /**
     * 当該仕入IDに紐づくカスタム項目値を削除
     *
     * @param int $subjectOptionId 仕入先ID
     * @param bool $isSoftDelete 論理削除の場合はtrue
     * @return bool
     */
    public function deleteBySubjectOptionId(int $subjectOptionId, bool $isSoftDelete=true) : bool
    {
        return $this->subjectOptionCustomValueRepository->deleteBySubjectOptionId($subjectOptionId, $isSoftDelete);
    }
}
