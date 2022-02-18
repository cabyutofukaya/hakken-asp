<?php

namespace App\Services;

use \Route;
use App\Repositories\ActLog\ActLogRepository;
use Illuminate\Http\Request;

class ActLogService
{
    public function __construct(ActLogRepository $actLogRepository)
    {
        $this->actLogRepository = $actLogRepository;
    }

    public function paginate($limit, $conditions, $andOr=null, $order="id", $orderType="desc")
    {
        $conditions = array_filter($conditions, 'strlen');
        return $this->actLogRepository->paginate($limit, $conditions, $andOr, $order, $orderType);
    }

    public function create(Request $request, int $status) : void
    {
        $user = $request->user();
        $data = [
            'user_id' => $user ? $user->id : null,
            'guard' => $user ? strtolower(class_basename(get_class($user))) : null,
            'route' => Route::currentRouteName(),
            'url' => $request->path(),
            'method' => $request->method(),
            'status' => $status,
            'message' => count($request->toArray()) != 0 ? json_encode($request->toArray()) : null,
            'remote_addr' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        $this->actLogRepository->create($data);
    }
}
