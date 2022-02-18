<?php
namespace App\Repositories\ReserveConfirmUser;

use App\Models\ReserveConfirmUser;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReserveConfirmUserRepository
{
    /**
    * @param object $reserveConfirmUser
    */
    public function __construct(ReserveConfirmUser $reserveConfirmUser)
    {
        $this->reserveConfirmUser = $reserveConfirmUser;
    }

    public function create(array $data) : ReserveConfirmUser
    {
        return $this->reserveConfirmUser->create($data);
    }
}