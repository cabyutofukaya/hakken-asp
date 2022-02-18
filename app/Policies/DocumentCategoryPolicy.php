<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\DocumentCommon;
use App\Models\AppUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class DocumentCategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any document_commons.
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
            if($appUser->isApproval('document_categories', config("consts.agency_roles.READ"))){
                return Response::allow();
            }
        }
        return Response::deny('システム設定の参照権限がありません(403 Forbidden)');
    }
}
