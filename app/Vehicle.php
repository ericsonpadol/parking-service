<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    //configuration
    protected $table = 'vehicles';
    protected $primaryKey = 'id';
    protected $fillable = [
        'plate_number',
        'color',
        'model',
        'brand',
        'users_id'
    ];
    protected $hidden = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function maker() {
        return $this->belongsTo('App\User');
    }
}
