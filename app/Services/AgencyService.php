<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\Agency;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\AgencyRole\AgencyRoleRepository;
use App\Repositories\AgencySequence\AgencySequenceRepository;
use App\Services\AgencyRoleService;
use App\Services\BusinessUserManagerSequenceService;
use App\Services\BusinessUserSequenceService;
use App\Services\DocumentCategoryService;
use App\Services\EstimateSequenceService;
use App\Services\WebEstimateSequenceService;
use App\Services\ReserveSequenceService;
use App\Services\WebReserveSequenceService;
use App\Services\StaffService;
use App\Services\UserCustomItemService;
use App\Services\UserSequenceService;
use App\Services\AgencyConsultationSequenceService;
use App\Services\ReserveInvoiceSequenceService;
use App\Services\ReserveReceiptSequenceService;
use App\Traits\ConstsTrait;
use Carbon\Carbon;
use Hash;
use Illuminate\Support\Arr;
use Vinkla\Hashids\Facades\Hashids;

class AgencyService
{
    use ConstsTrait;

    public function __construct(
        AgencyRepository $agencyRepository,
        AgencyRoleRepository $agencyRoleRepository,
        AgencyRoleService $agencyRoleService,
        AgencySequenceRepository $agencySequenceRepository,
        BusinessUserManagerSequenceService $businessUserManagerSequenceService,
        BusinessUserSequenceService $businessUserSequenceService,
        DocumentCategoryService $documentCategoryService,
        EstimateSequenceService $estimateSequenceService,
        WebEstimateSequenceService $webEstimateSequenceService,
        ReserveSequenceService $reserveSequenceService,
        WebReserveSequenceService $webReserveSequenceService,
        StaffService $staffService,
        UserCustomItemService $userCustomItemService,
        UserSequenceService $userSequenceService,
        AgencyConsultationSequenceService $agencyConsultationSequenceService,
        ReserveInvoiceSequenceService $reserveInvoiceSequenceService,
        ReserveReceiptSequenceService $reserveReceiptSequenceService
    ) {
        $this->agencyRepository = $agencyRepository;
        $this->agencyRoleRepository = $agencyRoleRepository;
        $this->agencyRoleService = $agencyRoleService;
        $this->agencySequenceRepository = $agencySequenceRepository;
        $this->businessUserManagerSequenceService = $businessUserManagerSequenceService;
        $this->businessUserSequenceService = $businessUserSequenceService;
        $this->documentCategoryService = $documentCategoryService;
        $this->estimateSequenceService = $estimateSequenceService;
        $this->webEstimateSequenceService = $webEstimateSequenceService;
        $this->reserveSequenceService = $reserveSequenceService;
        $this->webReserveSequenceService = $webReserveSequenceService;
        $this->staffService = $staffService;
        $this->userCustomItemService = $userCustomItemService;
        $this->userSequenceService = $userSequenceService;
        $this->agencyConsultationSequenceService = $agencyConsultationSequenceService;
        $this->reserveInvoiceSequenceService = $reserveInvoiceSequenceService;
        $this->reserveReceiptSequenceService = $reserveReceiptSequenceService;
    }

    public function find(int $id) : ?Agency
    {
        return $this->agencyRepository->find($id);
    }

    public function paginate($params, int $limit, array $with=[])
    {
        return $this->agencyRepository->paginate(is_array($params) ? $params : [], $limit, $with);
    }

    public function create(array $data) : Agency
    {
        $data['identifier'] = $this->createIdentifier(); // 識別番号を発行

        $this->contractDataOrganization($data); // 契約関連のリクエストデータを整理

        // プランが選択されている場合は契約フラグ definitive を有効に
        $data['definitive'] = Arr::get($data, 'contracts') ? true : false;

        $agency = $this->agencyRepository->create($data);

        // ユーザー権限初期セットを追加
        $agency->agency_roles()->createMany($this->agencyRoleRepository->getDefaultRoles($agency->id));

        Arr::get($data, 'contracts') && $agency->contracts()->createMany($data['contracts']);


        $this->userSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// 個人顧客連番レコード初期化

        $this->businessUserSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// 法人顧客連番レコード初期化

        $this->businessUserManagerSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// 法人顧客担当連番レコード初期化

        $this->estimateSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// 見積連番レコード初期化

        $this->webEstimateSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// Web見積連番レコード初期化

        $this->reserveSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// 予約連番レコード初期化

        $this->webReserveSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// Web予約連番レコード初期化

        $this->agencyConsultationSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// 相談連番レコード初期化

        $this->reserveInvoiceSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// 請求書連番レコード初期化

        $this->reserveReceiptSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// 領収書連番レコード初期化

        // account, password, person_in_charge_name 情報を元にマスターアカウントを作成
        $this->staffService->create($agency->id, [
            'account'           => $data['account'],
            'password'          => $data['password'],
            'name'              => $data['person_in_charge_name'],
            'email'             => $data['email'],
            'master'            => true,
            'agency_role_id'    => $this->agencyRoleService->getMasterRoleId($agency->id)
        ]);

        $this->userCustomItemService->setDefaults($agency); // カスタム項目に初期値をセット

        $this->documentCategoryService->setDefaults($agency); // 帳票フォーマットに初期値をセット

        return $agency;
    }

    /**
     * 更新
     *
     * @param int $id 会社ID
     * @param array $data 編集データ
     * @return Agency
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function update(int $id, array $data) : Agency
    {
        $agency = $this->agencyRepository->find($id);
        if ($agency->updated_at != Arr::get($data, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        $data = Arr::except($data, ['account', 'identifier']); // アカウント、識別子は更新不可

        $this->contractDataOrganization($data); // 契約関連のリクエストデータを整理

        $agency = $this->agencyRepository->update($id, $data);

        
        if ($password = Arr::get($data, 'master_staff.password')) { // マスターパスワードが入力されていたら更新
            $this->staffService->updateFields(
                $agency->master_staff->id,
                [
                    'password' => Hash::make($password)
                ]
            );
        }
        
        return $agency;
    }

    /**
     * 項目更新
     */
    public function updateField(int $id, array $params) : bool
    {
        return $this->agencyRepository->updateField($id, $params);
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->agencyRepository->delete($id, $isSoftDelete);
    }

    public function selectSearchCompanyName(string $name, ?int $exclusionId, int $limit): array
    {
        $name = $name ? $name : ''; // 空文字で初期化

        return $this->agencyRepository->selectSearchCompanyName($name, $exclusionId, $limit);
    }

    /**
     * 会社IDを取得
     *
     * @param string $account 会社アカウント
     * @return int 会社ID
     */
    public function getIdByAccount($account): ?int
    {
        $result = $this->agencyRepository->findBy(['account' => $account]);
        return $result ? $result->id : null;
    }

    /**
     * 当該アカウントが存在する場合はtrue
     *
     * @return boolean
     */
    public function isAccountExists(string $account): bool
    {
        return $this->agencyRepository->isAccountExists($account);
    }

    /**
     * スタッフ登録許可数レンジ配列
     */
    public function getNumberStaffAllowedRange(): array
    {
        return range(1, config('consts.const.NUMBER_STAFF_ALLOWED_MAX'));
    }

    /**
     * 会社識別子を作成
     *
     * 「アルファベット二文字 + 二桁の数字」の形式
     *  AA01 〜 ZZ99
     *  1 〜 66924 までの連番に対応
     *
     * @return string
     */
    public function createIdentifier() : string
    {
        // アルファベットテーブル（AA〜ZZ）
        foreach (range('A', 'Z') as $c1) {
            foreach (range('A', 'Z') as $c2) {
                $chars[] = "{$c1}{$c2}";
            }
        }

        $seqNumber = $this->agencySequenceRepository->getNextNumber();

        $ranges = array_chunk(range(1, $seqNumber), 99); // 100で繰り上がり

        $range = count($ranges) - 1;

        $seq = array_search($seqNumber, $ranges[count($ranges)-1]) + 1;

        return sprintf("%s%02d", $chars[$range], $seq);
    }

    /**
     * agenciesテーブルの契約関連リクエストデータを整理
     *
     * @param array $input データ配列
     * @return void
     */
    private function contractDataOrganization(array &$input) : void
    {
        if (Arr::get($input, 'trial_end_at')) {
            if (preg_match('/(\d{4})\-(\d{1,2})/', $input['trial_end_at'], $m)) {
                //トライアル終了日はYYYY-MM形式で渡ってくるので月末日を補完
                $input['trial_end_at'] = Carbon::create($m[1], $m[2], 1)->endOfMonth()->toDateString();
            }
        }

        if (Arr::get($input, 'contracts')) {
            foreach ($input['contracts'] as $k => $v) {
                if (preg_match('/(\d{4})\-(\d{1,2})\-(\d{1,2})/', $v['start_at'], $m)) {
                    // YYYY-MM-DD形式の日付に時刻（00:00:00）を補完
                    $input['contracts'][$k]['start_at'] = Carbon::create($m[1], $m[2], $m[3], 0, 0, 0);
                }

                if (preg_match('/(\d{4})\-(\d{1,2})\-(\d{1,2})/', $v['end_at'], $m)) {
                    // YYYY-MM-DD形式の日付に時刻（23:59:59）を補完
                    $input['contracts'][$k]['end_at'] = Carbon::create($m[1], $m[2], $m[3], 23, 59, 59);
                }
            }
        }
    }
}
