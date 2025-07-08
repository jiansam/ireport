<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

trait UID{

    public static function boot()
    
    {
        parent::boot();
        
        static::creating(function (Model $model) {
            
            $model->setKeyType('string');
            
            $model->setIncrementing(false);
            
            $model->setAttribute($model->getKeyName(),  uniqid(mt_rand()));
        });
    }
}