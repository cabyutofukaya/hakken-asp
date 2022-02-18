<?php

namespace App\Services;

use App\Repositories\Prefecture\PrefectureRepository;

class PrefectureService
{
    private $prefectureRepository;

    public function __construct(PrefectureRepository $prefectureRepository)
    {
        $this->prefectureRepository = $prefectureRepository;
    }

    public function paginate($limit)
    {
        return $this->prefectureRepository->paginate($limit);
    }

    public function getCodeNameList(): array
    {
        return $this->prefectureRepository->all()->pluck('name', 'code')->toArray();
    }
}
