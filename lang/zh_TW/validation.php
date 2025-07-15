<?php

return [
    'regex' => '格式錯誤',
    'email' => 'Email 格式錯誤',
    'custom' => [
        'account' => [
            'regex' => '帳號格式錯誤，請輸入 6~20 位中英文或數字',
        ],
        'tax_id' => [
            'integer' => '統一編號必須為數字',
            'min' => '統一編號必須為 8 碼數字',
            'max' => '統一編號必須為 8 碼數字',
        ],
    ],
];
