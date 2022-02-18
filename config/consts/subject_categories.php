<?php

return [  
    'DEFAULT_SUBJECT_CATEGORY' => 'option', // 初期選択値
    // 管理コード
    'SUBJECT_CATEGORY_OPTION' => 'option', // オプション科目
    'SUBJECT_CATEGORY_AIRPLANE' => 'airplane', // 航空券科目
    'SUBJECT_CATEGORY_HOTEL' => 'hotel', // ホテル科目
    'SUBJECT_CATEGORY_LIST' => [
        'subject_category_option' => 'option',
        'subject_category_airplane' => 'airplane',
        'subject_category_hotel' => 'hotel',
    ],
    // 税区分
    'ZEI_KBN_DEFAULT' => 10,
    'ZEI_KBN_TAX8' => 8,
    'ZEI_KBN_TAX10' => 10,
    'ZEI_KBN_TAX_FREE' => 'tf',
    'ZEI_KBN_NON_TAX' => 'nt',
    // 仕入ページなど
    'ZEI_KBN_LIST' => [
        'zei_kbn_tax8' => 8,
        'zei_kbn_tax10' => 10,
        'zei_kbn_tax_free' => 'tf',
        'zei_kbn_non_tax' => 'nt',
    ],
    // 書類用
    'DOCUMENT_ZEI_KBN_LIST' => [
        'zei_kbn_tax8' => 8,
        'zei_kbn_tax10' => 10,
        'zei_kbn_tax_free' => 'tf',
        'zei_kbn_non_tax' => 'nt',
    ],
];