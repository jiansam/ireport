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


            $prefix      = uniqid();
            $randString  = str_pad(mt_rand(0, 9999999), 7, '0', STR_PAD_LEFT);
            $id = $prefix.$randString;

            $model->setAttribute($model->getKeyName(), $id  );
        });
    }
}