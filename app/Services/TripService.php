<?php

namespace App\Services;

// часть расчётов здесь можно сделать через использование таких вещей как Collections и Carbon (даты и время), советую тебе очень плотно с ними познакомиться
// ещё одну чатсть можно либо вычислять на лету, либо на уровне базы данных, зависит от нагрузок на приложение.
class TripService
{
    public function calculateTotalTime($trips): array
    {
        $totalTime = [];

        foreach ($trips as $trip) {
            $driverId = $trip['driver_id'];
            $pickupTime = strtotime($trip['pickup_time']);
            $dropOffTime = strtotime($trip['dropoff_time']);

            $this->initializeDriverTotalTime($totalTime, $driverId, $pickupTime, $dropOffTime);
            $this->updateOverlap($totalTime, $driverId, $pickupTime, $dropOffTime);
        }

        return $totalTime;
    }

    public function getWorkingMinutes($timeData): int
    {
        $totalWorkedTimeInSeconds = $timeData['end'] - $timeData['start'] - $timeData['overlap'];
        $totalWorkedTimeInMinutes = $totalWorkedTimeInSeconds / 60;

        return round($totalWorkedTimeInMinutes);
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

    private function updateOverlap(&$totalTime, $driverId, $pickupTime, $dropOffTime): void
    {
        if ($pickupTime >= $totalTime[$driverId]['end']) {
            $previousEndTime = $totalTime[$driverId]['end'];
            $overlapInSeconds = $pickupTime - $previousEndTime;
            $totalTime[$driverId]['overlap'] += $overlapInSeconds;


            $totalTime[$driverId]['end'] = $dropOffTime;
        } else {
            $overlapInSeconds = max(0, $pickupTime - $totalTime[$driverId]['end']);
            $totalTime[$driverId]['overlap'] += $overlapInSeconds;
        }
    }

    public function getTripFromCsvLine(array $line): array
    {
        return [
            'trip_id' => $line[0],
            'driver_id' => $line[1],
            'pickup_time' => $line[2],
            'dropoff_time' => $line[3],
        ];
    }
}
