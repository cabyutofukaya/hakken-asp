<?php

namespace App\Repositories\MailTemplate;

use App\Models\MailTemplate;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface MailTemplateRepositoryInterface
{
    public function find(int $id, array $select): MailTemplate;

    public function paginateByAgencyId(int $agencyId, int $limit, array $select) : LengthAwarePaginator;

    public function create(array $data): MailTemplate;

    public function update(int $id, array $data): MailTemplate;

    public function delete(int $id, bool $isSoftDelete): bool;
}
