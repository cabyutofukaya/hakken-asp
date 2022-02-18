<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\UserMemberCard;
use App\Repositories\UserMemberCard\UserMemberCardRepository;
use App\Traits\HasManyGenTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Traits\UserCustomItemTrait;

class UserMemberCardService
{
    use HasManyGenTrait, UserCustomItemTrait;
    
    public function __construct(
        UserMemberCardRepository $userMemberCardRepository
        )
    {
        $this->userMemberCardRepository = $userMemberCardRepository;
    }

    public function find(int $id) : ?UserMemberCard
    {
        return $this->userMemberCardRepository->find($id);
    }

    /**
     * 当該ユーザーのメンバーカード情報を全取得
     */
    public function allByUserId($userId, array $with = []): Collection
    {
        return $this->userMemberCardRepository->getWhere(['user_id' => $userId], $with);
    }

    /**
     * 作成
     */
    public function create(array $data, ?string $genKey = null): UserMemberCard
    {
        $data['gen_key'] = is_null($genKey) ? $this->makeGenKey() : $genKey; // 世代管理キーをセット

        $userMemberCard = $this->userMemberCardRepository->create($data);

        // // TODO 以下はuser_member_cards用のカスタム項目テーブルを作った際に使用
        // // カスタム項目
        // $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        // if ($customFields) {
        //     $this->userMemberCardCustomValueService->upsertCustomFileds($customFields, $userMemberCard->id); // カスタムフィールド保存
        // }
        
        return $userMemberCard;
    }

    /**
     * upsert
     */
    public function updateOrCreate(array $attributes, array $values = [], ?string $genKey = null) : UserMemberCard
    {
        $values['gen_key'] = is_null($genKey) ? $this->makeGenKey() : $genKey; // 世代管理キーをセット

        $userMemberCard = $this->userMemberCardRepository->updateOrCreate($attributes, $values);

        // // TODO 以下はuser_member_cards用のカスタム項目テーブルを作った際に使用
        // // カスタム項目
        // $customFields = $this->customFieldsExtraction($values); // 入力データからカスタムフィールドを抽出
        // if ($customFields) {
        //     $this->userMemberCardCustomValueService->upsertCustomFileds($customFields, $userMemberCard->id); // カスタムフィールド保存
        // }
        
        return $userMemberCard;
    }

    /**
     * 更新
     * 
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function update(int $id, array $data): UserMemberCard
    {
        $userMemberCard = $this->userMemberCardRepository->find($id);
        if ($userMemberCard->updated_at != Arr::get($data, 'updated_at')) {
            throw new ExclusiveLockException;
        }
        return $this->userMemberCardRepository->update($id, $data);
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->userMemberCardRepository->delete($id, $isSoftDelete);
    }

}
