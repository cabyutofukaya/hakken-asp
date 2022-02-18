<?php

/**
 * トークン用の設定
 */
return [

    //有効期限の設定
    'expire' => [

        // デフォルト15分
        'accessToken' => env('ACCESS_TOKEN_EXPIRATION_SECONDS', 900),

        //デフォルト4週間
        'refreshToken' => env('REFRESH_TOKEN_EXPIRATION_SECONDS', 2419200),
    ]
];
