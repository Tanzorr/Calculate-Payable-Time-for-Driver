<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Trip;
use App\Services\TripService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $orderBy = $request->get('order', 'trip_id');
        $trips = Trip::orderBy($orderBy)->paginate(10);

        return view('trips', ['trips' => $trips]);
    }

    public function search(Request $request)
    {
        $search = $request->get('search');
        $trips = Trip::where('driver_id', 'like', '%' . $search . '%')
            ->orWhere('trip_id', 'like', '%' . $search . '%')
            ->orWhere('pickup_time', 'like', '%' . $search . '%')
            ->orWhere('dropoff_time', 'like', '%' . $search . '%')
            ->paginate(10);

        return view('trips', ['trips' => $trips]);
    }


    public function import(TripService $tripService, Request $request): RedirectResponse
    {
        $trips = Trip::paginate(10);

        if (isset($trips)) {
            Trip::truncate();
        }

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
            $tripLine = $tripService->getTripFromCsvLine($line);

            Trip::create($tripLine);
        }

        return back()->with('success', 'Data Imported successfully.');
    }

    public function calculate(TripService $tripService): RedirectResponse
    {
        $drivers = Driver::paginate(10);

        if (isset($drivers)) {
            Driver::truncate();
        }

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
