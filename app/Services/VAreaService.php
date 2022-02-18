<?php

namespace App\Services;

use App\Models\VArea;
use Illuminate\Support\Collection;
use App\Repositories\VArea\VAreaRepository;
use App\Repositories\Agency\AgencyRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use App\Traits\AreaSuggestTrait;

class VAreaService
{
    use AreaSuggestTrait;

    public function __construct(AgencyRepository $agencyRepository, VAreaRepository $vAreaRepository)
    {
        $this->agencyRepository = $agencyRepository;
        $this->vAreaRepository = $vAreaRepository;
    }

    /**
     * 当該UUIDを一件取得
     *
     * @param string $uuid
     */
    public function findByUuid(string $uuid, array $select=[]) : ?VArea
    {
        return $this->vAreaRepository->findByUuid($uuid, $select);
    }

    /**
     * selectメニュー用データを取得
     *
     * @param string $uuid UUID
     */
    public function getDefaultSelectRow(string $uuid)
    {
        $vArea = $this->vAreaRepository->findByUuid($uuid, ['uuid','name','code']);
        if ($vArea) {
            return $this->getSelectRow($vArea->uuid, $vArea->code, $vArea->name);
        } else {
            return [
                'label' => '',
                'value' => ''
            ];
        }
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
        return $this->vAreaRepository->paginateByAgencyId($agencyId, $params, $limit, $with, $select);
    }

    /**
     * 当該会社に紐づく名称一覧を取得
     *
     * @return array
     */
    public function getNameListByAgencyAccount(string $account) : array
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);

        return $this->vAreaRepository->getByAgencyAccount($agencyId, ['uuid', 'name'])->pluck("name", "uuid")->toArray();
    }

    /**
     * 検索
     *
     * @param int $limit 取得件数。nullの場合は全件取得
     */
    public function search(string $agencyAccount, string $str, array $with=[], array $select=[], $limit=null) : Collection
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->vAreaRepository->search($agencyId, $str, $with, $select, $limit);
    }
}
