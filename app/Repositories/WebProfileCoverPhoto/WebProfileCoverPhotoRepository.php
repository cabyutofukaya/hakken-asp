<?php
namespace App\Repositories\WebProfileCoverPhoto;

use App\Models\WebProfileCoverPhoto;
use App\Traits\DeleteImageFileTrait;

class WebProfileCoverPhotoRepository implements WebProfileCoverPhotoRepositoryInterface
{
    use DeleteImageFileTrait;

    /**
    * @param object $webProfileCoverPhoto
    */
    public function __construct(WebProfileCoverPhoto $webProfileCoverPhoto)
    {
        $this->webProfileCoverPhoto = $webProfileCoverPhoto;
    }

    public function create(array $data): WebProfileCoverPhoto
    {
        return $this->webProfileCoverPhoto->create($data);
    }

    /**
     * 当該プロフィールIDに紐づくレコードとファイルを削除
     */
    public function deleteByWebProfileId(int $webProfileId, bool $softDelete = true) : bool
    {
        foreach ($this->webProfileCoverPhoto->where('web_profile_id', $webProfileId)->get() as $row) {

            $this->deleteFile($row->file_name, $softDelete); // ファイル削除

            // レコード削除
            if ($softDelete) {
                $row->delete();
            } else {
                $row->forceDelete();
            }
        }
        return true;
    }
}
