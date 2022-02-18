<?php
namespace App\Repositories\WebModelcourseTag;

use App\Models\WebModelcourseTag;

class WebModelcourseTagRepository implements WebModelcourseTagRepositoryInterface
{
    /**
    * @param object $webModelcourseTag
    */
    public function __construct(WebModelcourseTag $webModelcourseTag)
    {
        $this->webModelcourseTag = $webModelcourseTag;
    }

    /**
     * 当該モデルコースIDのタグ情報を削除
     *
     * @param int $webModelcourseId モデルコースID
     * @return bool
     */
    public function deleteByWebModelcourseId(int $webModelcourseId) : bool
    {
        $this->webModelcourseTag->where('web_modelcourse_id', $webModelcourseId)->delete();
        return true;
    }
}
