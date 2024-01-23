<?php

namespace Tests\Unit;

use App\Services\TripService;
use Tests\TestCase;
use Faker\Factory as FakerFactory;

class TripServiceTest extends TestCase
{
    private $tripService;
    private $faker;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an instance of TripService once
        $this->tripService = new TripService();

        // Initialize Faker
        $this->faker = FakerFactory::create();
    }

    public function testCalculateTotalTime()
    {
        // Arrange
        $trips = [
            [
                'driver_id' => $this->faker->randomNumber(),
                'pickup_time' => $this->faker->dateTimeBetween('-1 day', 'now')->format('Y-m-d H:i:s'), // Ensure pickup time is earlier
                'dropoff_time' => $this->faker->dateTimeBetween('now', '+1 day')->format('Y-m-d H:i:s'), // Ensure dropoff time is later
            ],
            [
                'driver_id' => $this->faker->randomNumber(),
                'pickup_time' => $this->faker->dateTimeBetween('-1 day', 'now')->format('Y-m-d H:i:s'), // Ensure pickup time is earlier
                'dropoff_time' => $this->faker->dateTimeBetween('now', '+1 day')->format('Y-m-d H:i:s'), // Ensure dropoff time is later
            ],
        ];

        // Act
        $result = $this->tripService->calculateTotalTime($trips);

        // Assert
        $this->assertCount(count($trips), $result);

        // Check if keys dynamically based on driver IDs
        foreach ($trips as $trip) {
            $driverId = $trip['driver_id'];
            $this->assertArrayHasKey($driverId, $result);

            $this->assertIsArray($result[$driverId]);
            $this->assertArrayHasKey('start', $result[$driverId]);
            $this->assertArrayHasKey('end', $result[$driverId]);
            $this->assertArrayHasKey('overlap', $result[$driverId]);

            $this->assertIsInt($result[$driverId]['start']);
            $this->assertIsInt($result[$driverId]['end']);

            // Check if overlap is a float or an int
            $this->assertIsNumeric($result[$driverId]['overlap'], 'Overlap should be of type float or int. Actual type: ' . gettype($result[$driverId]['overlap']));


            $this->assertGreaterThanOrEqual(0, $result[$driverId]['start']);
            $this->assertGreaterThanOrEqual($result[$driverId]['start'], $result[$driverId]['end']);
        }
    }

    public function testGetWorkingMinutes()
    {
        // Case 1: Valid timeData
        $validTimeData = [
            'start' => strtotime('2022-01-01 12:00:00'),
            'end' => strtotime('2022-01-01 14:30:00'),
            'overlap' => 15 * 60, // in seconds
        ];
        $result1 = $this->tripService->getWorkingMinutes($validTimeData);
        $this->assertIsInt($result1);

        // Case 2: Negative overlap (should be treated as 0)
        $negativeOverlapTimeData = [
            'start' => strtotime('2022-01-01 08:00:00'),
            'end' => strtotime('2022-01-01 10:00:00'),
            'overlap' => 30 * 60, // in seconds
        ];
        $result2 = $this->tripService->getWorkingMinutes($negativeOverlapTimeData);
        $this->assertIsInt($result2);
        $this->assertEquals(90, $result2); // Expecting 120 minutes (2 hours)

        // Case 3: Large overlap
        $largeOverlapTimeData = [
            'start' => strtotime('2022-01-01 18:00:00'),
            'end' => strtotime('2022-01-01 20:00:00'),
            'overlap' => 120 * 60, // in seconds
        ];
        $result3 = $this->tripService->getWorkingMinutes($largeOverlapTimeData);
        $this->assertIsInt($result3);
        $this->assertEquals(0, $result3); // Expecting 0 minutes (negative overlap treated as 0)

        // Case 4: Random timeData generated using faker
        $randomTimeData = [
            'start' => strtotime($this->faker->dateTime()->format('Y-m-d H:i:s')),
            'end' => strtotime($this->faker->dateTime()->format('Y-m-d H:i:s')),
            'overlap' => $this->faker->numberBetween(0, 30) * 60, // in seconds
        ];
        $result4 = $this->tripService->getWorkingMinutes($randomTimeData);
        $this->assertIsInt($result4);
    }

    public function testGetTripFromCsvLine()
    {
        // Arrange
        $line = [
            $this->faker->randomNumber(),
            $this->faker->randomNumber(),
            $this->faker->dateTime()->format('Y-m-d H:i:s'),
            $this->faker->dateTime()->format('Y-m-d H:i:s'),
        ];

        // Act
        $result = $this->tripService->getTripFromCsvLine($line);

        // Assert
        $this->assertIsArray($result);
    }
}
