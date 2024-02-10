<form class="d-flex w-25" method="GET" action="{{ $action }}"
>
    @csrf
    <input
        type="text"
        class="search"
        placeholder="Search..."
        name="search"
        value="{{ $seachValue }}"
        id="search"
    />
    <button type="submit" class="btn btn-success mx-2">submit</button>
</form>
