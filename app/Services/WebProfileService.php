<?php

namespace App\Services;

use App\Models\WebProfile;
use App\Repositories\WebProfile\WebProfileRepository;
use App\Services\StaffService;
use App\Services\WebProfileCoverPhotoService;
use App\Services\WebProfileProfilePhotoService;
use App\Services\WebProfileTagService;
use App\Traits\BirthdayTrait;
use Illuminate\Support\Arr;

class WebProfileService
{
    use BirthdayTrait;
    
    public function __construct(
        StaffService $staffService,
        WebProfileCoverPhotoService $webProfileCoverPhotoService,
        WebProfileProfilePhotoService $webProfileProfilePhotoService,
        WebProfileRepository $webProfileRepository,
        WebProfileTagService $webProfileTagService
    ) {
        $this->staffService = $staffService;
        $this->webProfileCoverPhotoService = $webProfileCoverPhotoService;
        $this->webProfileProfilePhotoService = $webProfileProfilePhotoService;
        $this->webProfileRepository = $webProfileRepository;
        $this->webProfileTagService = $webProfileTagService;
    }

    /**
     * 1件取得
     */
    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : WebProfile
    {
        return $this->webProfileRepository->find($id, $with, $select, $getDeleted);
    }

    /**
     * 当該スタッフに紐づくプロフィール情報を一件取得
     *
     * @param int $staffId スタッフID
     */
    public function findByStaffId(int $staffId, array $with = []) : ?WebProfile
    {
        return $this->webProfileRepository->findWhere(['staff_id' => $staffId], $with);
    }

    public function updateFields(int $id, array $params) : bool
    {
        return $this->webProfileRepository->updateFields($id, $params);
    }

    /**
     * 新規登録or更新
     *
     * @param array $where サップサート条件
     * @param array $input
     * @return App\Models\WebProfile
     */
    public function upsert(array $where, array $input) : WebProfile
    {
        // HAKKEN機能の有効・無効フラグ保存
        $this->staffService->updateFields(
            $input['staff_id'],
            [
                'web_valid' => Arr::get($input, 'staff.web_valid'),
            ]
        );

        $webProfile = $this->webProfileRepository->updateOrCreate(
            $where,
            collect($input)->except([
                'staff',
                'web_profile_tags',
                'web_profile_profile_photo',
                'web_profile_cover_photo',
            ])->toArray() // リレーションフィールドは不要なので一応削除
        );

        /**
         * プロフィール情報に紐づくタグ情報を保存
         *
         * ① 既存のタグ情報を削除
         * ↓
         * ② 新しくタグリレーションを保存
         */

        // ① タグ削除
        $this->webProfileTagService->deleteByWebProfileId($webProfile->id);

        // ② タグリレーションを保存
        if (Arr::get($input, 'web_profile_tags.tag', [])) {
            $this->webProfileTagService->createTagsForWebProfile(
                $webProfile,
                collect($input['web_profile_tags']['tag'])->map(function ($tag, $key) {
                    return [
                        'tag' => trim($tag),
                    ];
                })->toArray()
            );
        }


        /**
         * プロフィール情報に紐づく写真データを保存
         */

        // プロフィール写真
        $webProfileProfilePhoto = Arr::get($input, 'web_profile_profile_photo');
        if (!$webProfileProfilePhoto || ($webProfileProfilePhoto && !Arr::get($webProfileProfilePhoto, 'id'))) { // 画像削除or画像更新

            // 画像レコード＆画像ファイル物理削除
            $this->webProfileProfilePhotoService->deleteByWebProfileId($webProfile->id, false);

            if ($webProfileProfilePhoto) { // アップロード画像あり
                $this->webProfileProfilePhotoService->create(
                    array_merge(
                        $webProfileProfilePhoto,
                        [
                            'agency_id' => $webProfile->agency_id,
                            'web_profile_id' => $webProfile->id,
                        ]
                    )
                );
            }
        } else {
            // 画像情報が変わっていない場合は処理ナシ
        }

        // カバー写真
        // プロフィール写真
        $webProfileCoverPhoto = Arr::get($input, 'web_profile_cover_photo');
        if (!$webProfileCoverPhoto || ($webProfileCoverPhoto && !Arr::get($webProfileCoverPhoto, 'id'))) { // 画像削除or画像更新

            // 画像レコード＆画像ファイル物理削除
            $this->webProfileCoverPhotoService->deleteByWebProfileId($webProfile->id, false);

            if ($webProfileCoverPhoto) { // アップロード画像あり
                $this->webProfileCoverPhotoService->create(
                    array_merge(
                        $webProfileCoverPhoto,
                        [
                            'agency_id' => $webProfile->agency_id,
                            'web_profile_id' => $webProfile->id,
                        ]
                    )
                );
            }
        } else {
            // 画像情報が変わっていない場合は処理ナシ
        }


        return $webProfile;
    }
}
