<?php

namespace App\Services;

use App\Models\MailTemplate;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\MailTemplate\MailTemplateRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class MailTemplateService
{
    public function __construct(AgencyRepository $agencyRepository, MailTemplateRepository $mailTemplateRepository)
    {
        $this->agencyRepository = $agencyRepository;
        $this->mailTemplateRepository = $mailTemplateRepository;
    }

    /**
     * 該当IDを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     */
    public function find(int $id, array $select=[])
    {
        return $this->mailTemplateRepository->find($id, $select);
    }

    /**
     * メールテンプレート一覧を取得（アカウント用）
     *
     * @param string $account
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $account, int $limit, $select=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);
        return $this->mailTemplateRepository->paginateByAgencyId($agencyId, $limit, $select);
    }

    public function create(array $data): MailTemplate
    {
        return $this->mailTemplateRepository->create($data);
    }

    public function update(int $id, array $data): MailTemplate
    {
        return $this->mailTemplateRepository->update($id, $data);
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->mailTemplateRepository->delete($id, $isSoftDelete);
    }
}
