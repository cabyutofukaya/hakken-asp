<?php

namespace App\Http\Controllers\Staff\Api;

use Illuminate\Support\Facades\Storage;
use App\Models\ReserveSchedulePhoto;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Staff\TempImageUploadRequest;
use Image;

class FileUploadController extends Controller
{
    /**
     * 一時画像をアップロード
     * 
     * オリジナル画像とサムネイル2種を作成（幅400px、幅150px）
     */
    public function tempImageUpload(TempImageUploadRequest $request)
    {
        $file = $request->file;

        // オリジナル画像アップロード
        $uploadPath = Storage::disk('s3')->putFile(config('consts.const.UPLOAD_IMAGE_DIR'), $file, 'private');
        $fileName = basename($uploadPath);

        $fileSize = Storage::disk('s3')->size($uploadPath); // バイト
        $originalFileName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();


        //////////// サムネイル画像作成(400,150)
        // 400サイズ
        $thumbm = Image::make($file)->resize(config('consts.const.THUMB_M'), null, function ($constraint) {
            $constraint->aspectRatio();
        });
        Storage::disk('s3')->put(config('consts.const.UPLOAD_THUMB_M_DIR').$fileName, (string)$thumbm->encode(), 'private');

        // 150サイズ
        $thumbs = Image::make($file)->resize(config('consts.const.THUMB_S'), null, function ($constraint) {
            $constraint->aspectRatio();
        });
        Storage::disk('s3')->put(config('consts.const.UPLOAD_THUMB_S_DIR').$fileName, (string)$thumbs->encode(), 'private');


        return response()->json([
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'original_file_name' => $originalFileName,
            'mime_type' => $mimeType,
        ], 201);
    }
}
