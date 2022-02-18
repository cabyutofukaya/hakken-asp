<?php

namespace App\Repositories\SubjectAirplaneCustomValue;

use Illuminate\Database\Eloquent\Model;

interface SubjectAirplaneCustomValueRepositoryInterface
{
    public function updateOrCreate(array $attributes, array $values = []) : Model;
}
