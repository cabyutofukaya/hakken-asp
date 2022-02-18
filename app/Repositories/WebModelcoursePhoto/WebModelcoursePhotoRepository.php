<?php
namespace App\Repositories\WebModelcoursePhoto;

use App\Models\WebModelcoursePhoto;
use App\Traits\DeleteImageFileTrait;

class WebModelcoursePhotoRepository implements WebModelcoursePhotoRepositoryInterface
{
    use DeleteImageFileTrait;

    /**
    * @param object $webModelcoursePhoto
    */
    public function __construct(WebModelcoursePhoto $webModelcoursePhoto)
    {
        $this->webModelcoursePhoto = $webModelcoursePhoto;
    }

    public function create(array $data): WebModelcoursePhoto
    {
        return $this->webModelcoursePhoto->create($data);
    }

    /**
     * 当該コースIDに紐づくレコードとファイルを削除
     */
    public function deleteByWebModelcourseId(int $webModelcourseId, bool $softDelete = true) : bool
    {
        foreach ($this->webModelcoursePhoto->where('web_modelcourse_id', $webModelcourseId)->get() as $row) {

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
