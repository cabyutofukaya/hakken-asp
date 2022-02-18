<?php

namespace App\Traits;

use Illuminate\Support\Arr;
use Lang;

/**
 * カスタム項目の保存を制御するtrait
 */
trait UserCustomItemTrait
{
    /**
     * カスタム項目のデータ一覧を取得し、かつformの初期値をセット
     * createフォーム用
     */
    public function getUserCustomItemsAndSetCustomFieldDefaultCreateInput(&$defaultValue, $userCustomItemService, $agencyAccount, $customCategoryCode, array $select = [], array $where = [], array $notWhere = [])
    {
        $userCustomItems = $userCustomItemService->getByCategoryCodeForAgencyAccount(
            $customCategoryCode,
            $agencyAccount,
            true,
            [],
            [
                'user_custom_items.id',
                'user_custom_items.code',
                'user_custom_items.key',
                'user_custom_items.type',
                'user_custom_items.name',
                'user_custom_items.input_type',
                'user_custom_items.list',
                'user_custom_items.display_position',
                'user_custom_items.unedit_item',
            ], // 取得カラムを指定。joinするので対象レコードを明示的に指定
            $where,
            $notWhere
        );

        // カスタム項目フィールドに初期値をセット
        foreach ($userCustomItems as $uci) {
            $val = '';
            // ↓リスト形式は、基本的に空optionを先頭につけるようにしているので以下の処理は一旦外し
            // if ($uci->type === config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST')) { // リスト形式でold値がない場合はリスト先頭を初期値に
            //     if (is_empty(old($uci->key)) && is_array($uci->list) && $uci->list) {
            //         $val = $uci->list[0];
            //     }
            // }
            $defaultValue[$uci->key] = old($uci->key, $val); // 値をセット
        }

        return $userCustomItems;
    }

    /**
     * 入力データからカスタムフィールドを抽出
     *
     * @param array $data 配列
     * @return array
     */
    public function customFieldsExtraction(array $data)
    {
        $extractions = collect($data)->filter(function ($value, $key) {
            return strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0;
        });

        return $extractions->all();
    }
}
