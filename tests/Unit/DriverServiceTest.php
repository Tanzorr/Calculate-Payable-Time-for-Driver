<?php

namespace Tests\Unit\Services;


use Tests\TestCase;
use App\Services\DriverService;


class DriverServiceTest extends TestCase
{
    private $driverService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->driverService = new DriverService();
    }

    public function testGetCsvHeaders()
    {
        $csvFileName = 'test.csv';
        $headers = $this->driverService->getCsvHeaders($csvFileName);

        $this->assertEquals('text/csv', $headers['Content-type']);
        $this->assertEquals("attachment; filename=$csvFileName", $headers['Content-Disposition']);
        $this->assertEquals('no-cache', $headers['Pragma']);
        $this->assertEquals('must-revalidate, post-check=0, pre-check=0', $headers['Cache-Control']);
        $this->assertEquals('0', $headers['Expires']);
    }

    public function testInitializeCsvHandle()
    {
        $handle = $this->driverService->initializeCsvHandle();
        $this->assertIsResource($handle);
    }

    public function testWriteCsvHeader()
    {
        $handle = $this->driverService->initializeCsvHandle();
        $this->driverService->writeCsvHeader($handle);

        $content = stream_get_contents($handle);
        $this->assertEquals("driver_id,total_minutes_with_passenger\n", $content);
    }

    public function testWriteDriversDataToCsv()
    {
        // Mock data for driversReport
        $driversReport = [
            (object)['driver_id' => 1, 'total_minutes_with_passenger' => 120],
            (object)['driver_id' => 2, 'total_minutes_with_passenger' => 90],
        ];

        $handle = $this->driverService->initializeCsvHandle();
        $this->driverService->writeDriversDataToCsv($handle, $driversReport);

        $content = stream_get_contents($handle);
        $expectedContent = "driver_id,total_minutes_with_passenger\n1,120\n2,90\n";

        $this->assertEquals($expectedContent, $content);
    }

    public function testDownloadCsvResponse()
    {
        // Mock data for headers
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=test.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        // Mock data for handle
        $handle = fopen('php://temp', 'w');
        fputcsv($handle, ['driver_id', 'total_minutes_with_passenger']);
        fputcsv($handle, [1, 120]);
        rewind($handle);

        $csvFileName = 'test.csv';

        $response = $this->driverService->downloadCsvResponse($handle, $csvFileName, $headers);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('attachment; filename=test.csv', $response->headers->get('Content-Disposition'));
    }
}

