<?php

namespace App\Services;

use Illuminate\Support\Arr;
use App\Models\DocumentRequest;
use Illuminate\Support\Collection;
use App\Repositories\DocumentRequest\DocumentRequestRepository;
use App\Repositories\DocumentCategory\DocumentCategoryRepository;
use App\Repositories\Agency\AgencyRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class DocumentRequestService
{
    public function __construct(DocumentCategoryRepository $documentCategoryRepository, DocumentRequestRepository $documentRequestRepository, AgencyRepository $agencyRepository)
    {
        $this->documentCategoryRepository = $documentCategoryRepository;
        $this->documentRequestRepository = $documentRequestRepository;
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
        return $this->documentRequestRepository->find($id, $select, $getDeleted);
    }

    /**
     * 全件取得
     */
    public function all(string $agencyAccount, array $select = [], bool $getDeleted = false, $order = "seq", $direction = "asc") : Collection
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->documentRequestRepository->getWhere(['agency_id' => $agencyId], $select, $getDeleted, $order, $direction);
    }

    /**
     * デフォルトデータを取得
     *
     * @param int $agencyId 会社ID
     * @param array $select 取得カラム
     * @return App\Models\DocumentRequest
     */
    public function getDefault(int $agencyId, array $select=[]) : ?DocumentRequest
    {
        $documentCategoryId = $this->documentCategoryRepository->getIdByCode(config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST'));

        return $this->documentRequestRepository->findWhere([
            'agency_id' => $agencyId,
            'document_category_id' => $documentCategoryId,
            'code' => config('consts.document_categories.CODE_REQUEST_DEFAULT'), //請求書コード
        ], $select);
    }

    /**
     * 一覧を取得
     *
     * @param string $account 会社アカウント
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $account, int $limit, array $with=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);
        return $this->documentRequestRepository->paginateByAgencyId($agencyId, $limit, $with);
    }

    /**
     * 設定を作成
     */
    public function create(array $data): DocumentRequest
    {
        if (!Arr::get($data, 'document_category_id')) { // 帳票カテゴリID
            $data['document_category_id'] = $this->documentCategoryRepository->getIdByCode(config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST'));
        }

        $data['seq'] = $this->nextSeq($data['agency_id']); // 順番をセット
        
        return $this->documentRequestRepository->create($data);
    }

    public function update(int $id, array $data): DocumentRequest
    {
        return $this->documentRequestRepository->update($id, $data);
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->documentRequestRepository->delete($id, $isSoftDelete);
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
        return $this->documentRequestRepository
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
        $result = $this->documentRequestRepository->getWhere(['agency_id' => $agencyId], ['id','name','deleted_at'], true);
        // 論理削除済みで$valuesのIDでないものは削除
        $filtered = $result->reject(function ($row, $key) use ($values) {
            return $row->trashed() && !in_array($row->id, $values);
        });

        return $filtered->pluck('name', 'id')->toArray();
    }

    /**
     * 検印欄表示数
     * selectフォーム用
     */
    public function getSealRange()
    {
        return range(0, config('consts.const.REQUEST_SEAL_MAXIMUM'));
    }

    /**
     * 次の順番を取得
     */
    public function nextSeq(int $agencyId) : int
    {
        return $this->documentRequestRepository->maxSeq($agencyId) + 1;
    }
}
