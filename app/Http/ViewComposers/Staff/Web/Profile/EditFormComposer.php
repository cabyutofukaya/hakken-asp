<?php
namespace App\Http\ViewComposers\Staff\Web\Profile;

use App\Models\WebProfile;
use App\Services\InterestService;
use App\Services\PurposeService;
use App\Services\WebProfileService;
use App\Services\MasterDirectionService;
use App\Traits\JsConstsTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;


/**
 * 編集フォームに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
{
    use JsConstsTrait;

    public function __construct(
        WebProfileService $webProfileService,
        PurposeService $purposeService,
        InterestService $interestService,
        MasterDirectionService $masterDirectionService
    ) {
        $this->webProfileService = $webProfileService;
        $this->purposeService = $purposeService;
        $this->interestService = $interestService;
        $this->masterDirectionService = $masterDirectionService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $webProfile = Arr::get($data, 'webProfile');
        $webProfile = $webProfile ? $webProfile : new WebProfile;

        //////////////////////////////////

        $my = auth("staff")->user();
        $agency = $my->agency;

        //////////////// form初期値を設定 ////////////////

        // 基本項目
        foreach (['post','name','name_kana','name_roman','email','tel','sex','birthday_y','birthday_m','birthday_d','introduction','business_area','purpose','interest'] as $f) {
            if ($f === 'sex') { // 性別は未設定の場合は初期値をセット
                $defaultValue[$f] = old($f, data_get($webProfile, $f, config('consts.web_profiles.DEFAULT_SEX')));
            } elseif ($f==='name') { // 名前が未設定の場合はスタッフ名で初期化
                $defaultValue[$f] = old($f, data_get($webProfile, 'name', data_get($my, 'name')));
            } else {
                $defaultValue[$f] = old($f, data_get($webProfile, $f));
            }
        }

        // HAKKEN機能の有効・無効
        if (!old('staff.web_valid')) {
            $defaultValue['staff']['web_valid'] = $my->web_valid;
        } else {
            $defaultValue['staff']['web_valid'] = old('staff.web_valid');
        }

        // 会社情報
        $defaultValue['agency']['company_name'] = $agency->company_name;

        // tag
        if (!old('web_profile_tags.tag')) {
            $defaultValue['web_profile_tags']['tag'] = $webProfile->web_profile_tags->pluck("tag")->toArray();
        } else {
            $defaultValue['web_profile_tags']['tag'] = old('web_profile_tags.tag');
        }

        /**
         * 画像（プロフィール、カバー）
         */
        // プロフィール
        if (!old('web_profile_profile_photo')) {
            $defaultValue['web_profile_profile_photo'] = $webProfile->web_profile_profile_photo->id ? $webProfile->web_profile_profile_photo->toArray() : null;
        } else {
            $defaultValue['web_profile_profile_photo'] = old('web_profile_profile_photo');
        }
        // カバー
        if (!old('web_profile_cover_photo')) {
            $defaultValue['web_profile_cover_photo'] = $webProfile->web_profile_cover_photo->id ? $webProfile->web_profile_cover_photo->toArray() : null;
        } else {
            $defaultValue['web_profile_cover_photo'] = old('web_profile_cover_photo');
        }

        // 地域情報
        $masterDirections = $this->masterDirectionService->getWebDirections();

        $formSelects = [
            'sexes' => config('consts.web_profiles.SEX_LIST'), // 性別
            'birthdayYears' => ['' => '年'] + $this->webProfileService->getBirthDayYearSelect(), // 誕生日年（「YYYY => YYYY年」形式の配列）
            'birthdayMonths' => ['' => '月'] + $this->webProfileService->getBirthDayMonthSelect(), // 誕生日月（「MM => MM月」形式の配列）
            'birthdayDays' => ['' => '日'] + $this->webProfileService->getBirthDayDaySelect(), // 誕生日日（「DD => DD月」形式の配列）
            'purposes' => $this->purposeService->getIdNameList(), // 旅行分野
            'interests' => $this->interestService->getIdNameList(), // 旅行内容
            'masterDirections' => $masterDirections, // エリア情報
        ];

        $consts = [
            'thumbSBaseUrl' => \Storage::disk('s3')->url(config('consts.const.UPLOAD_THUMB_S_DIR')),
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agency->account);

        $view->with(compact('webProfile', 'agency', 'defaultValue', 'formSelects', 'consts', 'jsVars'));
    }
}
