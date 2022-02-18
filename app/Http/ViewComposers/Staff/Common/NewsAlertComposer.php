<?php
namespace App\Http\ViewComposers\Staff\Common;

use Request;
use App\Services\AgencyNotificationService;
use Illuminate\View\View;

/**
 * ニュース未読バッジ
 */
class NewsAlertComposer
{
    public function __construct(AgencyNotificationService $agencyNotificationService) {
        $this->agencyNotificationService = $agencyNotificationService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        // 未読件数を取得
        $badge = $this->agencyNotificationService->getUnreadCount(auth('staff')->user()->agency_id);

        $view->with(compact('badge'));
    }
}
