<?php
namespace App\Repositories\Purpose;

use App\Models\Purpose;
use Illuminate\Support\Collection;

class PurposeRepository implements PurposeRepositoryInterface
{
    protected $purpose;

    /**
    * @param object $purpose
    */
    public function __construct(Purpose $purpose)
    {
        $this->purpose = $purpose;
    }

    public function find(int $id)
    {
        return $this->purpose->find($id);
    }

    public function all() : Collection
    {
        return $this->purpose->all();
    }

    /**
     * 当該IDリストに対応した名称一覧を取得
     */
    public function getNamesByIds(array $ids): array
    {
        return $this->purpose->whereIn('id', $ids)->pluck('name')->all();
    }

    /**
     * ページネーション で取得
     *
     * @var $limit
     * @return object
     */
    public function paginate(int $limit, array $with)
    {
        return $this->purpose->with($with)->sortable()->paginate($limit);
    }

    public function create(array $data)
    {
        return $this->purpose->create($data);
    }

    public function update(int $id, array $data): int
    {
        return $this->purpose->where('id', $id)->update($data);
    }

    public function delete(int $id): int
    {
        return $this->purpose->destroy($id);
    }
}