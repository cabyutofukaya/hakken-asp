<?php

namespace App\Services;

use App\Models\MasterDirection;
use App\Repositories\MasterDirection\MasterDirectionRepository;
use App\Traits\HasManyGenTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Yaml;

class MasterDirectionService
{
    use HasManyGenTrait;

    public function __construct(MasterDirectionRepository $masterDirectionRepository)
    {
        $this->masterDirectionRepository = $masterDirectionRepository;
    }

    /**
     * 方面コードからIDを取得
     */
    public function getIdByCode(string $code) : ?int
    {
        return $this->masterDirectionRepository->getIdByCode($code);
    }

    /**
     * Web連携方面を取得
     */
    public function getWebDirections() : array
    {
        return Yaml::parse(
            file_get_contents(config_path('consts/web_area.yml'))
        );
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
        if (!$this->masterDirectionRepository->getIdByCode($code)) {
            $params['uuid'] = Str::uuid(); // 新規登録時のみuuidをセット
        }

        return $this->masterDirectionRepository->updateOrCreate(
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
        return $this->masterDirectionRepository->deleteExceptionGenKey($genKey);
    }
}
