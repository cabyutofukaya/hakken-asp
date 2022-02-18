<?php

namespace App\Services;

use App\Models\VDirection;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\VDirection\VDirectionRepository;
use App\Traits\AreaSuggestTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class VDirectionService
{
    use AreaSuggestTrait;
    
    public function __construct(VDirectionRepository $vDirectionRepository, AgencyRepository $agencyRepository)
    {
        $this->vDirectionRepository = $vDirectionRepository;
        $this->agencyRepository = $agencyRepository;
    }

    /**
     * 当該UUIDを一件取得
     * 
     * @param string $uuid
     */
    public function findByUuid(string $uuid, array $select=[]) : ?VDirection
    {
        return $this->vDirectionRepository->findByUuid($uuid, $select);
    }

    /**
     * 方向一覧を取得（for 会社アカウント）
     *
     * @param string $account
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $account, array $params, int $limit, $select=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);
        return $this->vDirectionRepository->paginateByAgencyId($agencyId, $params, $limit, $select);
    }

    /**
     * 名称一覧を取得（for 会社アカウント）
     * 
     * @return array
     */
    public function getNameListByAgencyAccount(string $account) : array
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);

        return $this->vDirectionRepository->getByAgencyAccount($agencyId, ['uuid', 'name'])->pluck("name", "uuid")->toArray();
    }

    /**
     * 検索
     *
     * @param int $limit 取得件数。nullの場合は全件取得
     */
    public function search(string $agencyAccount, string $str, array $with=[], array $select=[], $limit=null) : Collection
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->vDirectionRepository->search($agencyId, $str, $with, $select, $limit);
    }

    /**
     * selectメニュー用データを取得
     *
     * @param string $uuid UUID
     */
    public function getDefaultSelectRow(string $uuid)
    {
        $vDirection = $this->vDirectionRepository->findByUuid($uuid, ['uuid','name','code']);
        if ($vDirection) {
            return $this->getSelectRow($vDirection->uuid, $vDirection->code, $vDirection->name);
        } else {
            return [
                'label' => '',
                'value' => ''
            ];
        }
    }
}
