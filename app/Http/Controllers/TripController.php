<?php

namespace App\Http\Controllers;

use App\Models\DriverReport;
use App\Models\Trip;
use App\Repository\TripRepository;
use App\Services\TripService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function __construct(private readonly TripRepository $tripRepository)
    {
    }

    public function index(Request $request)
    {
        $orderBy = $request->get('order', 'trip_id');
        $trips = $this->tripRepository->getList($orderBy);

        return view('trips', ['trips' => $trips]);
    }

    public function search(Request $request)
    {
        $search = $request->get('search');
        $trips = $this->tripRepository->searchTrip($search);

        return view('trips', ['trips' => $trips]);
    }

    public function import(TripService $tripService, Request $request): RedirectResponse
    {
        /**
         * Весь флоу от запроса до респонза должен выглядеть так:
         * 1. Приходит реквест в метод контроллера
         * 2. В идеале проводить вализацию на уровне реквест-класса
         *    (при инициализации реквеста-класса как аргумета метода в контроллере)
         *    Например: ExampleRequest. И в нем происходит вся валидация входных данных
         * 3. Потом ExampleRequest передаётся в сервис. Там происходит необходимая логика и обращение к БД
         * 4. Обращение к БД (к моделям) должно производиться через отдельные классы (репозитории)
         * 5. Сервис уже дёргает методы репозиторедв.
         * 6. Необходимые данные поднимаются обратно вверх.
         * 6. И возвращаются респонз данные контроллером
         *
         */

        $request->validate([
            'file' => 'required|mimes:xls,xlsx,csv'
        ]);

        if (!$request->file('file')->isValid()) {
            return back()->withErrors(['file' => 'Invalid file format. Please upload a valid Excel (xls, xlsx) or CSV file.']);
        }

        Trip::truncate();
        DriverReport::truncate();

        $file = $request->file('file');
        $fileContents = file($file->getPathname());

        foreach ($fileContents as $key => $line) {
            if ($key == 0) {
                continue;
            }

            $line = explode(',', $line);
            $tripLine = $tripService->getTripFromCsvLine($line);

            $this->tripRepository->create($tripLine);
        }

        return back()->with('success', 'Data Imported successfully.');
    }

    public function calculate(TripService $tripService): RedirectResponse
    {
        $trips = Trip::all();
        $totalTime = $tripService->calculateTotalTime($trips);

        foreach ($totalTime as $driverId => $timeData) {
            DriverReport::create([
                'driver_id' => $driverId,
                'total_minutes_with_passenger' => $tripService->getWorkingMinutes($timeData),
            ]);
        }

        return back()->with('success', 'Data Calculated successfully.');
    }
}
