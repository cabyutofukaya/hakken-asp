<?php
namespace App\Repositories\WebEstimateSequence;

use App\Models\WebEstimateSequence;

class WebEstimateSequenceRepository implements WebEstimateSequenceRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(WebEstimateSequence $webEstimateSequence)
    {
        $this->webEstimateSequence = $webEstimateSequence;
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
        $this->webEstimateSequence->insert([
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
        $webEstimateSequence = $this->webEstimateSequence->lockForUpdate()->where('agency_id', $agencyId)->first(); // 行ロックで取得

        if ($webEstimateSequence->updated_at->format('Y-m') === date('Y-m', strtotime($date))) {
            $nextNumber = $webEstimateSequence->current_number + 1;
            $webEstimateSequence->current_number = $nextNumber;
            $webEstimateSequence->save();
        } else {
            $nextNumber = 1;
            $webEstimateSequence->current_number = $nextNumber;
            $webEstimateSequence->updated_at = $date;
            $webEstimateSequence->save();
        }

        return $nextNumber;
    }
}
