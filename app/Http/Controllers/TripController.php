<?php

namespace App\Http\Controllers;

use App\Models\DriverReport;
use App\Models\Trip;
use App\Services\TripService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index(Request $request)
    {
        // старайся не использовать id поля, кроме как порядковых сортировок (по сути порядковая ортировка это сортировка по дате, но они всёравно совпадают)
        $orderBy = $request->get('order', 'trip_id');
        $trips = Trip::orderBy($orderBy)->paginate(10);

        return view('trips', ['trips' => $trips]);
    }

    public function search(Request $request)
    {
        $search = $request->get('search');
        # поиск по driver_id бесполезен, тем более с помощью like. Хорощая практика - когда ID не видно, но есть какой-то полноценный номер или имя
        # если пользователю приходится напрямую использовать ID, значит у системы плохой дизайн
        $trips = Trip::where('driver_id', 'like', '%' . $search . '%')
            ->orWhere('trip_id', 'like', '%' . $search . '%') # та же история. поиска по этому полю не должно быть, но должна быть возможность искать по датам через datetime
            ->orWhere('pickup_time', 'like', '%' . $search . '%') # к примеру можно сделать что-то формата pickup_time BETWEEN date1 AND date2
            ->orWhere('dropoff_time', 'like', '%' . $search . '%') // поиск по дате всегда делатеся через datepicker или что-то похожее, но никогда через обычную строку, для поиска добавь 2 поля типа datepicker
            ->paginate(10); # пагинация по 10 это очень мало, стандарт обычно 25, 50, 75 / 20, 30, 50 и тд.

        return view('trips', ['trips' => $trips]);
    }


    public function import(TripService $tripService, Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx,csv'
        ]);

        if ($request->file('file')->isValid()) {
            Trip::truncate(); // ты делаешь truncate, но не предупреждаешь об этоп пользователя. Эта операция должна быть на отдкльной кнопке с обязательны попапом мол "А вы уверены?"
            DriverReport::truncate(); // на ту же кнопку
            // в целом такие операции не делаются вообще, либо являются ооочень редким функционалом


            $file = $request->file('file');
            $fileContents = file($file->getPathname());


            $keys = [
                'trip_id',
                'driver_id',
                'pickup_time',
                'dropoff_time'
            ];

            // ознакомься с Laravel Collections
            // они прекрасно интегрируются с QueryBuilder и позволяют тебе упрощать некоторые алгоритмы до пары строк.
            $trips = collect($fileContents)
                ->skip(1) // skip header
                ->map(fn ($row) => str_getcsv($row, ','))
                ->map(fn (array $row) => array_combine($keys, $row))
                ->map(Trip::create(...));


            // foreach ($fileContents as $key => $line) {
            //     if ($key == 0) {
            //         continue;
            //     }

            //     $line = explode(',', $line); // str_getcsv будешт получше, он поддерживает больше нюансов


            //     Trip::create($tripLine);
            // }

            return back()->with('success', 'Data Imported successfully.');
        } else {
            return back()->withErrors(['file' => 'Invalid file format. Please upload a valid Excel (xls, xlsx) or CSV file.']);
        }
    }

    public function calculate(TripService $tripService): RedirectResponse
    {
        $trips = Trip::all();
        $totalTime = $tripService->calculateTotalTime($trips);

        // отчёты можно конечно считать в отдельную таблицу, но если посудить, то ты считаешь общее время с пасажиром и оно по сути 1-к-1му с водителем
        // а значит эту колонку можно ххранитьв таблице с водителями где можно хранить такие поля как номер машины или имя водителя по которому ведётся расчёт.
        // Если же ты хочешь вести какие-то более серьёзные вычесления, тогдя я тебе советую добавить во всю эту историю не только водителя но и пассажир как модели
        // в таком случае ты сможешь считать время водителя со всеми пасажирами, с конкретным пассажиром, время пассажира со всеми водителями. Отсюда можно будет делать больше функционала
        foreach ($totalTime as $driverId => $timeData) {
            DriverReport::create([
                'driver_id' => $driverId,
                'total_minutes_with_passenger' => $tripService->getWorkingMinutes($timeData),
            ]);
        }

        // Ещё один ОЧЕНЬ важный момент
        // Твоё приложение должно очень чётко и в обязателном порядке отвечать на пару вопросов:
        // Кто использует это приложение?
        // Какую проблему это человека это приложение решает?
        // Насколько оно упрощает жизнь этого человека и что надо сделать что упростить её ещё сильнее.

        return back()->with('success', 'Data Calculated successfully.');
    }
}
