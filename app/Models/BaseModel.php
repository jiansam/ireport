<?php
namespace App\Models;


use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use HasDateTimeFormatter;
    use UID;
    protected $guarded = [];
    protected $keyType = 'string';
}

