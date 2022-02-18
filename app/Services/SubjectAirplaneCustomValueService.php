<?php

namespace App\Services;

use App\Models\SubjectAirplaneCustomValue;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Repositories\SubjectAirplaneCustomValue\SubjectAirplaneCustomValueRepository;
use App\Repositories\UserCustomItem\UserCustomItemRepository;

class SubjectAirplaneCustomValueService
{
    public function __construct(SubjectAirplaneCustomValueRepository $subjectAirplaneCustomValueRepository, UserCustomItemRepository $userCustomItemRepository)
    {
        $this->subjectAirplaneCustomValueRepository = $subjectAirplaneCustomValueRepository;
        $this->userCustomItemRepository = $userCustomItemRepository;
    }

    /**
     * カスタム項目値をinsert or update
     *
     * @param array $fields 「項目キー => 値」形式の配列
     * @param int $subjectAirplaneId 航空券科目ID
     * @return bool
     */
    public function upsertCustomFileds(array $fields, int $subjectAirplaneId) : bool
    {
        $userCustomItems = $this->userCustomItemRepository->getByKeys(array_keys($fields,), [], ['id','key']);

        foreach ($userCustomItems as $uci) {
            $this->subjectAirplaneCustomValueRepository->updateOrCreate(
                ['subject_airplane_id' => $subjectAirplaneId, 'user_custom_item_id' => $uci->id],
                ['val' => Arr::get($fields, $uci->key)]
            );
        }

        return true;
    }

    /**
     * 当該航空券IDに紐づくカスタム項目値を削除
     *
     * @param int $subjectAirplaneId 航空券科目ID
     * @param bool $isSoftDelete 論理削除の場合はtrue
     * @return bool
     */
    public function deleteBySubjectAirplaneId(int $subjectAirplaneId, bool $isSoftDelete=true) : bool
    {
        return $this->subjectAirplaneCustomValueRepository->deleteBySubjectAirplaneId($subjectAirplaneId, $isSoftDelete);
    }
}
