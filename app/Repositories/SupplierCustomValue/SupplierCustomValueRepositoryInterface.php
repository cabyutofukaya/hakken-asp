<?php

namespace App\Repositories\SupplierCustomValue;

use App\Models\SupplierCustomValue;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

interface SupplierCustomValueRepositoryInterface
{
  public function updateOrCreate(array $attributes, array $values = []) : Model;
}

