<?php
namespace App\Http\ViewComposers\Staff\Web\Profile;

use App\Models\WebProfile;
use App\Traits\JsConstsTrait;
use App\Services\PurposeService;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * プレビューに使う項目などを提供するViewComposer
 */
class PreviewFormComposer
{
    public function __construct(PurposeService $purposeService)
    {
        $this->purposeService = $purposeService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $input = Arr::get($data, 'input');

        //////////////////////////////////

        // プロフィール写真
        $profilePhoto = null; // 非公開画像(正式保存前のs3への一時アップロード状態)も表示できるようにupload画像もupload済み画像もbase64で取得
        if (Arr::get($input, 'web_profile_profile_photo') || Arr::get($input, 'upload_web_profile_profile_photo')) {
            if (Arr::get($input, 'web_profile_profile_photo')) {
                $file = \Storage::disk('s3')->get(config('consts.const.UPLOAD_THUMB_M_DIR').json_decode(Arr::get($input, 'web_profile_profile_photo'))->file_name);
                $profilePhoto = sprintf("data:%s;base64,%s", json_decode(Arr::get($input, 'web_profile_profile_photo'))->mime_type, base64_encode($file));
            } else {
                $file = \Storage::disk('s3')->get(config('consts.const.UPLOAD_THUMB_M_DIR').json_decode(Arr::get($input, 'upload_web_profile_profile_photo'))->file_name);
                $profilePhoto = sprintf("data:%s;base64,%s", json_decode(Arr::get($input, 'upload_web_profile_profile_photo'))->mime_type, base64_encode($file));
            }
        }

        // カバー写真
        $coverPhoto = null; // 非公開画像(正式保存前のs3への一時アップロード状態)も表示できるようにupload画像もupload済み画像もbase64で取得
        if (Arr::get($input, 'web_profile_cover_photo') || Arr::get($input, 'upload_web_profile_cover_photo')) {
            if (Arr::get($input, 'web_profile_cover_photo')) {
                $file = \Storage::disk('s3')->get(config('consts.const.UPLOAD_IMAGE_DIR').json_decode(Arr::get($input, 'web_profile_cover_photo'))->file_name);
                $coverPhoto = sprintf("data:%s;base64,%s", json_decode(Arr::get($input, 'web_profile_cover_photo'))->mime_type, base64_encode($file));
            } else {
                $file = \Storage::disk('s3')->get(config('consts.const.UPLOAD_IMAGE_DIR').json_decode(Arr::get($input, 'upload_web_profile_cover_photo'))->file_name);
                $coverPhoto = sprintf("data:%s;base64,%s", json_decode(Arr::get($input, 'upload_web_profile_cover_photo'))->mime_type, base64_encode($file));
            }
        }

        // 得意な旅行分野
        $purposes = $this->purposeService->getNamesByIds(Arr::get($input, 'purpose', []));

        $consts = [
            'imageBaseUrl' => \Storage::disk('s3')->url(config('consts.const.UPLOAD_IMAGE_DIR')),
            'stays' => get_const_item('web_modelcourses', 'stay'),
        ];

        $view->with(compact('consts', 'purposes', 'profilePhoto', 'coverPhoto'));
    }
}
