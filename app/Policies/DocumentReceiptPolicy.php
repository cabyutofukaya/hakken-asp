<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\DocumentReceipt;
use App\Models\AppUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class DocumentReceiptPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any document_receipts.
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
            if($appUser->isApproval('document_receipts', config("consts.agency_roles.READ"))){
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
    public function view(AppUser $appUser, DocumentReceipt $documentReceipt)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('document_receipts', config("consts.agency_roles.READ")) && $documentReceipt->agency_id == $appUser->agency_id){
                return Response::allow();
            }
        }
        return Response::deny('システム設定の参照権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can create document_receipts.
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
            if($appUser->isApproval('document_receipts', config("consts.agency_roles.CREATE"))){
                return Response::allow();
            }
        }
        return Response::deny('システム設定の登録権限がありません(403 Forbidden)');
    }

    /**
     * Determine whether the user can update the document_receipts.
     *
     * @param  \App\Models\AppUser  $user
     * @param  \App\MailTemplate  $user
     * @return mixed
     */
    public function update(AppUser $appUser, DocumentReceipt $documentReceipt)
    {
        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('document_receipts', config("consts.agency_roles.UPDATE")) && $documentReceipt->agency_id == $appUser->agency_id){
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
    public function delete(AppUser $appUser, DocumentReceipt $documentReceipt)
    {
        if($documentReceipt->is_default) return Response::deny('許可されていないリクエストです(403 Forbidden)'); // デフォルト設定は削除不可

        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('document_receipts', config("consts.agency_roles.DELETE")) && $documentReceipt->agency_id == $appUser->agency_id && !$documentReceipt->undelete_item){
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
    public function restore(AppUser $appUser, DocumentReceipt $documentReceipt)
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
    public function forceDelete(AppUser $appUser, DocumentReceipt $documentReceipt)
    {
        if($documentReceipt->is_default) return Response::deny('許可されていないリクエストです(403 Forbidden)'); // デフォルト設定は削除不可

        $model = class_basename(get_class($appUser));
        if ($model === 'Admin') {
            return Response::allow();
        } elseif ($model === 'Staff') {
            if($appUser->isApproval('document_receipts', config("consts.agency_roles.DELETE")) && $documentReceipt->agency_id == $appUser->agency_id && !$documentReceipt->undelete_item){
                return Response::allow();
            }
        }
        return Response::deny('システム設定の削除権限がありません(403 Forbidden)');
    }
}
