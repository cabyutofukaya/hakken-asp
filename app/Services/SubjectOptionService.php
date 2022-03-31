<?php

namespace App\Services;

use Hashids;
use App\Models\SubjectOption;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\SubjectCategory\SubjectCategoryRepository;
use App\Repositories\SubjectOption\SubjectOptionRepository;
use App\Services\SubjectOptionCustomValueService;
use App\Traits\ConstsTrait;
use App\Traits\UserCustomItemTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Traits\SubjectSuggestTrait;

class SubjectOptionService
{
    use ConstsTrait, UserCustomItemTrait, SubjectSuggestTrait;
    
    public function __construct(SubjectCategoryRepository $subjectCategoryRepository, SubjectOptionRepository $subjectOptionRepository, AgencyRepository $agencyRepository, SubjectOptionCustomValueService $subjectOptionCustomValueService)
    {
        $this->subjectCategoryRepository = $subjectCategoryRepository;
        $this->subjectOptionRepository = $subjectOptionRepository;
        $this->agencyRepository = $agencyRepository;
        $this->subjectOptionCustomValueService = $subjectOptionCustomValueService;
    }
    
    /**
     * 一覧を取得（for 会社アカウント）
     *
     * @param string $agencyAccount 会社アカウント
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $agencyAccount, array $params, int $limit, array $with = [], array $select=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->subjectOptionRepository->paginateByAgencyId($agencyId, $params, $limit, $with, $select);
    }

    /**
     * 該当IDを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     */
    public function find(int $id, array $select=[])
    {
        return $this->subjectOptionRepository->find($id, $select);
    }

    /**
     * 当該仕入コードを一件取得
     *
     * @param string $code 仕入コード
     */
    public function findByCode(int $agencyId, string $code) : ?SubjectOption
    {
        return $this->subjectOptionRepository->findWhere(['agency_id' => $agencyId, 'code' => $code]);
    }

    /**
     * 作成
     */
    public function create(array $data): SubjectOption
    {
        if (!Arr::get($data, 'subject_category_id')) { // 科目カテゴリID
            $data['subject_category_id'] = $this->subjectCategoryRepository->getIdByCode(config('consts.subject_categories.SUBJECT_CATEGORY_OPTION'));
        }

        $subjectOption = $this->subjectOptionRepository->create($data);


        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->subjectOptionCustomValueService->upsertCustomFileds($customFields, $subjectOption->id); // カスタムフィールド保存
        }

        return $subjectOption;
    }

    public function update(int $id, array $data): SubjectOption
    {
        // コードは更新不可なので一応、配列に入っていたらカットしておく
        if (isset($data['code'])) {
            unset($data['code']);
        }

        $subjectOption = $this->subjectOptionRepository->update($id, $data);

        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->subjectOptionCustomValueService->upsertCustomFileds($customFields, $subjectOption->id); // カスタムフィールド保存
        }

        return $subjectOption;
    }

    /**
     * 検索
     *
     * @param int $limit 取得件数。nullの場合は全件取得
     */
    public function search(string $agencyAccount, string $str, array $with=[], array $select=[], $limit=null) : Collection
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->subjectOptionRepository->search($agencyId, $str, $with, $select, $limit);
    }

    /**
     * 初期表示リストを取得（react-select用）
     *
     * @param array $default 先頭データ
     */
    public function getDefaultOptions(int $agencyId, int $limit, array $defaultRow=[])
    {
        return $this->subjectOptionRepository->search($agencyId, '', ['supplier','v_subject_option_custom_values'], [], 30, 'id', 'desc')->map(function ($item, $key) {
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
        return $this->subjectOptionRepository->delete($id, $isSoftDelete);
    }
}
