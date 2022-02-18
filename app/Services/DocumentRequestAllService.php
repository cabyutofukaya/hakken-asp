<?php

namespace App\Services;

use Illuminate\Support\Arr;
use App\Models\DocumentRequestAll;
use Illuminate\Support\Collection;
use App\Repositories\DocumentRequestAll\DocumentRequestAllRepository;
use App\Repositories\DocumentCategory\DocumentCategoryRepository;
use App\Repositories\Agency\AgencyRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class DocumentRequestAllService
{
    public function __construct(DocumentCategoryRepository $documentCategoryRepository, DocumentRequestAllRepository $documentRequestAllRepository, AgencyRepository $agencyRepository)
    {
        $this->documentCategoryRepository = $documentCategoryRepository;
        $this->documentRequestAllRepository = $documentRequestAllRepository;
        $this->agencyRepository = $agencyRepository;
    }

    /**
     * 該当レコードを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     */
    public function find(int $id, array $select=[], bool $getDeleted = false)
    {
        return $this->documentRequestAllRepository->find($id, $select, $getDeleted);
    }

    /**
     * 共通設定一覧を取得
     *
     * @param string $account 会社アカウント
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $account, int $limit, array $with=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);
        return $this->documentRequestAllRepository->paginateByAgencyId($agencyId, $limit, $with);
    }

    /**
     * 帳票共通設定を作成
     */
    public function create(array $data): DocumentRequestAll
    {
        if (!Arr::get($data, 'document_category_id')) { // 帳票カテゴリID
            $data['document_category_id'] = $this->documentCategoryRepository->getIdByCode(config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST_ALL'));
        }

        $data['seq'] = $this->nextSeq($data['agency_id']); // 順番をセット
        
        return $this->documentRequestAllRepository->create($data);
    }

    public function update(int $id, array $data): DocumentRequestAll
    {
        return $this->documentRequestAllRepository->update($id, $data);
    }

    /**
     * デフォルトデータを取得
     *
     * @param int $agencyId 会社ID
     * @param array $select 取得カラム
     * @return App\Models\DocumentRequestAll
     */
    public function getDefault(int $agencyId, array $select=[]) : ?DocumentRequestAll
    {
        $documentCategoryId = $this->documentCategoryRepository->getIdByCode(config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST_ALL'));

        return $this->documentRequestAllRepository->findWhere([
            'agency_id' => $agencyId,
            'document_category_id' => $documentCategoryId,
            'code' => config('consts.document_categories.CODE_REQUEST_ALL_DEFAULT'), // 一括請求書コード
        ], $select);
    }

    /**
     * 検印欄表示数
     * selectフォーム用
     */
    public function getSealRange()
    {
        return range(0, config('consts.const.REQUEST_ALL_SEAL_MAXIMUM'));
    }

    /**
     * selectメニュー用の名前配列
     * 「ID => 名前」形式の配列
     *
     * @param int $agencyId 会社ID
     * @param bool $getDeleted 論理削除も取得する場合はtrue
     * @return array
     */
    public function getIdNameSelect(int $agencyId, bool $getDeleted = false) : array
    {
        return $this->documentRequestAllRepository
            ->getWhere(['agency_id' => $agencyId], ['id', 'name'], $getDeleted)
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * getIdNameSelectをベースに
     * 任意の値が論理削除されてしまった場合でも指定同値は論理削除も取得してリストアップ
     */
    public function getIdNameSelectSafeValues(int $agencyId, array $values) : array
    {
        $result = $this->documentRequestAllRepository->getWhere(['agency_id' => $agencyId], ['id','name','deleted_at'], true);
        // 論理削除済みで$valuesのIDでないものは削除
        $filtered = $result->reject(function ($row, $key) use ($values) {
            return $row->trashed() && !in_array($row->id, $values);
        });

        return $filtered->pluck('name', 'id')->toArray();
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->documentRequestAllRepository->delete($id, $isSoftDelete);
    }

    /**
     * 次の順番を取得
     */
    public function nextSeq(int $agencyId) : int
    {
        return $this->documentRequestAllRepository->maxSeq($agencyId) + 1;
    }
}
