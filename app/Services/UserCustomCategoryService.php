<?php

namespace App\Services;

use App\Models\UserCustomCategory;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Repositories\UserCustomCategory\UserCustomCategoryRepository;

class UserCustomCategoryService
{
    public function __construct(UserCustomCategoryRepository $userCustomCategoryRepository)
    {
        $this->userCustomCategoryRepository = $userCustomCategoryRepository;
    }

    public function find(int $id): UserCustomCategory
    {
        return $this->userCustomCategoryRepository->find($id);
    }

    public function all(array $with, string $sort='seq', string $direction='asc'): Collection
    {
        return $this->userCustomCategoryRepository->all($with, $sort, $direction);
    }

    /**
     * 当該タイプを有するカテゴリ一覧を取得
     */
    public function getNameListForCategoryItemType($type) : array
    {
        return $this->userCustomCategoryRepository->getListForCategoryItemType($type)->pluck('name', 'id')->toArray();
    }

    /**
     * 当該タイプを有するカテゴリのposition項目ラベル一覧を取得
     */
    public function getPositionLabelListForCategoryItemType(string $type) : array
    {
        return $this->userCustomCategoryRepository->getListForCategoryItemType($type)->map(function($row, $key){
            return [
                'id' => $row->id,
                'label' => $row->code === config('consts.user_custom_categories.CUSTOM_CATEGORY_SUBJECT') ? '設定科目' : '設置個所', // 科目マスタはpositionラベルが「設定科目」、それ以外は「設置個所」
            ];
        })->pluck('label', 'id')->toArray();
    }

    /**
     * 当該コードからIDを取得
     * 
     * @param string $code 管理コード
     * @return int
     */
    public function getIdByCode($code) : ?int
    {
        $result = $this->userCustomCategoryRepository->findByCode($code);
        return $result ? $result->id : null;
    }

    /**
     * 当該IDからコードを取得
     * 
     * @param int $id ID
     * @return string
     */
    public function getCodeById(int $id) : string
    {
        $result = $this->userCustomCategoryRepository->find($id);
        return $result->code;
    }


    /**
     * 当該コードからレコードを取得
     * 
     * @param string $code 管理コード
     * @return int
     */
    public function findByCode($code) : ?UserCustomCategory
    {
        return $this->userCustomCategoryRepository->findByCode($code);
    }
}
