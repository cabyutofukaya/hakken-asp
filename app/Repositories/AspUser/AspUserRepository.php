<?php
namespace App\Repositories\AspUser;

use App\Models\AspUser;
use Illuminate\Pagination\LengthAwarePaginator;

class AspUserRepository implements AspUserRepositoryInterface
{
    /**
    * @param object $aspUser
    */
    public function __construct(AspUser $aspUser)
    {
        $this->aspUser = $aspUser;
    }

    public function create(array $data) : AspUser
    {
        return $this->aspUser->create($data);
    }
}
