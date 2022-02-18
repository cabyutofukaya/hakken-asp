<?php
namespace App\Repositories\Bank;

use DB;
use App\Models\Bank;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BankRepository implements BankRepositoryInterface
{
    /**
    * @param Bank $bank
    */
    public function __construct(Bank $bank)
    {
        $this->bank = $bank;
    }

    /**
     * 検索して取得
     */
    public function getWhere(array $where, array $select=[]) : Collection
    {
        $query = $this->bank;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->get();
    }

    public function insert(array $rows) : void
    {
        DB::table('banks')->insert($rows);
    }

    // 全件削除
    public function truncate() : void
    {
        $this->bank->truncate();
    }
}
