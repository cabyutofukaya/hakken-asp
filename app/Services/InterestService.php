<?php

namespace App\Services;

use Hash;
use Lang;
use Illuminate\Support\Arr;
use App\Repositories\Interest\InterestRepository;

class InterestService
{
    private $interestRepository;

    public function __construct(InterestRepository $interestRepository)
    {
        $this->interestRepository = $interestRepository;
    }

    /**
     * IDと名称リストの配列を取得
     */
    public function getIdNameList(): array
    {
        return $this->interestRepository->all()->pluck('name', 'id')->toArray();
    }

    public function find(int $id)
    {
        return $this->interestRepository->find($id);
    }

    public function paginate(int $limit, array $with=[])
    {
        return $this->interestRepository->paginate($limit, $with);
    }

    public function create(array $data)
    {
        return $this->interestRepository->create($data);
    }

    public function update(int $id, array $data)
    {
        $data = Arr::only($data, ['name', 'seq']);
        return $this->interestRepository->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->interestRepository->delete($id);
    }
}
