<?php

namespace App\Services;

use App\Models\Agency;
use Illuminate\Support\Arr;
use App\Models\DocumentCategory;
use Illuminate\Support\Collection;
use App\Services\DocumentCommonService;
use App\Services\DocumentQuoteService;
use App\Services\DocumentReceiptService;
use App\Services\DocumentRequestService;
use App\Services\DocumentRequestAllService;
use App\Repositories\DocumentCategory\DocumentCategoryRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Traits\DocumentTrait;

class DocumentCategoryService
{
    use DocumentTrait;

    public function __construct(DocumentCategoryRepository $documentCategoryRepository, DocumentCommonService $documentCommonService, DocumentQuoteService $documentQuoteService, DocumentReceiptService $documentReceiptService, DocumentRequestService $documentRequestService, DocumentRequestAllService $documentRequestAllService)
    {
        $this->documentCategoryRepository = $documentCategoryRepository;
        $this->documentCommonService = $documentCommonService;
        $this->documentQuoteService = $documentQuoteService;
        $this->documentReceiptService = $documentReceiptService;
        $this->documentRequestService = $documentRequestService;
        $this->documentRequestAllService = $documentRequestAllService;
    }
    
    /**
     * 帳票カテゴリデータを取得
     *
     * @return Illuminate\Support\Collection
     */
    public function all(array $with=[], string $order="seq", string $direction="asc") : Collection
    {
        return $this->documentCategoryRepository->all($with, $order, $direction);
    }

    /**
     * 管理コードからIDを取得
     */
    public function getIdByCode(string $code) : ?int
    {
        return $this->documentCategoryRepository->getIdByCode($code);
    }

    /**
     * 帳票フォーマットを初期化
     *
     * @param Agency $agency
     */
    public function setDefaults(Agency $agency)
    {
        $documentCommonDefaultId = null; // 共通設定のデフォルト帳票ID

        // 共通設定
        foreach ([
            [
                'name' => 'デフォルト',
                'description' => 'デフォルト設定',
                'code' => config('consts.document_categories.CODE_COMMON_DEFAULT'),
                'setting' => [ // 設定項目は全チェック
                    config('consts.document_commons.ADDRESS_PERSON') => $this->settingFlatAll(config('consts.document_commons.ADDRESS_PERSON_LIST')),
                    config('consts.document_commons.ADDRESS_BUSINESS') => $this->settingFlatAll(config('consts.document_commons.ADDRESS_BUSINESS_LIST')),
                    config('consts.document_commons.COMPANY_INFO')
                    => $this->settingFlatAll(config('consts.document_commons.COMPANY_INFO_LIST')),
                ],
                'company_name' => $agency->company_name,
                'supplement1' => '',
                'supplement2' => '',
                'zip_code' => $agency->zip_code,
                'address1' => $agency->prefecture->name . $agency->address1,
                'address2' => $agency->address2,
                'tel' => $agency->tel,
                'fax' => $agency->fax,
                'agency_id' => $agency->id,
                'undelete_item' => true,
                'seq' => 0,
            ]
        ] as $conf) {
            $res = $this->documentCommonService->create($conf);
            if ($conf['code'] === config('consts.document_categories.CODE_COMMON_DEFAULT')) {
                $documentCommonDefaultId = $res->id;
            }
        }

        // 見積・予約確認書
        foreach ([
            [
                'name' => '見積書',
                'description' => 'デフォルト設定',
                'code' => config('consts.document_categories.CODE_QUOTE_DEFAULT'),
                'setting' => [ // 設定項目は全チェック
                    config('consts.document_quotes.DISPLAY_BLOCK') => $this->settingFlatAll(config('consts.document_quotes.DISPLAY_BLOCK_LIST')),
                    config('consts.document_quotes.RESERVATION_INFO') => $this->settingFlatAll(config('consts.document_quotes.RESERVATION_INFO_LIST')),
                    config('consts.document_quotes.AIR_TICKET_INFO')
                    => $this->settingFlatAll(config('consts.document_quotes.AIR_TICKET_INFO_LIST')),
                    config('consts.document_quotes.BREAKDOWN_PRICE')
                    => $this->settingFlatAll(config('consts.document_quotes.BREAKDOWN_PRICE_LIST')),
                ],
                'title' => '見積書',
                'management_name' => '見積番号',
                'seal' => true,
                'seal_number' => 2,
                'seal_items' => ['項目1','項目2'],
                'document_common_id' => $documentCommonDefaultId,
                'agency_id' => $agency->id,
                'undelete_item' => true,
                'seq' => 0,
            ],
            [
                'name' => '予約確認書',
                'description' => 'デフォルト設定',
                'code' => config('consts.document_categories.CODE_RESERVE_CONFIRM_DEFAULT'),
                'setting' => [ // 設定項目は全チェック
                    config('consts.document_quotes.DISPLAY_BLOCK') => $this->settingFlatAll(config('consts.document_quotes.DISPLAY_BLOCK_LIST')),
                    config('consts.document_quotes.RESERVATION_INFO') => $this->settingFlatAll(config('consts.document_quotes.RESERVATION_INFO_LIST')),
                    config('consts.document_quotes.AIR_TICKET_INFO')
                    => $this->settingFlatAll(config('consts.document_quotes.AIR_TICKET_INFO_LIST')),
                    config('consts.document_quotes.BREAKDOWN_PRICE')
                    => $this->settingFlatAll(config('consts.document_quotes.BREAKDOWN_PRICE_LIST')),
                ],
                'title' => '予約確認書',
                'management_name' => '予約番号',
                'seal' => true,
                'seal_number' => 2,
                'seal_items' => ['項目1','項目2'],
                'document_common_id' => $documentCommonDefaultId,
                'agency_id' => $agency->id,
                'undelete_item' => true,
                'seq' => 1,
            ]
        ] as $conf) {
            $this->documentQuoteService->create($conf);
        }

        // 請求書
        foreach ([
            [
                'name' => 'デフォルト',
                'description' => 'デフォルト設定',
                'code' => config('consts.document_categories.CODE_REQUEST_DEFAULT'),
                'setting' => [ // 設定項目は全チェック
                    config('consts.document_requests.DISPLAY_BLOCK') => $this->settingFlatAll(config('consts.document_requests.DISPLAY_BLOCK_LIST')),
                    config('consts.document_requests.RESERVATION_INFO') => $this->settingFlatAll(config('consts.document_requests.RESERVATION_INFO_LIST')),
                    config('consts.document_requests.AIR_TICKET_INFO')
                    => $this->settingFlatAll(config('consts.document_requests.AIR_TICKET_INFO_LIST')),        
                    config('consts.document_requests.BREAKDOWN_PRICE')
                    => $this->settingFlatAll(config('consts.document_requests.BREAKDOWN_PRICE_LIST')),
                ],
                'title' => '請求書',
                'seal' => true,
                'seal_number' => 2,
                'seal_items' => ['項目1','項目2'],
                'document_common_id' => $documentCommonDefaultId,
                'agency_id' => $agency->id,
                'undelete_item' => true,
                'seq' => 0,
            ],
        ] as $conf) {
            $this->documentRequestService->create($conf);
        }

        // 一括請求書
        foreach ([
            [
                'name' => 'デフォルト',
                'description' => 'デフォルト設定',
                'code' => config('consts.document_categories.CODE_REQUEST_ALL_DEFAULT'),
                'setting' => [ // 設定項目は全チェック
                    config('consts.document_request_alls.DISPLAY_BLOCK') => $this->settingFlatAll(config('consts.document_request_alls.DISPLAY_BLOCK_LIST')),
                    config('consts.document_request_alls.RESERVATION_INFO') => $this->settingFlatAll(config('consts.document_request_alls.RESERVATION_INFO_LIST')),
                    config('consts.document_request_alls.BREAKDOWN_PRICE')
                    => $this->settingFlatAll(config('consts.document_request_alls.BREAKDOWN_PRICE_LIST')),
                ],
                'title' => '一括請求書',
                'document_common_id' => $documentCommonDefaultId,
                'agency_id' => $agency->id,
                'undelete_item' => true,
                'seq' => 0,
            ],
        ] as $conf) {
            $this->documentRequestAllService->create($conf);
        }

        // 領収書
        foreach ([
            [
                'name' => 'デフォルト',
                'description' => 'デフォルト設定',
                'code' => config('consts.document_categories.CODE_RECEIPT_DEFAULT'),
                'proviso' => "但\n上記正に領収いたしました",
                'title' => '領収書',
                'document_common_id' => $documentCommonDefaultId,
                'agency_id' => $agency->id,
                'undelete_item' => true,
                'seq' => 0,
            ]
        ] as $conf) {
            $this->documentReceiptService->create($conf);
        }
    }
}
