<?php

namespace App\Repositories\MasterArea;

use App\Models\MasterArea;
use Illuminate\Database\Eloquent\Model;

interface MasterAreaRepositoryInterface
{
    public function getIdByCode(string $code) : ?int;
    
    public function updateOrCreate(array $attributes, array $values = []) : Model;

    public function deleteExceptionGenKey(string $genKey): bool;
}
