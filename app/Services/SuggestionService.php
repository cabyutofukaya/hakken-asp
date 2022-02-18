<?php

namespace App\Services;

use App\Repositories\Suggestion\SuggestionRepository;

class SuggestionService
{
    private $suggestionRepository;

    public function __construct(SuggestionRepository $suggestionRepository)
    {
        $this->suggestionRepository = $suggestionRepository;
    }

    /**
     * 該当IDを一件取得
     *
     * @param string $id ID
     */
    public function find(string $id)
    {
        return $this->suggestionRepository->find($id);
    }
}
