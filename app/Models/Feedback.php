<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;

class Feedback extends BaseModel
{
    use HasFactory; 
    protected $fillable = [
        'title',
        'status',
        'type',
        'member_id',
        'other',
        'content',
    ];
    protected static function boot()
    {
        parent::boot();
    }
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }
}
