<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;

class Order extends BaseModel
{
    use HasFactory;
    protected $fillable = [
        'member_id',
        'status',
        'price',
        'name',
        'phone',
        'email',
        'address',
        'pay_type',
        'point',
        'plan',
        'invoice_id',
        'memo',
    ];
    protected static function boot()
    {
        parent::boot();
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id', 'id');
    }
}
