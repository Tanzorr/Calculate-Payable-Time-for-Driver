@extends('layout.app')

@section('content')
    <h2>Trips table</h2>
    <div>
        <a href="{{ route('drivers-report.index') }}">Drivers Reports</a>
    </div>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="row d-flex align-items-center">
        <form class="form mb-5 mt-5 col-lg-4" action="{{ route('trips.import') }}" method="POST"
              enctype="multipart/form-data">
            @csrf
            <input type="file" name="file" accept=".csv">
            @error('file')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <button type="submit">Import CSV</button>
        </form>
        <form class="col-lg-4" method="GET" action="{{ route('trips.calculate') }}">
            @csrf
            <button type="submit" class="btn btn-success">Calculate Payable Time</button>
        </form>
        <form class="col-lg-4" method="GET" action="{{ route('trips.search') }}">
            @csrf
            <input
                type="text"
                class="search"
                placeholder="Search..."
                name="search"
                value="{{ request()->input('search') }}"
                id="search"
            />
            <button type="submit" class="btn btn-success">submit</button>
        </form>
    </div>
    <table class="table table-striped">
        <thead>
        <tr>
            <th><a href="{{ url('trips?order=trip_id') }}">Trip passenger Id</a></th>
            <th><a href="{{ url('trips?order=driver_id') }}">Driver id</a></th>
            <th><a href="{{ url('trips?order=pickup_time') }}">Pickup time</a></th>
            <th><a href="{{ url('trips?order=pickup_time') }}">Drop off time</a></th>
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

