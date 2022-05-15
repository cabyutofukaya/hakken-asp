<?php
namespace App\Repositories\AgencyWithdrawalItemHistoryCustomValue;

use App\Models\AgencyWithdrawalItemHistoryCustomValue;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AgencyWithdrawalItemHistoryCustomValueRepository implements AgencyWithdrawalItemHistoryCustomValueRepositoryInterface
{
    public function __construct(AgencyWithdrawalItemHistoryCustomValue $agencyWithdrawalItemHistoryCustomValue)
    {
        $this->agencyWithdrawalItemHistoryCustomValue = $agencyWithdrawalItemHistoryCustomValue;
    }

    /**
     * update or insert
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->agencyWithdrawalItemHistoryCustomValue->updateOrCreate(
            $attributes,
            $values
        );
    }

    /**
     * 項目更新
     */
    public function updateField(int $agencyWithdrawalItemHistoryCustomValueId, array $params) : Model
    {
        $this->agencyWithdrawalItemHistoryCustomValue->where('id', $agencyWithdrawalItemHistoryCustomValueId)->update($params);
        return $this->agencyWithdrawalItemHistoryCustomValue->findOrFail($agencyWithdrawalItemHistoryCustomValueId);
    }
}
