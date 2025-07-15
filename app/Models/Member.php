<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Models\BaseModel;

class Member extends BaseModel
{
    use HasFactory;

    // 付款狀態
    public const STATUS_PAID = '已付款';
    public const STATUS_AUTHORIZED = '已授權';
    public const STATUS_AUTH_FAILED = '授權失敗';
    public const STATUS_OVERDUE = '逾期未付';
    public const STATUS_PAYMENT_FAILED = '付款失敗';

    /**
     * 方案
     * 1. 單次
     * 2. 基礎
     * 3. 高用量
     */
    public const PLAN_SINGLE = 1;
    public const PLAN_BASIC = 2;
    public const PLAN_HIGH = 3;

    protected $hidden = [
        'password',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'member_id', 'id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'member_id', 'id');
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'member_id', 'id');
    }
    public function setting()
    {
        return $this->hasOne(Setting::class, 'member_id', 'id');
    }
}
