<?php

namespace App\Services;

use App\Models\Agency;
use App\Models\UserCustomItem;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\UserCustomCategory\UserCustomCategoryRepository;
use App\Repositories\UserCustomCategoryItem\UserCustomCategoryItemRepository;
use App\Repositories\UserCustomItem\UserCustomItemRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Lang;

class UserCustomItemService
{
    public function __construct(UserCustomItemRepository $userCustomItemRepository, UserCustomCategoryRepository $userCustomCategoryRepository, UserCustomCategoryItemRepository $userCustomCategoryItemRepository, AgencyRepository $agencyRepository)
    {
        $this->userCustomItemRepository = $userCustomItemRepository;
        $this->userCustomCategoryRepository = $userCustomCategoryRepository;
        $this->userCustomCategoryItemRepository = $userCustomCategoryItemRepository;
        $this->agencyRepository = $agencyRepository;
    }

    public function find(int $id): UserCustomItem
    {
        return $this->userCustomItemRepository->find($id);
    }

    public function getCategoriesForAgency(int $agencyId, $category) : Collection
    {
        return $this->userCustomItemRepository->getCategoriesForAgency($agencyId, $category);
    }

    /**
     * 作成
     */
    public function create(array $data): UserCustomItem
    {
        $data['key'] = get_uniqid(); // 項目キーを設定

        // リスト項目が重複するとよからぬ不具合を起こすかもしれないので重複削除しておく
        $data['list'] = array_values(array_unique(Arr::get($data, 'list', [])));
        return $this->userCustomItemRepository->create($data);
    }

    public function update(string $id, array $data): UserCustomItem
    {
        if (!Arr::get($data, 'list') || !is_array(Arr::get($data, 'list'))) {
            $data['list'] = [];
        }
        // リスト項目が重複するとよからぬ不具合を起こすかもしれないので重複削除しておく
        $data['list'] = array_values(array_unique($data['list']));


        return $this->userCustomItemRepository->update($id, $data);
    }

    /**
     * 当該カテゴリにおいて一番大きなturn値を取得
     */
    public function maxSeqForAgency(int $agencyId, $category) : int
    {
        return $this->userCustomItemRepository->maxSeqForAgency($agencyId, $category);
    }

    /**
     * 有効・無効フラグを切り替え
     */
    public function updateFlg(string $id, int $flg) : UserCustomItem
    {
        return $this->userCustomItemRepository->update($id, ['flg'=>$flg]);
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->userCustomItemRepository->delete($id, $isSoftDelete);
    }

    /**
     * 当該管理コードに紐づくレコードを一件取得
     *
     * @param int $agencyId 会社ID
     * @param string $code 管理コード
     * @return App\Models\UserCustomItem
     */
    public function findByCodeForAgency(int $agencyId, string $code, array $select = [], ?bool $flg = null): ?UserCustomItem
    {
        if (is_null($flg)) { // 表示フラグの検索指定ナシ
            return $this->userCustomItemRepository->findWhere(['agency_id' => $agencyId, 'code' => $code], $select);
        } else {
            return $this->userCustomItemRepository->findWhere(['agency_id' => $agencyId, 'code' => $code, 'flg' => $flg], $select);
        }
    }

    /**
     * 当該管理コードのキーを取得
     * 有効・無効に関係なく取得
     *
     * @param string $code 管理コード
     * @param int $agencyId 会社ID
     */
    public function getKeyByCodeForAgency(string $code, int $agencyId) : ?string
    {
        return $this->userCustomItemRepository->getKeyByCodeForAgency($code, $agencyId);
    }

    /**
     * 当該会社IDに紐づく「所属」カスタム情報を取得
     */
    public function findShozokuByAgencyId(int $agencyId, array $select = [], bool $flg = true) : ?UserCustomItem
    {
        return $this->userCustomItemRepository->findWhere(['agency_id' => $agencyId, 'code' => config('consts.user_custom_items.SHOZOKU'),'flg' => $flg], $select);
    }

    /**
     * （旅行会社ごとの）当該カテゴリコードに属するカスタム項目を取得
     *
     * @param string $code カテゴリコード
     * @param string $agencyAccount 会社アカウント
     * @param bool $flg 表示フラグ
     * @param array $where 検索パラメータ
     */
    public function getByCategoryCodeForAgencyAccount(string $code, string $agencyAccount, ?bool $flg = null, array $with = [], array $select = [], array $where = []) : Collection
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->userCustomItemRepository->getByCategoryCodeForAgencyId(
            $code, 
            (int)$agencyId, 
            $flg, 
            $with, 
            $select,
            $where
        );
    }

    /**
     * 初期データをセット
     *
     * @param Agency $agency
     */
    public function setDefaults(Agency $agency) : void
    {
        foreach ([
            ////////// 予約/見積 ////////////
            [
                'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_RESERVE'),
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
                'input_type' => null,
                'name' => '旅行種別',
                'code' => config('consts.user_custom_items.CODE_APPLICATION_TRAVEL_TYPE'),
                'position' => config('consts.user_custom_items.POSITION_APPLICATION_BASE_FIELD'),
                'seq' => 5,
                'fixed_item' => true,
                'undelete_item' => true,
                'unedit_item' => false,
                'list' => config('consts.user_custom_items.CODE_APPLICATION_TRAVEL_TYPE_DEFAULT_LIST'),
                'protect_list' => config('consts.user_custom_items.CODE_APPLICATION_TRAVEL_TYPE_DEFAULT_LIST_PROTECT'),
            ], // 旅行種別
            [
                'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_RESERVE'),
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
                'input_type' => null,
                'name' => '予約ステータス',
                'code' => config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS'),
                'position' => config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD'),
                'seq' => 10,
                'fixed_item' => true,
                'undelete_item' => true,
                'unedit_item' => true,
                'list' => config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS_DEFAULT_LIST'),
                'protect_list' => config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS_DEFAULT_LIST_PROTECT'),
            ], // 予約ステータス
            [
                'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_RESERVE'),
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
                'input_type' => null,
                'name' => '見積ステータス',
                'code' => config('consts.user_custom_items.CODE_APPLICATION_ESTIMATE_STATUS'),
                'position' => config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD'),
                'seq' => 15,
                'fixed_item' => true,
                'undelete_item' => true,
                'unedit_item' => true,
                'list' => config('consts.user_custom_items.CODE_APPLICATION_ESTIMATE_STATUS_DEFAULT_LIST'),
                'protect_list' => config('consts.user_custom_items.CODE_APPLICATION_ESTIMATE_STATUS_DEFAULT_LIST_PROTECT'),
            ], // 見積ステータス
            [
                'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_RESERVE'),
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
                'input_type' => null,
                'name' => '区分',
                'code' => config('consts.user_custom_items.CODE_APPLICATION_KBN'),
                'position' => config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD'),
                'seq' => 20,
                'fixed_item' => true,
                'undelete_item' => true,
                'unedit_item' => false,
                'list' => config('consts.user_custom_items.CODE_APPLICATION_KBN_DEFAULT_LIST'),
                'protect_list' => [],
            ], // 区分
            [
                'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_RESERVE'),
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
                'input_type' => null,
                'name' => '申込種別',
                'code' => config('consts.user_custom_items.CODE_APPLICATION_TYPE'),
                'position' => config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD'),
                'seq' => 25,
                'fixed_item' => true,
                'undelete_item' => true,
                'unedit_item' => false,
                'list' => config('consts.user_custom_items.CODE_APPLICATION_TYPE_DEFAULT_LIST'),
                'protect_list' => [],
            ], // 申込種別
            [
                'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_RESERVE'),
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
                'input_type' => null,
                'name' => '分類',
                'code' => config('consts.user_custom_items.CODE_APPLICATION_CLASS'),
                'position' => config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD'),
                'seq' => 30,
                'fixed_item' => true,
                'undelete_item' => true,
                'unedit_item' => false,
                'list' => config('consts.user_custom_items.CODE_APPLICATION_CLASS_DEFAULT_LIST'),
                'protect_list' => [],
            ], // 分類
            [
                'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_RESERVE'),
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_DATE'),
                'input_type' => config('consts.user_custom_items.INPUT_TYPE_DATE_01'),
                'name' => '申込日',
                'code' => config('consts.user_custom_items.CODE_APPLICATION_APPLICATION_DATE'),
                'position' => config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD'),
                'seq' => 35,
                'fixed_item' => true,
                'undelete_item' => true,
                'unedit_item' => true,
                'list' => [],
                'protect_list' => [],
            ], // 申込日
            [
                'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_RESERVE'),
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_DATE'),
                'input_type' => config('consts.user_custom_items.INPUT_TYPE_DATE_01'),
                'name' => '案内期限',
                'code' => config('consts.user_custom_items.CODE_APPLICATION_GUIDANCE_DEADLINE'),
                'position' => config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD'),
                'seq' => 40,
                'fixed_item' => true,
                'undelete_item' => true,
                'unedit_item' => true,
                'list' => [],
                'protect_list' => [],
            ], // 案内期限
            [
                'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_RESERVE'),
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_DATE'),
                'input_type' => config('consts.user_custom_items.INPUT_TYPE_DATE_01'),
                'name' => 'FNL日',
                'code' => config('consts.user_custom_items.CODE_APPLICATION_FNL_DATE'),
                'position' => config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD'),
                'seq' => 45,
                'fixed_item' => true,
                'undelete_item' => true,
                'unedit_item' => true,
                'list' => [],
                'protect_list' => [],
            ], // FNL日
            [
                'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_RESERVE'),
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_DATE'),
                'input_type' => config('consts.user_custom_items.INPUT_TYPE_DATE_01'),
                'name' => 'ticketlimit',
                'code' => config('consts.user_custom_items.CODE_APPLICATION_TICKETLIMIT'),
                'position' => config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD'),
                'seq' => 50,
                'fixed_item' => true,
                'undelete_item' => true,
                'unedit_item' => true,
                'list' => [],
                'protect_list' => [],
            ], // ticketlimit


            ////////// 個人顧客 ////////////
            [
                'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_PERSON'),
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
                'input_type' => null,
                'name' => '顧客区分',
                'code' => config('consts.user_custom_items.CODE_USER_CUSTOMER_KBN'),
                'position' => config('consts.user_custom_items.POSITION_PERSON_CUSTOM_FIELD'),
                'seq' => 5,
                'fixed_item' => true,
                'undelete_item' => true,
                'unedit_item' => false,
                'list' => [],
                'protect_list' => [],
            ], // 顧客区分
            [
                'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_PERSON'),
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
                'input_type' => null,
                'name' => 'ランク',
                'code' => config('consts.user_custom_items.CODE_USER_CUSTOMER_RANK'),
                'position' => config('consts.user_custom_items.POSITION_PERSON_CUSTOM_FIELD'),
                'seq' => 10,
                'fixed_item' => true,
                'undelete_item' => true,
                'unedit_item' => false,
                'list' => [],
                'protect_list' => [],
            ], // 顧客ランク
            [
                'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_PERSON'),
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
                'input_type' => null,
                'name' => '受付担当者',
                'code' => config('consts.user_custom_items.CODE_USER_CUSTOMER_RECEPTIONIST'),
                'position' => config('consts.user_custom_items.POSITION_PERSON_CUSTOM_FIELD'),
                'seq' => 15,
                'fixed_item' => true,
                'undelete_item' => true,
                'unedit_item' => false,
                'list' => [],
                'protect_list' => [],
            ], // 受付担当者
            [
                'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_PERSON'),
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
                'input_type' => null,
                'name' => '航空会社',
                'code' => config('consts.user_custom_items.CODE_USER_CUSTOMER_AIRPLANE_COMPANY'),
                'position' => config('consts.user_custom_items.POSITION_PERSON_MILEAGE_MODAL'),
                'seq' => 20,
                'fixed_item' => true,
                'undelete_item' => true,
                'unedit_item' => false,
                'list' => config('consts.user_custom_items.CODE_USER_CUSTOMER_AIRPLANE_COMPANY_DEFAULT_LIST'),
                'protect_list' => [],
            ], // マイレージ航空会社
    
            ////////// 法人顧客 ////////////
            [
                'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_BUSINESS'),
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
                'input_type' => null,
                'name' => '顧客区分',
                'code' => config('consts.user_custom_items.CODE_BUSINESS_CUSTOMER_KBN'),
                'position' => config('consts.user_custom_items.POSITION_BUSINESS_CUSTOM_FIELD'),
                'seq' => 5,
                'fixed_item' => true,
                'undelete_item' => true,
                'unedit_item' => false,
                'list' => [],
                'protect_list' => [],
            ], // 顧客区分
            [
                'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_BUSINESS'),
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
                'input_type' => null,
                'name' => 'ランク',
                'code' => config('consts.user_custom_items.CODE_BUSINESS_CUSTOMER_RANK'),
                'position' => config('consts.user_custom_items.POSITION_BUSINESS_CUSTOM_FIELD'),
                'seq' => 10,
                'fixed_item' => true,
                'undelete_item' => true,
                'unedit_item' => false,
                'list' => [],
                'protect_list' => [],
            ], // 顧客ランク

           ////////// ユーザー管理 ////////////
            [
                'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_USER'),
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
                'input_type' => null,
                'name' => '所属',
                'code' => config('consts.user_custom_items.CODE_STAFF_SHOZOKU'),
                'position' => config('consts.user_custom_items.POSITION_STAFF_BASE_FIELD'),
                'seq' => 5,
                'fixed_item' => true,
                'undelete_item' => true,
                'unedit_item' => false,
                'list' => [],
                'protect_list' => [],
            ], // 所属

           ////////// オプション科目 ////////////
            [
                'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_SUBJECT'),
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
                'input_type' => null,
                'name' => 'オプション区分',
                'code' => config('consts.user_custom_items.CODE_SUBJECT_OPTION_KBN'),
                'position' => config('consts.user_custom_items.POSITION_SUBJECT_OPTION'),
                'seq' => 5,
                'fixed_item' => true,
                'undelete_item' => true,
                'unedit_item' => false,
                'list' => config('consts.user_custom_items.CODE_SUBJECT_OPTION_KBN_DEFAULT_LIST'),
                'protect_list' => [],
            ], // 区分
           ////////// ホテル科目 ////////////
            [
            'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_SUBJECT'),
            'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
            'input_type' => null,
            'name' => 'ホテル区分',
            'code' => config('consts.user_custom_items.CODE_SUBJECT_HOTEL_KBN'),
            'position' => config('consts.user_custom_items.POSITION_SUBJECT_HOTEL'),
            'seq' => 5,
            'fixed_item' => true,
            'undelete_item' => true,
            'unedit_item' => false,
            'list' => config('consts.user_custom_items.CODE_SUBJECT_HOTEL_KBN_DEFAULT_LIST'),
            'protect_list' => [],
        ], // 区分
        [
            'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_SUBJECT'),
            'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
            'input_type' => null,
            'name' => '部屋タイプ',
            'code' => config('consts.user_custom_items.CODE_SUBJECT_HOTEL_ROOM_TYPE'),
            'position' => config('consts.user_custom_items.POSITION_SUBJECT_HOTEL'),
            'seq' => 10,
            'fixed_item' => true,
            'undelete_item' => true,
            'unedit_item' => false,
            'list' => config('consts.user_custom_items.CODE_SUBJECT_HOTEL_ROOM_TYPE_DEFAULT_LIST'),
            'protect_list' => [],
        ], // 部屋タイプ
        [
            'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_SUBJECT'),
            'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
            'input_type' => null,
            'name' => '食事タイプ',
            'code' => config('consts.user_custom_items.CODE_SUBJECT_HOTEL_MEAL_TYPE'),
            'position' => config('consts.user_custom_items.POSITION_SUBJECT_HOTEL'),
            'seq' => 15,
            'fixed_item' => true,
            'undelete_item' => true,
            'unedit_item' => false,
            'list' => config('consts.user_custom_items.CODE_SUBJECT_HOTEL_MEAL_TYPE_DEFAULT_LIST'),
            'protect_list' => [],
        ], // 食事タイプ
        ////////// 航空券科目 ////////////
        [
            'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_SUBJECT'),
            'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
            'input_type' => null,
            'name' => '航空会社',
            'code' => config('consts.user_custom_items.CODE_SUBJECT_AIRPLANE_COMPANY'),
            'position' => config('consts.user_custom_items.POSITION_SUBJECT_AIRPLANE'),
            'seq' => 5,
            'fixed_item' => true,
            'undelete_item' => true,
            'unedit_item' => false,
            'list' => config('consts.user_custom_items.CODE_SUBJECT_AIRPLANE_COMPANY_DEFAULT_LIST'),
            'protect_list' => [],
        ], // 航空会社

        ////////// 入手金管理 ////////////
        [
            'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_MANAGEMENT'),
            'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
            'input_type' => null,
            'name' => '出金方法',
            'code' => config('consts.user_custom_items.CODE_MANAGEMENT_WITHDRAWAL_METHOD'),
            'position' => config('consts.user_custom_items.POSITION_PAYMENT_MANAGEMENT'),
            'seq' => 5,
            'fixed_item' => true,
            'undelete_item' => true,
            'unedit_item' => false,
            'list' => config('consts.user_custom_items.CODE_MANAGEMENT_WITHDRAWAL_METHOD_DEFAULT_LIST'),
            'protect_list' => config('consts.user_custom_items.CODE_MANAGEMENT_WITHDRAWAL_METHOD_DEFAULT_LIST_PROTECT'),
        ], // 出金方法
        [
            'category_code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_MANAGEMENT'),
            'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
            'input_type' => null,
            'name' => '入金方法',
            'code' => config('consts.user_custom_items.CODE_MANAGEMENT_DEPOSIT_METHOD'),
            'position' => config('consts.user_custom_items.POSITION_INVOICE_MANAGEMENT'),
            'seq' => 10,
            'fixed_item' => true,
            'undelete_item' => true,
            'unedit_item' => false,
            'list' => config('consts.user_custom_items.CODE_MANAGEMENT_DEPOSIT_METHOD_DEFAULT_LIST'),
            'protect_list' => config('consts.user_custom_items.CODE_MANAGEMENT_DEPOSIT_METHOD_DEFAULT_LIST_PROTECT'),
        ], // 入金方法
        
        ] as $conf) {
            $userCustomCategory = $this->userCustomCategoryRepository->findByCode($conf['category_code']);

            $userCustomCategoryItem = $this->userCustomCategoryItemRepository->findWhere(['user_custom_category_id' => $userCustomCategory->id, 'type' => $conf['type']]);
        
            $this->create([
                'user_custom_category_id' => $userCustomCategory->id,
                'user_custom_category_item_id' => $userCustomCategoryItem->id,
                'type' => $userCustomCategoryItem->type,
                'input_type' => $conf['input_type'],
                'agency_id' => $agency->id,
                'name' => $conf['name'],
                'code' => $conf['code'],
                'display_position' => $conf['position'],
                'undelete_item' => $conf['undelete_item'],
                'unedit_item' => $conf['unedit_item'],
                'fixed_item' => $conf['fixed_item'],
                'seq' => $conf['seq'],
                'flg' => true,
                'list' => $conf['list'],
                'protect_list' => $conf['protect_list']
            ]);
        }
    }
}
