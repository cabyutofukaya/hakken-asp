<?php

namespace App\Repositories\SubjectOptionCustomValue;

use Illuminate\Database\Eloquent\Model;

interface SubjectOptionCustomValueRepositoryInterface
{
    public function updateOrCreate(array $attributes, array $values = []) : Model;
}
