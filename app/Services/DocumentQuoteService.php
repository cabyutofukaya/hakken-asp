<?php

namespace App\Services;

use Illuminate\Support\Arr;
use App\Models\DocumentQuote;
use Illuminate\Support\Collection;
use App\Repositories\DocumentQuote\DocumentQuoteRepository;
use App\Repositories\DocumentCategory\DocumentCategoryRepository;
use App\Repositories\Agency\AgencyRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * 見積・予約確認書
 */
class DocumentQuoteService
{
    public function __construct(DocumentCategoryRepository $documentCategoryRepository, DocumentQuoteRepository $documentQuoteRepository, AgencyRepository $agencyRepository)
    {
        $this->documentCategoryRepository = $documentCategoryRepository;
        $this->documentQuoteRepository = $documentQuoteRepository;
        $this->agencyRepository = $agencyRepository;
    }

    /**
     * 該当レコードを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     */
    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false)
    {
        return $this->documentQuoteRepository->find($id, $with, $select, $getDeleted);
    }

    /**
     * 全件取得
     */
    public function all(string $agencyAccount, array $select = [], bool $getDeleted = false, $order = "seq", $direction = "asc") : Collection
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->documentQuoteRepository->getWhere(['agency_id' => $agencyId], $select, $getDeleted, $order, $direction);
    }

    /**
     * 任意のコードデータを取得
     *
     * @param int $agencyId 会社ID
     * @param array $select 取得カラム
     * @return App\Models\DocumentQuote
     */
    public function getDefaultByCode(int $agencyId, string $code, array $select=[]) : ?DocumentQuote
    {
        $documentCategoryId = $this->documentCategoryRepository->getIdByCode(config('consts.document_categories.DOCUMENT_CATEGORY_QUOTE')); // 念の為document_category_idも検索条件に加える

        return $this->documentQuoteRepository->findWhere([
            'agency_id' => $agencyId,
            'document_category_id' => $documentCategoryId,
            'code' => $code
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
        return $this->documentQuoteRepository->paginateByAgencyId($agencyId, $limit, $with);
    }

    /**
     * 設定を作成
     */
    public function create(array $data): DocumentQuote
    {
        if (!Arr::get($data, 'document_category_id')) { // 帳票カテゴリID
            $data['document_category_id'] = $this->documentCategoryRepository->getIdByCode(config('consts.document_categories.DOCUMENT_CATEGORY_QUOTE'));
        }

        $data['seq'] = $this->nextSeq($data['agency_id']); // 順番をセット
        
        return $this->documentQuoteRepository->create($data);
    }

    public function update(int $id, array $data): DocumentQuote
    {
        return $this->documentQuoteRepository->update($id, $data);
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->documentQuoteRepository->delete($id, $isSoftDelete);
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
        return $this->documentQuoteRepository
            ->getWhere(['agency_id' => $agencyId], ['id', 'name'], $getDeleted)
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * デフォルト系以外の「見積・予約確認」テンプレートがある場合はtrue
     *
     * @param int $agencyId 会社ID
     * @param bool $getDeleted 論理削除も取得する場合はtrue
     * @return bool
     */
    public function hasOriginalDocumentQuoteTemplate(int $agencyId, bool $getDeleted = false) : bool
    {
        return $this->documentQuoteRepository->getAppendableTemplates($agencyId, config('consts.reserve_confirms.NO_ADD_OR_DELETE_CODE_LIST'), ['id'], $getDeleted)->count() > 0;
    }

    /**
     * getIdNameSelectAppendableをベースに
     * 任意の値が論理削除されてしまった場合でも指定同値は論理削除も取得してリストアップ
     */
    public function getIdNameSelectAppendableSafeValues(int $agencyId, array $values) : array
    {
        $result = $this->documentQuoteRepository->getAppendableTemplates($agencyId, config('consts.reserve_confirms.NO_ADD_OR_DELETE_CODE_LIST'), ['id','name','deleted_at'], true);
        // 論理削除済みで$valuesのIDでないものは削除
        $filtered = $result->reject(function ($row, $key) use ($values) {
            return $row->trashed() && !in_array($row->id, $values);
        });

        return $filtered->pluck('name', 'id')->toArray();
    }

    /**
     * selectメニュー用の名前配列（デフォルト系テンプレートを除く）
     * 「ID => 名前」形式の配列
     * 
     * @param int $agencyId 会社ID
     * @param bool $getDeleted 論理削除も取得する場合はtrue
     * @return array 
     */
    public function getIdNameSelectAppendable(int $agencyId, bool $getDeleted = false) : array
    {
        return $this->documentQuoteRepository->getAppendableTemplates($agencyId, config('consts.reserve_confirms.NO_ADD_OR_DELETE_CODE_LIST'), ['id', 'name'], $getDeleted)
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * デフォルト系以外のテンプレートで並びが一番初めのデータを取得
     */
    public function getFirstAppendable(int $agencyId, bool $getDeleted = false) : ?DocumentQuote
    {
        return $this->documentQuoteRepository->getAppendableTemplates($agencyId, config('consts.reserve_confirms.NO_ADD_OR_DELETE_CODE_LIST'), [], $getDeleted)->first();
    }

    /**
     * 検印欄表示数
     * selectフォーム用
     */
    public function getSealRange()
    {
        return range(0, config('consts.const.QUOTE_SEAL_MAXIMUM'));
    }

    /**
     * 次の順番を取得
     */
    public function nextSeq(int $agencyId) : int
    {
        return $this->documentQuoteRepository->maxSeq($agencyId) + 1;
    }
}
