<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Services\DriverService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DriverController extends Controller
{
    public function index()
    {
        $drivers = Driver::paginate(10);
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
