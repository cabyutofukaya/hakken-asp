<?php
namespace App\Repositories\UserSequence;

use App\Models\UserSequence;

class UserSequenceRepository implements UserSequenceRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(UserSequence $userSequence)
    {
        $this->userSequence = $userSequence;
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
        $this->userSequence->insert([
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
        $userSequence = $this->userSequence->lockForUpdate()->where('agency_id', $agencyId)->first(); // 行ロックで取得

        if ($userSequence->updated_at->format('Y-m') === date('Y-m', strtotime($date))) {
            $nextNumber = $userSequence->current_number + 1;
            $userSequence->current_number = $nextNumber;
            $userSequence->save();
        } else {
            $nextNumber = 1;
            $userSequence->current_number = $nextNumber;
            $userSequence->updated_at = $date;
            $userSequence->save();
        }

        return $nextNumber;
    }
}
