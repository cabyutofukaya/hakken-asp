<?php

namespace App\Repositories\ReservePurchasingSubjectAirplaneCustomValue;

use App\Models\ReservePurchasingSubjectAirplaneCustomValue;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

interface ReservePurchasingSubjectAirplaneCustomValueRepositoryInterface
{
  public function updateOrCreate(array $attributes, array $values = []) : Model;
}

