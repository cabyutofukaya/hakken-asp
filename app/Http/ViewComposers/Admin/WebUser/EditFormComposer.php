<?php
namespace App\Http\ViewComposers\Admin\WebUser;

use App\Models\Agency;
use Illuminate\View\View;
use Illuminate\Support\Arr;
use App\Services\PrefectureService;
use App\Services\CountryService;
use App\Services\WebUserService;


/**
 * Webユーザー情報編集フォームに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
{

    public function __construct(PrefectureService $prefectureService, CountryService $countryService, WebUserService $webUserService)
    {
        $this->webUserService = $webUserService;
        $this->prefectureService = $prefectureService;
        $this->countryService = $countryService;
    }
    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $ageKbns = ['' => '-'] + get_const_item('web_users', 'age_kbn'); // 年齢区分
        $birthdayYears = ['' => '年'] + $this->webUserService->getBirthDayYearSelect(); // 誕生日年（「YYYY => YYYY年」形式の配列）
        $birthdayMonths = ['' => '月'] + $this->webUserService->getBirthDayMonthSelect(); // 誕生日月（「MM => MM月」形式の配列）
        $birthdayDays = ['' => '日'] + $this->webUserService->getBirthDayDaySelect(); // 誕生日日（「DD => DD月」形式の配列）
        $prefectures = ['' => '都道府県'] + $this->prefectureService->getCodeNameList(); // 都道府県（「都道府県コード => 都道府県名」形式の配列）,
        $countries = ['' => '-'] + $this->countryService->getCodeNameList(); // 国名リスト

        $view->with(compact('ageKbns', 'birthdayYears', 'birthdayMonths', 'birthdayDays', 'prefectures', 'countries'));
    }
}
