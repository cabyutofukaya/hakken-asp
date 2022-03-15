<?php

namespace App\Traits;

/**
 * 画像ファイルの削除を扱うtrait
 */
trait DeleteImageFileTrait
{
    // ファイル削除
    public function deleteFile($fileName, $softDelete)
    {
        foreach ([
            config('consts.const.UPLOAD_IMAGE_DIR'),
            config('consts.const.UPLOAD_THUMB_M_DIR'),
            config('consts.const.UPLOAD_THUMB_S_DIR')
            ] as $dir) {
            if ($softDelete) {
                \Storage::disk('s3')->setVisibility($dir.$fileName, 'private'); // 一応、対象ファイルの公開状態をprivateに変更
            } else {
                \Storage::disk('s3')->delete($dir.$fileName); // 対象ファイルを物理削除
            }
        }
        return true;
    }
}
