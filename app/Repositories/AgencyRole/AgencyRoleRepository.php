<?php
namespace App\Repositories\AgencyRole;

use App\Models\AgencyRole;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AgencyRoleRepository implements AgencyRoleRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(AgencyRole $agencyRole)
    {
        $this->agencyRole = $agencyRole;
    }

    public function all(): Collection
    {
        return $this->agencyRole->all();
    }

    public function create(array $data): AgencyRole
    {
        return $this->agencyRole->create($data);
    }

    /**
     * 権限情報を取得
     *
     * データがない場合は 404ステータス
     *
     * @param int $id
     */
    public function find(int $id): AgencyRole
    {
        return $this->agencyRole->findOrFail($id);
    }

    public function update(int $id, array $data): AgencyRole
    {
        $agencyRole = $this->find($id);
        $agencyRole->fill($data)->save();
        return $agencyRole;
    }
    
    public function delete(int $id): int
    {
        return $this->agencyRole->destroy($id);
    }

    public function getMasterRoleId(int $agencyId): int
    {
        return $this->agencyRole->where("agency_id", $agencyId)->where('master', true)->value("id");
    }

    public function getWhere(array $where, array $select=[]): Collection
    {
        $query = $this->agencyRole;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->get();
    }

    /**
     * ページネーション で取得（アカウント用）
     *
     * @var $limit
     * @return object
     */
    public function paginateByAgencyAccount(string $agencyAccount, array $params, int $limit, array $with, bool $getStaffCount = false): LengthAwarePaginator
    {
        $query = $this->agencyRole;
        $query = $with ? $this->agencyRole->with($with) : $query;
        $query = $getStaffCount ? $this->agencyRole->withCount('staffs') : $query;

        $query = $query->whereHas('agency', function ($q) use ($agencyAccount) {
            $q->where('account', $agencyAccount);
        })->sortable();
        
        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, 'like', "%$val%");
        }
        return $query->paginate($limit);
        // return $query->orderBy($sort, $direction)->paginate($limit);
    }

    /**
     * 初期権限セットを取得
     *
     * @param int $agencyId
     * @return array
     */
    public function getDefaultRoles(int $agencyId) : array
    {
        return
        [
            ['agency_id' => $agencyId, 'name' => 'システム管理者', 'description' => '全ての権限を持つ管理者アカウント', 'master' => true,
                'authority' =>
                    [
                        'user_member_cards|user_mileages|user_visas|users' => array_values(config("consts.agency_roles.ACTIONS_LIST")),
                        'business_users|business_user_managers' => array_values(config("consts.agency_roles.ACTIONS_LIST")),
                        'participants|reserve_confirms|reserve_invoices|reserve_receipts|reserve_itineraries|reserves|web_online_schedules|web_reserve_exts' => array_values(config("consts.agency_roles.ACTIONS_LIST")),
                        'account_payable_details|account_payable_reserves|account_payables|agency_bundle_deposits|agency_deposits|agency_withdrawals|reserve_bundle_invoices|reserve_bundle_receipts|reserve_invoices|reserve_receipts|v_reserve_invoices' => array_values(config("consts.agency_roles.ACTIONS_LIST")),
                        'agency_consultations|web_messages|web_message_histories' => array_values(config("consts.agency_roles.ACTIONS_LIST")),
                        'directions|v_directions|areas|v_areas|cities|subject_options|subject_airplanes|subject_hotels|suppliers' => array_values(config("consts.agency_roles.ACTIONS_LIST")),
                        'agency_roles|document_categories|document_commons|document_quotes|document_receipts|document_request_alls|document_requests|mail_templates|staffs|user_custom_items' => array_values(config("consts.agency_roles.ACTIONS_LIST")),
                        'web_companies|web_profiles|web_modelcourses' => array_values(config("consts.agency_roles.ACTIONS_LIST")),
                    ]
            ],
            ['agency_id' => $agencyId, 'name' => 'オペレーター', 'description' => 'オペレーター', 'master' => false,
                'authority' =>
                    [
                        'user_member_cards|user_mileages|user_visas|users' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [
                            config("consts.agency_roles.CREATE"),
                            config("consts.agency_roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                        'business_users|business_user_managers' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [
                            config("consts.agency_roles.CREATE"),
                            config("consts.agency_roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                        'participants|reserve_confirms|reserve_invoices|reserve_receipts|reserve_itineraries|reserves|web_online_schedules|web_reserve_exts' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [])), // 許可しない権限を配列要素に渡す 
                        'account_payable_details|account_payable_reserves|account_payables|agency_bundle_deposits|agency_deposits|agency_withdrawals|reserve_bundle_invoices|reserve_bundle_receipts|reserve_invoices|reserve_receipts|v_reserve_invoices' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [
                            config("consts.agency_roles.READ"),
                            config("consts.agency_roles.CREATE"),
                            config("consts.agency_roles.UPDATE"),
                            config("consts.agency_roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                        'agency_consultations|web_messages|web_message_histories' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [
                            config("consts.agency_roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                        'directions|v_directions|areas|v_areas|cities|subject_options|subject_airplanes|subject_hotels|suppliers' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [
                            config("consts.agency_roles.CREATE"),
                            config("consts.agency_roles.UPDATE"),
                            config("consts.agency_roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                        'agency_roles|document_categories|document_commons|document_quotes|document_receipts|document_request_alls|document_requests|mail_templates|staffs|user_custom_items' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [
                            config("consts.agency_roles.READ"),
                            config("consts.agency_roles.CREATE"),
                            config("consts.agency_roles.UPDATE"),
                            config("consts.agency_roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                        'web_companies|web_profiles|web_modelcourses' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [])), // 許可しない権限を配列要素に渡す
                    ]
            ],
            ['agency_id' => $agencyId, 'name' => '経理', 'description' => '経理', 'master' => false,
                'authority' =>
                    [
                        'user_member_cards|user_mileages|user_visas|users' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [])), // 許可しない権限を配列要素に渡す
                        'business_users|business_user_managers' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [])), // 許可しない権限を配列要素に渡す
                        'participants|reserve_confirms|reserve_invoices|reserve_receipts|reserve_itineraries|reserves|web_online_schedules|web_reserve_exts' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [])), // 許可しない権限を配列要素に渡す
                        'account_payable_details|account_payable_reserves|account_payables|agency_bundle_deposits|agency_deposits|agency_withdrawals|reserve_bundle_invoices|reserve_bundle_receipts|reserve_invoices|reserve_receipts|v_reserve_invoices' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [])), // 許可しない権限を配列要素に渡す
                        'agency_consultations|web_messages|web_message_histories' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [
                            config("consts.agency_roles.CREATE"),
                            config("consts.agency_roles.UPDATE"),
                            config("consts.agency_roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                        'directions|v_directions|areas|v_areas|cities|subject_options|subject_airplanes|subject_hotels|suppliers' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [
                            config("consts.agency_roles.CREATE"),
                            config("consts.agency_roles.UPDATE"),
                            config("consts.agency_roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                        'agency_roles|document_categories|document_commons|document_quotes|document_receipts|document_request_alls|document_requests|mail_templates|staffs|user_custom_items' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [
                            config("consts.agency_roles.READ"),
                            config("consts.agency_roles.CREATE"),
                            config("consts.agency_roles.UPDATE"),
                            config("consts.agency_roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                        'web_companies|web_profiles|web_modelcourses' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [
                            config("consts.agency_roles.CREATE"),
                            config("consts.agency_roles.UPDATE"),
                            config("consts.agency_roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                    ]
            ],
            ['agency_id' => $agencyId, 'name' => '一般', 'description' => '一般', 'master' => false,
                'authority' =>
                    [
                        'user_member_cards|user_mileages|user_visas|users' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [])), // 許可しない権限を配列要素に渡す
                        'business_users|business_user_managers' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [])), // 許可しない権限を配列要素に渡す 
                        'participants|reserve_confirms|reserve_invoices|reserve_receipts|reserve_itineraries|reserves|web_online_schedules|web_reserve_exts' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [])), // 許可しない権限を配列要素に渡す
                        'account_payable_details|account_payable_reserves|account_payables|agency_bundle_deposits|agency_deposits|agency_withdrawals|reserve_bundle_invoices|reserve_bundle_receipts|reserve_invoices|reserve_receipts|v_reserve_invoices' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [
                            config("consts.agency_roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                        'agency_consultations|web_messages|web_message_histories' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [])), // 許可しない権限を配列要素に渡す
                        'directions|v_directions|areas|v_areas|cities|subject_options|subject_airplanes|subject_hotels|suppliers' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [
                            config("consts.agency_roles.CREATE"),
                            config("consts.agency_roles.UPDATE"),
                            config("consts.agency_roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                        'agency_roles|document_categories|document_commons|document_quotes|document_receipts|document_request_alls|document_requests|mail_templates|staffs|user_custom_items' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [
                            config("consts.agency_roles.READ"),
                            config("consts.agency_roles.CREATE"),
                            config("consts.agency_roles.UPDATE"),
                            config("consts.agency_roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                        'web_companies|web_profiles|web_modelcourses' => array_values(array_diff(array_values(config("consts.agency_roles.ACTIONS_LIST")), [])), // 許可しない権限を配列要素に渡す
                    ]
            ],
        ];
    }
}
