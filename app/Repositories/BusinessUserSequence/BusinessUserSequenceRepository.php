<?php
namespace App\Repositories\BusinessUserSequence;

use App\Models\BusinessUserSequence;

class BusinessUserSequenceRepository implements BusinessUserSequenceRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(BusinessUserSequence $businessUserSequence)
    {
        $this->businessUserSequence = $businessUserSequence;
    }

    /**
     * 連番を初期化
     *
     * @param int $agencyId 会社ID
     * @param string $date
     * @return bool
     */
    public function initCurrentNumber(int $agencyId, $date) : bool
    {
        $this->businessUserSequence->insert([
            'current_number'    => 0,
            'agency_id'         => $agencyId,
            'updated_at'        => $date,
        ]);
        return true;
    }

    /**
     * 次の連番を取得
     *
     * @param int $agencyId 会社ID
     * @param string $date
     * @return int
     */
    public function getNextNumber(int $agencyId, $date) : int
    {
        $businessUserSequence = $this->businessUserSequence->lockForUpdate()->where('agency_id', $agencyId)->first(); // 行ロックで取得

        if ($businessUserSequence->updated_at->format('Y-m') === date('Y-m', strtotime($date))) {
            $nextNumber = $businessUserSequence->current_number + 1;
            $businessUserSequence->current_number = $nextNumber;
            $businessUserSequence->save();
        } else {
            $nextNumber = 1;
            $businessUserSequence->current_number = $nextNumber;
            $businessUserSequence->updated_at = $date;
            $businessUserSequence->save();
        }

        return $nextNumber;
    }
}
