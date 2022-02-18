<?php
namespace App\Repositories\ReserveReceiptSequence;

use App\Models\ReserveReceiptSequence;

class ReserveReceiptSequenceRepository implements ReserveReceiptSequenceRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(ReserveReceiptSequence $reserveReceiptSequence)
    {
        $this->reserveReceiptSequence = $reserveReceiptSequence;
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
        $this->reserveReceiptSequence->insert([
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
        $reserveReceiptSequence = $this->reserveReceiptSequence->lockForUpdate()->where('agency_id', $agencyId)->first(); // 行ロックで取得

        if ($reserveReceiptSequence->updated_at->format('Y-m') === date('Y-m', strtotime($date))) {
            $nextNumber = $reserveReceiptSequence->current_number + 1;
            $reserveReceiptSequence->current_number = $nextNumber;
            $reserveReceiptSequence->save();
        } else {
            $nextNumber = 1;
            $reserveReceiptSequence->current_number = $nextNumber;
            $reserveReceiptSequence->updated_at = $date;
            $reserveReceiptSequence->save();
        }

        return $nextNumber;
    }
}
