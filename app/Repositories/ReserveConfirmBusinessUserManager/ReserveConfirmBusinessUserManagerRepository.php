<?php
namespace App\Repositories\ReserveConfirmBusinessUserManager;

use App\Models\ReserveConfirmBusinessUserManager;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReserveConfirmBusinessUserManagerRepository
{
    /**
    * @param object $reserveConfirmBusinessUserManager
    */
    public function __construct(ReserveConfirmBusinessUserManager $reserveConfirmBusinessUserManager)
    {
        $this->reserveConfirmBusinessUserManager = $reserveConfirmBusinessUserManager;
    }

    public function create(array $data) : ReserveConfirmBusinessUserManager
    {
        return $this->reserveConfirmBusinessUserManager->create($data);
    }
}