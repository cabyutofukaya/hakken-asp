<?php
namespace App\Repositories\Inflow;

use App\Models\Inflow;
use Illuminate\Pagination\LengthAwarePaginator;

class InflowRepository implements InflowRepositoryInterface
{
    protected $inflow;

    /**
    * @param object $inflow
    */
    public function __construct(Inflow $inflow)
    {
        $this->inflow = $inflow;
    }

    /**
     * ページネーション で取得
     *
     * @var $name
     * @return object
     */
    public function paginate(int $limit): LengthAwarePaginator
    {
        return $this->inflow->sortable()->paginate($limit);
    }

    public function all(): object
    {
        return $this->inflow->all();
    }

    public function find(int $id): Inflow
    {
        return $this->inflow->find($id);
    }

    public function create(array $data): Inflow
    {
        return $this->inflow->create($data);
    }

    public function update(int $id, array $data): Inflow
    {
        $inflow = $this->find($id);
        $inflow->fill($data)->save();
        return $inflow;
    }

    public function delete(int $id): int
    {
        return $this->inflow->destroy($id);
    }
}