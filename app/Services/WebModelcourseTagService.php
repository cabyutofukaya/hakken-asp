<?php

namespace App\Services;

use App\Models\WebModelcourse;
use App\Models\WebModelcourseTag;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\WebModelcourseTag\WebModelcourseTagRepository;
use Illuminate\Support\Arr;

class WebModelcourseTagService
{
    
    public function __construct(AgencyRepository $agencyRepository, WebModelcourseTagRepository $webModelcourseTagRepository)
    {
        $this->agencyRepository = $agencyRepository;
        $this->webModelcourseTagRepository = $webModelcourseTagRepository;
    }

    /**
     * web_modelcoursesのタグリレーションを作成
     */
    public function createTagsForWebModelcourse(WebModelcourse $webModelcourse, array $data)
    {
        $webModelcourse->web_modelcourse_tags()->createMany($data);
    }

    /**
     * 当該モデルコースIDのタグ情報を削除
     */
    public function deleteByWebModelcourseId(int $webModelcourseId) : bool
    {
        return $this->webModelcourseTagRepository->deleteByWebModelcourseId($webModelcourseId);
    }
}
