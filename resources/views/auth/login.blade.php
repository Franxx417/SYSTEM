<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ isset($brandingService) ? $brandingService->getAppName() : 'Procurement System' }}</title>
    <!-- Bootstrap 5.3.8 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ route('branding.css') }}">
    @vite(['resources/css/app.css','resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
    <!-- Login screen: collects username/password and posts to login route -->
    <div class="container min-vh-100 d-flex align-items-center">
        <div class="row justify-content-center w-100">
            <div class="col-12 col-sm-8 col-md-6 col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            @if(isset($brandingService) && $brandingService->hasLogo())
                                <div class="mb-3">
                                    <img src="{{ $brandingService->getLogoPath() }}" alt="Logo" style="max-height: 80px; max-width: 200px; height: auto;">
                                </div>
                            @else
                                @php
                                    $primaryColor = isset($brandingService) ? $brandingService->getPrimaryColor() : '#0d6efd';
                                @endphp
                                <div class="mx-auto mb-3 d-inline-flex align-items-center justify-content-center rounded-circle" style="width:56px;height:56px;background:{{ $primaryColor }}20;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="{{ $primaryColor }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                                </div>
                            @endif
                            <h4 class="fw-semibold mb-0">{{ isset($brandingService) ? $brandingService->getAppName() : 'Procurement System' }}</h4>
                            <small class="text-muted">{{ isset($brandingService) ? $brandingService->getAppTagline() : 'Sign in to your account' }}</small>
                        </div>

                        <!-- Login form uses Laravel CSRF and our custom auth controller -->
                        <form method="POST" action="{{ route('login.post') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input class="form-control" type="text" name="username" placeholder="Username" value="{{ old('username') }}" required maxlength="100" />
                                @error('username')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input class="form-control" type="password" name="password" placeholder="Password" required maxlength="255" />
                                @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="d-grid">
                                <button class="btn btn-primary" type="submit">
                                    <span class="me-2" n="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 17l5-5-5-5"/><path d="M4 17l5-5-5-5"/></svg>
                                    </span>
                                    Sign in
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>


