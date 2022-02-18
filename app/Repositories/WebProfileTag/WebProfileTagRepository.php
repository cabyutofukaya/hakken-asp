<?php
namespace App\Repositories\WebProfileTag;

use App\Models\WebProfileTag;

class WebProfileTagRepository implements WebProfileTagRepositoryInterface
{
    /**
    * @param object $webProfileTag
    */
    public function __construct(WebProfileTag $webProfileTag)
    {
        $this->webProfileTag = $webProfileTag;
    }

    /**
     * 当該プロフィールIDのタグ情報を削除
     *
     * @param int $webProfileId プロフィールID
     * @return bool
     */
    public function deleteByWebProfileId(int $webProfileId) : bool
    {
        $this->webProfileTag->where('web_profile_id', $webProfileId)->delete();
        return true;
    }
}
