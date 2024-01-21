<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        $drivers = Driver::paginate(10);
        return view('drivers', ['drivers' => $drivers]);
    }

    public function export()
    {
        $driversReport = Driver::all();
        $csvFileName = 'drivers-report.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $handle = fopen('php://temp', 'w');
        fputcsv($handle, ['driver_id', 'total_minutes_with_passenger']);

        rewind($handle);

        fputcsv($handle, ['driver_id', 'total_minutes_with_passenger']);
        foreach ($driversReport as $driver) {
            fputcsv($handle, [$driver->driver_id, round($driver->total_minutes_with_passenger)]);
        }

        fseek($handle, 0);

        return response()->streamDownload(
            fn() => fpassthru($handle),
            $csvFileName,
            $headers
        );
    }
}
