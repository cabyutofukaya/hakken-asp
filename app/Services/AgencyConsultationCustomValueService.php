<?php

namespace App\Services;

use App\Models\AgencyConsultationCustomValue;
use Illuminate\Support\Arr;
use App\Repositories\AgencyConsultationCustomValue\AgencyConsultationCustomValueRepository;
use App\Repositories\UserCustomItem\UserCustomItemRepository;
use Illuminate\Database\Eloquent\Model;

class AgencyConsultationCustomValueService
{
    public function __construct(
        AgencyConsultationCustomValueRepository $agencyConsultationCustomValueRepository,
        UserCustomItemRepository $userCustomItemRepository
    )
    {
        $this->agencyConsultationCustomValueRepository = $agencyConsultationCustomValueRepository;
        $this->userCustomItemRepository = $userCustomItemRepository;
    }

    /**
     * カスタム項目値を作成
     */
    public function create(array $data) : AgencyConsultationCustomValue
    {
        return $this->agencyConsultationCustomValueRepository->create($data);
    }

    /**
     * カスタム項目値をinsert or update
     *
     * @param array $fields 「項目キー => 値」形式の配列
     * @param int $agencyConsultationId 相談ID
     * @return bool
     */
    public function upsertCustomFileds(array $fields, int $agencyConsultationId) : bool
    {
        $userCustomItems = $this->userCustomItemRepository->getByKeys(array_keys($fields,), [], ['id','key']);

        foreach ($userCustomItems as $uci) {
            $this->agencyConsultationCustomValueRepository->updateOrCreate(
                ['agency_consultation_id' => $agencyConsultationId, 'user_custom_item_id' => $uci->id],
                ['val' => Arr::get($fields, $uci->key)]
            );
        }

        return true;
    }

    /**
     * 項目更新
     */
    public function updateField(int $agencyConsultationCustomValueId, array $params) : Model
    {
        return $this->agencyConsultationCustomValueRepository->updateField($agencyConsultationCustomValueId, $params);
    }

    /**
     * 当該IDに紐づくカスタム項目値を削除
     *
     * @param int $agencyConsultationId 相談ID
     * @param bool $isSoftDelete 論理削除の場合はtrue
     * @return bool
     */
    public function deleteByUserId(int $agencyConsultationId, bool $isSoftDelete=true) : bool
    {
        return $this->agencyConsultationCustomValueRepository->deleteByUserId($agencyConsultationId, $isSoftDelete);
    }
}
