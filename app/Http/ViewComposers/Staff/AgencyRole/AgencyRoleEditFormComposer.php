<?php
namespace App\Http\ViewComposers\Staff\AgencyRole;

use Illuminate\Support\Arr;
use Illuminate\View\View;
use App\Services\StaffService;
use Lang;

/**
 * ユーザー権限編集フォームに使う選択項目などを提供するViewComposer
 */
class AgencyRoleEditFormComposer
{
    public function __construct(StaffService $staffService)
    {
        $this->staffService = $staffService;
    }
    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $agencyRole = Arr::get($data, 'agencyRole', null);

        //////////////////////////////////

        $formSelects = array();
        $formSelects['roleItems'] = array();

        $targets = Lang::get('values.agency_roles.targets');
        $actions = Lang::get('values.agency_roles.actions');

        // 対象によっては未実装の機能もあるが、とりあえず全てのアクションを対象にリストを作る
        foreach (config("consts.agency_roles.TARGETS_LIST") as $targetKey => $targetVal) {
            $row = array();
            $row['target'] = $targetVal;
            $row['label'] = Arr::get($targets, $targetKey);

            foreach (config("consts.agency_roles.ACTIONS_LIST") as $actionKey => $actionVal) {
                $item = array();
                $item['action'] = $actionVal;
                $item['label'] = Arr::get($actions, $actionKey);

                $row['items'][] = $item;
            }

            $formSelects['roleItems'][] = $row;
        }

        $formSelects['statuses'] = get_const_item('staffs', 'status');

        $view->with(compact('formSelects', 'agencyRole'));
    }
}
