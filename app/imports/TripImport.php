<?php

namespace App\imports;

use App\Models\Trip;
use Maatwebsite\Excel\Concerns\ToModel;

class TripImport implements ToModel
{
    public function model(array $row)
    {
        return new Trip([
            'id' => $row[0], // 'id' => '1
            'driver_id' => $row[1], // 'driver_id' => '1
            'pickup_time' => $row[2], // 'pickup_time' => '2016-01-01 00:00:00
            'dropoff_time' => $row[3], // 'dropoff_time' => '2016-01-01 00:00:00
        ]);
    }

}
