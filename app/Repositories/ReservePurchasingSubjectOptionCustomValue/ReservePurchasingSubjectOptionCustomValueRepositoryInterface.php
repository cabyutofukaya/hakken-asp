<?php

namespace App\Repositories\ReservePurchasingSubjectOptionCustomValue;

use App\Models\ReservePurchasingSubjectOptionCustomValue;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

interface ReservePurchasingSubjectOptionCustomValueRepositoryInterface
{
  public function updateOrCreate(array $attributes, array $values = []) : Model;
}

