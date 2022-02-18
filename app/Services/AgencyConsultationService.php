<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\AgencyConsultation;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\AgencyConsultation\AgencyConsultationRepository;
use App\Services\AgencyConsultationSequenceService;
use App\Services\AgencyConsultationCustomValueService;
use App\Traits\ConstsTrait;
use Illuminate\Http\Request;
use App\Traits\UserCustomItemTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class AgencyConsultationService
{
    use ConstsTrait, UserCustomItemTrait;
    
    public function __construct(AgencyConsultationRepository $agencyConsultationRepository, AgencyConsultationSequenceService $agencyConsultationSequenceService, AgencyRepository $agencyRepository, AgencyConsultationCustomValueService $agencyConsultationCustomValueService)
    {
        $this->agencyConsultationRepository = $agencyConsultationRepository;
        $this->agencyRepository = $agencyRepository;
        $this->agencyConsultationSequenceService = $agencyConsultationSequenceService;
        $this->agencyConsultationCustomValueService = $agencyConsultationCustomValueService;
    }

    /**
     * 相談一覧を取得
     *
     * @param string $agencyAccount 会社アカウント
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $agencyAccount, array $params, int $limit, array $with = [], $select = []) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);

        return $this->agencyConsultationRepository->paginateByAgencyId($agencyId, $params, $limit, $with, $select);
    }

    public function find(int $id) : ?AgencyConsultation
    {
        return $this->agencyConsultationRepository->find($id);
    }

    /**
     * 相談番号から当該レコードを1件取得
     */
    public function findByControlNumber(string $controlNumber, string $agencyAccount) : ?AgencyConsultation
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->agencyConsultationRepository->findWhere([
            'agency_id' => $agencyId,
            'control_number' => $controlNumber,
        ]);
    }

    /**
     * 作成
     */
    public function create(array $data): AgencyConsultation
    {
        $data['control_number'] = $this->createUserNumber($data['agency_id']); // 顧客番号を生成

        $consultation = $this->agencyConsultationRepository->create($data);

        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->agencyConsultationCustomValueService->upsertCustomFileds($customFields, $consultation->id); // カスタムフィールド保存
        }

        return $consultation;
    }

    /**
     * 更新
     *
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function update(int $id, array $data): AgencyConsultation
    {
        $old = $this->agencyConsultationRepository->find($id);
        if ($old->updated_at != Arr::get($data, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        // 相談番号は更新不可なので一応、配列に入っていたらカットしておく
        if (isset($data['control_number'])) {
            unset($data['control_number']);
        }
        $consultation = $this->agencyConsultationRepository->update($id, $data);

        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->agencyConsultationCustomValueService->upsertCustomFileds($customFields, $consultation->id); // カスタムフィールド保存
        }

        return $consultation;
    }

    /**
     * 管理番号を生成
     * 接頭辞に相談管理を表す「D」を付ける
     *
     * フォーマット: 西暦下2桁 + 会社識別子 + - + 月 + 3桁連番 + アルファベット
     *
     * @param string $agencyId 会社ID
     * @return string
     */

    public function createUserNumber($agencyId) : string
    {
        $chars = range('A', 'Z');

        // 次の連番を取得
        $seqNumber = $this->agencyConsultationSequenceService->getNextNumber($agencyId, date('Y-m-d'));

        $ranges = array_chunk(range(1, $seqNumber), 999); // 1000で繰り上がり

        $range = count($ranges) - 1;

        $seq = array_search($seqNumber, $ranges[count($ranges)-1]) + 1;


        $agency = $this->agencyRepository->find($agencyId);


        return sprintf("D%02d%s-%02d%03d%s", date('y'), $agency->identifier, date('m'), $seq, $chars[$range]);
    }
}
