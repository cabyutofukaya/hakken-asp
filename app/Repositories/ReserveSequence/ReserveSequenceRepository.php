<?php
namespace App\Repositories\ReserveSequence;

use App\Models\ReserveSequence;

class ReserveSequenceRepository implements ReserveSequenceRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(ReserveSequence $reserveSequence)
    {
        $this->reserveSequence = $reserveSequence;
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
        $this->reserveSequence->insert([
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
        $reserveSequence = $this->reserveSequence->lockForUpdate()->where('agency_id', $agencyId)->first(); // 行ロックで取得

        if ($reserveSequence->updated_at->format('Y-m') === date('Y-m', strtotime($date))) {
            $nextNumber = $reserveSequence->current_number + 1;
            $reserveSequence->current_number = $nextNumber;
            $reserveSequence->save();
        } else {
            $nextNumber = 1;
            $reserveSequence->current_number = $nextNumber;
            $reserveSequence->updated_at = $date;
            $reserveSequence->save();
        }

        return $nextNumber;
    }
}
