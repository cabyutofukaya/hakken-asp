<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\Staff;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\Staff\StaffRepository;
use App\Services\StaffCustomValueService;
use App\Traits\ConstsTrait;
use App\Traits\UserCustomItemTrait;
use Hash;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Lang;
use Vinkla\Hashids\Facades\Hashids;

class StaffService
{
    use ConstsTrait, UserCustomItemTrait;

    public function __construct(StaffRepository $staffRepository, StaffCustomValueService $staffCustomValueService, AgencyRepository $agencyRepository)
    {
        $this->staffRepository = $staffRepository;
        $this->staffCustomValueService = $staffCustomValueService;
        $this->agencyRepository = $agencyRepository;
    }

    /**
     * 該当IDを一件取得
     *
     * @param int $agencyId 会社ID
     * @param int $staffId スタッフID
     * @param bool $getDeleted 論理削除を含めるか
     */
    public function find(int $staffId, array $with = [], array $select=[], bool $getDeleted = false)
    {
        return $this->staffRepository->find($staffId, $with, $select, $getDeleted);
    }

    /**
     * スタッフ情報を一件取得
     *
     * @param int $agencyId 会社ID
     * @param string $account アカウント
     * @return App\Models\Staff
     */
    public function findByAccount(int $agencyId, string $account, array $with=[], array $select=[]) : ?Staff
    {
        $staff = $this->staffRepository->findWhere(['agency_id' => $agencyId, 'account' => $account], [], []);

        if (!$staff) {
            return null;
        }

        return $this->staffRepository->find($staff->id, $with, $select);
    }

    public function findWhere(array $where) : ?Staff
    {
        $this->staffRepository->findWhere($where);
    }

    /**
     * スタッフ一覧を取得（ID用）
     *
     * @param int $agencyId
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with=[], $select = []) : LengthAwarePaginator
    {
        return $this->staffRepository->paginateByAgencyId($agencyId, $params, $limit, $with, $select);
    }

    /**
     * スタッフ一覧を取得（アカウント用）
     *
     * @param string $account
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $account, array $params, int $limit, array $with=[], $select=[]) : LengthAwarePaginator
    {
        $agencyId =  $this->agencyRepository->getIdByAccount($account);
        return $this->staffRepository->paginateByAgencyId($agencyId, $params, $limit, $with, $select);
    }

    public function create(int $agencyId, array $data)
    {
        $data['agency_id'] = $agencyId;
        $data['password'] = Hash::make($data['password']);//パスワードをハッシュ化

        $staff = $this->staffRepository->create($data);

        // カスタム項目保存
        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->staffCustomValueService->upsertCustomFileds($customFields, $staff->id);
        }

        return $staff;
    }


    /**
     * 更新
     *
     * @param int $agencyId 会社ID
     * @param int $staffId スタッフID
     * @param array $data 編集データ
     * @return User
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function update(int $agencyId, int $staffId, array $data) : Staff
    {
        $staff = $this->staffRepository->find($staffId);
        if (Arr::get($data, 'updated_at') && $staff->updated_at != Arr::get($data, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        if ($staff->master) { // スーパーマスターの場合は念のため権限設定を除いておく
            $data = Arr::except($data, ['agency_role_id']);
        }
        
        $data = Arr::except($data, ['account']); // アカウントは更新不可
        $data['agency_id'] = $agencyId;
        
        if ($password = Arr::get($data, 'password')) {//パスワードが入力されていたら更新
            $data['password'] = Hash::make($password);
        } else {
            $data = Arr::except($data, ['password']);
        }
        
        // カスタム項目更新
        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->staffCustomValueService->upsertCustomFileds($customFields, $staff->id);
        }

        return $this->staffRepository->update($staffId, $data);
    }

    public function updateFields(int $staffId, array $params) : bool
    {
        return $this->staffRepository->updateFields($staffId, $params);
    }
    
    /**
     * 当該アカウントがマスター管理者ならばTrue
     *
     * @param int $agencyId 会社ID
     * @param string $account スタッフアカウント
     * @return boolean
     */
    public function isMasterAccount(int $agencyId, string $account) : bool
    {
        $staff = $this->staffRepository->findWhere(['agency_id' => $agencyId, 'account' => $account], [], ['master']);
        return $staff && $staff->master;
    }

    /**
     * 当該IDがマスター管理者ならばTrue
     *
     * @param int $id スタッフID
     * @return boolean
     */
    public function isMasterId(int $id) : bool
    {
        $staff = $this->staffRepository->find($id);
        return $staff && $staff->master;
    }

    public function delete(int $agencyId, int $staffId): int
    {
        $staff = $this->staffRepository->find($staffId);
        if ($staff->agency_id === $agencyId) { // 一応、会社IDとスタッフIDの組み合わせが正しいかチェック
            return $this->staffRepository->delete($staffId);
        }
        return 0;
    }

    /**
     * スタッフ数を取得（代理店ごと）
     */
    public function countByAgencyId(int $agencyId)
    {
        return $this->staffRepository->countByAgencyId($agencyId);
    }

    /**
     * 任意の権限IDを持つスタッフ数を取得
     *
     * @param int $agencyRoleId 権限ID
     * @return int
     */
    public function getCountByAgencyRoleId(int $agencyRoleId) : int
    {
        return $this->staffRepository->getCountByAgencyRoleId($agencyRoleId);
    }

    /**
     * 有効プラン数を更新
     *
     * @param int $id スタッフID
     * @param int $number プラン数
     * @param bool $incDeleted 更新スタッフに論理削除も含める場合はtrue
     */
    public function updateNumberOfPlan(
        int $id,
        int $number,
        bool $incDeleted = true
    ) : bool {
        return $this->staffRepository->updateWhere(
            ['id' => $id],
            ['number_of_plan' => $number],
            $incDeleted
        );
    }

    /**
     * 当該アカウントが登録済みか
     */
    public function isAccountExists($agencyId, $account) : bool
    {
        return $this->staffRepository->getWhere(['agency_id' => $agencyId, 'account' => $account])->count() > 0;
    }

    /**
     * 定数データを取得
     */
    public function getStatuses(): array
    {
        $values = Lang::get('values.staffs.status');
        foreach (config("consts.staffs.STATUS_LIST") as $key => $val) {
            $data[$val] = Arr::get($values, $key);
        }
        return $data;
    }

    /**
     * selectメニュー用の名前配列
     * 「ID => 名前」形式の配列
     *
     * @param string $agencyAccount 会社アカウント
     * @param bool $getDeleted 論理削除も取得する場合はtrue
     * @return array
     */
    public function getIdNameSelect(string $agencyAccount, bool $getDeleted = false) : array
    {
        $agencyId =  $this->agencyRepository->getIdByAccount($agencyAccount);

        return $this->staffRepository
            ->getWhere(['agency_id' => $agencyId], ['id', 'name', 'status', 'deleted_at'], $getDeleted)
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * getIdNameSelectをベースに
     * 任意の値が論理削除されてしまった場合でも指定同値は論理削除も取得してリストアップ
     *
     * @param array $values スタッフIDリスト
     */
    public function getIdNameSelectSafeValues(int $agencyId, array $values) : array
    {
        $result = $this->staffRepository->getWhere(['agency_id' => $agencyId], ['id','name','status','deleted_at'], true);
        // 論理削除済みで$valuesのIDでないものは削除
        $filtered = $result->reject(function ($row, $key) use ($values) {
            return $row->trashed() && !in_array($row->id, $values);
        });

        return $filtered->pluck('name', 'id')->toArray();
    }
}
