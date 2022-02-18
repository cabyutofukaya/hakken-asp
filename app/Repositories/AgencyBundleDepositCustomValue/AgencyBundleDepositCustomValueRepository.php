<?php
namespace App\Repositories\AgencyBundleDepositCustomValue;

use App\Models\AgencyBundleDepositCustomValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AgencyBundleDepositCustomValueRepository implements AgencyBundleDepositCustomValueRepositoryInterface
{
    public function __construct(AgencyBundleDepositCustomValue $agencyBundleDepositCustomValue)
    {
        $this->agencyBundleDepositCustomValue = $agencyBundleDepositCustomValue;
    }

    /**
     * update or insert
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->agencyBundleDepositCustomValue->updateOrCreate(
            $attributes,
            $values
        );
    }

    /**
     * 項目更新
     */
    public function updateField(int $agencyBundleDepositCustomValueId, array $params) : Model
    {
        $this->agencyBundleDepositCustomValue->where('id', $agencyBundleDepositCustomValueId)->update($params);
        return $this->agencyBundleDepositCustomValue->findOrFail($agencyBundleDepositCustomValueId);

        // $agencyBundleDepositCustomValue = $this->agencyBundleDepositCustomValue->findOrFail($agencyBundleDepositCustomValueId);
        // foreach ($params as $k => $v) {
        //     $agencyBundleDepositCustomValue->{$k} = $v; // プロパティに値をセット
        // }
        // $agencyBundleDepositCustomValue->save();

        // return $agencyBundleDepositCustomValue;
    }
}
