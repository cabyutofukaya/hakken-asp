<?php
namespace App\Services;

use App\Models\Bank;
use App\Repositories\Bank\BankRepository;
use App\Traits\ConstsTrait;
use DB;
use Exception;
use Illuminate\Support\Collection;

class BankService
{
    use ConstsTrait;
    
    public function __construct(BankRepository $bankRepository)
    {
        $this->bankRepository = $bankRepository;
    }

    /**
     * 検索して1件取得
     */
    public function getWhere(array $where, array $select=[]) : Collection
    {
        return $this->bankRepository->getWhere($where, $select);
    }

    /**
     * バルクインサート
     *
     * @return [type] [description]
     */
    public function insert(array $rows) : void
    {
        $this->bankRepository->insert($rows);
    }

    // 全件削除
    public function truncate() : void
    {
        $this->bankRepository->truncate();
    }

    /**
     * 検索条件(金融機関コード or 支店コード)にマッチした金融機関名・支店名のリスト
     * selectフォーム用
     *
     * @param string $kinyuCode 金融機関コード
     * @param string $tenpoCode 支店コード
     * @param array $multiRowDefault リストが複数ある場合のデフォルト値
     */
    public function getTenpoNamesForSelectItem(?string $kinyuCode, ?string $tenpoCode, array $multiRowDefault=[''=>'---'])
    {
        if ($tenpoCode) { // 店舗コードが入力されている場合は、金融期間コードと店舗コードの組み合わせで検索
            $params['kinyu_code'] = $kinyuCode;
            $params['tenpo_code'] = $tenpoCode;
            $select = [
                'kinyu_name',
                'tenpo_name',
            ];
        } else { // 店舗コードが入力されていない場合は金融期間コードで検索
            $params['kinyu_code'] = $kinyuCode;
            $select = [
                'kinyu_name',
            ];
        }

        $results = $this->bankRepository->getWhere($params, $select);

        $kinyuNames = $results->pluck('kinyu_name')->unique()->toArray(); // 銀行名
        $tenpoNames = $results->pluck('tenpo_name')->unique()->toArray(); // 支店名

        $kinyuNames = $kinyuNames ? array_combine($kinyuNames, $kinyuNames) : [];
        $tenpoNames = $tenpoNames ? array_combine($tenpoNames, $tenpoNames) : [];

        // 選択肢が複数ある場合は配列の先頭に未選択項目を付与
        if (count($kinyuNames) > 1) {
            $kinyuNames = $multiRowDefault + $kinyuNames;
        }
        if (count($tenpoNames) > 1) {
            $tenpoNames = $multiRowDefault + $tenpoNames;
        }

        return [
            'kinyu_names' => $kinyuNames,
            'tenpo_names' => $tenpoNames,
        ];
    }
}
