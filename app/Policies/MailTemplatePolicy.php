<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\MailTemplate;
use App\Models\AppUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MailTemplatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any mail_templates.
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
            if($appUser->isApproval('mail_templates', config("consts.agency_roles.READ"))){
                return Response::allow();
            }
        }
        return Response::deny('システム設定の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can view the agencies.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(AppUser $appUser, MailTemplate $mailTemplate)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('mail_templates', config("consts.agency_roles.READ")) && $mailTemplate->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('システム設定の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create mail_templates.
     *
     * @param  \App\Models\AppUser  $appUser
     * @return mixed
     */
    public function create(AppUser $appUser)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('mail_templates', config("consts.agency_roles.CREATE"))){
                return Response::allow();
            }
        }
        return Response::deny('システム設定の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the mail_templates.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\MailTemplate  $user
     * @return mixed
     */
    public function update(AppUser $appUser, MailTemplate $mailTemplate)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('mail_templates', config("consts.agency_roles.UPDATE")) && $mailTemplate->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('システム設定の更新権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\AppUser  $appUser
     * @param  \App\MailTemplate  $mailTemplate
     * @return mixed
     */
    public function delete(AppUser $appUser, MailTemplate $mailTemplate)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('mail_templates', config("consts.agency_roles.DELETE")) && $mailTemplate->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('システム設定の削除権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\Ugency  $user
     * @return mixed
     */
    public function restore(AppUser $appUser, MailTemplate $mailTemplate)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the user.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function forceDelete(AppUser $appUser, MailTemplate $mailTemplate)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('mail_templates', config("consts.agency_roles.DELETE")) && $mailTemplate->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('システム設定の削除権限がありません(403 Forbidden)');
    }
}
