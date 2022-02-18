<?php

namespace App\Http\Controllers\User\Api;

use Gate;
use App\Services\ChatService;
use App\Services\SuggestionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatController extends Controller
{

    public function __construct(ChatService $chatService, SuggestionService $suggestionService)
    {
        $this->chatService = $chatService;
        $this->suggestionService = $suggestionService;
    }

    /**
     * @param string $consultationId 相談ID
     */
    public function index($uuid)
    {
        $suggestion = $this->suggestionService->find($uuid);
        // 認可チェック
        Gate::authorize('view', $suggestion);

        return $this->chatService->paginateBySuggestionId($uuid, 10);
    }
}
