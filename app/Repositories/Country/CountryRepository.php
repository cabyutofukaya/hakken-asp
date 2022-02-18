<?php
namespace App\Repositories\Country;

use App\Models\Country;
use Illuminate\Support\Collection;

class CountryRepository implements CountryRepositoryInterface
{
    /**
    * @param object $country
    */
    public function __construct(Country $country)
    {
        $this->country = $country;
    }

    public function all() : Collection
    {
        return $this->country->all();
    }
}