<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UserCustomItemDestroyRequest;
use App\Http\Requests\Staff\UserCustomItemStoreDateRequest;
use App\Http\Requests\Staff\UserCustomItemStoreListRequest;
use App\Http\Requests\Staff\UserCustomItemStoreTextRequest;
use App\Http\Requests\Staff\UserCustomItemUpdateDateRequest;
use App\Http\Requests\Staff\UserCustomItemUpdateListRequest;
use App\Http\Requests\Staff\UserCustomItemUpdateTextRequest;
use App\Models\UserCustomItem;
use App\Services\UserCustomCategoryItemService;
use App\Services\UserCustomItemService;
use DB;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * カスタム項目
 */
class UserCustomItemController extends AppController
{
    public function __construct(UserCustomItemService $userCustomItemService, UserCustomCategoryItemService $userCustomCategoryItemService)
    {
        $this->userCustomItemService = $userCustomItemService;
        $this->userCustomCategoryItemService = $userCustomCategoryItemService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', [new UserCustomItem]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.user_custom_item.index');
    }


    //////// create

    /**
     * カスタム項目作成フォーム（テキスト項目）
     *
     * @param string $agencyAccount 会社アカウント
     */
    public function createText($agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new UserCustomItem]);
        if (!$response->allowed()) {
            abort(403);
        }

        $defaultUserCustomCategoryId = request()->get("default_category");

        if ($userCustomCategoryItem = $this->userCustomCategoryItemService->findWhere(['user_custom_category_id' => $defaultUserCustomCategoryId, 'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_TEXT')])) {
            return view('staff.user_custom_item.create.text', compact('defaultUserCustomCategoryId'));
        }
        abort(404);
    }

    /**
     * カスタム項目のリスト項目作成フォーム
     */
    public function createList($agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new UserCustomItem]);
        if (!$response->allowed()) {
            abort(403);
        }

        $defaultUserCustomCategoryId = request()->get("default_category");

        if ($userCustomCategoryItem = $this->userCustomCategoryItemService->findWhere(['user_custom_category_id' => $defaultUserCustomCategoryId, 'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST')])) {
            return view('staff.user_custom_item.create.list', compact('defaultUserCustomCategoryId'));
        }
        abort(404);
    }

    /**
     * カスタム項目作成フォーム（日時項目）
     *
     * @param string $agencyAccount 会社アカウント
     */
    public function createDate($agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new UserCustomItem]);
        if (!$response->allowed()) {
            abort(403);
        }
        $defaultUserCustomCategoryId = request()->get("default_category");

        if ($userCustomCategoryItem = $this->userCustomCategoryItemService->findWhere(['user_custom_category_id' => $defaultUserCustomCategoryId, 'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_DATE')])) {
            return view('staff.user_custom_item.create.date', compact('defaultUserCustomCategoryId'));
        }
        abort(404);
    }

    //////// store

    /**
     * カスタム項目作成（テキスト項目タイプ）
     */
    public function storeText(UserCustomItemStoreTextRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('store', new UserCustomItem);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        try {
            $input = $request->validated();
            $input['type'] = config("consts.user_custom_items.CUSTOM_ITEM_TYPE_TEXT");
    
            if ($userCustomCategoryItem = $this->userCustomCategoryItemService->findWhere(['user_custom_category_id' => $input['user_custom_category_id'], 'type' => $input['type']])) { // user_custom_category_id と type の組み合わせでユニーク

                $input['agency_id'] = auth('staff')->user()->agency->id;
                $input['user_custom_category_item_id'] = $userCustomCategoryItem->id;
                
                $userCustomItem = DB::transaction(function () use ($input) {
                    $input['seq'] = $this->userCustomItemService->maxSeqForAgency($input['agency_id'], $input['user_custom_category_item_id']) + 1; // 次のseq値をセット
        
                    return $this->userCustomItemService->create($input, $input['agency_id']);
                });
                
                if ($userCustomItem) {
                    return redirect()->route('staff.system.custom.index', ['agencyAccount'=>$agencyAccount, 'tab'=>$userCustomItem->user_custom_category->code])->with('success_message', "カスタム項目「{$userCustomItem->name}」を登録しました"); // tabは一覧ページでデフォルトでopen状態にするカテゴリを指定
                }
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }

        abort(500);
    }

    /**
     * カスタム項目作成（リスト項目タイプ）
     */
    public function storeList(UserCustomItemStoreListRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('store', new UserCustomItem);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }
        
        try {
            $input = $request->validated();
            $input['type'] = config("consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST");
    
            if ($userCustomCategoryItem = $this->userCustomCategoryItemService->findWhere(['user_custom_category_id' => $input['user_custom_category_id'], 'type' => $input['type']])) { // user_custom_category_id と type の組み合わせでユニーク

                $input['agency_id'] = auth('staff')->user()->agency->id;
                $input['user_custom_category_item_id'] = $userCustomCategoryItem->id;

                $userCustomItem = DB::transaction(function () use ($input) {
                    $input['seq'] = $this->userCustomItemService->maxSeqForAgency($input['agency_id'], $input['user_custom_category_item_id']) + 1; // 次のseq値をセット

                    $userCustomItem =  $this->userCustomItemService->create($input, $input['agency_id']);

                    return $userCustomItem;
                });
                
                if ($userCustomItem) {
                    return redirect()->route('staff.system.custom.index', ['agencyAccount'=>$agencyAccount, 'tab'=>$userCustomItem->user_custom_category->code])->with('success_message', "カスタム項目「{$userCustomItem->name}」を登録しました"); // tabは一覧ページでデフォルトでopen状態にするカテゴリを指定
                }
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }

        abort(500);
    }

    /**
     * カスタム項目作成（日時項目タイプ）
     */
    public function storeDate(UserCustomItemStoreDateRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('store', new UserCustomItem);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        try {
            $input = $request->validated();
            $input['type'] = config("consts.user_custom_items.CUSTOM_ITEM_TYPE_DATE");

            if ($userCustomCategoryItem = $this->userCustomCategoryItemService->findWhere(['user_custom_category_id' => $input['user_custom_category_id'], 'type' => $input['type']])) { // user_custom_category_id と type の組み合わせでユニーク

                $input['agency_id'] = auth('staff')->user()->agency->id;
                $input['user_custom_category_item_id'] = $userCustomCategoryItem->id;
                
                $userCustomItem = DB::transaction(function () use ($input) {
                    $input['seq'] = $this->userCustomItemService->maxSeqForAgency($input['agency_id'], $input['user_custom_category_item_id']) + 1; // 次のseq値をセット
        
                    return $this->userCustomItemService->create($input, $input['agency_id']);
                });
                
                if ($userCustomItem) {
                    return redirect()->route('staff.system.custom.index', ['agencyAccount'=>$agencyAccount, 'tab'=>$userCustomItem->user_custom_category->code])->with('success_message', "カスタム項目「{$userCustomItem->name}」を登録しました"); // tabは一覧ページでデフォルトでopen状態にするカテゴリを指定
                }
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }

        abort(500);
    }

    //////// edit

    /**
     * 編集（テキスト項目）
     */
    public function editText($agencyAccount, $userCustomItemId)
    {
        $userCustomItem = $this->userCustomItemService->find($userCustomItemId);

        // 認可チェック
        $response = Gate::inspect('view', [$userCustomItem]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.user_custom_item.edit.text', compact('userCustomItem'));
    }

    /**
     * 編集（リスト項目）
     */
    public function editList($agencyAccount, $userCustomItemId)
    {
        $userCustomItem = $this->userCustomItemService->find($userCustomItemId);

        // 認可チェック
        $response = Gate::inspect('view', [$userCustomItem]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.user_custom_item.edit.list', compact('userCustomItem'));
    }

    /**
     * 編集（日時項目）
     */
    public function editDate($agencyAccount, $userCustomItemId)
    {
        $userCustomItem = $this->userCustomItemService->find($userCustomItemId);

        // 認可チェック
        $response = Gate::inspect('view', [$userCustomItem]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.user_custom_item.edit.date', compact('userCustomItem'));
    }

    //////// update

    private function _commonUpdate($request, $agencyAccount, $userCustomItemId)
    {
        $oldObj = $this->userCustomItemService->find($userCustomItemId);
        // 認可チェック
        $response = Gate::inspect('update', [$oldObj]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();
        $input['list'] = Arr::get($input, "list", null); // listパラメータが無い場合は既存の値を削除できるようにnullで初期化

        $newObj = $this->userCustomItemService->update($userCustomItemId, $input);
        if ($newObj) {
            return redirect()->route('staff.system.custom.index', ['agencyAccount'=>$agencyAccount, 'tab'=>$newObj->user_custom_category->code])->with('success_message', "カスタム項目「{$newObj->name}」を更新しました"); // tabは一覧ページでデフォルトでopen状態にするカテゴリを指定
        }
        abort(500);
    }

    /**
     * カスタム項目更新（テキスト項目タイプ）
     */
    public function updateText(UserCustomItemUpdateTextRequest $request, $agencyAccount, $userCustomItemId)
    {
        return $this->_commonUpdate($request, $agencyAccount, $userCustomItemId);
    }

    /**
     * カスタム項目更新（リストタイプ）
     */
    public function updateList(UserCustomItemUpdateListRequest $request, $agencyAccount, $userCustomItemId)
    {
        return $this->_commonUpdate($request, $agencyAccount, $userCustomItemId);
    }

    /**
     * カスタム項目更新（日時タイプ）
     */
    public function updateDate(UserCustomItemUpdateDateRequest $request, $agencyAccount, $userCustomItemId)
    {
        return $this->_commonUpdate($request, $agencyAccount, $userCustomItemId);
    }

    //////// destroy

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserCustomItemDestroyRequest $request, string $agencyAccount, int $userCustomItemId)
    {
        $userCustomItem = $this->userCustomItemService->find($userCustomItemId);
        // 認可チェック
        $response = Gate::inspect('forceDelete', [$userCustomItem]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }
        
        $this->userCustomItemService->delete($userCustomItemId, false); // 完全削除

        return redirect()->route('staff.system.custom.index', ['agencyAccount' => $agencyAccount, 'tab' => $userCustomItem->user_custom_category->code])->with('decline_message', "カスタム項目「{$userCustomItem->name}」を削除しました"); // tabは一覧ページでデフォルトでopen状態にするカテゴリを指定
    }
}
