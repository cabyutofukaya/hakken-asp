<?php
namespace App\Providers;

use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class StaffAuthServiceProvider extends \Illuminate\Auth\EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) ||
            (count($credentials) === 1 &&
            array_key_exists('password', $credentials))) {
            return;
        }

        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // Eloquent User "model" that will be utilized by the Guard instances.
        $query = $this->newModelQuery();

        // 所属する旅行会社のstatus状態をチェック
        $query->whereHas('agency', function ($q) {
            $q->where('status', config("consts.agencies.STATUS_MAIN_REGISTRATION")); // 有効
        });

        foreach ($credentials as $key => $value) {
            if (Str::contains($key, 'password')) {
                continue;
            }

            if (is_array($value) || $value instanceof Arrayable) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }
}
