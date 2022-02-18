<?php

namespace App\Http\Controllers\Staff\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Staff\AppController;
use App\Http\Requests\Staff\WebCompanyUpsertRequest;
use App\Models\WebCompany;
use App\Services\WebCompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;


class CompanyController extends AppController
{
    public function __construct(WebCompanyService $webCompanyService)
    {
        $this->webCompanyService = $webCompanyService;
    }

    /**
     * 会社情報編集ページ
     */
    public function edit(string $agencyAccount)
    {
        // 認可チェック。本ページは表示権限がないと閲覧不可
        $response = \Gate::inspect('viewAny', new WebCompany);
        if (!$response->allowed()) {
            abort(403);
        }

        $webCompany = $this->webCompanyService->findByAgencyId(auth('staff')->user()->agency_id);

        return view("staff.web.company.edit", compact("webCompany"));
    }

    /**
     * 会社情報作成or更新処理
     */
    public function upsert(WebCompanyUpsertRequest $request, string $agencyAccount)
    {
        $agencyId = auth('staff')->user()->agency_id;

        $webCompany = $this->webCompanyService->findByAgencyId($agencyId);

        // 認可チェック
        if ($webCompany) {
            $response = \Gate::inspect('update', [$webCompany]);
        } else {
            $response = \Gate::inspect('create', new WebCompany);
        }
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();
        $input['agency_id'] = $agencyId;

        try {
            /**
             * アップロード画像処理
             */
            // ロゴ画像
            if ($fileName = Arr::get($input, 'upload_logo_image')) {
                // upload画像がある場合は、公開状態をprivateからpublicに変更（オリジナル画像とサムネイル画像）して保存用カラムをupload_logo_imageからlogo_imageに切り替える
                foreach ([config('consts.const.UPLOAD_IMAGE_DIR'),
                config('consts.const.UPLOAD_THUMB_M_DIR'),
                config('consts.const.UPLOAD_THUMB_S_DIR')] as $dir) {
                    \Storage::disk('s3')->setVisibility($dir.$fileName, 'public');
                }
                $input['logo_image'] = $fileName;
            }

            // イメージ画像
            if (Arr::get($input, 'upload_images')) {
                foreach ($input['upload_images'] as $k => $fileName) {
                    // upload画像がある場合は、公開状態をprivateからpublicに変更（オリジナル画像とサムネイル画像）して保存用カラムをupload_imagesからimagesに切り替える
                    foreach ([config('consts.const.UPLOAD_IMAGE_DIR'),
                    config('consts.const.UPLOAD_THUMB_M_DIR'),
                    config('consts.const.UPLOAD_THUMB_S_DIR')] as $dir) {
                        \Storage::disk('s3')->setVisibility($dir.$fileName, 'public');
                    }
                    if (!isset($input['images']) || !is_array($input['images'])) {
                        $input['images'] = [];
                    }
                    $input['images'][$k] = $fileName;
                }
            }

            $input = collect($input)->except(['upload_logo_image', 'upload_images'])->toArray(); // upload_ フィールドは不要なので一応削除

            if ($new = $this->webCompanyService->upsert(['agency_id' => $agencyId], $input)) {
                return redirect()->route('staff.front.company.edit', [$agencyAccount])->with('success_message', "会社情報を更新しました");
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }
}
