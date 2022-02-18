<?php

namespace App\Services;

use App\Models\WebModelcourse;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\WebModelcourse\WebModelcourseRepository;
use App\Services\WebModelcoursePhotoService;
use App\Services\WebModelcourseTagService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class WebModelcourseService
{
    public function __construct(AgencyRepository $agencyRepository, WebModelcourseRepository $webModelcourseRepository, WebModelcourseTagService $webModelcourseTagService, WebModelcoursePhotoService $webModelcoursePhotoService)
    {
        $this->agencyRepository = $agencyRepository;
        $this->webModelcourseRepository = $webModelcourseRepository;
        $this->webModelcourseTagService = $webModelcourseTagService;
        $this->webModelcoursePhotoService = $webModelcoursePhotoService;
    }

    /**
     * 一覧を取得（for 会社アカウント）
     *
     * @param string $account 会社アカウント
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $account, array $params, int $limit, array $with = [], array $select=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);
        return $this->webModelcourseRepository->paginateByAgencyId($agencyId, $params, $limit, $with, $select);
    }

    /**
     * IDから一件取得
     */
    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false) : WebModelcourse
    {
        return $this->webModelcourseRepository->find($id, $with, $select, $getDeleted);
    }

    /**
     * コース番号から一件取得
     */
    public function findByCourseNo(string $courseNo, int $agencyId) : ?WebModelcourse
    {
        return $this->webModelcourseRepository->findWhere([
            'agency_id' => $agencyId,
            'course_no' => $courseNo,
        ]);
    }

    /**
     * 新規登録or更新
     *
     * @param array $where サップサート条件
     * @param array $input
     * @return App\Models\WebModelcourse
     */
    public function upsert(array $where, array $input) : WebModelcourse
    {
        if (!$where['id']) { // idが未設定=新規作成の場合はコースNOを生成
            $input['course_no'] = $this->makeCourseNo((int)$input['agency_id']);
        }

        $webModelcourse = $this->webModelcourseRepository->updateOrCreate(
            $where,
            collect($input)->except([
                'web_modelcourse_tags',
                'web_modelcourse_photo',
            ])->toArray() // リレーションフィールドは不要なので一応削除
        );

        /**
         * モデルコースに紐づくタグ情報を保存
         *
         * ① 既存のタグ情報を削除
         * ↓
         * ② 新しくタグリレーションを保存
         */

        // ① タグ削除
        $this->webModelcourseTagService->deleteByWebModelcourseId($webModelcourse->id);

        // ② タグリレーションを保存
        if (Arr::get($input, 'web_modelcourse_tags.tag', [])) {
            $this->webModelcourseTagService->createTagsForWebModelcourse(
                $webModelcourse,
                collect($input['web_modelcourse_tags']['tag'])->map(function ($tag, $key) {
                    return [
                        'tag' => trim($tag),
                    ];
                })->toArray()
            );
        }


        /**
         * メイン写真を保存
         */
        $webModelcoursePhoto = Arr::get($input, 'web_modelcourse_photo');
        if (!$webModelcoursePhoto || ($webModelcoursePhoto && !Arr::get($webModelcoursePhoto, 'id'))) { // 画像削除or画像更新

            // 画像レコード＆画像ファイル物理削除
            $this->webModelcoursePhotoService->deleteByWebModelcourseId($webModelcourse->id, false);

            if ($webModelcoursePhoto) { // アップロード画像あり
                $this->webModelcoursePhotoService->create(
                    array_merge(
                        $webModelcoursePhoto,
                        [
                            'agency_id' => $webModelcourse->agency_id,
                            'web_modelcourse_id' => $webModelcourse->id,
                        ]
                    )
                );
            }
        } else {
            // 画像情報が変わっていない場合は処理ナシ
        }


        return $webModelcourse;
    }

    /**
     * 当該IDのモデルコースをコピーして新しいコースを作成
     * 作成者は第2引数のスタッフ
     *
     * @param int $webModelcourseId コピー対象のコースID
     * @param int $authorId 作成者ID
     * @return App\Models\WebModelcourse
     */
    public function copyForAuthor(int $webModelcourseId, int $authorId) : WebModelcourse
    {
        $webModelcourse = $this->find($webModelcourseId);

        // リレーションを取得しておく
        $tags = $webModelcourse->web_modelcourse_tags()->get(); // タグ
        $photo = $webModelcourse->web_modelcourse_photo()->get(); // メイン写真

        // 複製
        $cloneCourse = $webModelcourse->replicate();
        $cloneCourse->course_no = $this->makeCourseNo($cloneCourse->agency_id); // コース番号を生成
        $cloneCourse->author_id = $authorId;
        $cloneCourse->push();

        /**
         * 元データのリレーションを複製データにコピー
         */

        // タグ
        foreach ($tags as $tag) {
            $cloneCourse->web_modelcourse_tags()->create($tag->toArray());
        }

        // 写真
        // s3に保存されている写真をコピー保存して、コピー先ファイル名でリレーションを作成
        foreach ($photo as $p) {
            try {
                // コピー元ファイル名
                $fromFileName = $p->file_name;
                $pathInfo = pathinfo($fromFileName); // 拡張子情報等

                // コピー先ファイル名を生成
                $toFileName = sprintf("%s.%s", md5(uniqid(rand(), true)), $pathInfo['extension']);

                // 3つのディレクトリにコピー
                foreach ([config('consts.const.UPLOAD_IMAGE_DIR'),config('consts.const.UPLOAD_THUMB_M_DIR'),config('consts.const.UPLOAD_THUMB_S_DIR')] as $dir) {
                    \Storage::disk('s3')->copy(
                        $dir.$fromFileName,
                        $dir.$toFileName
                    );
                }

                $p->file_name = $toFileName; // ファイル名を書き換え
                $cloneCourse->web_modelcourse_photo()->create($p->toArray());
            } catch (\Exception $e) {
                // ほぼないと思うがファイル名のバッティングなど。エラーの場合はスキップ
            }
        }

        $cloneCourse->save();

        return $cloneCourse;
    }

    public function updateFields(int $id, array $params) : bool
    {
        return $this->webModelcourseRepository->updateFields($id, $params);
    }

    /**
     * 当該作成者の有効コース数を取得
     */
    public function getValidCountByAuthorId(int $authorId) : int
    {
        return $this->webModelcourseRepository->getValidCountByAuthorId($authorId);
    }

    /**
     * コースNoを作成
     */
    public function makeCourseNo(int $agencyId) : string
    {
        $count = $this->webModelcourseRepository->getCount($agencyId, true);
        return sprintf("MD%04d", ($count + 1));
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->webModelcourseRepository->delete($id, $isSoftDelete);
    }
}
