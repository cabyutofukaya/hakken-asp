<?php
namespace App\Repositories\SubjectHotelCustomValue;

use App\Models\SubjectHotelCustomValue;
use Illuminate\Database\Eloquent\Model;

class SubjectHotelCustomValueRepository implements SubjectHotelCustomValueRepositoryInterface
{
    /**
    * @param object $userCustomItem
    */
    public function __construct(SubjectHotelCustomValue $subjectHotelCustomValue)
    {
        $this->subjectHotelCustomValue = $subjectHotelCustomValue;
    }

    /**
     * update or insert
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->subjectHotelCustomValue->updateOrCreate(
            $attributes,
            $values
        );
    }
}
