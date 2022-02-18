<?php

namespace App\Services;

use App\Models\Country;
use App\Repositories\Country\CountryRepository;

class CountryService
{
    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    /**
     * 国コードと国名リストの配列を取得
     */
    public function getCodeNameList(): array
    {
        return $this->countryRepository->all()->pluck('name', 'code')->toArray();
    }

}
