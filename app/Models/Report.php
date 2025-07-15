<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;

class Report extends BaseModel
{
    use HasFactory;

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }
}
