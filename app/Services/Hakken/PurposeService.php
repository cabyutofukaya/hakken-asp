<?php

namespace App\Services\Hakken;

use Hash;
use Lang;
use Illuminate\Support\Arr;
use App\Repositories\Purpose\PurposeRepository;

class PurposeService
{
    private $purposeRepository;

    public function __construct(PurposeRepository $purposeRepository)
    {
        $this->purposeRepository = $purposeRepository;
    }

    public function find(int $id)
    {
        return $this->purposeRepository->find($id);
    }

    public function paginate(int $limit, array $with=[])
    {
        return $this->purposeRepository->paginate($limit, $with);
    }

    public function create(array $data)
    {
        return $this->purposeRepository->create($data);
    }

    public function update(int $id, array $data)
    {
        $data = Arr::only($data, ['name', 'seq']);
        return $this->purposeRepository->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->purposeRepository->delete($id);
    }
}
