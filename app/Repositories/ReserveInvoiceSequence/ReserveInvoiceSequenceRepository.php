<?php
namespace App\Repositories\ReserveInvoiceSequence;

use App\Models\ReserveInvoiceSequence;

class ReserveInvoiceSequenceRepository implements ReserveInvoiceSequenceRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(ReserveInvoiceSequence $reserveInvoiceSequence)
    {
        $this->reserveInvoiceSequence = $reserveInvoiceSequence;
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
        $this->reserveInvoiceSequence->insert([
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
        $reserveInvoiceSequence = $this->reserveInvoiceSequence->lockForUpdate()->where('agency_id', $agencyId)->first(); // 行ロックで取得

        if ($reserveInvoiceSequence->updated_at->format('Y-m') === date('Y-m', strtotime($date))) {
            $nextNumber = $reserveInvoiceSequence->current_number + 1;
            $reserveInvoiceSequence->current_number = $nextNumber;
            $reserveInvoiceSequence->save();
        } else {
            $nextNumber = 1;
            $reserveInvoiceSequence->current_number = $nextNumber;
            $reserveInvoiceSequence->updated_at = $date;
            $reserveInvoiceSequence->save();
        }

        return $nextNumber;
    }
}
