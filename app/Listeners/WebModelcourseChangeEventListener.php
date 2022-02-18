<?php

namespace App\Listeners;

use App\Events\WebModelcourseChangeEvent;
use App\Services\StaffService;
use App\Services\WebModelcourseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class WebModelcourseChangeEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(StaffService $staffService, WebModelcourseService $webModelcourseService)
    {
        $this->staffService = $staffService;
        $this->webModelcourseService = $webModelcourseService;
    }

    /**
     * Handle the event.
     *
     * @param  WebModelcourseChangeEvent  $event
     * @return void
     */
    public function handle(WebModelcourseChangeEvent $event)
    {
        /**
         * 変更前・変更後の作成者IDを元に有効モデル数の集計値を更新
         *
         * HAKKENのマイスター検索で有効プランを1件以上保持しているマイスターを抽出する際に、
         * SQL負荷軽減のため集計数をあらかじめstaffレコードに保存しておく
         */
        $oldAuthorId = data_get($event->oldWebModelcourse, 'author_id');
        $newAuthorId = data_get($event->newWebModelcourse, 'author_id');

        if ($oldAuthorId) {
            $this->staffService->updateNumberOfPlan(
                $oldAuthorId,
                $this->webModelcourseService->getValidCountByAuthorId($oldAuthorId),
                true // 論理削除も含めて更新
            );
        }

        if ($newAuthorId) {
            $this->staffService->updateNumberOfPlan(
                $newAuthorId,
                $this->webModelcourseService->getValidCountByAuthorId($newAuthorId),
                true // 論理削除も含めて更新
            );
        }
    }
}
