<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\SubjectAirplaneStoretRequest;
use App\Http\Requests\Staff\SubjectAirplaneUpdateRequest;
use App\Models\SubjectAirplane;
use App\Services\SubjectAirplaneService;
use DB;
use Exception;
use Gate;
use Hashids;
use Illuminate\Http\Request;
use Log;

class SubjectAirplaneController extends AppController
{
    public function __construct(SubjectAirplaneService $subjectAirplaneService)
    {
        $this->subjectAirplaneService = $subjectAirplaneService;
    }

    /**
     * 航空券科目の作成処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SubjectAirplaneStoretRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new SubjectAirplane]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->all(); // カスタムフィールドがあるのでvalidatedではなくallで取得
        $input['agency_id'] = auth('staff')->user()->agency_id;

        try {
            $subjectAirplane = DB::transaction(function () use ($input) {
                return $this->subjectAirplaneService->create($input);
            });

            if ($subjectAirplane) {
                return redirect()->route('staff.master.subject.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE')])->with('success_message', "「{$subjectAirplane->name}」を登録しました");
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }

    /**
     * 航空券科目の編集form
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $subjectAirplane = $this->subjectAirplaneService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('view', [$subjectAirplane]);
        if (!$response->allowed()) {
            abort(403);
        }
        return view('staff.subject.edit.airplane', compact('subjectAirplane'));
    }

    /**
     * 航空券科目更新処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(SubjectAirplaneUpdateRequest $request, $agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $old = $this->subjectAirplaneService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('update', [$old]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->all(); // カスタムフィールドがあるのでvalidatedではなくallで取得
        if ($new = $this->subjectAirplaneService->update($decodeId, $input)) {
            return redirect()->route('staff.master.subject.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE')])->with('success_message', "「{$new->name}」を更新しました");
        }
        abort(500);
    }

    /**
     * 航空券科目削除
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $subjectAirplane = $this->subjectAirplaneService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('delete', [$subjectAirplane]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }
        
        if ($this->subjectAirplaneService->delete((int)$decodeId, true)) {
            return redirect()->route('staff.master.subject.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE')])->with('decline_message', "「{$subjectAirplane->name}」を削除しました");
        }
        abort(400); // Bad Request
    }
}
