<?php

namespace App\Repositories\AgencyWithdrawalItemHistoryCustomValue;

use App\Models\AgencyWithdrawalItemHistoryCustomValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface AgencyWithdrawalItemHistoryCustomValueRepositoryInterface
{
    public function updateOrCreate(array $attributes, array $values = []) : Model;

    public function updateField(int $agencyWithdrawalItemHistoryCustomValueId, array $params) : Model;
}
