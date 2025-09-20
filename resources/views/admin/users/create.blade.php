<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
    <div class="container my-3" style="max-width:720px;">
        <h2 class="mb-3">Create User</h2>
        <form class="card p-3 shadow-sm" method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input class="form-control" type="text" name="name" value="{{ old('name') }}" required maxlength="200" />
                @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" value="{{ old('email') }}" required maxlength="255" />
                @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Position</label>
                <input class="form-control" type="text" name="position" value="{{ old('position') }}" required maxlength="100" />
                @error('position')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Department</label>
                <input class="form-control" type="text" name="department" value="{{ old('department') }}" required maxlength="100" />
                @error('department')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input class="form-control" type="text" name="username" value="{{ old('username') }}" required maxlength="100" />
                @error('username')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input class="form-control" type="password" name="password" required maxlength="255" />
                @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select class="form-select" name="role_type_id" required>
                    @foreach($roleTypes as $id => $label)
                        <option value="{{ $id }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('role_type_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mt-2">
                <button class="btn btn-primary" type="submit">Create</button>
                <a class="btn btn-link" href="{{ route('admin.users.index') }}">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>


