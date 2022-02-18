<?php

namespace App\Repositories\WebMessage;

use App\Models\WebMessage;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface WebMessageRepositoryInterface
{
  public function create(array $data) : WebMessage;

  public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): WebMessage;

  public function getByIds(array $ids, array $with=[], array $select=[], $order = "created_at", $direction = "desc", bool $getDeleted = false) : Collection;

  public function getLatestMessages(int $reserveId, int $limit, array $with=[], array $select=[], bool $getDeleted = false) : Collection;

  public function getMessagesOlderThanId(int $reserveId, int $olderThanId, int $limit, array $with=[], array $select=[], bool $getDeleted = false) : Collection;

  public function isExistsOlderThenId(int $id, int $reserveId) : bool;
  
  public function updateForIds(array $ids, array $param);

  public function updateFields(int $id, array $params) : bool;

  public function getAgencyUnreadCountByReserveId(int $reserveId) : int;

  public function getUserUnreadCountByReserveId(int $reserveId) : int;
}
