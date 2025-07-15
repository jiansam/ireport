<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;

class Invoice extends BaseModel
{
    use HasFactory;

    // 發票樣式
    public const TYPE_TWO_PART = 2;   // 二聯式發票
    public const TYPE_THREE_PART = 3; // 三聯式發票 

    // 載具類型
    public const CARRIER_TYPE_MOBILE = 'mobile'; // 手機條碼載具
    public const CARRIER_TYPE_CARD = 'card'; // 卡片載具
    public const CARRIER_TYPE_DONATE = 'donate'; // 愛心捐贈載具

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
    public function member()
    {
        return $this->hasOneThrough(Member::class, Order::class, 'id', 'id', 'order_id', 'member_id');
    }

}
