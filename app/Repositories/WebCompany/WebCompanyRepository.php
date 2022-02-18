<?php
namespace App\Repositories\WebCompany;

use App\Models\WebCompany;

class WebCompanyRepository implements WebCompanyRepositoryInterface
{
    /**
    * @param object $webCompany
    */
    public function __construct(WebCompany $webCompany)
    {
        $this->webCompany = $webCompany;
    }

    /**
     * 検索して1件取得
     */
    public function findWhere(array $where, array $with=[], array $select=[], bool $getDeleted = false) : ?WebCompany
    {
        $query = $this->webCompany;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        foreach ($where as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, $val);
        }
        return $query->first();
    }

    /**
     * アップサート
     */
    public function updateOrCreate(array $attributes, array $values = []) : WebCompany
    {
        return $this->webCompany->updateOrCreate(
            $attributes,
            $values
        );
    }
}
