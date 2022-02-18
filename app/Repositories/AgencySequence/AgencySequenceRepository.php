<?php
namespace App\Repositories\AgencySequence;

use App\Models\AgencySequence;

class AgencySequenceRepository implements AgencySequenceRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(AgencySequence $agencySequence)
    {
        $this->agencySequence = $agencySequence;
    }

    /**
     * 次の連番を取得
     *
     * @return int
     */
    public function getNextNumber() : int
    {
        $agencySequence = $this->agencySequence->lockForUpdate()->first(); // 行ロックで取得

        $nextNumber = $agencySequence->current_number + 1;
        $agencySequence->current_number = $nextNumber;
        $agencySequence->updated_at = date('Y-m-d H:i:s');
        $agencySequence->save();

        return $nextNumber;
    }
}
