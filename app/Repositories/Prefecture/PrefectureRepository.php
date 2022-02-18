<?php
namespace App\Repositories\Prefecture;

use App\Models\Prefecture;
use Illuminate\Pagination\LengthAwarePaginator;

class PrefectureRepository implements PrefectureRepositoryInterface
{
    protected $prefecture;

    /**
    * @param object $prefecture
    */
    public function __construct(Prefecture $prefecture)
    {
        $this->prefecture = $prefecture;
    }

    /**
     * 名前で1レコードを取得
     *
     * @var $name
     * @return object
     */
    public function paginate(int $limit) : LengthAwarePaginator
    {
        return $this->prefecture->sortable()->paginate($limit);
    }

    public function all()
    {
        return $this->prefecture->all();
    }
}