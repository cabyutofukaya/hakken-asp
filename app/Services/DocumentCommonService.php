<?php

namespace App\Services;

use App\Models\DocumentCommon;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\DocumentCategory\DocumentCategoryRepository;
use App\Repositories\DocumentCommon\DocumentCommonRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class DocumentCommonService
{
    public function __construct(
        AgencyRepository $agencyRepository,
        DocumentCategoryRepository $documentCategoryRepository,
        DocumentCommonRepository $documentCommonRepository
    ) {
        $this->agencyRepository = $agencyRepository;
        $this->documentCategoryRepository = $documentCategoryRepository;
        $this->documentCommonRepository = $documentCommonRepository;
    }

    /**
     * 該当レコードを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     */
    public function find(int $id, array $select=[], bool $getDeleted = false)
    {
        return $this->documentCommonRepository->find($id, $select, $getDeleted);
    }

    /**
     * デフォルト帳票IDを取得
     * 
     * @param int $agencyId 会社ID
     */
    public function getDefaultId(int $agencyId) : ?int
    {
        $doc = $this->getDefault($agencyId, ['id']);
        return $doc ? $doc->id : null;
    }

    /**
     * デフォルトデータを取得
     *
     * @param int $agencyId 会社ID
     * @param array $select 取得カラム
     * @return App\Models\DocumentCommon
     */
    public function getDefault(int $agencyId, array $select = []) : ?DocumentCommon
    {
        $documentCategoryId = $this->documentCategoryRepository->getIdByCode(config('consts.document_categories.DOCUMENT_CATEGORY_COMMON')); // 念の為document_category_idも検索条件に加える

        return $this->documentCommonRepository->findWhere([
            'agency_id' => $agencyId,
            'document_category_id' => $documentCategoryId,
            'code' => config('consts.document_categories.CODE_COMMON_DEFAULT')
        ], $select);
    }

    /**
     * 共通設定一覧を取得
     *
     * @param string $account 会社アカウント
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $agencyAccount, int $limit, array $with=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->documentCommonRepository->paginateByAgencyId($agencyId, $limit, $with);
    }

    /**
     * 全件取得
     */
    public function all(string $agencyAccount, array $select = [], bool $getDeleted = false, $order = "seq", $direction = "asc") : Collection
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->documentCommonRepository->getWhere(['agency_id' => $agencyId], $select, $getDeleted, $order, $direction);
    }

    /**
     * 帳票共通設定を作成
     */
    public function create(array $data): DocumentCommon
    {
        if (!Arr::get($data, 'document_category_id')) { // 帳票カテゴリID
            $data['document_category_id'] = $this->documentCategoryRepository->getIdByCode(config('consts.document_categories.DOCUMENT_CATEGORY_COMMON'));
        }

        $data['seq'] = $this->nextSeq($data['agency_id']); // 順番をセット

        return $this->documentCommonRepository->create($data);
    }

    public function update(int $id, array $data): DocumentCommon
    {
        return $this->documentCommonRepository->update($id, $data);
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->documentCommonRepository->delete($id, $isSoftDelete);
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
        return $this->documentCommonRepository
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
        $result = $this->documentCommonRepository->getWhere(['agency_id' => $agencyId], ['id','name','deleted_at'], true);
        // 論理削除済みで$valuesのIDでないものは削除
        $filtered = $result->reject(function ($row, $key) use ($values) {
            return $row->trashed() && !in_array($row->id, $values);
        });

        return $filtered->pluck('name', 'id')->toArray();
    }

    /**
     * 次の順番を取得
     */
    public function nextSeq(int $agencyId) : int
    {
        return $this->documentCommonRepository->maxSeq($agencyId) + 1;
    }
}
