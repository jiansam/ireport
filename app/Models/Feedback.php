<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;

class Feedback extends BaseModel
{
    use HasFactory;

    // 狀態
    public const STATUS_UNPROCESSED = 0; // 未處理
    public const STATUS_PROCESSING = 1;  // 處理中
    public const STATUS_PROCESSED = 2;   // 已處理

    // 類型
    public const TYPE_REPORT_MISMATCH = '報告產出不符合預期';
    public const TYPE_KEY_FINDINGS = 'Key Findings 欄位問題';
    public const TYPE_CLINICAL_HISTORY = 'Clnical History 欄位問題';
    public const TYPE_PREVIOUS_REPORT = 'Previous Report 欄位問題';
    public const TYPE_TEMPLATE = 'Template 欄位問題';
    public const TYPE_REPORT = 'Report 欄位問題';
    public const TYPE_HOT_KEYS = 'Hot Keys 功能問題';
    public const TYPE_SAVE_HISTORY = '報告儲存與歷史紀錄查詢問題';
    public const TYPE_SPEED = '報告產生速度問題';
    public const TYPE_OTHER = '其他';
    
    use HasFactory;

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }
}
