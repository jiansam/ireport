<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;

class Order extends BaseModel
{
    use HasFactory;
    /**付款狀態
     * 1. 付款
     * 2. 未付款
     * 3. 授權
     * 4. 尚未授權
     * 5. 逾期
     * 6. 付款失敗
    */
    public const STATUS_PAY = 1;
    public const STATUS_NOT_PAY = 2;
    public const STATUS_AUTHORIZE = 3;
    public const STATUS_NOT_AUTHORIZE = 4;
    public const STATUS_EXPIRED  = 5;
    public const STATUS_FAIL = 6;

    /**
     * 付款方式
     * 1 綠界
     * 2 Paypal
     * @var integer
     */
    public const PAY_TYPE_GREEN = 1;
    public const PAY_TYPE_PAYPAL = 2;

    /**
    * 1.單次方案
    * 2.基礎方案
    * 3.高用量
    */
    public const PLAN_POINT = 1;
    public const PLAN_NORMAL = 2;
    public const PLAN_HIGHT = 3;

    /**
     * 訂閱費用
     */
    public const PLAN_NORMAL_PRICE_1=495;
    public const PLAN_NORMAL_PRICE_2=999;
    public const PLAN_NORMAL_PRICE_3=9900;
    public const PLAN_HIGHT_PRICE_1=750;
    public const PLAN_HIGHT_PRICE_2=1500;
    public const PLAN_HIGHT_PRICE_3=15000;

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id', 'id');
    }
}
