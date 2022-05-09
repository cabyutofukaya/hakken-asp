<?php
namespace App\Repositories\AgencyDepositCustomValue;

use App\Models\AgencyDepositCustomValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AgencyDepositCustomValueRepository implements AgencyDepositCustomValueRepositoryInterface
{
    public function __construct(AgencyDepositCustomValue $agencyDepositCustomValue)
    {
        $this->agencyDepositCustomValue = $agencyDepositCustomValue;
    }

    /**
     * update or insert
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->agencyDepositCustomValue->updateOrCreate(
            $attributes,
            $values
        );
    }

    /**
     * 項目更新
     */
    public function updateField(int $agencyDepositCustomValueId, array $params) : Model
    {
        $this->agencyDepositCustomValue->where('id', $agencyDepositCustomValueId)->update($params);
        return $this->agencyDepositCustomValue->findOrFail($agencyDepositCustomValueId);

        // $agencyDepositCustomValue = $this->agencyDepositCustomValue->findOrFail($agencyDepositCustomValueId);
        // foreach ($params as $k => $v) {
        //     $agencyDepositCustomValue->{$k} = $v; // プロパティに値をセット
        // }
        // $agencyDepositCustomValue->save();

        // return $agencyDepositCustomValue;
    }
}
