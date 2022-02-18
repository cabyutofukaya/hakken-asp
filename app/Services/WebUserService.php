<?php

namespace App\Services;

use App\Models\WebUser;
use App\Repositories\WebUser\WebUserRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Traits\BirthdayTrait;
use Illuminate\Support\Arr;

class WebUserService
{
    use BirthdayTrait;
    
    public function __construct(
        WebUserRepository $webUserRepository
    ) {
        $this->webUserRepository = $webUserRepository;
    }

    /**
     * 1件取得
     */
    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : WebUser
    {
        return $this->webUserRepository->find($id, $with, $select, $getDeleted);
    }

    /**
     * 更新
     *
     * @param int $id ユーザーID
     * @param array $data 編集データ
     * @return WebUser
     */
    public function update(int $id, array $data) : WebUser
    {
        // 顧客番号、メールアドレスは更新不可
        $data = Arr::except($data, ['user_number','email']);

        return $this->webUserRepository->update($id, $data);
    }

    /**
     * フィールド更新
     */
    public function updateField(int $id, array $params) : bool
    {
        return $this->webUserRepository->updateField($id, $params);
    }

    /**
     * ページネーションで取得
     */
    public function paginate(array $params, int $limit, array $with=[], array $select = []) : LengthAwarePaginator
    {
        $params = collect(request()->query())->only(['user_number', 'name', 'name_kana', 'name_roman'])->toArray(); // 検索パラメータ

        return $this->webUserRepository->paginate($params, $limit, $with, $select);
    }

    /**
     * 定数データを取得
     */
    public function getStatusSelect(): array
    {
        $values = \Lang::get('values.web_users.status');
        foreach (config("consts.web_users.STATUS_LIST") as $key => $val) {
            $data[$val] = Arr::get($values, $key);
        }
        return $data;
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->webUserRepository->delete($id, $isSoftDelete);
    }

}
