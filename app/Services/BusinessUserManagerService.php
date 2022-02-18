<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\BusinessUserManager;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\BusinessUserManager\BusinessUserManagerRepository;
use App\Services\BusinessUserManagerSequenceService;
use App\Traits\ConstsTrait;
use App\Traits\HasManyGenTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class BusinessUserManagerService
{
    use ConstsTrait, HasManyGenTrait;

    public function __construct(
        BusinessUserManagerRepository $businessUserManagerRepository,
        BusinessUserManagerSequenceService $businessUserManagerSequenceService,
        AgencyRepository $agencyRepository
    ) {
        $this->businessUserManagerRepository = $businessUserManagerRepository;
        $this->businessUserManagerSequenceService = $businessUserManagerSequenceService;
        $this->agencyRepository = $agencyRepository;
    }

    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false) : ?BusinessUserManager
    {
        return $this->businessUserManagerRepository->find($id, $with, $select, $getDeleted);
    }

    /**
     * 顧客番号にマッチするレコードを1件取得
     */
    public function findByUserNumber($userNumber, $agencyAccount, array $with=[], array $select=[], bool $getDeleted = false) : ?BusinessUserManager
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->businessUserManagerRepository->findWhere(['user_number' => $userNumber, 'agency_id' => $agencyId], $with, $select, $getDeleted);
    }

    /**
     * 申込者検索（予約ページの参加者検索に使用）
     *
     * @param int $agencyId 会社ID
     * @param string $name 顧客名
     * @param string $userNumber 顧客番号
     * @param array $with
     * @param array $select
     * @param int $lmit 取得件数。全取得の場合はnull
     */
    public function applicantSearch(int $agencyId, ?string $name, ?string $userNumber, array $with=[], array $select = [], ?int $limit = null, bool $getDeleted = false) : Collection
    {
        return $this->businessUserManagerRepository->applicantSearch($agencyId, $name, $userNumber, $with, $select, $limit, $getDeleted);
    }

    /**
     * 当該ユーザーの取引先担当者情報を全取得
     */
    public function allByUserId($businessUserId, array $with = []): Collection
    {
        return $this->businessUserManagerRepository->getWhere(['business_user_id' => $businessUserId], $with);
    }

    /**
     * 作成
     */
    public function create(array $data, $genKey = null): BusinessUserManager
    {
        $data['gen_key'] = is_null($genKey) ? $this->makeGenKey() : $genKey;// // 世代管理キーをセット

        $data['user_number'] = $this->createUserNumber($data['agency_id']); // 顧客番号を生成

        return $this->businessUserManagerRepository->create($data);
    }

    /**
     * 更新
     *
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function update(int $id, array $data): BusinessUserManager
    {
        $businessUserManager = $this->businessUserManagerRepository->find($id);
        if ($businessUserManager->updated_at != Arr::get($data, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        return $this->businessUserManagerRepository->update($id, $data);
    }

    /**
     * update or insert
     */
    public function updateOrCreate(array $attributes, array $values = []) : BusinessUserManager
    {
        return $this->businessUserManagerRepository->updateOrCreate(
            $attributes,
            $values
        );
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->businessUserManagerRepository->delete($id, $isSoftDelete);
    }

    public function deleteExceptionGenKey(string $genKey, int $businessUserId, bool $isSoftDelete): bool
    {
        return $this->businessUserManagerRepository->deleteExceptionGenKey($genKey, $businessUserId, $isSoftDelete);
    }

    /**
     * selectメニュー用の名前配列
     *
     * @param int $agencyId 会社ID
     * @return array
     */
    public function getNameSelectByAgencyId(int $agencyId) : array
    {
        return $this->businessUserManagerRepository->allByAgencyId($agencyId, [], ['id','name'])->map(function ($manager, $key) {
            return [
                'id' => $manager->id,
                'name' => $manager->name
            ];
        })->pluck('name', 'id')->toArray();
    }

    /**
     * 担当者IDごとの部署名配列
     *
     * @param int $agencyId 会社ID
     * @return array
     */
    public function getDepartmentNameSelectByAgencyId(int $agencyId) : array
    {
        return $this->businessUserManagerRepository->allByAgencyId($agencyId, [], ['id','department_name'])->map(function ($manager, $key) {
            return [
                'id' => $manager->id,
                'department_name' => $manager->department_name
            ];
        })->pluck('department_name', 'id')->toArray();
    }

    /**
     * 管理番号を生成
     * 接頭辞に担当者を表す「M」を付ける
     *
     * フォーマット: P西暦下2桁 + 会社識別子 + - + 月 + 3桁連番 + アルファベット
     *
     * @param string $agencyId 会社ID
     * @return string
     */

    public function createUserNumber($agencyId) : string
    {
        $chars = range('A', 'Z');

        // 次の連番を取得
        $seqNumber = $this->businessUserManagerSequenceService->getNextNumber($agencyId, date('Y-m-d'));

        $ranges = array_chunk(range(1, $seqNumber), 999); // 1000で繰り上がり

        $range = count($ranges) - 1;

        $seq = array_search($seqNumber, $ranges[count($ranges)-1]) + 1;


        $agency = $this->agencyRepository->find($agencyId);


        return sprintf("M%02d%s-%02d%03d%s", date('y'), $agency->identifier, date('m'), $seq, $chars[$range]);
    }
}
