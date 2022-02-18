<?php

namespace App\Services;

use App\Models\Agency;
use App\Models\AgencyNotification;
use App\Models\SystemNews;
use App\Repositories\SystemNews\SystemNewsRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class SystemNewsService
{
    public function __construct(
        SystemNewsRepository $systemNewsRepository
    ) {
        $this->systemNewsRepository = $systemNewsRepository;
    }

    /**
     * 該当IDを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     */
    public function find(int $id, array $select=[], bool $getDeleted=false) : ?SystemNews
    {
        return $this->systemNewsRepository->find($id, $select, $getDeleted);
    }

    /**
     * 一覧を取得
     *
     * @param int $limit
     * @param array $with
     */
    public function paginate(array $params, int $limit, array $with = [], array $select=[], bool $getDeleted = false) : LengthAwarePaginator
    {
        return $this->systemNewsRepository->paginate($params, $limit, $with, $select, $getDeleted);
    }

    /**
     * 通知作成
     *
     * system_newsレコードに入力内容を保存
     * ↓
     * 各旅行会社用の通知テーブル(agency_notifications)に通知データを保存
     */
    public function create(array $data): SystemNews
    {
        $systemNews = $this->systemNewsRepository->create($data);

        // 全登録会社に送信
        Agency::orderBy('id')->chunk(100, function ($agencies) use ($systemNews) { // 念の為100件ずつ処理
            $rows = [];
            $dt = date('Y-m-d H:i:s');

            foreach ($agencies as $agency) {
                $row = [];
                $row['agency_id'] = $agency->id;
                $row['system_news_id'] = $systemNews->id;
                $row['content'] = $systemNews->content;
                $row['regist_date'] = $systemNews->regist_date;
                $row['notification_type'] = config('consts.agency_notifications.NOTIFICATION_ADMIN'); // 管理者通知
                $row['read_at'] = null; // 既読日時初期化
                $row['created_at'] = $dt;
                $row['updated_at'] = $dt;
                
                $rows[] = $row;
            }
            // バルクインサート
            AgencyNotification::insert($rows);
        });

        return $systemNews;
    }

    /**
     * 通知更新
     *
     * system_newsレコードの入力内容を更新
     * ↓
     * 各旅行会社用の通知テーブル(agency_notifications)に通知データを更新
     */
    public function update(int $id, array $data): SystemNews
    {
        $systemNews = $this->systemNewsRepository->update($id, $data);

        /**
         * 当該通知に紐づくagency_notificationsを更新
         * 登録日時、本文
         */
        AgencyNotification::where('system_news_id', $systemNews->id)->update([
            'content' => $systemNews->content,
            'regist_date' => $systemNews->regist_date,
        ]);

        return $systemNews;
    }

    /**
     *  削除
     *
     * system_newsレコードを削除
     * ↓
     * 各旅行会社用の通知テーブル(agency_notifications)を削除
     */
    public function delete(int $id, bool $isSoftDelete): bool
    {
        $this->systemNewsRepository->delete($id, $isSoftDelete);

        /**
         * 当該通知に紐づくagency_notificationsを削除
         * 一応、親レコードの削除タイプに合わせる(論理削除、物理削除)
         */
        if ($isSoftDelete) { // 論理削除
            AgencyNotification::where('system_news_id', $id)->delete();
        } else {
            AgencyNotification::where('system_news_id', $id)->forceDelete();
        }
        return true;
    }
}
