<?php

namespace App\Repository;

use App\Models\Trip;
use Elastic\Apm\TransactionInterface;

class TripRepository
{
    public function searchTrip(string $searcRequesthData): array // or Collection
    {
        return Trip::where('driver_id', 'like', '%' . $searcRequesthData . '%')
            ->orWhere('trip_id', 'like', '%' . $searcRequesthData . '%')
            ->orWhere('pickup_time', 'like', '%' . $searcRequesthData . '%')
            ->orWhere('dropoff_time', 'like', '%' . $searcRequesthData . '%')
            ->paginate(10);
    }

    public function getList(string $orderBy): array // or Collection
    {
        return Trip::orderBy($orderBy)->paginate(10);
    }

    public function create(array $tripLine): Trip
    {
        return Trip::create($tripLine);
    }
}
