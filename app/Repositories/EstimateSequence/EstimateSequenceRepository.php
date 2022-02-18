<?php
namespace App\Repositories\EstimateSequence;

use App\Models\EstimateSequence;

class EstimateSequenceRepository implements EstimateSequenceRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(EstimateSequence $estimateSequence)
    {
        $this->estimateSequence = $estimateSequence;
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
        $this->estimateSequence->insert([
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
        $estimateSequence = $this->estimateSequence->lockForUpdate()->where('agency_id', $agencyId)->first(); // 行ロックで取得

        if ($estimateSequence->updated_at->format('Y-m') === date('Y-m', strtotime($date))) {
            $nextNumber = $estimateSequence->current_number + 1;
            $estimateSequence->current_number = $nextNumber;
            $estimateSequence->save();
        } else {
            $nextNumber = 1;
            $estimateSequence->current_number = $nextNumber;
            $estimateSequence->updated_at = $date;
            $estimateSequence->save();
        }

        return $nextNumber;
    }
}
