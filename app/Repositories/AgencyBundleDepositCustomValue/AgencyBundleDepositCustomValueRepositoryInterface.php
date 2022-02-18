<?php

namespace App\Repositories\AgencyBundleDepositCustomValue;

use App\Models\AgencyBundleDepositCustomValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface AgencyBundleDepositCustomValueRepositoryInterface
{
    public function updateOrCreate(array $attributes, array $values = []) : Model;

    public function updateField(int $agencyBundleDepositCustomValueId, array $params) : Model;
}
