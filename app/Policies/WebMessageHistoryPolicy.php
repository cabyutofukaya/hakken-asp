<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\WebMessageHistory;
use App\Models\AppUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class WebMessageHistoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any web_companies.
     *
     * @param  \App\Models\AppUser  $toUser
     * @return mixed
     */
    public function viewAny(AppUser $appUser)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('web_message_histories', config("consts.agency_roles.READ"))){
                return Response::allow();
            }
        }
        return Response::deny('相談履歴の参照権限がありません(403 Forbidden)');
    }
}
