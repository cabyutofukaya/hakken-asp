<?php

namespace App\Services;

use App\Repositories\Chat\ChatRepository;

class ChatService
{
    private $chatRepository;

    public function __construct(ChatRepository $chatRepository)
    {
        $this->chatRepository = $chatRepository;
    }

    /**
     * チャットリストを取得
     *
     * @param int $id チャットID
     */
    public function paginateBySuggestionId(string $sid, int $limit, array $with=[])
    {
        return $this->chatRepository->paginateBySuggestionId($sid, $limit, $with);
    }

}
