<?php

namespace App\Repositories\AgencyDepositCustomValue;

use App\Models\AgencyDepositCustomValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface AgencyDepositCustomValueRepositoryInterface
{
    public function updateOrCreate(array $attributes, array $values = []) : Model;

    public function updateField(int $agencyDepositCustomValueId, array $params) : Model;
}
