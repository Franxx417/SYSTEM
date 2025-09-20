<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
    <div class="container my-3">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Dashboard</h2>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-secondary" type="submit">Logout</button>
            </form>
        </div>

        <div class="mt-3">
            <p class="lead">Welcome, {{ $auth['name'] }} ({{ $auth['role'] }})</p>
            @if($auth['role'] === 'authorized_personnel')
                <a class="btn btn-primary" href="{{ route('admin.users.index') }}">Manage Users</a>
            @endif
        </div>
    </div>
</body>
</html>


