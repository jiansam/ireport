<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Models\BaseModel;

class Member extends BaseModel
{
    use HasFactory;



    /**
     * 1.一般免費
     * 2.試用期
     *  3.年訂閱、
     *  4.月訂閱
     *  5.已購買
     *  6.團購會員
     * @var integer
     */
    public const STATUS_FREE = 1;
    public const STATUS_TEST = 2;
    public const STATUS_YEAR = 3;
    public const STATUS_MONTH = 4;
    public const STATUS_PAY = 5;
    public const STATUS_GROUP=6;

    protected $hidden = [
        'password',
    ];

    /**
     *現行綁定訂單有訂閱或購買才有
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function order()
    {
        return $this->hasOne(Order::class, 'order_id', 'id');
    }

    /**
     *所有訂單
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
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
