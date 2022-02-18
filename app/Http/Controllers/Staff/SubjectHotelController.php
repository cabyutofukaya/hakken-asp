<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\SubjectHotelStoretRequest;
use App\Http\Requests\Staff\SubjectHotelUpdateRequest;
use App\Models\SubjectHotel;
use App\Services\SubjectHotelService;
use DB;
use Exception;
use Gate;
use Hashids;
use Illuminate\Http\Request;
use Log;

class SubjectHotelController extends AppController
{
    public function __construct(SubjectHotelService $subjectHotelService)
    {
        $this->subjectHotelService = $subjectHotelService;
    }

    /**
     * ホテル科目作成処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SubjectHotelStoretRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new SubjectHotel]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->all(); // カスタムフィールドがあるのでvalidatedではなくallで取得
        $input['agency_id'] = auth('staff')->user()->agency_id;

        try {
            $subjectHotel = DB::transaction(function () use ($input) {
                return $this->subjectHotelService->create($input);
            });

            if ($subjectHotel) {
                return redirect()->route('staff.master.subject.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL')])->with('success_message', "「{$subjectHotel->name}」を登録しました");
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }

    /**
     * ホテル科目編集form
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $subjectHotel = $this->subjectHotelService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('view', [$subjectHotel]);
        if (!$response->allowed()) {
            abort(403);
        }
        return view('staff.subject.edit.hotel', compact('subjectHotel'));
    }

    /**
     * ホテル科目更新処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(SubjectHotelUpdateRequest $request, $agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $old = $this->subjectHotelService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('update', [$old]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->all(); // カスタムフィールドがあるのでvalidatedではなくallで取得
        if ($new = $this->subjectHotelService->update($decodeId, $input)) {
            return redirect()->route('staff.master.subject.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL')])->with('success_message', "「{$new->name}」を更新しました");
        }
        abort(500);
    }

    /**
     * ホテル科目削除
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $subjectHotel = $this->subjectHotelService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('delete', [$subjectHotel]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }
        
        if ($this->subjectHotelService->delete((int)$decodeId, true) > 0) {
            return redirect()->route('staff.master.subject.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL')])->with('decline_message', "「{$subjectHotel->name}」を削除しました");
        }
        abort(400); // Bad Request
    }
}
