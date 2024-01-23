@extends('layout.app')

@section('content')
    <h2 class="mb-2 mt-3">Drivers time report</h2>
    <div class="pb-4">
        <a href="{{ route('trips.index') }}">Trip list</a>
    </div>
    <div class="row d-flex align-items-center justify-content-between">
        <form method="GET" action="{{ route('drivers-report.export') }}" class="col-lg-6">
            @csrf
            <button type="submit" class="btn btn-success">Download CSV file</button>
        </form>
        <x-search-form
            :action="route('drivers-report.search')"
            :seachValue="request()->input('search')"
        ></x-search-form>
    </div>
    <table class="table table-striped">
        <thead>
        <tr>
            <th><a href="{{ url('trips/drivers-report?order=driver_id') }}">Driver id</a></th>
            <th><a href="{{ url('trips/drivers-report?order=total_minutes_with_passenger') }}">Total minutes with
                    passenger</a></th>
        </thead>
        <tbody>
        @foreach($driverReports as $driverReport)
            <tr>
                <td>{{$driverReport->driver_id}}</td>
                <td>{{$driverReport->total_minutes_with_passenger}}</td>
            </tr>
            @empty($driverReport)
                <tr>
                    <td colspan="2">No drivers found</td>
                </tr>
            @endempty
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">
        {{ $driverReports->links('pagination::bootstrap-4') }}
    </div>
@endsection
