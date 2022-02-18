<?php

namespace App\Http\Controllers\Staff\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Staff\AppController;
use App\Http\Requests\Staff\WebProfileUpsertRequest;
use App\Models\WebProfile;
use App\Services\WebProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProfileController extends AppController
{
    public function __construct(WebProfileService $webProfileService)
    {
        $this->webProfileService = $webProfileService;
    }

    /**
     * プロフィール編集ページ
     */
    public function edit(string $agencyAccount)
    {
        // 認可チェック。本ページは表示権限がないと閲覧不可。view権限だと新規作成or編集の両方に対応できないためviewAnyで判定
        $response = \Gate::inspect('viewAny', new WebProfile);
        if (!$response->allowed()) {
            abort(403);
        }

        $webProfile = $this->webProfileService->findByStaffId(auth('staff')->user()->id);

        return view("staff.web.profile.edit", compact("webProfile"));
    }

    /**
     * プロフィール情報作成or更新処理
     */
    public function upsert(WebProfileUpsertRequest $request, string $agencyAccount)
    {
        $staff = auth('staff')->user();

        $webProfile = $this->webProfileService->findByStaffId($staff->id);

        // 認可チェック
        if ($webProfile) {
            $response = \Gate::inspect('update', [$webProfile]);
        } else {
            $response = \Gate::inspect('create', new WebProfile);
        }
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();
        $input['agency_id'] = $staff->agency_id;
        $input['staff_id'] = $staff->id;

        try {
            /**
             * アップロード画像処理
             *
             * プロフィール、カバー画像
             */
            foreach (['web_profile_profile_photo','web_profile_cover_photo'] as $f) {
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

            $input = collect($input)->except(['upload_web_profile_profile_photo', 'upload_web_profile_cover_photo'])->toArray(); // upload_ フィールドは不要なので一応削除

            $new = \DB::transaction(function () use ($staff, $input) {
                return $this->webProfileService->upsert(['staff_id' => $staff->id], $input);
            });

            if ($new) {
                return redirect()->route('staff.front.profile.edit', [$agencyAccount])->with('success_message', "プロフィール情報を更新しました");
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }
}
