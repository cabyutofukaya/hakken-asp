<?php

namespace App\Services;

use App\Models\ReserveCustomValue;
use Illuminate\Support\Arr;
use App\Repositories\ReserveCustomValue\ReserveCustomValueRepository;
use App\Repositories\UserCustomItem\UserCustomItemRepository;
use Illuminate\Database\Eloquent\Model;

class ReserveCustomValueService
{
    public function __construct(
        ReserveCustomValueRepository $reserveCustomValueRepository,
        UserCustomItemRepository $userCustomItemRepository
    ) {
        $this->reserveCustomValueRepository = $reserveCustomValueRepository;
        $this->userCustomItemRepository = $userCustomItemRepository;
    }

    /**
     * カスタム項目値を作成
     */
    public function create(array $data) : ReserveCustomValue
    {
        return $this->reserveCustomValueRepository->create($data);
    }

    /**
     * カスタム項目値をinsert or update
     *
     * @param array $fields 「項目キー => 値」形式の配列
     * @param int $reserveId 法人顧客ID
     * @return bool
     */
    public function upsertCustomFileds(array $fields, int $reserveId) : bool
    {
        $userCustomItems = $this->userCustomItemRepository->getByKeys(array_keys($fields, ), [], ['id','key']);

        foreach ($userCustomItems as $uci) {
            $this->reserveCustomValueRepository->updateOrCreate(
                ['reserve_id' => $reserveId, 'user_custom_item_id' => $uci->id],
                ['val' => Arr::get($fields, $uci->key)]
            );
        }

        return true;
    }

    /**
     * 項目更新
     */
    public function updateField(int $reserveCustomValueId, array $params) : Model
    {
        return $this->reserveCustomValueRepository->updateField($reserveCustomValueId, $params);
    }

    /**
     * 当該管理コードの値をセット。管理コードと値は複数セットで指定可
     * $codeValsは 「管理コード=>値」形式の配列
     * 
     * @param array $codeVals 管理コードと値の配列
     * @param int $agencyId 会社ID
     * @param int $reserveId 予約ID
     * @return bool
     */
    public function setValuesForCodes(array $codeVals, int $agencyId, int $reserveId) : bool
    {
        $codes = array_keys($codeVals);

        $rows = $this->userCustomItemRepository->getByCodesForAgency($codes, $agencyId, [], ['id','code']);

        $userCustomItems = [];
        foreach ($rows as $row) {
            $tmp = [];

            $date = date('Y-m-d H:i:s');
            
            $tmp['reserve_id'] = $reserveId;
            $tmp['user_custom_item_id'] = $row->id;
            $tmp['created_at'] = $date;
            $tmp['updated_at'] = $date;
            $tmp['val'] = Arr::get($codeVals, $row->code);

            $userCustomItems[] = $tmp;
        }

        // reserve_custom_valuesテーブルにバルクインサート
        return $this->reserveCustomValueRepository->insert($userCustomItems);
    }

    /**
     * 当該IDに紐づくカスタム項目値を削除
     *
     * @param int $reserveId 法人顧客ID
     * @param bool $isSoftDelete 論理削除の場合はtrue
     * @return bool
     */
    public function deleteByUserId(int $reserveId, bool $isSoftDelete=true) : bool
    {
        return $this->reserveCustomValueRepository->deleteByUserId($reserveId, $isSoftDelete);
    }
}
