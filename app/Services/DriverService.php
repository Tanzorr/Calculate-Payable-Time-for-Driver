<?php

namespace App\Services;

class DriverService
{
    // сервисы должны работать как чёрная коробочка, ты не должен вызывать кучу методов чтобы получить один результат
    // тебе тут нужен скорее метод который вернёт handle в который записанны все данные а вот заголовки должны отправиться на уровень контроллера.
    public function getCsvHeaders(string $csvFileName): array
    {
        return [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        ];
    }

    public function initializeCsvHandle()
    {
        return fopen('php://temp', 'w');
    }

    public function writeCsvHeader($handle): void
    {
        fputcsv($handle, ['driver_id', 'total_minutes_with_passenger']);
        rewind($handle);
    }

    public function writeDriversDataToCsv($handle, $driversReport): void
    {
        fputcsv($handle, ['driver_id', 'total_minutes_with_passenger']);
        foreach ($driversReport as $driver) {
            fputcsv($handle, [$driver->driver_id, round($driver->total_minutes_with_passenger)]);
        }
        fseek($handle, 0);
    }

    public function downloadCsvResponse($handle, string $csvFileName, array $headers)
    {
        return response()->streamDownload(
            fn () => fpassthru($handle),
            $csvFileName,
            $headers
        );
    }
}
