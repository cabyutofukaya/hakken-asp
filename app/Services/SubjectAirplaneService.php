<?php

namespace App\Services;

use Hashids;
use App\Models\SubjectAirplane;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\SubjectAirplane\SubjectAirplaneRepository;
use App\Repositories\SubjectCategory\SubjectCategoryRepository;
use App\Services\SubjectAirplaneCustomValueService;
use App\Traits\ConstsTrait;
use App\Traits\UserCustomItemTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Traits\SubjectSuggestTrait;

class SubjectAirplaneService
{
    use ConstsTrait, UserCustomItemTrait, SubjectSuggestTrait;
    
    public function __construct(SubjectCategoryRepository $subjectCategoryRepository, SubjectAirplaneRepository $subjectAirplaneRepository, AgencyRepository $agencyRepository, SubjectAirplaneCustomValueService $subjectAirplaneCustomValueService)
    {
        $this->subjectCategoryRepository = $subjectCategoryRepository;
        $this->subjectAirplaneRepository = $subjectAirplaneRepository;
        $this->agencyRepository = $agencyRepository;
        $this->subjectAirplaneCustomValueService = $subjectAirplaneCustomValueService;
    }

    /**
     * 一覧を取得（for 会社アカウント）
     *
     * @param string $account 会社アカウント
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $account, array $params, int $limit, array $with = [], array $select=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);
        return $this->subjectAirplaneRepository->paginateByAgencyId($agencyId, $params, $limit, $with, $select);
    }

    /**
     * 該当IDを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     */
    public function find(int $id, array $select=[])
    {
        return $this->subjectAirplaneRepository->find($id, $select);
    }

    /**
     * 当該仕入コードを一件取得
     *
     * @param string $code 仕入コード
     */
    public function findByCode(int $agencyId, string $code) : ?SubjectAirplane
    {
        return $this->subjectAirplaneRepository->findWhere(['agency_id' => $agencyId, 'code' => $code]);
    }

    /**
     * 検索
     *
     * @param int $limit 取得件数。nullの場合は全件取得
     */
    public function search(string $agencyAccount, string $str, array $with=[], array $select=[], $limit=null) : Collection
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->subjectAirplaneRepository->search($agencyId, $str, $with, $select, $limit);
    }

    /**
     * 作成
     */
    public function create(array $data): SubjectAirplane
    {
        if (!Arr::get($data, 'subject_category_id')) { // 科目カテゴリID
            $data['subject_category_id'] = $this->subjectCategoryRepository->getIdByCode(config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE'));
        }
        $subjectAirplane = $this->subjectAirplaneRepository->create($data);

        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->subjectAirplaneCustomValueService->upsertCustomFileds($customFields, $subjectAirplane->id); // カスタムフィールド保存
        }

        return $subjectAirplane;

    }

    public function update(int $id, array $data): SubjectAirplane
    {
        // コードは更新不可なので一応、配列に入っていたらカットしておく
        if (isset($data['code'])) {
            unset($data['code']);
        }
        
        $subjectAirplane = $this->subjectAirplaneRepository->update($id, $data);

        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->subjectAirplaneCustomValueService->upsertCustomFileds($customFields, $subjectAirplane->id); // カスタムフィールド保存
        }

        return $subjectAirplane;
    }

    /**
     * 初期表示リストを取得（react-select用）
     *
     * @param array $default 先頭データ
     */
    public function getDefaultOptions(int $agencyId, int $limit, array $defaultRow=[]) : array
    {
        return $this->subjectAirplaneRepository->search($agencyId, '', ['supplier','v_subject_airplane_custom_values'], [], 30, 'id', 'desc')->map(function ($item, $key) {
            return $this->getSelectRow($item);
        })->prepend($defaultRow)->all();
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->subjectAirplaneRepository->delete($id, $isSoftDelete);
    }

}
