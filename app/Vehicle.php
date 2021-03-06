<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $date = ['deleted_at'];

    //configuration
    protected $table = 'vehicles';
    protected $tblUsers = 'users';
    protected $primaryKey = 'id';
    protected $fillable = [
        'plate_number',
        'color',
        'model',
        'brand',
        'users_id',
        'id',
        'image_uri'
    ];
    protected $hidden = [
        'created_at',
    ];

    public function maker() {
        return $this->belongsTo('App\User');
    }

}
