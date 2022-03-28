<?php

namespace App\Http\Controllers\Staff\Api;

use App\Models\MailTemplate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MailTemplateService;
use App\Http\Resources\Staff\MailTemplateResource;
use Gate;
use Hashids;
use Log;

class MailTemplateController extends Controller
{
    public function __construct(MailTemplateService $mailTemplateService)
    {
        $this->mailTemplateService = $mailTemplateService;
    }

    public function index($agencyAccount)
    {
        // 認可チェック
        $response = Gate::authorize('viewAny', new MailTemplate);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $limit = request()->get("per_page", 10);

        return MailTemplateResource::collection($this->mailTemplateService->paginateByAgencyAccount(
            $agencyAccount,
            $limit
        ));
    }

    public function destroy($agencyAccount, $encodeId)
    {
        $decodeId = Hashids::decode($encodeId)[0] ?? null;
        $mailTemplate = $this->mailTemplateService->find((int)$decodeId);

        if (!$mailTemplate) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('delete', [$mailTemplate]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }
        
        if($this->mailTemplateService->delete($mailTemplate->id, true)){ // 論理削除
            return response('', 200);
        }
        abort(500);
    }
}
