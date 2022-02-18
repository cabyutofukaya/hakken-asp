<?php
namespace App\Repositories\WebProfileProfilePhoto;

use App\Models\WebProfileProfilePhoto;
use App\Traits\DeleteImageFileTrait;

class WebProfileProfilePhotoRepository implements WebProfileProfilePhotoRepositoryInterface
{
    use DeleteImageFileTrait;

    /**
    * @param object $webProfileProfilePhoto
    */
    public function __construct(WebProfileProfilePhoto $webProfileProfilePhoto)
    {
        $this->webProfileProfilePhoto = $webProfileProfilePhoto;
    }

    public function create(array $data): WebProfileProfilePhoto
    {
        return $this->webProfileProfilePhoto->create($data);
    }

    /**
     * 当該プロフィールIDに紐づくレコードとファイルを削除
     */
    public function deleteByWebProfileId(int $webProfileId, bool $softDelete = true) : bool
    {
        foreach ($this->webProfileProfilePhoto->where('web_profile_id', $webProfileId)->get() as $row) {

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
