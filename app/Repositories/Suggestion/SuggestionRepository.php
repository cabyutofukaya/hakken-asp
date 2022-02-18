<?php
namespace App\Repositories\Suggestion;

use App\Models\Suggestion;

class SuggestionRepository implements SuggestionRepositoryInterface
{
    protected $suggestion;

    /**
    * @param object $suggestion
    */
    public function __construct(Suggestion $suggestion)
    {
        $this->suggestion = $suggestion;
    }

    public function find(string $id)
    {
        return $this->suggestion->find($id);
    }
}