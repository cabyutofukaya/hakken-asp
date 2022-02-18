<?php
namespace App\Repositories\WebReserveSequence;

use App\Models\WebReserveSequence;

class WebReserveSequenceRepository implements WebReserveSequenceRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(WebReserveSequence $webReserveSequence)
    {
        $this->webReserveSequence = $webReserveSequence;
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
        $this->webReserveSequence->insert([
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
        $webReserveSequence = $this->webReserveSequence->lockForUpdate()->where('agency_id', $agencyId)->first(); // 行ロックで取得

        if ($webReserveSequence->updated_at->format('Y-m') === date('Y-m', strtotime($date))) {
            $nextNumber = $webReserveSequence->current_number + 1;
            $webReserveSequence->current_number = $nextNumber;
            $webReserveSequence->save();
        } else {
            $nextNumber = 1;
            $webReserveSequence->current_number = $nextNumber;
            $webReserveSequence->updated_at = $date;
            $webReserveSequence->save();
        }

        return $nextNumber;
    }
}
