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
                'pickup_time' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
                'dropoff_time' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
            ],
            [
                'driver_id' => $this->faker->randomNumber(),
                'pickup_time' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
                'dropoff_time' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
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
        // Arrange
        $timeData = [
            'start' => strtotime($this->faker->dateTime()->format('Y-m-d H:i:s')),
            'end' => strtotime($this->faker->dateTime()->format('Y-m-d H:i:s')),
            'overlap' => $this->faker->numberBetween(0, 30), // in minutes
        ];

        // Act
        $result = $this->tripService->getWorkingMinutes($timeData);

        // Assert
        $this->assertIsInt($result);
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
