<?php

namespace App\Repositories\BusinessUserCustomValue;

use App\Models\BusinessUserCustomValue;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

interface BusinessUserCustomValueRepositoryInterface
{
  public function updateOrCreate(array $attributes, array $values = []) : Model;
}

