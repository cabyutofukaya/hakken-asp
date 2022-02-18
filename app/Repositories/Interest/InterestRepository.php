<?php
namespace App\Repositories\Interest;

use App\Models\Interest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InterestRepository implements InterestRepositoryInterface
{
    protected $interest;

    /**
    * @param object $interest
    */
    public function __construct(Interest $interest)
    {
        $this->interest = $interest;
    }

    public function find(int $id) : Interest
    {
        return $this->interest->find($id);
    }

    public function all() : Collection
    {
        return $this->interest->all();
    }

    /**
     * ページネーション で取得
     *
     * @var $limit
     * @return object
     */
    public function paginate(int $limit, array $with) : LengthAwarePaginator
    {
        return $this->interest->with($with)->sortable()->paginate($limit);
    }

    public function create(array $data): Interest
    {
        return $this->interest->create($data);
    }

    public function update(int $id, array $data): int
    {
        return $this->interest->where('id', $id)->update($data);
    }

    public function delete(int $id): int
    {
        return $this->interest->destroy($id);
    }
}