<?php
namespace App\Repositories\AgencyConsultationSequence;

use App\Models\AgencyConsultationSequence;

class AgencyConsultationSequenceRepository implements AgencyConsultationSequenceRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(AgencyConsultationSequence $agencyConsultationSequence)
    {
        $this->agencyConsultationSequence = $agencyConsultationSequence;
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
        $this->agencyConsultationSequence->insert([
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
        $agencyConsultationSequence = $this->agencyConsultationSequence->lockForUpdate()->where('agency_id', $agencyId)->first(); // 行ロックで取得

        if ($agencyConsultationSequence->updated_at->format('Y-m') === date('Y-m', strtotime($date))) {
            $nextNumber = $agencyConsultationSequence->current_number + 1;
            $agencyConsultationSequence->current_number = $nextNumber;
            $agencyConsultationSequence->save();
        } else {
            $nextNumber = 1;
            $agencyConsultationSequence->current_number = $nextNumber;
            $agencyConsultationSequence->updated_at = $date;
            $agencyConsultationSequence->save();
        }

        return $nextNumber;
    }
}
