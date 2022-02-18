<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MasterAreaCsvImportStoreRequest;
use App\Models\MasterArea;
use App\Services\MasterAreaService;
use App\Services\MasterDirectionService;
use DB;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Log;

/**
 * 国・地域マスターデータ管理
 */
class MasterAreaController extends Controller
{
    public function __construct(MasterAreaService $masterAreaService, MasterDirectionService $masterDirectionService)
    {
        $this->masterAreaService = $masterAreaService;
        $this->masterDirectionService = $masterDirectionService;
    }
    
    // CSVアップロードページ
    public function createCsvImport()
    {
        // 登録権限チェック
        $response = Gate::inspect('create', [new MasterArea]);
        if (!$response->allowed()) {
            return back()->withInput()->withErrors(array('auth_error' => "権限エラーです"));
        }

        return view("admin.master_area.create");
    }

    // CSVインポート処理
    public function storeCsvImport(MasterAreaCsvImportStoreRequest $request)
    {
        set_time_limit(300); // 5分

        // 登録権限チェック
        $response = Gate::inspect('create', [new MasterArea]);
        if (!$response->allowed()) {
            return back()->withInput()->withErrors(array('auth_error' => "権限エラーです"));
        }

        $cnt = 0;
        $files = $request->file('csv');
        $errorMessage = '';
        $validator = null;

        try {
            DB::transaction(function () use ($files, &$cnt, &$validator) {

                // 世代管理キーを生成
                $genKey = $this->masterAreaService->makeGenKey();

                foreach ($files as $file) {
                    $detect_order = 'SJIS-win,ASCII,JIS,UTF-8,CP51932'; // SJIS-winで渡ってくる想定
                    $buffer = file_get_contents($file->getRealPath());
                    if (!$encoding = mb_detect_encoding($buffer, $detect_order, true)) {
                        // 文字コードの自動判定に失敗
                        unset($buffer);
                        throw new \RuntimeException('Character set detection failed');
                    }

                    $buffer = mb_convert_encoding($buffer, 'UTF-8', $encoding); // UTF-8に変換してbufferに書き込み

                    $temp = tmpfile();
                    $meta = stream_get_meta_data($temp);
                    fwrite($temp, $buffer);
                    rewind($temp); // ファイルポインタを先頭に
                    unset($buffer);

                    // utf-8でファイル読み込み
                    $rows = new \SplFileObject($meta['uri']);
                    $rows->setFlags(
                        \SplFileObject::READ_CSV
                        | \SplFileObject::READ_AHEAD //先読み・巻き戻しで読み出す。
                        | \SplFileObject::SKIP_EMPTY //ファイルの空行を読み飛ばす。read_aheadとセットで使わないとダメ。
                        | \SplFileObject::DROP_NEW_LINE //行末の改行を読み飛ばす。
                    );

                    /**** master_areasテーブルへデータセット ****/

                    // コードの重複チェック
                    if (collect($rows)->pluck('0')->count() != collect($rows)->pluck('0')->unique()->count()) {
                        throw new Exception("重複している「国・地域コード」があります。方面コードをご確認ください。");
                    }

                    foreach ($rows as $row) {
                        $row = Arr::only($row, [0, 1, 2, 3, 4]);
                        $row[] = null; // 方面ID。一旦nullで初期化
                        $row[] = $genKey; // 世代管理キー

                        $row = array_combine([
                            'code',
                            'master_direction_code',
                            'name',
                            'name_en',
                            'is_default',
                            'master_direction_id',
                            'gen_key',
                        ], $row);

                        // boolean型に
                        $row['is_default'] = !$row['is_default'] ? false : true;

                        /* csv 1行ずつバリデーションをかける */

                        $line = $cnt + 1;

                        $validator = \Validator::make($row, [
                            'code' => 'required|max:100',
                            'master_direction_code' => ['required','exists:master_directions,code'],
                            'name' => 'required|max:100',
                            'id_default' => ['nullable','regex:/(0|1)/'],
                            'gen_key' => 'required',
                        ], [
                            'code.required' => "国・地域コードは必須です({$line} 行目)",
                            'code.max' => "方面コードが長すぎます(100文字まで)({$line} 行目)",
                            'master_direction_code.required' => "方面コードは必須です({$line} 行目)",
                            'master_direction_code.exists' => "方面コードの値が存在しません({$line} 行目)",
                            'name.required' => "国・地域名称は必須です({$line} 行目)",
                            'name.max' => "国・地域名称が長すぎます(100文字まで)({$line} 行目)",
                            'name_en.required' => "国・地域名称(英)は必須です({$line} 行目)",
                            'name_en.max' => "国・地域名称(英)が長すぎます(100文字まで)({$line} 行目)",
                            'id_default.regex' => "デフォルト表示は0か1の値を入力してください({$line} 行目)",
                            'gen_key.required' => "世代管理キーは必須です({$line} 行目)",
                        ]);
                
                        if ($validator->fails()) { // バリデーションエラー
                            throw new ValidationException();
                        }

                        // 新規or更新
                        $this->masterAreaService->upsert($row['code'], $row);

                        $cnt++;
                    }
                }

                // 削除権限チェック
                $response = Gate::inspect('forceDelete', [new MasterArea]);
                if (!$response->allowed()) {
                    throw new Exception("権限エラーです");
                }

                // insert or updateの対象にならなかったレコードは全削除
                $this->masterAreaService->deleteExceptionGenKey($genKey);
            });

            return redirect()->route('admin.areas.master_areas.import.create')->with('success_message', sprintf("%s 件のインポート処理が完了しました", number_format($cnt)));
        } catch (ValidationException $e) { // バリデーションエラー
            return redirect()->route('admin.areas.master_areas.import.create')
                        ->withErrors($validator)
                        ->withInput();
        } catch (Exception $e) {
            Log::error($e);
            $errorMessage = $e->getMessage();

            return redirect()->route('admin.areas.master_areas.import.create')
                        ->with('error_message', $errorMessage ? $errorMessage : "CSVのインポート処理に失敗しました");
        }
    }
}
