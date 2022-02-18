<?php

namespace App\Http\Controllers\User\Api;

use Auth;
use Gate;
use App\Http\Controllers\Controller;
use App\Services\SuggestionService;
// use App\Http\Requests\User\SuggestionShowRequest;
use Illuminate\Http\Request;

// 旅行会社からの提案情報
class SuggestionController extends Controller
{
    private $suggestionService;

    public function __construct(SuggestionService $suggestionService)
    {
        $this->suggestionService = $suggestionService;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function show(SuggestionShowRequest $request, $uuid)
    public function show($uuid)
    { 
        if ($suggestion = $this->suggestionService->find($uuid)) {
            // 認可チェック
            Gate::authorize('view', $suggestion);

           return $suggestion;
        }
        abort(404);
    }
}
