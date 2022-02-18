<?php

namespace App\Repositories\Chat;

interface ChatRepositoryInterface
{
    public function paginateBySuggestionId(string $sid, int $limit, array $with);
}
