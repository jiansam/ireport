<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;

class Order extends BaseModel
{
    use HasFactory;

    public const STATUS_PAY = 1; //付款
    public const STATUS_NOT_PAY = 2; //未付款
    public const STATUS_AUTHORIZE = 3; //授權
    public const STATUS_NOT_AUTHORIZE = 4;//尚未授權
    public const STATUS_EXPIRED  = 5;//逾期
    public const STATUS_FAIL = 6;//付款失敗


    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id', 'id');
    }
}
