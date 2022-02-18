<?php
namespace App\Repositories\Chat;

use App\Models\Chat;

class ChatRepository implements ChatRepositoryInterface
{
    protected $chat;

    /**
    * @param object $chat
    */
    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    public function paginateBySuggestionId(string $sid, int $limit, array $with)
    {
        return $this->chat->with($with)->where('suggestion_id', $sid)->paginate($limit);
    }
}