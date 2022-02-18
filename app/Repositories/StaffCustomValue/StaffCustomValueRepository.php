<?php
namespace App\Repositories\StaffCustomValue;

use App\Models\StaffCustomValue;
use Illuminate\Support\Collection;
use DB;
use Illuminate\Database\Eloquent\Model;

class StaffCustomValueRepository implements StaffCustomValueRepositoryInterface
{
    /**
    * @param object $userCustomItem
    */
    public function __construct(StaffCustomValue $staffCustomValue)
    {
        $this->staffCustomValue = $staffCustomValue;
    }

    /**
     * update or insert
     *  
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->staffCustomValue->updateOrCreate(
            $attributes,
            $values
        );
    }

}
