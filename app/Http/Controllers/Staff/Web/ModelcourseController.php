<?php

namespace App\Http\Controllers\Staff\Web;

use App\Events\WebModelcourseChangeEvent;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Staff\AppController;
use App\Http\Requests\Staff\WebModelcourseStoreRequest;
use App\Http\Requests\Staff\WebModelcourseUpdateRequest;
use App\Services\WebModelcourseService;
use App\Models\WebModelcourse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ModelcourseController extends AppController
{
    public function __construct(WebModelcourseService $webModelcourseService)
    {
        $this->webModelcourseService = $webModelcourseService;
    }

    /**
     * 一覧ページ
     */
    public function index(string $agencyAccount)
    {
        // 認可チェック
        $response = \Gate::inspect('viewAny', new WebModelcourse);
        if (!$response->allowed()) {
            abort(403);
        }

        return view("staff.web.modelcourse.index");
    }

    /**
     * 詳細ページ
     */
    public function show(string $agencyAccount, string $courseNo)
    {
        $webModelcourse = $this->webModelcourseService->findByCourseNo($courseNo, auth('staff')->user()->agency_id);

        if (!$webModelcourse) {
            abort(404);
        }

        // 認可チェック
        $response = \Gate::inspect('view', $webModelcourse);
        if (!$response->allowed()) {
            abort(403);
        }

        return view("staff.web.modelcourse.show", compact('webModelcourse', 'courseNo'));
    }

    /**
     * 作成ページ
     */
    public function create(string $agencyAccount)
    {
        // 認可チェック
        $response = \Gate::inspect('create', new WebModelcourse);
        if (!$response->allowed()) {
            abort(403);
        }

        return view("staff.web.modelcourse.create");
    }

    /**
     * 作成処理
     */
    public function store(WebModelcourseStoreRequest $request, string $agencyAccount)
    {
        $response = \Gate::inspect('create', new WebModelcourse);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();
        $input['agency_id'] = auth('staff')->user()->agency_id;
        $input['show'] = true; // 表示フラグはONで初期化

        try {

            // アップロード画像保存準備
            $this->uploadImageReadySave($input);

            $input = collect($input)->except(['upload_web_modelcourse_photo'])->toArray(); // upload_ フィールドは不要なので一応削除

            $result = \DB::transaction(function () use ($input) {
                $result = $this->webModelcourseService->upsert(['id' => null], $input);

                event(new WebModelcourseChangeEvent(null, $result)); // コース作成・更新イベント

                return $result;
            });

            if ($result) {
                return redirect()->route('staff.front.modelcourse.index', [$agencyAccount])->with('success_message', "モデルコース「{$result->course_no}」を作成しました");
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * 編集ページ
     */
    public function edit(string $agencyAccount, string $courseNo)
    {
        $webModelcourse = $this->webModelcourseService->findByCourseNo($courseNo, auth('staff')->user()->agency_id);

        if (!$webModelcourse) {
            abort(404);
        }

        // 認可チェック
        $response = \Gate::inspect('view', $webModelcourse);
        if (!$response->allowed()) {
            abort(403);
        }

        return view("staff.web.modelcourse.edit", compact('webModelcourse', 'courseNo'));
    }

    /**
     * 更新処理
     */
    public function update(WebModelcourseUpdateRequest $request, string $agencyAccount, string $courseNo)
    {
        $webModelcourse = $this->webModelcourseService->findByCourseNo($courseNo, auth('staff')->user()->agency_id);

        // 認可チェック
        $response = \Gate::inspect('update', $webModelcourse);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();
        try {

            // アップロード画像保存準備
            $this->uploadImageReadySave($input);

            $input = collect($input)->except(['upload_web_modelcourse_photo'])->toArray(); // upload_ フィールドは不要なので一応削除

            $result = \DB::transaction(function () use ($webModelcourse, $input) {
                $result = $this->webModelcourseService->upsert(['id' => $webModelcourse->id], $input);

                event(new WebModelcourseChangeEvent($webModelcourse, $result)); // コース作成・更新イベント

                return $result;
            });

            if ($result) {
                return redirect()->route('staff.front.modelcourse.index', [$agencyAccount])->with('success_message', "モデルコース「{$result->course_no}」を更新しました");
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * アップロードした画像の保存準備処理
     *
     * @param array $input
     */
    private function uploadImageReadySave(&$input)
    {
        /**
         * アップロード画像処理
         *
         * メイン画像
         */
        foreach (['web_modelcourse_photo'] as $f) {
            try {
                $input[$f] = json_decode($input[$f], true);

                if (($json = Arr::get($input, "upload_{$f}"))) {
                    $uploadInfo = json_decode($json, true); // アップロード画像情報はjson形式でPOSTされるのでデコードする
                    // upload画像がある場合は、公開状態をprivateからpublicに変更（オリジナル画像とサムネイル画像）して保存用カラムをupload_(カラム名)からカラム名に切り替える
                    foreach ([config('consts.const.UPLOAD_IMAGE_DIR'),
                    config('consts.const.UPLOAD_THUMB_M_DIR'),
                    config('consts.const.UPLOAD_THUMB_S_DIR')] as $dir) {
                        \Storage::disk('s3')->setVisibility($dir.Arr::get($uploadInfo, 'file_name'), 'public');
                    }

                    $input[$f] = $uploadInfo;
                }
            } catch (\Exception $e) {
                \Log::error($e);
            }
        }
    }
}
