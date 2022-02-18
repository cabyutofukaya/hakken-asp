<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\UserMileage;
use App\Repositories\UserMileage\UserMileageRepository;
use App\Services\UserMileageCustomValueService;
use App\Traits\HasManyGenTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Traits\UserCustomItemTrait;

class UserMileageService
{
    use HasManyGenTrait, UserCustomItemTrait;
    
    public function __construct(
        UserMileageRepository $userMileageRepository,
        UserMileageCustomValueService $userMileageCustomValueService
    ) {
        $this->userMileageRepository = $userMileageRepository;
        $this->userMileageCustomValueService = $userMileageCustomValueService;
    }

    public function find(int $id) : ?UserMileage
    {
        return $this->userMileageRepository->find($id);
    }

    /**
     * 当該ユーザーのマイレージ情報を全取得
     */
    public function allByUserId($userId, array $with = []): Collection
    {
        return $this->userMileageRepository->getWhere(['user_id' => $userId], $with);
    }

    /**
     * 作成
     * 
     * @param ?string $genKey 世代管理キー
     */
    public function create(array $data, ?string $genKey = null): UserMileage
    {
        $data['gen_key'] = is_null($genKey) ? $this->makeGenKey() : $genKey; // 世代管理キーをセット

        $userMileage = $this->userMileageRepository->create($data);

        // カスタム項目
        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->userMileageCustomValueService->upsertCustomFileds($customFields, $userMileage->id); // カスタムフィールド保存
        }

        return $userMileage;
    }

    /**
     * upsert
     */
    public function updateOrCreate(array $attributes, array $values = [], ?string $genKey = null) : UserMileage
    {
        $values['gen_key'] = is_null($genKey) ? $this->makeGenKey() : $genKey; // 世代管理キーをセット

        // upsert
        $userMileage = $this->userMileageRepository->updateOrCreate($attributes, $values);

        // カスタム項目
        $customFields = $this->customFieldsExtraction($values); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->userMileageCustomValueService->upsertCustomFileds($customFields, $userMileage->id); // カスタムフィールド保存
        }
        
        return $userMileage;
    }

    /**
     * 更新
     *
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function update(int $id, array $data): UserMileage
    {
        $userMileage = $this->userMileageRepository->find($id);
        if ($userMileage->updated_at != Arr::get($data, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        return $this->userMileageRepository->update($id, $data);
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->userMileageRepository->delete($id, $isSoftDelete);
    }
}
