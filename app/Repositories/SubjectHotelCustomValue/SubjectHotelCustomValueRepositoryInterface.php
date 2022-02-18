<?php

namespace App\Repositories\SubjectHotelCustomValue;

use Illuminate\Database\Eloquent\Model;

interface SubjectHotelCustomValueRepositoryInterface
{
    public function updateOrCreate(array $attributes, array $values = []) : Model;
}
