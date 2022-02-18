<?php

namespace App\Repositories\UserMileageCustomValue;

use App\Models\UserMileageCustomValue;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

interface UserMileageCustomValueRepositoryInterface
{
  public function updateOrCreate(array $attributes, array $values = []) : Model;
}

