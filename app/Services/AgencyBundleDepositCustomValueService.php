<?php

namespace App\Services;

use App\Models\AgencyBundleDepositCustomValue;
use App\Repositories\AgencyBundleDepositCustomValue\AgencyBundleDepositCustomValueRepository;
use App\Repositories\UserCustomItem\UserCustomItemRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AgencyBundleDepositCustomValueService
{
    public function __construct(
        AgencyBundleDepositCustomValueRepository $agencyBundleDepositCustomValueRepository,
        UserCustomItemRepository $userCustomItemRepository
    )
    {
        $this->agencyBundleDepositCustomValueRepository = $agencyBundleDepositCustomValueRepository;
        $this->userCustomItemRepository = $userCustomItemRepository;
    }

    /**
     * カスタム項目値をinsert or update
     *
     * @param array $fields 「項目キー => 値」形式の配列
     * @param int $agencyBundleDepositId 出金ID
     * @return bool
     */
    public function upsertCustomFileds(array $fields, int $agencyBundleDepositId) : bool
    {
        $userCustomItems = $this->userCustomItemRepository->getByKeys(array_keys($fields,), [], ['id','key']);

        foreach ($userCustomItems as $uci) {
            $this->agencyBundleDepositCustomValueRepository->updateOrCreate(
                ['agency_bundle_deposit_id' => $agencyBundleDepositId, 'user_custom_item_id' => $uci->id],
                ['val' => Arr::get($fields, $uci->key)]
            );
        }

        return true;
    }

    /**
     * 項目更新
     */
    public function updateField(int $agencyBundleDepositCustomValueId, array $params) : Model
    {
        return $this->agencyBundleDepositCustomValueRepository->updateField($agencyBundleDepositCustomValueId, $params);
    }

    /**
     * 当該IDに紐づくカスタム項目値を削除
     *
     * @param int $agencyBundleDepositId 出金ID
     * @param bool $isSoftDelete 論理削除の場合はtrue
     * @return bool
     */
    public function deleteByUserId(int $agencyBundleDepositId, bool $isSoftDelete=true) : bool
    {
        return $this->agencyBundleDepositCustomValueRepository->deleteByUserId($agencyBundleDepositId, $isSoftDelete);
    }
}
