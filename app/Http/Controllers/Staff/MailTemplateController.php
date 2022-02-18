<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\MailTemplateStoretRequest;
use App\Http\Requests\Staff\MailTemplateUpdateRequest;
use App\Models\MailTemplate;
use App\Services\MailTemplateService;
use Gate;
use Hashids;
use Illuminate\Http\Request;

class MailTemplateController extends AppController
{
    public function __construct(MailTemplateService $mailTemplateService)
    {
        $this->mailTemplateService = $mailTemplateService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', [new MailTemplate]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.mail_template.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // 認可チェック
        $response = Gate::inspect('create', [new MailTemplate]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.mail_template.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MailTemplateStoretRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new MailTemplate]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();
        $input['agency_id'] = auth('staff')->user()->agency_id;

        if ($mailTemplate = $this->mailTemplateService->create($input)) {
            return redirect()->route('staff.system.mail.index', $agencyAccount)->with('success_message', "「{$mailTemplate->name}」を登録しました");
        }
        abort(500);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $mailTemplate = $this->mailTemplateService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('view', [$mailTemplate]);
        if (!$response->allowed()) {
            abort(403);
        }
        return view('staff.mail_template.edit', compact('mailTemplate'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(MailTemplateUpdateRequest $request, $agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $mailTemplate = $this->mailTemplateService->find($decodeId);

        // 認可チェック
        $response = Gate::inspect('update', [$mailTemplate]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();
        if ($mailTemplate = $this->mailTemplateService->update($decodeId, $input)) {
            return redirect()->route('staff.system.mail.index', $agencyAccount)->with('success_message', "「{$mailTemplate->name}」を更新しました");
        }
        abort(500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
