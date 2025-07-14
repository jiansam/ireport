<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;

class Setting extends BaseModel
{
    use HasFactory;
    protected $fillable = [
        'member_id',
        'key',
        'value',
        'type',
        'description',
        'updated_by',
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
