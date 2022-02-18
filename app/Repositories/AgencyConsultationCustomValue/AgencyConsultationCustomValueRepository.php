<?php
namespace App\Repositories\AgencyConsultationCustomValue;

use App\Models\AgencyConsultationCustomValue;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AgencyConsultationCustomValueRepository implements AgencyConsultationCustomValueRepositoryInterface
{
    /**
    * @param object $userCustomItem
    */
    public function __construct(AgencyConsultationCustomValue $agencyConsultationCustomValue)
    {
        $this->agencyConsultationCustomValue = $agencyConsultationCustomValue;
    }

    /**
     * update or insert
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->agencyConsultationCustomValue->updateOrCreate(
            $attributes,
            $values
        );
    }

}
