@extends('layout.app')

@section('content')
    <h2>Trips table</h2>

    <div>
        <a href="{{ route('drivers-report.index') }}">Drivers Reports</a>
    </div>
    <div class="row">
        <form class="form mb-5 mt-5 col-lg-6" action="{{ route('trips.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="file" accept=".csv">
            <button type="submit">Import CSV</button>
        </form>
        <form class="col-lg-6" method="GET" action="{{ route('trips.calculate') }}">
            @csrf
            <button type="submit" class="btn btn-success">Calculate Payable Time</button>
        </form>
    </div>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Trip passenger Id</th>
            <th>Driver id</th>
            <th>Pickup time</th>
            <th>Drop off time</th>
        </tr>
        </thead>
        <tbody>
        @foreach($trips as $trip)
            <tr>
                <td>{{ $trip->trip_id }}</td>
                <td>{{ $trip->driver_id }}</td>
                <td>{{ $trip->pickup_time }}</td>
                <td>{{ $trip->dropoff_time }}</td>
            </tr>
            @empty($trip)
                <tr>
                    <td colspan="4">No trips found</td>
                </tr>
            @endempty
        @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $trips->links('pagination::bootstrap-4') }}
    </div>

@endsection

