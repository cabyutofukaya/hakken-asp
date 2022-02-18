<?php

namespace App\Services;

use App\Models\Purpose;
use App\Repositories\Purpose\PurposeRepository;

class PurposeService
{
    public function __construct(PurposeRepository $purposeRepository)
    {
        $this->purposeRepository = $purposeRepository;
    }

    /**
     * IDと名称リストの配列を取得
     */
    public function getIdNameList(): array
    {
        return $this->purposeRepository->all()->pluck('name', 'id')->toArray();
    }
}
