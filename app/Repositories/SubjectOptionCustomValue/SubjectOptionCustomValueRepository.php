<?php
namespace App\Repositories\SubjectOptionCustomValue;

use App\Models\SubjectOptionCustomValue;
use Illuminate\Database\Eloquent\Model;

class SubjectOptionCustomValueRepository implements SubjectOptionCustomValueRepositoryInterface
{
    /**
    * @param object $userCustomItem
    */
    public function __construct(SubjectOptionCustomValue $subjectOptionCustomValue)
    {
        $this->subjectOptionCustomValue = $subjectOptionCustomValue;
    }

    /**
     * update or insert
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->subjectOptionCustomValue->updateOrCreate(
            $attributes,
            $values
        );
    }
}
