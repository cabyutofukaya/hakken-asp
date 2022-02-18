<?php

namespace App\Repositories\StaffCustomValue;

use App\Models\StaffCustomValue;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

interface StaffCustomValueRepositoryInterface
{
  public function updateOrCreate(array $attributes, array $values = []) : Model;
}

