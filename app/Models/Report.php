<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;

class Report extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'clinical_history',
        'key_findings',
        'image_analysis',
        'interpretation',
        'report_template',
        'created_at',
        'updated_at',
    ];

    protected static function boot()
    {
        parent::boot();
    }

    /**
     * 報告屬於會員 (多對一關聯)
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }
}
