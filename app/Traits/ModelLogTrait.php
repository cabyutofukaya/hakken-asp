<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use App\Models\ModelLog;

trait ModelLogTrait
{
    // public static function saveModelLog($types = ['created', 'updated', 'deleted', 'retrieved'])
    // {
    public static function saveModelLog($types = ['created', 'updated', 'deleted'])
    {
        foreach ($types as $type) {
            forward_static_call([__CLASS__, $type], function ($model) use ($type) {
                // if ($user = auth()->user()) {
                    $user = auth()->user() ? auth()->user() : null; // $user===null はAPIによる実行処理など
                    $guard = $user ? strtolower(class_basename(get_class($user))) : null;

                    $modelLog = new ModelLog();
                    $modelLog->model = get_class($model);
                    $modelLog->model_id = $model->id;
                    $modelLog->guard = $guard;
                    $modelLog->user_id = optional($user)->id;
                    $modelLog->operation_type = $type;
                    $modelLog->message = $model->isDirty() ? json_encode(collect($model->getDirty())->except(['updated_at'])) : null;
                    $modelLog->save();
                // }
            });
        }
    }

    // Relationship
    public function model_logs()
    {
        return $this->hasMany(ModelLog::class, 'model_id', 'id')
            ->where('model', __CLASS__);
    }
}
