<?php
namespace App\Repositories\MailTemplate;

use App\Models\MailTemplate;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MailTemplateRepository implements MailTemplateRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(MailTemplate $mailTemplate)
    {
        $this->mailTemplate = $mailTemplate;
    }

    /**
     * 当該IDを取得
     *
     * データがない場合は 404ステータス
     *
     * @param int $id
     */
    public function find(int $id, array $select = []): MailTemplate
    {
        return $select ? $this->mailTemplate->select($select)->findOrFail($id) : $this->mailTemplate->findOrFail($id);
    }

    /**
     * ページネーション で取得（ID用）
     *
     * @var $limit
     * @return object
     */
    public function paginateByAgencyId(int $agencyId, int $limit, array $select) : LengthAwarePaginator
    {
        $query = $this->mailTemplate;
        $query = $select ? $query->select($select) : $query;

        return $query->where('agency_id', $agencyId)->sortable()->paginate($limit);
    }

    public function create(array $data): MailTemplate
    {
        return $this->mailTemplate->create($data);
    }

    public function update(int $id, array $data): MailTemplate
    {
        $mailTemplate = $this->find($id);
        $mailTemplate->fill($data)->save();
        return $mailTemplate;
    }

    /**
     * 削除
     * 
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue
     * @return boolean
     */
    public function delete(int $id, bool $isSoftDelete): bool
    {
        if ($isSoftDelete) {
            $this->mailTemplate->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }
}
