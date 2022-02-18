<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Support\Collection;
use App\Repositories\City\CityRepository;
use App\Repositories\Agency\AgencyRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Traits\AreaSuggestTrait;

class CityService
{
    use AreaSuggestTrait;
    
    public function __construct(AgencyRepository $agencyRepository, CityRepository $cityRepository)
    {
        $this->agencyRepository = $agencyRepository;
        $this->cityRepository = $cityRepository;
    }

    /**
     * 該当IDを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     */
    public function find(int $id, array $select=[]) : ?City
    {
        return $this->cityRepository->find($id, $select);
    }

    /**
     * 科目カテゴリデータを取得
     *
     * @return Illuminate\Support\Collection
     */
    public function allByAgencyAccount(string $account, array $with=[], array $select=[], string $order="id", string $direction="asc") : Collection
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);
        return $this->cityRepository->allByAgencyId($agencyId, $with, $select, $order, $direction);
    }

    /**
     * 一覧を取得（アカウント用）
     *
     * @param string $account
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $account, array $params, int $limit, array $with = [], array $select=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);
        return $this->cityRepository->paginateByAgencyId($agencyId, $params, $limit, $with, $select);
    }

    /**
     * 検索
     *
     * @param int $limit 取得件数。nullの場合は全件取得
     */
    public function search(string $agencyAccount, string $str, array $with=[], array $select=[], $limit=null) : Collection
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->cityRepository->search($agencyId, $str, $with, $select, $limit);
    }

    public function create(array $data): City
    {
        return $this->cityRepository->create($data);
    }

    public function update(int $id, array $data): City
    {
        // 国・地域コードは更新不可なので一応、配列に入っていたらカットしておく
        if (isset($data['code'])) {
            unset($data['code']);
        }
        return $this->cityRepository->update($id, $data);
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->cityRepository->delete($id, $isSoftDelete);
    }

    /**
     * selectメニュー用の名前配列
     * 
     * @param string $account 会社アカウント
     * @return array
     */
    public function getNameSelectByAgencyAccount(string $account) : array
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);
        return $this->cityRepository->allByAgencyId($agencyId, [], ['id','name','code'])->map(function($city, $key){
            return [
                // 'id' => $city->getRouteKey(), // ハッシュID
                'id' => $city->id,
                'name' => sprintf("%s%s", $city->code, $city->name)
            ];
        })->pluck('name', 'id')->toArray();
    }

    /**
     * selectメニュー用データを取得
     *
     * @param string $uuid UUID
     */
    public function getDefaultSelectRow(int $id)
    {
        $city = $this->cityRepository->find($id, ['id','name','code']);
        if ($city) {
            return $this->getSelectRow($city->id, $city->code, $city->name);
        } else {
            return [
                'label' => '',
                'value' => ''
            ];
        }
    }
}
