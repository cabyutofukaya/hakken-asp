<?php
namespace App\Repositories\AspUserExt;

use App\Models\AspUserExt;
use Illuminate\Pagination\LengthAwarePaginator;

class AspUserExtRepository implements AspUserExtRepositoryInterface
{
    /**
    * @param object $aspUserExt
    */
    public function __construct(AspUserExt $aspUserExt)
    {
        $this->aspUserExt = $aspUserExt;
    }

    /**
     * バルクインサート
     */
    public function insert(array $rows) : bool
    {
        $this->aspUserExt->insert($rows);
        return true;
    }
}
