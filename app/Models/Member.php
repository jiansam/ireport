<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Models\BaseModel;

class Member extends BaseModel
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'phone_number',
        'email',
        'address',
        'carrier_num',
        'tax_id',
        'account',
        'password',
        'google_id',
        'status',
        'point',
        'login_time',
        'start_time',
        'end_time',
    ];

    protected $hidden = [
        'password',
    ];
    protected static function boot()
    {
        parent::boot();
    }

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
