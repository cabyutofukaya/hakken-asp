<?php

namespace App\Services;

use App\Models\AgencyDepositCustomValue;
use App\Repositories\AgencyDepositCustomValue\AgencyDepositCustomValueRepository;
use App\Repositories\UserCustomItem\UserCustomItemRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AgencyDepositCustomValueService
{
    public function __construct(
        AgencyDepositCustomValueRepository $agencyDepositCustomValueRepository,
        UserCustomItemRepository $userCustomItemRepository
    )
    {
        $this->agencyDepositCustomValueRepository = $agencyDepositCustomValueRepository;
        $this->userCustomItemRepository = $userCustomItemRepository;
    }

    /**
     * カスタム項目値をinsert or update
     *
     * @param array $fields 「項目キー => 値」形式の配列
     * @param int $AgencyDepositId 出金ID
     * @return bool
     */
    public function upsertCustomFileds(array $fields, int $AgencyDepositId) : bool
    {
        $userCustomItems = $this->userCustomItemRepository->getByKeys(array_keys($fields,), [], ['id','key']);

        foreach ($userCustomItems as $uci) {
            $this->agencyDepositCustomValueRepository->updateOrCreate(
                ['agency_deposit_id' => $AgencyDepositId, 'user_custom_item_id' => $uci->id],
                ['val' => Arr::get($fields, $uci->key)]
            );
        }

        return true;
    }

    /**
     * 項目更新
     */
    public function updateField(int $agencyDepositCustomValueId, array $params) : Model
    {
        return $this->agencyDepositCustomValueRepository->updateField($agencyDepositCustomValueId, $params);
    }

    /**
     * 当該IDに紐づくカスタム項目値を削除
     *
     * @param int $AgencyDepositId 出金ID
     * @param bool $isSoftDelete 論理削除の場合はtrue
     * @return bool
     */
    public function deleteByUserId(int $AgencyDepositId, bool $isSoftDelete=true) : bool
    {
        return $this->agencyDepositCustomValueRepository->deleteByUserId($AgencyDepositId, $isSoftDelete);
    }
}
