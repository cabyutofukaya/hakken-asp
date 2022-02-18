<?php

namespace App\Repositories\AgencyConsultationCustomValue;

use App\Models\AgencyConsultationCustomValue;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

interface AgencyConsultationCustomValueRepositoryInterface
{
  public function updateOrCreate(array $attributes, array $values = []) : Model;
}

