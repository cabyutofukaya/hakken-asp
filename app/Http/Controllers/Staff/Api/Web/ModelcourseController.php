<?php

namespace App\Http\Controllers\Staff\Api\Web;

use App\Events\WebModelcourseChangeEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\WebModelcourseCopyRequest;
use App\Http\Requests\Staff\WebModelcourseShowUpdateRequest;
use App\Http\Resources\Staff\WebModelcourse\IndexResource;
use App\Models\WebModelcourse;
use App\Services\WebModelcourseService;
use Illuminate\Http\Request;

class ModelcourseController extends Controller
{
    public function __construct(WebModelcourseService $webModelcourseService)
    {
        $this->webModelcourseService = $webModelcourseService;
    }

    // 一覧
    public function index($agencyAccount)
    {
        // 認可チェック
        $response = \Gate::authorize('viewAny', new WebModelcourse);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return IndexResource::collection($this->webModelcourseService->paginateByAgencyAccount(
            $agencyAccount,
            [],
            request()->get("per_page", 10),
            ['author','departure','destination'],
            []
        ));
    }

    /**
     * 表示フラグ更新
     *
     * @param string $agencyAccount 会社アカウント
     */
    public function showUpdate(WebModelcourseShowUpdateRequest $request, string $agencyAccount, int $webModelcourseId)
    {
        // 認可チェック
        $webModelcourse = $this->webModelcourseService->find($webModelcourseId);

        if (!$webModelcourse) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        $response = \Gate::authorize('update', $webModelcourse);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $show = $request->input("show");
            $result = \DB::transaction(function () use ($webModelcourse, $show) {
                $this->webModelcourseService->updateFields($webModelcourse->id, ['show' => $show]);

                event(new WebModelcourseChangeEvent(
                    $webModelcourse,
                    $this->webModelcourseService->find($webModelcourse->id)
                )); // コース作成・更新イベント
                
                return true;
            });

            if ($result) {
                return response('', 200);
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * コースの複製
     *
     * @param int $webModelcourseId コピー対象のコースID
     */
    public function copy(WebModelcourseCopyRequest $request, string $agencyAccount, int $webModelcourseId)
    {
        // 認可チェック
        $webModelcourse = $this->webModelcourseService->find($webModelcourseId);

        if (!$webModelcourse) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        $response = \Gate::authorize('create', new WebModelcourse);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $authorId = $request->input("author_id");
        try {
            $webModelcourse = \DB::transaction(function () use ($webModelcourseId, $authorId) {
                $copyWebModelcourse = $this->webModelcourseService->copyForAuthor($webModelcourseId, $authorId);

                event(new WebModelcourseChangeEvent(
                    null,
                    $copyWebModelcourse
                )); // コース作成・更新イベント

                return $copyWebModelcourse;
            });

            if ($webModelcourse) {
                // 一覧を返す
                return IndexResource::collection($this->webModelcourseService->paginateByAgencyAccount(
                    $agencyAccount,
                    [],
                    request()->get("per_page", 10),
                    ['author','departure','destination'],
                    []
                ));
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * 一件削除
     *
     * @param int $webModelcourseId コースID
     */
    public function destroy(Request $request, string $agencyAccount, int $webModelcourseId)
    {
        $webModelcourse = $this->webModelcourseService->find($webModelcourseId);

        if (!$webModelcourse) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = \Gate::inspect('delete', [$webModelcourse]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $result = \DB::transaction(function () use ($webModelcourse) {
                $result = $this->webModelcourseService->delete($webModelcourse->id, true); // 論理削除

                event(new WebModelcourseChangeEvent(
                    $webModelcourse,
                    null
                )); // コース作成・更新イベント

                return $result;
            });

            if ($result) {
                if ($request->input("set_message")) {
                    $request->session()->flash('decline_message', "モデルコース「{$webModelcourse->course_no}」を削除しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
                }
                return response('', 200);
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }
}
