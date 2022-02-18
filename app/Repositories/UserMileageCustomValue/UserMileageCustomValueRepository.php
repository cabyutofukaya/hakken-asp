<?php
namespace App\Repositories\UserMileageCustomValue;

use App\Models\UserMileageCustomValue;
use Illuminate\Support\Collection;
use DB;
use Illuminate\Database\Eloquent\Model;

class UserMileageCustomValueRepository implements UserMileageCustomValueRepositoryInterface
{
    /**
    * @param object $userCustomItem
    */
    public function __construct(UserMileageCustomValue $userMileageCustomValue)
    {
        $this->userMileageCustomValue = $userMileageCustomValue;
    }

    /**
     * update or insert
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->userMileageCustomValue->updateOrCreate(
            $attributes,
            $values
        );
    }

}
