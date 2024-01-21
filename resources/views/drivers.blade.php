@extends('layout.app')

@section('content')
    <h2 class="mb-5 mt-5">Drivers time report</h2>
    <div class="pb-4">
        <a href="{{ route('trips.index') }}">Trip list</a>
    </div>
    <form method="GET" action="{{ route('drivers-report.export') }}" class="mb-5">
        @csrf
        <button type="submit" class="btn btn-success">Download CSV file</button>
    </form>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>Driver id</th>
            <th>Total minutes with passenger</th>
        </thead>
        <tbody>
        @foreach($drivers as $driver)
            <tr>
                <td>{{$driver->driver_id}}</td>
                <td>{{$driver->total_minutes_with_passenger}}</td>
            </tr>
        @empty($driver)
            <tr>
                <td colspan="2">No drivers found</td>
            </tr>
        @endempty
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">
        {{ $drivers->links('pagination::bootstrap-4') }}
    </div>
@endsection
