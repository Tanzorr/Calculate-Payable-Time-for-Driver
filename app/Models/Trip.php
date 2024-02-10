<?php

namespace App\Models;

use App\Http\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Trip extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'trip_id',
        'driver_id',
        'pickup_time',
        'dropoff_time',
    ];

    public function scopeSearch(Builder|QueryBuilder $query, $search): Builder|QueryBuilder
    {
        return $query->where('driver_id', 'like', '%' . $search . '%')
            ->orWhere('trip_id', 'like', '%' . $search . '%')
            ->orWhere('pickup_time', 'like', '%' . $search . '%')
            ->orWhere('dropoff_time', 'like', '%' . $search . '%');
    }
}
