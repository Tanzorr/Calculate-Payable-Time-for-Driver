<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Trip;
use App\Services\TripService;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index()
    {
        $tirps = Trip::paginate(10);
        return view('trips', ['trips' => $tirps]);
    }


    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx,csv'
        ]);

        $file = $request->file('file');

        $fileContents = file($file->getPathname());

        foreach ($fileContents as $key => $line) {
            if ($key == 0) {
                continue;
            }
            $line = explode(',', $line);
            Trip::create([
                'trip_id' => $line[0],
                'driver_id' => $line[1],
                'pickup_time' => $line[2],
                'dropoff_time' => $line[3],
            ]);
        }

        return back()->with('success', 'Data Imported successfully.');
    }

    public function calculate(TripService $tripService)
    {
        $trips = Trip::all();
        $totalTime = $tripService->calculateTotalTime($trips);

        foreach ($totalTime as $driverId => $timeData) {
            Driver::create([
                'driver_id' => $driverId,
                'total_minutes_with_passenger' => $tripService->getWorkingMinutes($timeData),
            ]);
        }

        return back()->with('success', 'Data Calculated successfully.');
    }

}
