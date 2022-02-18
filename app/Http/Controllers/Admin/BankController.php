<?php

namespace App\Http\Controllers\Admin;

use DB;
use Gate;
use Exception;
use Log;
use Illuminate\Support\Arr;
use App\Models\Bank;
use App\Services\BankService;
use App\Http\Requests\Admin\BankCsvImportStoreRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * 銀行データ管理
 */
class BankController extends Controller
{
    public function __construct(BankService $bankService)
    {
        $this->bankService = $bankService;
    }
    
    // CSVアップロードページ
    public function createCsvImport()
    {
        // 認可チェック
        $response = Gate::inspect('create', [new Bank]);
        if (!$response->allowed()) {
            return back()->withInput()->withErrors(array('auth_error' => "権限エラーです"));
        }

        return view("admin.bank.create");
    }

    // CSVインポート処理
    public function storeCsvImport(BankCsvImportStoreRequest $request)
    {
        set_time_limit(600); // 10分

        // 認可チェック
        $response = Gate::inspect('create', [new Bank]);
        if (!$response->allowed()) {
            return back()->withInput()->withErrors(array('auth_error' => "権限エラーです"));
        }

        $cnt = 0;
        $files = $request->file('csv');

        try {
            // 銀行データを全削除
            $this->bankService->truncate();

            DB::transaction(function () use ($files, &$cnt) {
                $kinyuCodes = array(); // 金融期間コードを重複させないようにするためのチェック配列

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

                    /**** banksテーブルへデータセット ****/

                    $columns = [
                        'kinyu_code', 
                        'tenpo_code', 
                        'kinyu_kana', 
                        'kinyu_name', 
                        'tenpo_kana', 
                        'tenpo_name', 
                        'zip_code', 
                        'address', 
                        'tel', 
                        'tegata_kokanjyo_no', 
                        'narabi_code', 
                        'kamei', 
                        'created_at', 
                        'updated_at'
                    ];

                    $saveRows = array();

                    foreach ($rows as $row) {
                        $row = Arr::only($row, [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]); //
                        $row = array_merge($row); // キーを振り直し
                        $row[] = date("Y-m-d H:i:s"); // 作成日
                        $row[] = null; // 更新日
                        $saveRows[] = array_combine($columns, $row);
                    }

                    $cnt += count($saveRows); // 件数計算

                    foreach (array_chunk($saveRows, 1000) as $chunkRows) {
                        $this->bankService->insert($chunkRows);
                    }
                }
            });

            return redirect()->route('admin.banks.import.create')->with('success_message', sprintf("%s 件のインポート処理が完了しました", number_format($cnt)));
        } catch (Exception $e) {
            //
        }

        return redirect()->route('admin.banks.import.create')->with(' error_message', "CSVのインポート処理に失敗しました");
    }
}
