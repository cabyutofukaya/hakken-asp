<?php

namespace App\Repositories\AgencyWithdrawalCustomValue;

use App\Models\AgencyWithdrawalCustomValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface AgencyWithdrawalCustomValueRepositoryInterface
{
    public function updateOrCreate(array $attributes, array $values = []) : Model;

    public function updateField(int $agencyWithdrawalCustomValueId, array $params) : Model;
}
