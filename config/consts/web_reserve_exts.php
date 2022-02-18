<?php

return [  
    // 見積ステータス
    'ESTIMATE_STATUS_CONSULT' => 1, // 相談リクエスト
    'ESTIMATE_STATUS_ESTIMATE' => 2, // 見積
    'ESTIMATE_STATUS_RESERVE_REQUEST' =>  3, // 予約依頼
    'ESTIMATE_STATUS_RESERVE_CANCEL' => 4, // 取消
    'ESTIMATE_STATUS_RESERVE_REJECTION' => 5, // 辞退
    'ESTIMATE_STATUS_LIST' => [
        'estimate_status_consult' => 1,
        'estimate_status_estimate' => 2,
        'estimate_status_reserve_request' => 3,
        'estimate_status_reserve_cancel' => 4,
        'estimate_status_reserve_rejection' => 5,
    ],
];