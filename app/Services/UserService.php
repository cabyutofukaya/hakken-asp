<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\User;
use App\Models\WebUser;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\User\UserRepository;
use App\Repositories\UserMemberCard\UserMemberCardRepository;
use App\Repositories\UserMileage\UserMileageRepository;
use App\Repositories\UserVisa\UserVisaRepository;
use App\Services\UserCustomValueService;
use App\Services\UserSequenceService;
use App\Services\UserMileageService;
use App\Services\UserVisaService;
use App\Services\UserMemberCardService;
use App\Services\AspUserService;
use App\Traits\ConstsTrait;
use App\Traits\HasManyGenTrait;
use App\Traits\UserCustomItemTrait;
use App\Traits\BirthdayTrait;
use Hash;
use Hashids;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Lang;

/**
 * 個人顧客管理
 */
class UserService
{
    use ConstsTrait, UserCustomItemTrait, HasManyGenTrait, BirthdayTrait;
    
    public function __construct(
        AgencyRepository $agencyRepository,
        UserCustomValueService $userCustomValueService,
        UserMemberCardRepository $userMemberCardRepository,
        UserMileageRepository $userMileageRepository,
        UserRepository $userRepository,
        UserSequenceService $userSequenceService,
        UserVisaRepository $userVisaRepository,
        UserMileageService $userMileageService,
        UserVisaService $userVisaService,
        UserMemberCardService $userMemberCardService,
        AspUserService $aspUserService
    ) {
        $this->agencyRepository = $agencyRepository;
        $this->userCustomValueService = $userCustomValueService;
        $this->userMemberCardRepository = $userMemberCardRepository;
        $this->userMileageRepository = $userMileageRepository;
        $this->userRepository = $userRepository;
        $this->userSequenceService = $userSequenceService;
        $this->userVisaRepository = $userVisaRepository;
        $this->userMileageService = $userMileageService;
        $this->userVisaService = $userVisaService;
        $this->userMemberCardService = $userMemberCardService;
        $this->aspUserService = $aspUserService;
    }

    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false) : ?User
    {
        return $this->userRepository->find($id, $with, $select, $getDeleted);
    }

    /**
     * 顧客番号にマッチするレコードを1件取得
     */
    public function findByUserNumber($userNumber, $agencyAccount, array $with = [], array $select = [], bool $getDeleted = false) : ?User
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->userRepository->findWhere(['user_number' => $userNumber, 'agency_id' => $agencyId], $with, $select, $getDeleted);
    }
    
    /**
     * ユーザー番号からIDを取得
     *
     * @param string $userNumber
     * @param int $agencyId
     * @return int
     */
    public function getIdByUserNumber(string $userNumber, int $agencyId) : ?int
    {
        return $this->userRepository->getIdByUserNumber($userNumber, $agencyId);
    }

    /**
     * 全ユーザーデータを取得（管理画面用）
     */
    public function paginate(int $limit, array $with) : LengthAwarePaginator
    {
        return $this->userRepository->paginate($limit, $with);
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
        return $this->userRepository->paginateByAgencyId($agencyId, $params, $limit, $with, $select);
    }

    /**
     * 検索して取得
     */
    public function getWhere(array $where, array $with=[], array $select=[], $limit=null) : Collection
    {
        return $this->userRepository->getWhere($where, $with, $select, $limit);
    }

    /**
     * 申込者検索（予約ページの参加者検索に使用）
     *
     * @param int $agencyId 会社ID
     * @param string $name ユーザー名
     * @param string $userNumber 顧客番号
     * @param array $with
     * @param array $select
     * @param int $lmit 取得件数。全取得の場合はnull
     */
    public function applicantSearch(int $agencyId, ?string $name, ?string $userNumber, array $with = [], array $select = [], ?int $limit = null, bool $getDeleted = false) : Collection
    {
        return $this->userRepository->applicantSearch($agencyId, $name, $userNumber, $with, $select, $limit, $getDeleted);
    }

    /**
     * 個人顧客作成(ASPユーザー作成用)
     *
     * @param bool $setUserNumber 顧客番号を発行する場合はTrue（旅行参加者として登録する場合は不要）
     */
    public function createAspUser(array $data, bool $setUserNumber = true) : User
    {
        // User作成用データを抽出
        $userData = collect($data)->only(['agency_id','status'])->toArray();

        if ($setUserNumber) { // $setUserNumber=true → 顧客番号を生成
            $userData['user_number'] = $this->createUserNumber($data['agency_id'], config('consts.users.USER_NUMBER_PREFIX_ASP'));
        }

        // AspUser作成用データを抽出
        $userableData = collect(Arr::get($data, 'userable'))->except(['user_ext'])->toArray();

        // AspUserExt作成用データを抽出
        $userExtData = Arr::get($data, 'userable.user_ext', []);

        /**
         * AspUser,AspUserExt,Userレコードを作成
         */
        // asp_usersを作成
        $userable = $this->aspUserService->create($userableData);

        // user_extリレーションを作成
        $userable->user_ext()->create(array_merge($userExtData, ['agency_id' => $userData['agency_id']])); // 会社IDを付与
        

        // usersを作成
        $user = $this->userRepository->create(
            array_merge(
                $userData,
                // ポリモーフィックリレーション
                [
                    'userable_type' => get_class($userable),
                    'userable_id' => $userable->id
                ]
            )
        );

        /**
         * カスタムフィールド関連
         */

        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出

        if ($customFields) {
            $this->userCustomValueService->upsertCustomFileds($customFields, $user->id); // カスタムフィールド保存
        }


        /**
         * 各種リレーションを保存
         */

        // 世代管理キーを生成
        $genKey = $this->makeGenKey();

        // ビザ情報
        foreach (Arr::get($data, 'user_visas', []) as $row) {
            $row['user_id'] = $user->id;
            $this->userVisaService->create($row, $genKey);
        }

        // マイレージ情報
        foreach (Arr::get($data, 'user_mileages', []) as $row) {
            $row['user_id'] = $user->id;
            $this->userMileageService->create($row, $genKey);
        }

        // メンバーズカード情報
        foreach (Arr::get($data, 'user_member_cards', []) as $row) {
            $row['user_id'] = $user->id;
            $this->userMemberCardService->create($row, $genKey);
        }

        return $this->find($user->id);
    }

    /**
     * Webユーザーからusersレコードを作成
     *
     * @param WebUser $webUser
     * @param array $data
     * @param bool $setUserNumber
     * @return User
     */
    public function createFromWebUser(WebUser $webUser, array $data, bool $setUserNumber = true) : User
    {
        if ($setUserNumber) {
            $data['user_number'] = $this->createUserNumber($data['agency_id'], config('consts.users.USER_NUMBER_PREFIX_WEB')); // 顧客番号を生成
        }

        // ポリモーフィックリレーションを作成
        $data['userable_type'] = get_class($webUser);
        $data['userable_id'] = $webUser->id;

        // // 顧客区分
        // $data['user_kbn'] = config('consts.users.USER_KBN_WEB');

        return $this->userRepository->create($data);
    }

    /**
     * web_user_idからユーザー情報を取得
     *
     * @param int $webUserId WebユーザーID
     * @param int $agencyId 会社ID
     * @param bool $getDeleted 論理削除を含める場合はtrue
     * @return ?User
     */
    public function findByWebUserId(int $webUserId, int $agencyId, bool $getDeleted = false) : ?User
    {
        return $this->userRepository->findByWebUserId($webUserId, $agencyId, $getDeleted);
    }

    /**
     * 更新
     *
     * @param int $id ユーザーID
     * @param array $data 編集データ
     * @param bool $checkUpdate 同時編集をチェックする場合はtrue
     * @return User
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function update(int $id, array $data, bool $checkUpdate = true) : User
    {
        $oldUser = $this->userRepository->find($id);
        if ($checkUpdate) {
            if ($oldUser->updated_at != Arr::get($data, 'updated_at')) {
                throw new ExclusiveLockException;
            }
        }

        // Userデータを抽出
        $userData = collect($data)->only(['agency_id','status'])->toArray();
        // 念の為、顧客番号は編集対象から除外
        $userData = Arr::except($userData, ['user_number']);

        if ($oldUser->userable_type === 'App\Models\WebUser') { // WebUserの場合はuserableの変更不可
            // UserExt作成用データを抽出
            $userExtData = Arr::get($data, 'userable.user_ext', []);

            $user = $this->userRepository->update($id, $userData);
        } else { //WebUserでない場合は全てのフィールドの更新が可能
            // userable作成用データを抽出
            $userableData = collect(Arr::get($data, 'userable'))->except(['user_ext'])->toArray();

            // UserExt作成用データを抽出
            $userExtData = Arr::get($data, 'userable.user_ext', []);

            $user = $this->userRepository->update($id, $userData);
            $user->userable->update($userableData);
        }

        if ($user->userable->user_ext) {
            $user->userable->user_ext->update($userExtData);
        } else {
            $user->userable->user_ext()->create(array_merge($userExtData, ['agency_id' => $user->agency_id])); // 会社IDを付与
        }


        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->userCustomValueService->upsertCustomFileds($customFields, $user->id); // カスタムフィールド保存
        }

        // 世代管理キーを生成
        $genKey = $this->makeGenKey();

        // 各種リレーションをinsert or update

        /********** ビザ情報 **********/
        // upsert
        foreach (collect(Arr::get($data, 'user_visas', []))->map(function ($item, $key) use ($genKey, $user) {
            // UserIDの付与
            $item['user_id'] = $user->id;
            return $item;
        }) as $row) {
            $this->userVisaService->updateOrCreate(['id' => Arr::get($row, 'id')], $row, $genKey);
        }
        // 登録or更新対象にならなかったレコードの全削除
        $this->userVisaRepository->deleteExceptionGenKey($genKey, $user->id, false); // 物理削除


        /********** マイレージ情報 **********/
        // upsert
        foreach (collect(Arr::get($data, 'user_mileages', []))->map(function ($item, $key) use ($genKey, $user) {
            // UserIDの付与
            $item['user_id'] = $user->id;
            return $item;
        }) as $row) {
            $this->userMileageService->updateOrCreate(['id' => Arr::get($row, 'id')], $row, $genKey);
        }
        // 登録or更新対象にならなかったレコードの全削除
        $this->userMileageRepository->deleteExceptionGenKey($genKey, $user->id, false); // 物理削除


        /********** メンバーカード情報 **********/
        // upsert
        foreach (collect(Arr::get($data, 'user_member_cards', []))->map(function ($item, $key) use ($genKey, $user) {
            // UserIDの付与
            $item['user_id'] = $user->id;
            return $item;
        }) as $row) {
            $this->userMemberCardService->updateOrCreate(['id' => Arr::get($row, 'id')], $row, $genKey);
        }
        // 登録or更新対象にならなかったレコードの全削除
        $this->userMemberCardRepository->deleteExceptionGenKey($genKey, $user->id, false); // 物理削除

        
        return $this->find($user->id);
    }

    public function updateField(int $userId, array $params) : bool
    {
        return $this->userRepository->updateField($userId, $params);
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->userRepository->delete($id, $isSoftDelete);
    }

    /**
     * 定数データを取得
     */
    public function getStatusSelect(): array
    {
        $values = Lang::get('values.users.status');
        foreach (config("consts.users.STATUS_LIST") as $key => $val) {
            $data[$val] = Arr::get($values, $key);
        }
        return $data;
    }

    /**
     * user_extsレコードのageカラムをアップサート
     *
     * @param int $age 年齢
     * @param int $userId ユーザーID
     */
    public function upsertAgeByUserId(?int $age, int $userId) : bool
    {
        $user = $this->userRepository->find($userId);
        if ($user->userable_type === 'App\Models\AspUser') {
            $user->userable->user_ext()->updateOrCreate(['asp_user_id' => $user->userable_id], ['age' => $age]);
        } elseif ($user->userable_type === 'App\Models\WebUser') {
            $user->userable->user_ext()->updateOrCreate(['web_user_id' => $user->userable_id], ['age' => $age]);
        }
        return true;
    }

    /////

    /**
     * 全ユーザーデータを取得（旅行会社用）
     */
    public function paginateByAgencyId(int $agencyId, int $limit, array $with) : LengthAwarePaginator
    {
        return $this->userRepository->paginateByAgencyId($agencyId, $limit, $with);
    }

    /**
     * ユーザー情報を取得（旅行会社用）
     *
     */
    public function findByUserNumberForAgencyAccount(string $userNumber, string $agencyAccount) : ?User
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->userRepository->findByUserNumberForAgencyId($userNumber, $agencyId);
    }


    /**
     * お客様番号を生成
     *
     * ASPユーザーは接頭辞に個人顧客を表す「P」
     * WEBユーザーは接頭辞にWEB個人顧客を表す「WP」
     * をつける
     *
     * フォーマット: (P/WP)西暦下2桁 + 会社識別子 + - + 月 + 3桁連番 + アルファベット
     *
     * @param string $agencyId 会社ID
     * @return string
     */

    public function createUserNumber($agencyId, $prefix = 'P') : string
    {
        $chars = range('A', 'Z');

        // 次の連番を取得
        $seqNumber = $this->userSequenceService->getNextNumber($agencyId, date('Y-m-d'));

        $ranges = array_chunk(range(1, $seqNumber), 999); // 1000で繰り上がり

        $range = count($ranges) - 1;

        $seq = array_search($seqNumber, $ranges[count($ranges)-1]) + 1;


        $agency = $this->agencyRepository->find($agencyId);


        return sprintf("%s%02d%s-%02d%03d%s", $prefix, date('y'), $agency->identifier, date('m'), $seq, $chars[$range]);
    }
}
