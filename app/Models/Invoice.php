<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;

class Invoice extends BaseModel
{
    use HasFactory;

    /**
     * 可以批量賦值的屬性
     */
    protected $fillable = [
        'order_id',
        'invoice_no',
        'total_amount',
        'carrier_type',
        'carrier_num',
        'buyer_name',
        'buyer_email',
        'buyer_ubn',
        'buyer_address',
        'invoice_date',
        'invoice_status',
        'random_number',
        'created_at',
        'updated_at',
    ];

    /**
     * 屬性類型轉換
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
        'invoice_date' => 'datetime',
        'invoice_status' => 'integer',
    ];

    /**
     * 模型啟動時自動生成 ID
     */
    protected static function boot()
    {
        parent::boot(); // 使用 BaseModel 的 UID 功能
    }

    /**
     * 發票屬於訂單 (多對一關聯)
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * 透過訂單關聯到會員
     */
    public function member()
    {
        return $this->hasOneThrough(Member::class, Order::class, 'id', 'id', 'order_id', 'member_id');
    }

    /**
     * 載具類型文字轉換
     */
    public function getCarrierTypeTextAttribute()
    {
        $types = [
            'mobile' => '手機條碼',
            'card' => '市民卡',
            'donate' => '愛心捐贈',
            'print' => '紙本發票',
        ];

        return $types[$this->carrier_type] ?? '未知載具';
    }

    /**
     * 發票狀態文字轉換
     */
    public function getInvoiceStatusTextAttribute()
    {
        $statuses = [
            0 => '待開立',
            1 => '已開立',
            2 => '已作廢',
            3 => '已退回',
        ];

        return $statuses[$this->invoice_status] ?? '未知狀態';
    }
}
