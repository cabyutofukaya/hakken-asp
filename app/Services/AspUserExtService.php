<?php

namespace App\Services;

use App\Models\AspUserExt;
use App\Repositories\AspUserExt\AspUserExtRepository;

class AspUserExtService
{
    public function __construct(
        AspUserExtRepository $aspUserExtRepository
    ) {
        $this->aspUserExtRepository = $aspUserExtRepository;
    }

    /**
     * バルクインサート
     */
    public function insert(array $rows) : bool
    {
        $this->aspUserExtRepository->insert($rows);
        return true;
    }
}
