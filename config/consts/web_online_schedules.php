<?php

return [  
    // リクエストステータス
    'ONLINE_REQUEST_STATUS_REQUEST' => 1, // 相談依頼
    'ONLINE_REQUEST_STATUS_CHANGE' => 2, // 変更依頼
    'ONLINE_REQUEST_STATUS_CONSENT' =>  3, // 承諾
    'ONLINE_REQUEST_STATUS_CANCEL' =>  4, // キャンセル
    'ONLINE_REQUEST_STATUS_LIST' => [
        'online_request_status_request' => 1,
        'online_request_status_change' => 2,
        'online_request_status_consent' => 3,
        'online_request_status_cancel' => 4,
    ],
    'SENDER_TYPE_USER' => 'user',
    'SENDER_TYPE_CLIENT' => 'client',
    'SENDER_TYPE_LIST' => [
        'sender_type_user' => 'user',
        'sender_type_client' => 'client',
    ],
];