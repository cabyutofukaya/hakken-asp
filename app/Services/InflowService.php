<?php

namespace App\Services;

use App\Models\Inflow;
use Illuminate\Support\Arr;
use App\Repositories\Inflow\InflowRepository;
use App\Exceptions\ExclusiveLockException;

class InflowService
{
    private $inflowRepository;

    public function __construct(InflowRepository $inflowRepository)
    {
        $this->inflowRepository = $inflowRepository;
    }

    public function paginate($limit)
    {
        return $this->inflowRepository->paginate($limit);
    }

    public function all()
    {
        return $this->inflowRepository->all();
    }

    public function find(int $id) : Inflow
    {
        return $this->inflowRepository->find($id);
    }

    public function create(array $data) : Inflow
    {
        return $this->inflowRepository->create($data);
    }

    /**
     * 更新
     *
     * @param int $id 流入ID
     * @param array $data 編集データ
     * @return Inflow
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function update(int $id, array $data): Inflow
    {
        $inflow = $this->inflowRepository->find($id);
        if ($inflow->updated_at != Arr::get($data, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        return $this->inflowRepository->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->inflowRepository->delete($id);
    }

    public function getNameList()
    {
        return $this->inflowRepository->all()->pluck('site_name', 'id')->toArray();
    }
}
