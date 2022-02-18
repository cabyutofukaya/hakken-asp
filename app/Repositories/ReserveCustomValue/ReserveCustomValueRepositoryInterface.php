<?php

namespace App\Repositories\ReserveCustomValue;

use App\Models\ReserveCustomValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface ReserveCustomValueRepositoryInterface
{
    public function create(array $data) : ReserveCustomValue;

    public function updateOrCreate(array $attributes, array $values = []) : Model;

    public function updateField(int $reserveCustomValueId, array $params) : Model;

    public function insert(array $data) : bool;
}
