<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\UserVisa;
use App\Repositories\UserVisa\UserVisaRepository;
use App\Traits\HasManyGenTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Traits\UserCustomItemTrait;

class UserVisaService
{
    use HasManyGenTrait, UserCustomItemTrait;

    public function __construct(
        UserVisaRepository $userVisaRepository
        )
    {
        $this->userVisaRepository = $userVisaRepository;
    }

    public function find(int $id) : ?UserVisa
    {
        return $this->userVisaRepository->find($id);
    }

    /**
     * 当該ユーザーのビザ情報を全取得
     */
    public function allByUserId($userId, array $with = []): Collection
    {
        return $this->userVisaRepository->getWhere(['user_id' => $userId], $with);
    }

    /**
     * 作成
     */
    public function create(array $data, ?string $genKey = null): UserVisa
    {
        $data['gen_key'] = is_null($genKey) ? $this->makeGenKey() : $genKey; // 世代管理キーをセット

        $userVisa = $this->userVisaRepository->create($data);

        // // TODO 以下はuser_visas用のカスタム項目テーブルを作った際に使用
        // // カスタム項目
        // $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        // if ($customFields) {
        //     $this->userVisaCustomValueService->upsertCustomFileds($customFields, $userVisa->id); // カスタムフィールド保存
        // }
        
        return $userVisa;
    }

    /**
     * upsert
     */
    public function updateOrCreate(array $attributes, array $values = [], ?string $genKey = null) : UserVisa
    {
        $values['gen_key'] = is_null($genKey) ? $this->makeGenKey() : $genKey; // 世代管理キーをセット

        $userVisa = $this->userVisaRepository->updateOrCreate($attributes, $values);

        // // TODO 以下はuser_visas用のカスタム項目テーブルを作った際に使用
        // // カスタム項目
        // $customFields = $this->customFieldsExtraction($values); // 入力データからカスタムフィールドを抽出
        // if ($customFields) {
        //     $this->userVisaCustomValueService->upsertCustomFileds($customFields, $userVisa->id); // カスタムフィールド保存
        // }

        return $userVisa;

    }

    /**
     * 更新
     */
    public function update(int $id, array $data): UserVisa
    {
        $userVisa = $this->userVisaRepository->find($id);
        if ($userVisa->updated_at != Arr::get($data, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        return $this->userVisaRepository->update($id, $data);
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->userVisaRepository->delete($id, $isSoftDelete);
    }

}
