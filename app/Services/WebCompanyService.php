<?php

namespace App\Services;

use App\Models\WebCompany;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\WebCompany\WebCompanyRepository;


class WebCompanyService
{
    public function __construct(AgencyRepository $agencyRepository, WebCompanyRepository $webCompanyRepository)
    {
        $this->agencyRepository = $agencyRepository;
        $this->webCompanyRepository = $webCompanyRepository;
    }

    /**
     * 当該会社に紐づく会社情報を一件取得
     */
    public function findByAgencyId(int $agencyId) : ?WebCompany
    {
        return $this->webCompanyRepository->findWhere(['agency_id' => $agencyId]);
    }

    /**
     * 新規登録or更新
     *
     * @param array $where アップサート条件
     * @param array $input
     */
    public function upsert(array $where, array $input) : WebCompany
    {
        return $this->webCompanyRepository->updateOrCreate($where, $input);
    }
}
