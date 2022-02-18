<?php

namespace App\Services;

use App\Repositories\WebProposal\WebProposalRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\WebProposal;

/**
 * Web提案のサービスクラス。
 */
class WebProposalService
{
    public function __construct(
        WebProposalRepository $webProposalRepository
    ) {
        $this->webProposalRepository = $webProposalRepository;
    }
}
