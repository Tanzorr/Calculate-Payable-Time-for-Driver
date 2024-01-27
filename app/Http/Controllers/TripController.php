<?php

namespace App\Http\Controllers;

use App\Models\DriverReport;
use App\Models\Trip;
use App\Services\TripService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TripController extends Controller
{
    private TripService $tripService;

    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }

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


    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx,csv'
        ]);

        if ($request->file('file')->isValid()) {
            Trip::truncate();
            DriverReport::truncate();

            $file = $request->file('file');
            $fileContents = file($file->getPathname());

            foreach ($fileContents as $key => $line) {
                if ($key == 0) {
                    continue;
                }

                $line = explode(',', $line);
                $tripLine = $this->tripService->getTripFromCsvLine($line);

                Trip::create($tripLine);
            }

            return back()->with('success', 'Data Imported successfully.');
        } else {
            return back()->withErrors(['file' => 'Invalid file format. Please upload a valid Excel (xls, xlsx) or CSV file.']);
        }
    }

    public function calculate(): RedirectResponse
    {
        $trips = Trip::all();
        $totalTime = $this->tripService->calculateTotalTime($trips);

        foreach ($totalTime as $driverId => $timeData) {
            DriverReport::create([
                'driver_id' => $driverId,
                'total_minutes_with_passenger' => $this->tripService->getWorkingMinutes($timeData),
            ]);
        }

        return back()->with('success', 'Data Calculated successfully.');
    }
}
