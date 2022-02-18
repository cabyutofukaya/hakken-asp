<?php

namespace App\Services;

use App\Models\AgencyWithdrawalCustomValue;
use App\Repositories\AgencyWithdrawalCustomValue\AgencyWithdrawalCustomValueRepository;
use App\Repositories\UserCustomItem\UserCustomItemRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AgencyWithdrawalCustomValueService
{
    public function __construct(
        AgencyWithdrawalCustomValueRepository $agencyWithdrawalCustomValueRepository,
        UserCustomItemRepository $userCustomItemRepository
    )
    {
        $this->agencyWithdrawalCustomValueRepository = $agencyWithdrawalCustomValueRepository;
        $this->userCustomItemRepository = $userCustomItemRepository;
    }

    /**
     * カスタム項目値をinsert or update
     *
     * @param array $fields 「項目キー => 値」形式の配列
     * @param int $agencyWithdrawalId 出金ID
     * @return bool
     */
    public function upsertCustomFileds(array $fields, int $agencyWithdrawalId) : bool
    {
        $userCustomItems = $this->userCustomItemRepository->getByKeys(array_keys($fields,), [], ['id','key']);

        foreach ($userCustomItems as $uci) {
            $this->agencyWithdrawalCustomValueRepository->updateOrCreate(
                ['agency_withdrawal_id' => $agencyWithdrawalId, 'user_custom_item_id' => $uci->id],
                ['val' => Arr::get($fields, $uci->key)]
            );
        }

        return true;
    }

    /**
     * 項目更新
     */
    public function updateField(int $agencyWithdrawalCustomValueId, array $params) : Model
    {
        return $this->agencyWithdrawalCustomValueRepository->updateField($agencyWithdrawalCustomValueId, $params);
    }

    /**
     * 当該IDに紐づくカスタム項目値を削除
     *
     * @param int $agencyWithdrawalId 出金ID
     * @param bool $isSoftDelete 論理削除の場合はtrue
     * @return bool
     */
    public function deleteByUserId(int $agencyWithdrawalId, bool $isSoftDelete=true) : bool
    {
        return $this->agencyWithdrawalCustomValueRepository->deleteByUserId($agencyWithdrawalId, $isSoftDelete);
    }
}
