<?php

namespace App\Services;

use App\Models\MasterArea;
use App\Repositories\MasterArea\MasterAreaRepository;
use App\Repositories\MasterDirection\MasterDirectionRepository;
use App\Traits\HasManyGenTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\AreaSuggestTrait;

class MasterAreaService
{
    use HasManyGenTrait, AreaSuggestTrait;

    public function __construct(MasterAreaRepository $masterAreaRepository, MasterDirectionRepository $masterDirectionRepository)
    {
        $this->masterAreaRepository = $masterAreaRepository;
        $this->masterDirectionRepository = $masterDirectionRepository;
    }
    
    /**
     * 初期表示リストを取得（react-select用）
     * 
     * @param array $default 先頭データ
     */
    public function getDefaultOptions(array $defaultRow=[]) : array
    {
        return $this->masterAreaRepository->getDefaultList()->map(function($item, $key){
            return $this->getSelectRow($item->uuid, $item->code, $item->name);
        })->prepend($defaultRow)->all();
    }

    /**
     * 方面マスターをinsert or update
     *
     * @param string $coce 方面コード
     * @param array $params 保存データ
     * @return bool
     */
    public function upsert(string $code, array $params) : Model
    {
        if (!$this->masterAreaRepository->getIdByCode($code)) {
            $params['uuid'] = Str::uuid(); // 新規登録時のみuuidをセット
        }

        $params['master_direction_uuid'] = $this->masterDirectionRepository->getUuidByCode($params['master_direction_code']); // 方面IDをセット

        return $this->masterAreaRepository->updateOrCreate(
            ['code' => $code],
            $params
        );
    }

    /**
     * 当該世代キー以外のレコード削除
     *
     * @param string $genKey 世代キー
     */
    public function deleteExceptionGenKey(string $genKey): bool
    {
        return $this->masterAreaRepository->deleteExceptionGenKey($genKey);
    }
}
