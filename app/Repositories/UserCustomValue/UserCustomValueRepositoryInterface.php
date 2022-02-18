<?php

namespace App\Repositories\UserCustomValue;

use App\Models\UserCustomValue;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

interface UserCustomValueRepositoryInterface
{
  public function updateOrCreate(array $attributes, array $values = []) : Model;
}

