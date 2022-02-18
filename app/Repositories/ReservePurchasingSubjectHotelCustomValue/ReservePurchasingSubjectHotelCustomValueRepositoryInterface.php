<?php

namespace App\Repositories\ReservePurchasingSubjectHotelCustomValue;

use App\Models\ReservePurchasingSubjectHotelCustomValue;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

interface ReservePurchasingSubjectHotelCustomValueRepositoryInterface
{
  public function updateOrCreate(array $attributes, array $values = []) : Model;
}

