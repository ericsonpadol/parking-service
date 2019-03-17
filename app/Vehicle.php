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
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'plate_number',
        'color',
        'model',
        'brand',
        'users_id'
    ];
    protected $hidden = [
        'created_at',
    ];

    public function maker() {
        return $this->belongsTo('App\User');
    }

}
