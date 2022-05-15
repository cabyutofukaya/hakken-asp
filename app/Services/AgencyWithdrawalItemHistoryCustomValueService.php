<?php

namespace App\Services;

use App\Models\AgencyWithdrawalItemHistoryCustomValue;
use App\Repositories\AgencyWithdrawalItemHistoryCustomValue\AgencyWithdrawalItemHistoryCustomValueRepository;
use App\Repositories\UserCustomItem\UserCustomItemRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AgencyWithdrawalItemHistoryCustomValueService
{
    public function __construct(
        AgencyWithdrawalItemHistoryCustomValueRepository $agencyWithdrawalItemHistoryCustomValueRepository,
        UserCustomItemRepository $userCustomItemRepository
    )
    {
        $this->agencyWithdrawalItemHistoryCustomValueRepository = $agencyWithdrawalItemHistoryCustomValueRepository;
        $this->userCustomItemRepository = $userCustomItemRepository;
    }

    /**
     * カスタム項目値をinsert or update
     *
     * @param array $fields 「項目キー => 値」形式の配列
     * @param int $agencyWithdrawalItemHistoryId 出金ID
     * @return bool
     */
    public function upsertCustomFileds(array $fields, int $agencyWithdrawalItemHistoryId) : bool
    {
        $userCustomItems = $this->userCustomItemRepository->getByKeys(array_keys($fields,), [], ['id','key']);

        foreach ($userCustomItems as $uci) {
            $this->agencyWithdrawalItemHistoryCustomValueRepository->updateOrCreate(
                ['agency_withdrawal_item_history_id' => $agencyWithdrawalItemHistoryId, 'user_custom_item_id' => $uci->id],
                ['val' => Arr::get($fields, $uci->key)]
            );
        }

        return true;
    }

    /**
     * 項目更新
     */
    public function updateField(int $agencyWithdrawalItemHistoryCustomValueId, array $params) : Model
    {
        return $this->agencyWithdrawalItemHistoryCustomValueRepository->updateField($agencyWithdrawalItemHistoryCustomValueId, $params);
    }

    /**
     * 当該IDに紐づくカスタム項目値を削除
     *
     * @param int $agencyWithdrawalItemHistoryId 出金ID
     * @param bool $isSoftDelete 論理削除の場合はtrue
     * @return bool
     */
    public function deleteByUserId(int $agencyWithdrawalItemHistoryId, bool $isSoftDelete=true) : bool
    {
        return $this->agencyWithdrawalItemHistoryCustomValueRepository->deleteByUserId($agencyWithdrawalItemHistoryId, $isSoftDelete);
    }
}
