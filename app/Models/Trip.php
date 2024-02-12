<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/*
* @mixin \Eloquent
**/
class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'driver_id',
        'pickup_time',
        'dropoff_time',
    ];
}
