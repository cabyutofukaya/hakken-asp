<?php
namespace App\Repositories\AgencyWithdrawalCustomValue;

use App\Models\AgencyWithdrawalCustomValue;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AgencyWithdrawalCustomValueRepository implements AgencyWithdrawalCustomValueRepositoryInterface
{
    public function __construct(AgencyWithdrawalCustomValue $agencyWithdrawalCustomValue)
    {
        $this->agencyWithdrawalCustomValue = $agencyWithdrawalCustomValue;
    }

    /**
     * update or insert
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->agencyWithdrawalCustomValue->updateOrCreate(
            $attributes,
            $values
        );
    }

    /**
     * 項目更新
     */
    public function updateField(int $agencyWithdrawalCustomValueId, array $params) : Model
    {
        $this->agencyWithdrawalCustomValue->where('id', $agencyWithdrawalCustomValueId)->update($params);
        return $this->agencyWithdrawalCustomValue->findOrFail($agencyWithdrawalCustomValueId);

        // $agencyWithdrawalCustomValue = $this->agencyWithdrawalCustomValue->findOrFail($agencyWithdrawalCustomValueId);
        // foreach ($params as $k => $v) {
        //     $agencyWithdrawalCustomValue->{$k} = $v; // プロパティに値をセット
        // }
        // $agencyWithdrawalCustomValue->save();

        // return $agencyWithdrawalCustomValue;
    }
}
