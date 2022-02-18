<?php

namespace App\Services;

use App\Models\SubjectHotelCustomValue;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Repositories\SubjectHotelCustomValue\SubjectHotelCustomValueRepository;
use App\Repositories\UserCustomItem\UserCustomItemRepository;

class SubjectHotelCustomValueService
{
    public function __construct(SubjectHotelCustomValueRepository $subjectHotelCustomValueRepository, UserCustomItemRepository $userCustomItemRepository)
    {
        $this->subjectHotelCustomValueRepository = $subjectHotelCustomValueRepository;
        $this->userCustomItemRepository = $userCustomItemRepository;
    }

    /**
     * カスタム項目値をinsert or update
     *
     * @param array $fields 「項目キー => 値」形式の配列
     * @param int $subjectHotelId ホテル科目ID
     * @return bool
     */
    public function upsertCustomFileds(array $fields, int $subjectHotelId) : bool
    {
        $userCustomItems = $this->userCustomItemRepository->getByKeys(array_keys($fields,), [], ['id','key']);

        foreach ($userCustomItems as $uci) {
            $this->subjectHotelCustomValueRepository->updateOrCreate(
                ['subject_hotel_id' => $subjectHotelId, 'user_custom_item_id' => $uci->id],
                ['val' => Arr::get($fields, $uci->key)]
            );
        }

        return true;
    }

    /**
     * 当該ホテル科目IDに紐づくカスタム項目値を削除
     *
     * @param int $subjectHotelId ホテル科目ID
     * @param bool $isSoftDelete 論理削除の場合はtrue
     * @return bool
     */
    public function deleteBySubjectHotelId(int $subjectHotelId, bool $isSoftDelete=true) : bool
    {
        return $this->subjectHotelCustomValueRepository->deleteBySubjectHotelId($subjectHotelId, $isSoftDelete);
    }
}
