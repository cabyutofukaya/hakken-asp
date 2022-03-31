<?php

namespace App\Services;

use Hashids;
use App\Models\SubjectHotel;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\SubjectCategory\SubjectCategoryRepository;
use App\Repositories\SubjectHotel\SubjectHotelRepository;
use App\Services\SubjectHotelCustomValueService;
use App\Traits\ConstsTrait;
use App\Traits\UserCustomItemTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Traits\SubjectSuggestTrait;

class SubjectHotelService
{
    use ConstsTrait, UserCustomItemTrait, SubjectSuggestTrait;
    
    public function __construct(SubjectCategoryRepository $subjectCategoryRepository, SubjectHotelRepository $subjectHotelRepository, AgencyRepository $agencyRepository, SubjectHotelCustomValueService $subjectHotelCustomValueService)
    {
        $this->subjectCategoryRepository = $subjectCategoryRepository;
        $this->subjectHotelRepository = $subjectHotelRepository;
        $this->agencyRepository = $agencyRepository;
        $this->subjectHotelCustomValueService = $subjectHotelCustomValueService;
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
        return $this->subjectHotelRepository->paginateByAgencyId($agencyId, $params, $limit, $with, $select);
    }

    /**
     * 該当IDを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     */
    public function find(int $id, array $select=[])
    {
        return $this->subjectHotelRepository->find($id, $select);
    }

    /**
     * 当該仕入コードを一件取得
     *
     * @param string $code 仕入コード
     */
    public function findByCode(int $agencyId, string $code) : ?SubjectHotel
    {
        return $this->subjectHotelRepository->findWhere(['agency_id' => $agencyId, 'code' => $code]);
    }

    /**
     * 検索
     *
     * @param int $limit 取得件数。nullの場合は全件取得
     */
    public function search(string $agencyAccount, string $str, array $with=[], array $select=[], $limit=null) : Collection
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->subjectHotelRepository->search($agencyId, $str, $with, $select, $limit);
    }

    /**
     * 作成
     */
    public function create(array $data): SubjectHotel
    {
        if (!Arr::get($data, 'subject_category_id')) { // 科目カテゴリID
            $data['subject_category_id'] = $this->subjectCategoryRepository->getIdByCode(config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL'));
        }
        $subjectHotel = $this->subjectHotelRepository->create($data);


        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->subjectHotelCustomValueService->upsertCustomFileds($customFields, $subjectHotel->id); // カスタムフィールド保存
        }

        return $subjectHotel;
    }

    public function update(int $id, array $data): SubjectHotel
    {
        // コードは更新不可なので一応、配列に入っていたらカットしておく
        if (isset($data['code'])) {
            unset($data['code']);
        }
        
        $subjectHotel = $this->subjectHotelRepository->update($id, $data);

        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->subjectHotelCustomValueService->upsertCustomFileds($customFields, $subjectHotel->id); // カスタムフィールド保存
        }

        return $subjectHotel;
    }

    /**
     * 初期表示リストを取得（react-select用）
     *
     * @param array $default 先頭データ
     */
    public function getDefaultOptions(int $agencyId, int $limit, array $defaultRow=[]) : array
    {
        return $this->subjectHotelRepository->search($agencyId, '', ['supplier','v_subject_hotel_custom_values'], [], 30, 'id', 'desc')->map(function ($item, $key) {
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
        return $this->subjectHotelRepository->delete($id, $isSoftDelete);
    }
}
