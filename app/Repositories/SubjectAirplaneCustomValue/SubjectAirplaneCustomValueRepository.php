<?php
namespace App\Repositories\SubjectAirplaneCustomValue;

use App\Models\SubjectAirplaneCustomValue;
use Illuminate\Database\Eloquent\Model;

class SubjectAirplaneCustomValueRepository implements SubjectAirplaneCustomValueRepositoryInterface
{
    /**
    * @param object $userCustomItem
    */
    public function __construct(SubjectAirplaneCustomValue $subjectAirplaneCustomValue)
    {
        $this->subjectAirplaneCustomValue = $subjectAirplaneCustomValue;
    }

    /**
     * update or insert
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->subjectAirplaneCustomValue->updateOrCreate(
            $attributes,
            $values
        );
    }
}
