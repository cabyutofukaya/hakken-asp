<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\BusinessUser;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\BusinessUser\BusinessUserRepository;
use App\Repositories\BusinessUserManager\BusinessUserManagerRepository;
use App\Repositories\UserMemberCard\UserMemberCardRepository;
use App\Repositories\UserMileage\UserMileageRepository;
use App\Services\BusinessUserCustomValueService;
use App\Services\BusinessUserManagerService;
use App\Services\BusinessUserSequenceService;
use App\Traits\ConstsTrait;
use App\Traits\HasManyGenTrait;
use App\Traits\UserCustomItemTrait;
use Hash;
use Hashids;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Lang;

class BusinessUserService
{
    use ConstsTrait, UserCustomItemTrait, HasManyGenTrait;
    
    public function __construct(
        AgencyRepository $agencyRepository,
        BusinessUserCustomValueService $businessUserCustomValueService,
        BusinessUserManagerRepository $businessUserManagerRepository,
        BusinessUserManagerService $businessUserManagerService,
        BusinessUserRepository $businessUserRepository,
        BusinessUserSequenceService $businessUserSequenceService,
        UserMemberCardRepository $userMemberCardRepository,
        UserMileageRepository $userMileageRepository
    ) {
        $this->agencyRepository = $agencyRepository;
        $this->businessUserCustomValueService = $businessUserCustomValueService;
        $this->businessUserManagerRepository = $businessUserManagerRepository;
        $this->businessUserManagerService = $businessUserManagerService;
        $this->businessUserRepository = $businessUserRepository;
        $this->businessUserSequenceService = $businessUserSequenceService;
        $this->userMemberCardRepository = $userMemberCardRepository;
        $this->userMileageRepository = $userMileageRepository;
    }

    public function find(int $id) : ?BusinessUser
    {
        return $this->businessUserRepository->find($id);
    }

    /**
     * 顧客番号にマッチするレコードを1件取得
     */
    public function findByUserNumber($userNumber, $agencyAccount, array $with=[], array $select=[]) : ?BusinessUser
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->businessUserRepository->findWhere(['user_number' => $userNumber, 'agency_id' => $agencyId], $with, $select);
    }

    /**
     * 顧客番号からIDを取得
     * 
     * @param string $userNumber
     * @param int $agencyId
     * @return int
     */
    public function getIdByUserNumber(string $userNumber, int $agencyId) : ?int
    {
        return $this->businessUserRepository->getIdByUserNumber($userNumber, $agencyId);
    }

    /**
     * 一覧を取得（for 会社アカウント）
     *
     * @param string $account 会社アカウント
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $account, array $params, int $limit, array $with = [], array $select=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);
        return $this->businessUserRepository->paginateByAgencyId($agencyId, $params, $limit, $with, $select);
    }

    public function create(array $data) : BusinessUser
    {
        $data['user_number'] = $this->createUserNumber($data['agency_id']); // 顧客番号を生成

        $businessUser = $this->businessUserRepository->create($data);

        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->businessUserCustomValueService->upsertCustomFileds($customFields, $businessUser->id); // カスタムフィールド保存
        }


        // 世代管理キーを生成
        $genKey = $this->makeGenKey();

        // 各種リレーションを保存

        // 担当者情報
        foreach (Arr::get($data, 'business_user_managers', []) as $row) {
            // $row['gen_key'] = $genKey;
            // $businessUser->business_user_managers()->create($row);
            $row['business_user_id'] = $businessUser->id;
            $row['agency_id'] = $data['agency_id'];
            $this->businessUserManagerService->create($row, $genKey);
        }

        return $businessUser;
    }

    /**
     * 更新
     *
     * @param int $id 顧客ID
     * @param array $data 編集データ
     * @return User
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function update(int $agencyId, int $id, array $data) : BusinessUser
    {
        $businessUser = $this->businessUserRepository->find($id);
        if ($businessUser->updated_at != Arr::get($data, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        // 顧客番号は更新不可
        $data = Arr::except($data, ['user_number']);

        $this->businessUserRepository->update($id, $data);

        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->businessUserCustomValueService->upsertCustomFileds($customFields, $businessUser->id); // カスタムフィールド保存
        }


        // 世代管理キーを生成
        $genKey = $this->makeGenKey();

        // 各種リレーションをinsert or update

        /********** 取引先担当者情報 **********/
        // upsert
        foreach (collect(Arr::get($data, 'business_user_managers', []))->map(function ($item, $key) use ($agencyId, $genKey, $businessUser) {
            // IDのデコード、世代管理キー・UserIDの付与
            $item['id'] = $item['id'];
            $item['gen_key'] = $genKey;
            $item['business_user_id'] = $businessUser->id;
            $item['agency_id'] = $agencyId;
            return $item;
        }) as $row) {
            $this->businessUserManagerService->updateOrCreate(['id' => $row['id']], $row);
        }
        // 登録or更新対象にならなかったレコードの全削除
        $this->businessUserManagerService->deleteExceptionGenKey($genKey, $businessUser->id, false); // 物理削除

        return $businessUser;
    }

    /**
     * 検索して取得
     */
    public function getWhere(array $where, array $with=[], array $select=[], $limit=null) : Collection
    {
        return $this->businessUserRepository->getWhere($where, $with, $select, $limit);
    }

    public function updateField(int $id, array $params) : bool
    {
        return $this->businessUserRepository->updateField($id, $params);
    }

    /**
     * 当該会社の担当者一覧を取得
     * 
     * @param int $id 会社ID
     * @param bool $getDeleted 削除済みの取得する場合はtrue
     * @return Illuminate\Support\Collection
     */
    public function getManagers(int $id, bool $getDeleted = true) : Collection
    {
        return $this->businessUserManagerRepository->getWhere(['business_user_id' => $id], [], [], $getDeleted);
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->businessUserRepository->delete($id, $isSoftDelete);
    }

    /**
     * 定数データを取得
     */
    public function getStatusSelect(): array
    {
        $values = Lang::get('values.business_users.status');
        foreach (config("consts.business_users.STATUS_LIST") as $key => $val) {
            $data[$val] = Arr::get($values, $key);
        }
        return $data;
    }

    /**
     * お客様番号を生成
     * 接頭辞に法人顧客を表す「C」を付ける
     *
     * フォーマット: C西暦下2桁 + 会社識別子 + - + 月 + 3桁連番 + アルファベット
     *
     * @param string $agencyId 会社ID
     * @return string
     */
    public function createUserNumber($agencyId) : string
    {
        $chars = range('A', 'Z');

        // 次の連番を取得
        $seqNumber = $this->businessUserSequenceService->getNextNumber($agencyId, date('Y-m-d'));

        $ranges = array_chunk(range(1, $seqNumber), 999); // 1000で繰り上がり

        $range = count($ranges) - 1;

        $seq = array_search($seqNumber, $ranges[count($ranges)-1]) + 1;


        $agency = $this->agencyRepository->find($agencyId);


        return sprintf("C%02d%s-%02d%03d%s", date('y'), $agency->identifier, date('m'), $seq, $chars[$range]);
    }

}
