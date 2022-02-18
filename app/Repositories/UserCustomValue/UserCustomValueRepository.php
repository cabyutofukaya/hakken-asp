<?php
namespace App\Repositories\UserCustomValue;

use App\Models\UserCustomValue;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class UserCustomValueRepository implements UserCustomValueRepositoryInterface
{
    /**
    * @param object $userCustomItem
    */
    public function __construct(UserCustomValue $userCustomValue)
    {
        $this->userCustomValue = $userCustomValue;
    }

    /**
     * update or insert
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->userCustomValue->updateOrCreate(
            $attributes,
            $values
        );
    }

}
