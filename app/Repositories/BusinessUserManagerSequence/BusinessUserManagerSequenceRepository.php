<?php
namespace App\Repositories\BusinessUserManagerSequence;

use App\Models\BusinessUserManagerSequence;

class BusinessUserManagerSequenceRepository implements BusinessUserManagerSequenceRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(BusinessUserManagerSequence $businessUserManagerSequence)
    {
        $this->businessUserManagerSequence = $businessUserManagerSequence;
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
        $this->businessUserManagerSequence->insert([
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
        $businessUserManagerSequence = $this->businessUserManagerSequence->lockForUpdate()->where('agency_id', $agencyId)->first(); // 行ロックで取得

        if ($businessUserManagerSequence->updated_at->format('Y-m') === date('Y-m', strtotime($date))) {
            $nextNumber = $businessUserManagerSequence->current_number + 1;
            $businessUserManagerSequence->current_number = $nextNumber;
            $businessUserManagerSequence->save();
        } else {
            $nextNumber = 1;
            $businessUserManagerSequence->current_number = $nextNumber;
            $businessUserManagerSequence->updated_at = $date;
            $businessUserManagerSequence->save();
        }

        return $nextNumber;
    }
}
