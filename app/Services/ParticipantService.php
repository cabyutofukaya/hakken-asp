<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\Participant;
use App\Models\Reserve;
use App\Models\User;
use App\Models\ParticipantReserve;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\Participant\ParticipantRepository;
use App\Repositories\Reserve\ReserveRepository;
use App\Services\AspUserService;
use App\Services\AspUserExtService;
use App\Services\ParticipantSequenceService;
use App\Services\ReserveParticipantAirplanePriceService;
use App\Services\ReserveParticipantHotelPriceService;
use App\Services\ReserveParticipantOptionPriceService;
use App\Services\UserService;
use App\Traits\ConstsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class ParticipantService
{
    use ConstsTrait;

    // participantsテーブルに保存する要素（USERS_COLUMN）
    const PARTICIPANT_USERABLE_COLUMN = [
        'name',
        'name_kana',
        'name_roman',
        'sex',
        'birthday_y',
        'birthday_m',
        'birthday_d',
        'mobile_phone',
        'passport_number',
        'passport_issue_date',
        'passport_expiration_date',
        'passport_issue_country_code',
        'citizenship_code',
    ];

    // participantsテーブルに保存する要素（user_extカラム）
    const PARTICIPANT_USER_EXT_COLUMN = [
        'age',
        'age_kbn',
        'note',
    ];

    public function __construct(
        ParticipantRepository $participantRepository,
        AgencyRepository $agencyRepository,
        UserService $userService,
        AspUserService $aspUserService,
        AspUserExtService $aspUserExtService,
        ReserveRepository $reserveRepository,
        ReserveParticipantAirplanePriceService $reserveParticipantAirplanePriceService,
        ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService,
        ReserveParticipantOptionPriceService $reserveParticipantOptionPriceService
    ) {
        $this->agencyRepository = $agencyRepository;
        $this->participantRepository = $participantRepository;
        $this->userService = $userService;
        $this->aspUserService = $aspUserService;
        $this->aspUserExtService = $aspUserExtService;
        $this->reserveRepository = $reserveRepository;
        $this->reserveParticipantAirplanePriceService = $reserveParticipantAirplanePriceService;
        $this->reserveParticipantHotelPriceService = $reserveParticipantHotelPriceService;
        $this->reserveParticipantOptionPriceService = $reserveParticipantOptionPriceService;
    }

    /**
     * 当該IDを取得
     */
    public function find(int $id, array $with = [], array $select = []) : ?Participant
    {
        return $this->participantRepository->find($id, $with, $select);
    }

    /**
     * 当該予約情報に参加済みユーザーか否か
     */
    public function isExistsInReserve(int $userId, int $reserveId) : bool
    {
        return $this->participantRepository->isExistsInReserve($userId, $reserveId);
    }

    /**
     * ユーザー情報から参加者情報を作成
     *
     * @param boolean $representative 代表者に設定する場合はtrue
     */
    public function createFromUser(Reserve $reserve, int $agencyId, User $user, bool $representative = false) : Participant
    {
        $userData = data_get($user, 'userable')->toArray();
        $userData['name'] = $userData['org_name']; // userableのnameカラムは削除or無効状態時にラベル(名前の末尾に「削除」等の文字列)がついてしまうのでオリジナルの名前(org_name)で設定

        $participant = $this->participantRepository->create(
            array_merge(
                collect($userData)->only(self::PARTICIPANT_USERABLE_COLUMN)->toArray(),
                collect(Arr::get($user, 'userable.user_ext'))->only(self::PARTICIPANT_USER_EXT_COLUMN)->toArray(), // user_extカラム
                [
                    'agency_id' => $agencyId,
                    'reserve_id' => $reserve->id,
                    'user_id' => $user->id,
                    'representative' => $representative,
                    'cancel' => false,
                ]
            )
        );

        // 予約情報に参加者を紐付け
        $reserve->participants()->attach($participant->id);

        return $participant;
    }

    /**
     * 参加者作成
     *
     * userデータを作成 → 参加者情報を作成 → 参加者情報を予約情報に紐付け
     *
     * @param int $reserveId 予約ID
     * @param array $userData ユーザー作成用データ
     */
    public function create(Reserve $reserve, array $userData): Participant
    {
        // users, userable, user_ext用のデータをそれぞれ抽出
        $data = collect($userData)->only(['agency_id','status'])->toArray();
        $data['userable'] = collect($userData)->only(self::PARTICIPANT_USERABLE_COLUMN)->toArray();
        $data['userable']['agency_id'] = $data['agency_id']; // 会社IDをセット
        $data['userable']['user_ext'] = collect($userData)->only(self::PARTICIPANT_USER_EXT_COLUMN)->toArray();

        /**
         * 個人顧客データをお客様番号ナシで作成。
         */
        $user = $this->userService->createAspUser($data, false);

        /**
         * 参加者情報を作成し、予約情報に紐付け
         */
        return $this->createFromUser($reserve, $user->agency_id, $user, false);
    }

    /**
     * 参加者バルクインサート。顧客番号の作成はナシ
     *
     * @param int $agencyId 会社ID
     * @param Reserve $reserve 予約情報
     * @param int $number 作成人数
     * @param string $ageKbn 年齢区分
     * @param int $status ステータス
     * @return bool
     */
    public function bulkCreate(int $agencyId, Reserve $reserve, int $number, string $ageKbn, int $status) : bool
    {
        /**
         * asp_usersをバルクインサート
         * ↓
         * 作成したasp_userに対してuser_extリレーションを作成(新規登録したasp_usersのidはダミーで登録した参加者名から検索)
         * ↓
         * 作成したasp_userに対してuserリレーションを作成
         * ↓
         * 作成したusersを元にparticipantsレコードを作成
         * ↓
         * reservesの多対多リレーションの中間テーブルを作成
         */

        $tmpUniqidName = uniqid(rand()); // バルクインサートした際に作成レコードの目印としてnameカラムにユニーク値をセットしておく

        $createdAt = date('Y-m-d H:i:s');
        
        // asp_userをバルクインサート
        $aspUsers = [];
        for ($i = 0; $i< $number; $i++) {
            $tmp = [];
            $tmp['agency_id'] = $agencyId;
            $tmp['name'] = $tmpUniqidName;
            $tmp['created_at'] = $createdAt;
            $tmp['updated_at'] = $createdAt;
            
            $aspUsers[] = $tmp;
        }
        $this->aspUserService->insert($aspUsers);

        // バルクインサートしたasp_users ID一覧を取得
        $aspUserIds = $this->aspUserService->getWhereIds(['agency_id' => $agencyId, 'name' => $tmpUniqidName]);

        // ダミーでセットした名前を書き換え
        $nameTable = []; // 「id => asp_user_id, name => 参加者名」形式の配列

        $participanCount = $reserve->participant_count; // 当該予約の参加者数(参加者名のナンバリング表示に使用)
        foreach ($aspUserIds as $i => $aspUserId) {
            $participanCount++;
            $tmp = [];
            $tmp['id'] = $aspUserId;
            $tmp['name'] = "参加者-{$participanCount}";

            $nameTable[] = $tmp;
        }
        // 名前をバルクアップデート
        $this->aspUserService->updateBulk($nameTable, "id");

        // user_extリレーションを作成
        $userExts = [];
        foreach ($aspUserIds as $aspUserId) {
            $tmp = [];
            $tmp['agency_id'] = $agencyId;
            $tmp['asp_user_id'] = $aspUserId;
            $tmp['created_at'] = $createdAt;
            $tmp['updated_at'] = $createdAt;

            $userExts[] = $tmp;
        }
        $this->aspUserExtService->insert($userExts);

        // usersレコードを作成
        $users = [];
        foreach ($aspUserIds as $aspUserId) {
            $tmp = [];
            $tmp['agency_id'] = $agencyId;
            $tmp['userable_type'] = 'App\Models\AspUser';
            $tmp['userable_id'] = $aspUserId;
            $tmp['status'] = $status;
            $tmp['created_at'] = $createdAt;
            $tmp['updated_at'] = $createdAt;

            $users[] = $tmp;
        }
        $this->userService->insert($users);
        // バルクインサートしたusers ID、userable_id一覧を取得
        $userIdInfos = $this->userService->getIdInfoByUserableId($agencyId, 'App\Models\AspUser', $aspUserIds);


        $nameArr = collect($nameTable)->pluck('name', 'id')->toArray(); // 「asp_user_id=>名前」形式の配列に変換
        // participantsレコードを作成
        $participants = [];
        foreach ($userIdInfos as $userIdInfo) {
            $tmp = [];
            $tmp['agency_id'] = $agencyId;
            $tmp['reserve_id'] = $reserve->id;
            $tmp['user_id'] = $userIdInfo['id'];
            $tmp['name'] = Arr::get($nameArr, $userIdInfo['userable_id']);
            $tmp['age_kbn'] = $ageKbn;
            $tmp['cancel'] = false; //　キャンセルパラメータはfalseで初期化
            $tmp['created_at'] = $createdAt;
            $tmp['updated_at'] = $createdAt;

            $participants[] = $tmp;
        }
        $this->insert($participants);
        // バルクインサートしたparticipants ID一覧を取得
        $participantIds = $this->getIdsByReserveIdAndUserIds($reserve->id, collect($userIdInfos)->pluck('id')->toArray());

        // 多対多の中間テーブルを作成
        $participantReserves = [];
        foreach ($participantIds as $participantId) {
            $tmp = [];
            $tmp['reserve_id'] = $reserve->id;
            $tmp['participant_id'] = $participantId;

            $participantReserves[] = $tmp;
        }
        ParticipantReserve::insert($participantReserves);

        return true;
    }

    /**
     * バルクインサート
     */
    public function insert(array $rows) : bool
    {
        $this->participantRepository->insert($rows);
        return true;
    }

    /**
     * 更新
     *
     */
    public function update(int $id, array $data): bool
    {
        // 参加者レコードを更新
        $this->participantRepository->updateField($id, collect($data)->only(array_merge(self::PARTICIPANT_USERABLE_COLUMN, self::PARTICIPANT_USER_EXT_COLUMN))->toArray());
        return true;
    }

    /**
     * 一覧を取得
     *
     * @param int $reserveId
     * @param int $limit
     * @param array $with
     */
    public function paginateByReserveId(int $reserveId, array $params, int $limit, array $with=[], $select=[]) : LengthAwarePaginator
    {
        return $this->participantRepository->paginateByReserveId($reserveId, $params, $limit, $with, $select);
    }
    
    /**
     * 予約IDとユーザーIDリストを条件にID一覧を取得
     */
    public function getIdsByReserveIdAndUserIds(int $reserveId, array $userIds) : array
    {
        return $this->participantRepository->getIdsByReserveIdAndUserIds($reserveId, $userIds);
    }

    /**
     * 代表者を設定
     *
     * @param int $id 参加者ID
     * @param int $reserveId 予約ID
     */
    public function setRepresentative(int $id, int $reserveId) : ?Participant
    {
        /**
         * ①当該予約参加者の代表フラグを一旦全てOff
         * ↓
         * ②当該参加者の代表フラグをOn
         */

        // ①
        $this->participantRepository->updateWhere(
            ['reserve_id' => $reserveId],
            ['representative' => false]
        );

        // ②
        $this->participantRepository->updateField($id, ['representative' => true]);

        return $this->participantRepository->find($id);
    }

    /**
     * 参加者をキャンセルするためのパラメータ
     */
    private function getCancelParam()
    {
        return [
            'cancel' => true,
            // 'representative' => false, // 念の為、代表者フラグをOff。 → キャンセル時も特にoffにする必要もない気がするので一旦無効化
        ];
    }

    /**
     * 取り消し
     *
     * @param int $id 参加者ID
     */
    public function setCancel(int $id) : ?Participant
    {
        $this->participantRepository->updateField($id, $this->getCancelParam());

        return $this->participantRepository->find($id);
    }

    /**
     * 当該予約に紐づく参加者をキャンセル
     *
     * @param int $reserveId 予約ID
     */
    public function setCancelByReserveId(int $reserveId) : bool
    {
        $this->participantRepository->updateWhere(
            ['reserve_id' => $reserveId],
            $this->getCancelParam()
        );
        return true;
    }

    /**
     * 全取得
     *
     * @param int $reserveId
     * @param array $with
     * @param array $select
     * @param bool $getCanceller 取消者を取得する場合はtrue
     */
    public function getByReserveId(int $reserveId, array $with=[], $select=[], bool $getCanceller = false) : Collection
    {
        return $this->participantRepository->getByReserveId($reserveId, $with, $select, $getCanceller);
    }

    /**
     * IDリストにマッチするレコードを全取得
     *
     * @param array $ids
     * @param array $with
     * @param bool $getDeleted 論理削除データも含む場合はTrue
     */
    public function getByIds(array $ids, array $with=[], $select=[], $getDeleted = false) : Collection
    {
        return $this->participantRepository->getByIds($ids, $with, $select, $getDeleted);
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param bool $isSoftDelete 論理削除の場合はTrue
     */
    public function delete(int $id, bool $isSoftDelete = true): bool
    {
        // 論理削除の場合は念の為代表者フラグをOffにしておく
        if ($isSoftDelete) {
            $this->participantRepository->updateField($id, ['representative' => false]);
        }
        return $this->participantRepository->delete($id, $isSoftDelete);
    }
}
