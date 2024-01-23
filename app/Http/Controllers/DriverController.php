<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Services\DriverService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $orderBy = $request->get('order', 'driver_id');
        $drivers = Driver::orderBy($orderBy)->paginate(10);
        return view('drivers', ['drivers' => $drivers]);
    }

    public function search(Request $request)
    {
        $search = $request->get('search');
        $drivers = Driver::where('driver_id', 'like', '%' . $search . '%')
            ->orWhere('total_minutes_with_passenger', 'like', '%' . $search . '%')
            ->paginate(10);

        return view('drivers', ['drivers' => $drivers]);
    }

    public function export(DriverService $driverService): StreamedResponse
    {
        $driversReport = Driver::all();
        $csvFileName = 'drivers-report.csv';

        $headers = $driverService->getCsvHeaders($csvFileName);

        $handle = $driverService->initializeCsvHandle();
        $driverService->writeCsvHeader($handle);
        $driverService->writeDriversDataToCsv($handle, $driversReport);

        return $driverService->downloadCsvResponse($handle, $csvFileName, $headers);
    }
}
