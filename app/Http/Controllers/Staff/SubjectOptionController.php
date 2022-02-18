<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\SubjectOptionStoretRequest;
use App\Http\Requests\Staff\SubjectOptionUpdateRequest;
use App\Models\SubjectOption;
use App\Services\SubjectOptionService;
use DB;
use Exception;
use Gate;
use Hashids;
use Illuminate\Http\Request;
use Log;

class SubjectOptionController extends AppController
{
    public function __construct(SubjectOptionService $subjectOptionService)
    {
        $this->subjectOptionService = $subjectOptionService;
    }

    /**
     * オプション項目作成処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SubjectOptionStoretRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new SubjectOption]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->all(); // カスタムフィールドがあるのでvalidatedではなくallで取得
        $input['agency_id'] = auth('staff')->user()->agency_id;

        try {
            $subjectOption = DB::transaction(function () use ($input) {
                return $this->subjectOptionService->create($input);
            });

            if ($subjectOption) {
                return redirect()->route('staff.master.subject.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.subject_categories.SUBJECT_CATEGORY_OPTION')])->with('success_message', "「{$subjectOption->name}」を登録しました");
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }

    /**
     * オプション科目編集form
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $subjectOption = $this->subjectOptionService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('view', [$subjectOption]);
        if (!$response->allowed()) {
            abort(403);
        }
        return view('staff.subject.edit.option', compact('subjectOption'));
    }

    /**
     * オプション科目更新処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(SubjectOptionUpdateRequest $request, $agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $old = $this->subjectOptionService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('update', [$old]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->all(); // カスタムフィールドがあるのでvalidatedではなくallで取得
        if ($new = $this->subjectOptionService->update($decodeId, $input)) {
            return redirect()->route('staff.master.subject.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.subject_categories.SUBJECT_CATEGORY_OPTION')])->with('success_message', "「{$new->name}」を更新しました");
        }
        abort(500);
    }

    /**
     * オプション科目削除
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $subjectOption = $this->subjectOptionService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('delete', [$subjectOption]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }
        
        if ($this->subjectOptionService->delete((int)$decodeId, true)) {
            return redirect()->route('staff.master.subject.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.subject_categories.SUBJECT_CATEGORY_OPTION')])->with('decline_message', "「{$subjectOption->name}」を削除しました");
        }
        abort(400); // Bad Request
    }
}
