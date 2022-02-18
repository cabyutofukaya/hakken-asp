<?php

namespace App\Services;

use App\Models\WebProfile;
use App\Models\WebProfileTag;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\WebProfileTag\WebProfileTagRepository;
use Illuminate\Support\Arr;

class WebProfileTagService
{
    
    public function __construct(AgencyRepository $agencyRepository, WebProfileTagRepository $webProfileTagRepository)
    {
        $this->agencyRepository = $agencyRepository;
        $this->webProfileTagRepository = $webProfileTagRepository;
    }

    /**
     * web_profilesのタグリレーションを作成
     */
    public function createTagsForWebProfile(WebProfile $webProfile, array $data)
    {
        $webProfile->web_profile_tags()->createMany($data);
    }

    /**
     * 当該プロフィールIDのタグ情報を削除
     */
    public function deleteByWebProfileId(int $webProfileId) : bool
    {
        return $this->webProfileTagRepository->deleteByWebProfileId($webProfileId);
    }
}
