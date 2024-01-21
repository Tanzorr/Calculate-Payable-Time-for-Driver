<?php

namespace App\Services;

use App\Models\Driver;

class TripService
{
    public function calculateTotalTime($trips): array
    {
        $totalTime = [];

        foreach ($trips as $trip) {
            $driverId = $trip->driver_id;
            $pickupTime = strtotime($trip->pickup_time);
            $dropOffTime = strtotime($trip->dropoff_time);

            $this->initializeDriverTotalTime($totalTime, $driverId, $pickupTime, $dropOffTime);
            $this->updateOverlap($totalTime, $driverId, $pickupTime);
        }

        return $totalTime;
    }

    public function getWorkingMinutes($timeData): int
    {
        return round(($timeData['end'] - $timeData['start'] - $timeData['overlap']) / 60); // Convert seconds to minutes
    }

    private function initializeDriverTotalTime(&$totalTime, $driverId, $pickupTime, $dropOffTime): void
    {
        if (!isset($totalTime[$driverId])) {
            $totalTime[$driverId] = [
                'start' => $pickupTime,
                'end' => $dropOffTime,
                'overlap' => 0,
            ];
        }
    }

    private function updateOverlap(&$totalTime, $driverId, $pickupTime): void
    {
        $previousEndTime = $totalTime[$driverId]['end'];
        $overlap = max(0, $previousEndTime - $pickupTime);
        $totalTime[$driverId]['overlap'] += $overlap / 60; // Convert seconds to minutes

        // Update end time if the current trip extends beyond the previous end time
        $totalTime[$driverId]['end'] = max($previousEndTime, $pickupTime);
    }
}
