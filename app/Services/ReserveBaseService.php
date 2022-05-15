<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\Reserve;
use Carbon\Carbon;
use App\Repositories\ReserveBase\ReserveBaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * 受付種別を問わずreservesレコードを操作できるservice
 */
class ReserveBaseService
{
    public function __construct(
        ReserveBaseRepository $reserveBaseRepository

    ){
        $this->reserveBaseRepository = $reserveBaseRepository;
    }

    /**
     * IDから一件取得
     */
    public function findForAgencyId(?int $id, int $agencyId, array $with = [], array $select=[], bool $getDeleted = false)
    {
        return $this->reserveBaseRepository->findForAgencyId($id, $agencyId, $with, $select, $getDeleted);
    }
}
