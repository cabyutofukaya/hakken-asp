<?php
namespace App\Repositories\AgencyConsultation;

use App\Models\AgencyConsultation;
use Illuminate\Pagination\LengthAwarePaginator;

class AgencyConsultationRepository implements AgencyConsultationRepositoryInterface
{
    /**
    * @param AgencyConsultation $agencyConsultation
    */
    public function __construct(AgencyConsultation $agencyConsultation)
    {
        $this->agencyConsultation = $agencyConsultation;
    }

    /**
     * ページャーで検索
     * 
     * @param int $agencyId 会社ID
     * @param array $params 検索パラメータ
     * @param int $limit 取得件数
     * @param array $with リレーション
     * @param array $select 取得項目
     * @return LengthAwarePaginator
     */
    public function paginateByAgencyId(int $agencyId, array $params = [], int $limit, array $with = [], array $select =[]) : LengthAwarePaginator
    {
        $query = $this->agencyConsultation;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        
        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }

            if (strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目
                $query = $query->whereHas('v_agency_consultation_custom_values', function ($q) use ($key, $val) {
                    $q->where('key', $key)->where('val', 'like', "%$val%");
                });
            } elseif ($key === 'reserve_estimate_number') { // 予約・見積番号
                $query = $query->whereHas('reserve', function ($q) use ($val) {
                    $q->where('control_number', 'like', "%$val%")
                        ->orWhere('estimate_number', 'like', "%$val%");
                });
            } elseif ($key === 'deadline_from') { //期限from
                $query = $query->where('deadline', '>=', $val);
            } elseif ($key === 'deadline_to') { //期限to
                $query = $query->where('deadline', '<=', $val);
            } elseif ($key === 'reception_date_from') { //受付日from
                $query = $query->where('reception_date', '>=', $val);
            } elseif ($key === 'reception_date_to') { //受付日to
                $query = $query->where('reception_date', '<=', $val);
            } else {
                $query = $query->where($key, 'like', "%$val%");
            }
        }

        return $query->where('agency_consultations.agency_id', $agencyId)->sortable()->paginate($limit);// sortableする際にagency_idがリレーション先のテーブルにも存在するのでエラー回避のために明示的にagency_idを指定する
    }

    // public function paginateByTaxonomy(?string $taxonomy, int $agencyId, $params, $limit, $with, $select) : LengthAwarePaginator
    // {
    //     // 種別に応じて取得スコープを設定
    //     switch ($taxonomy) {
    //         case config('consts.agency_consultations.TAXONOMY_RESERVE'): // 見積・予約
    //             $query = $this->agencyConsultation->reserve();
    //             break;
    //         case config('consts.agency_consultations.TAXONOMY_PERSON'): // 個人顧客
    //             $query = $this->agencyConsultation->person();
    //             break;
    //         case config('consts.agency_consultations.TAXONOMY_BUSINESS'): // 法人顧客
    //             $query = $this->agencyConsultation->business();
    //             break;
    //         default: // スコープ無し
    //             $query = $this->agencyConsultation;
    //     }

    //     $query = $with ? $query->with($with) : $query;
    //     $query = $select ? $query->select($select) : $query;
        
    //     foreach ($params as $key => $val) {
    //         if (is_empty($val)) {
    //             continue;
    //         }

    //         $query = $query->where($key, 'like', "%$val%");
    //     }

    //     return $query->where('agency_id', $agencyId)->sortable()->paginate($limit);
    // }

    /**
     * 当該IDを取得
     *
     * データがない場合は 404ステータス
     *
     * @param int $id
     */
    public function find(int $id, array $select = []): ?AgencyConsultation
    {
        return $select ? $this->agencyConsultation->select($select)->findOrFail($id) : $this->agencyConsultation->findOrFail($id);
    }

    public function create(array $data) : AgencyConsultation
    {
        return $this->agencyConsultation->create($data);
    }

    public function update(int $id, array $data): AgencyConsultation
    {
        $agencyConsultation = $this->find($id);
        $agencyConsultation->fill($data)->save();
        return $agencyConsultation;
    }

    /**
     * 検索して一件取得
     */
    public function findWhere(array $where, array $with = [], array $select = []) : ?AgencyConsultation
    {
        $query = $this->agencyConsultation;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->first();
    }
}
