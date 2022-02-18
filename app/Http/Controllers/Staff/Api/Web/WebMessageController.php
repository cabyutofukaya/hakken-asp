<?php

namespace App\Http\Controllers\Staff\Api\Web;

use App\Models\WebMessage;
use App\Events\WebMessageReadEvent;
use App\Events\WebMessageSendEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\WebMessageCheckReadRequest;
use App\Http\Requests\Staff\WebMessageReadRequest;
use App\Http\Requests\Staff\WebMessageStoreRequest;
use App\Http\Resources\Staff\WebMessage\IndexResource;
use App\Http\Resources\Staff\WebMessage\ReadResource;
use App\Services\WebMessageService;
use App\Services\WebReserveEstimateService;
use Gate;
use Illuminate\Http\Request;

class WebMessageController extends Controller
{
    public function __construct(WebMessageService $webMessageService, WebReserveEstimateService $webReserveEstimateService)
    {
        $this->webMessageService = $webMessageService;
        $this->webReserveEstimateService = $webReserveEstimateService;
    }

    /**
     * メッセージ一覧
     */
    public function index(Request $request, string $agencyAccount, int $reserveId)
    {
        $reserve = $this->webReserveEstimateService->find($reserveId);

        // 認可チェック。一応、reservesレコードに対する表示権限でチェック
        $response = Gate::inspect('viewAny', [new WebMessage, $reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        list($messages, $readIds) = $this->webMessageService->setReadAndGetMessages($reserve->id, $request->input('last_id', null), (int)$request->input('limit'), "created_at", "asc");

        // 既読処理後イベント
        event(new WebMessageReadEvent($readIds));

        
        // さらに古いメッセージがあるか否か
        $isOlderThenMessage = $this->webMessageService->isExistsOlderThenId($messages->min("id"), $reserve->id);

        return [
            'data' => IndexResource::collection($messages),
            'next_page' => $isOlderThenMessage,
        ];
    }

    /**
     * メッセージ作成
     */
    public function store(WebMessageStoreRequest $request, string $agencyAccount, int $reserveId)
    {
        $reserve = $this->webReserveEstimateService->find($reserveId);

        $response = Gate::inspect('create', [new WebMessage, $reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // // 一応、reservesレコードに対する表示権限でチェック
        // $response = Gate::inspect('view', [$reserve]);
        // if (!$response->allowed()) {
        //     abort(403, $response->message());
        // }

        $staff = auth('staff')->user();

        if ($message = $this->webMessageService->create([
            'agency_id' => $reserve->agency_id,
            'reserve_id' => $reserve->id,
            'senderable_type' => get_class($staff),
            'senderable_id' => $staff->id,
            'message' => $request->input("message"),
            'send_at' => $request->input("send_at"),
        ])) {
            //　メッセージ作成イベント(→ユーザー側の未読数を更新等)
            event(new WebMessageSendEvent($message));
            
            return new IndexResource($message);
        }
        abort(500);
    }

    /**
     * 既読処理
     */
    public function read(WebMessageReadRequest $request, string $agencyAccount, int $reserveId)
    {
        // $reserve = $this->webReserveEstimateService->find($reserveId);

        // // 認可チェック。一応、reservesレコードに対する表示権限でチェック
        // $response = Gate::inspect('view', [$reserve]);
        // if (!$response->allowed()) {
        //     abort(403, $response->message());
        // }
        
        $id = $request->input("id");
        $message = $this->webMessageService->find($id);

        $response = Gate::inspect('update', [$message]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        if ($this->webMessageService->read($id)) {

            // 既読処理後イベント
            event(new WebMessageReadEvent([$id]));

            return response('', 200);
        }
    }

    /**
     * パラメータで渡されたID配列に対する
     * 既読状況を返す
     */
    public function checkRead(WebMessageCheckReadRequest $request, string $agencyAccount, int $reserveId)
    {
        $result = $this->webMessageService->getByIds($request->input('ids', []), [], ['id','read_at']);

        return ReadResource::collection($result);
    }
}
