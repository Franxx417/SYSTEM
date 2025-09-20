{{--
  Layout: App Shell
  Purpose: Provides the common chrome (sidebar, header, modal) for all pages.
  Child views should define sections: title, page_heading, page_subheading, content.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','Dashboard') - Procurement</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <link rel="stylesheet" href="/css/custom.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @stack('styles')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/modal-manager.js"></script>
    <style>
        .sidebar { width: 240px; }
        .sidebar .nav-link.active { background: #0d6efd; color: #fff; }
        .content-wrap { margin-left: 0; }
        @media (min-width: 992px) { .content-wrap { margin-left: 240px; } }

        textarea {
        field-sizing: content;
        min-height: 300px;
        overflow: hidden;
        }
    </style>
</head>
<body class="bg-light">
    {{-- App shell: simple sidebar + top header; content renders below from child views --}}
    <div class="d-flex">
        <nav class="sidebar d-none d-lg-block border-end bg-white position-fixed top-0 bottom-0">
            <div class="p-3 border-bottom d-flex align-items-center gap-2">
                @php
                    $logo = null; $appName = 'Procurement';
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                            $logo = \Illuminate\Support\Facades\DB::table('settings')->where('key','branding.logo_path')->value('value');
                            $appName = \Illuminate\Support\Facades\DB::table('settings')->where('key','app.name')->value('value') ?? 'Procurement';
                        }
                    } catch (\Throwable $e) { /* ignore until settings exist */ }
                @endphp
                @if($logo)
                    <img src="{{ $logo }}" alt="Logo" style="height:28px;width:auto"/>
                @endif
                <div>
                    <div class="fw-bold">{{ $appName }}</div>
                    <div class="text-muted small">Management System</div>
                </div>
            </div>
            <ul class="nav flex-column p-2">
                <li class="nav-item"><a class="nav-link @if(request()->is('dashboard')) active @endif" href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link @if(request()->is('items*')) active @endif" href="{{ route('items.index') }}">Items</a></li>
                @php($auth = session('auth_user'))
                @if($auth && $auth['role']==='requestor')
                    <li class="nav-item"><a class="nav-link @if(request()->is('po*')) active @endif" href="{{ route('po.index') }}">My Purchase Orders</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('suppliers*')) active @endif" href="{{ route('suppliers.index') }}">Suppliers</a></li>
                @elseif($auth && $auth['role']==='authorized_personnel')
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.users.index') }}">Users</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('suppliers*')) active @endif" href="{{ route('suppliers.index') }}">Suppliers</a></li>
                @elseif($auth && $auth['role']==='superadmin')
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.users.index') }}">Users</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('suppliers*')) active @endif" href="{{ route('suppliers.index') }}">Suppliers</a></li>
                @endif
                <li class="nav-item mt-auto">
                    <form method="POST" action="{{ route('logout') }}" class="p-2">@csrf<button class="btn btn-outline-secondary w-100">Logout</button></form>
                </li>
            </ul>
        </nav>

        <main class="content-wrap w-100">
            <header class="bg-white border-bottom py-3 px-3 px-lg-4 d-flex justify-content-between align-items-center">
                <div>
                    <div class="h5 mb-0">@yield('page_heading','Dashboard')</div>
                    <div class="text-muted">@yield('page_subheading','Overview')</div>
                </div>
                <div class="d-none d-lg-block text-end">
                    <div class="fw-semibold">{{ $auth['name'] ?? '' }}</div>
                    <div class="text-muted small">{{ $auth['department'] ?? '' }}</div>
                </div>
            </header>
            {{-- Main content area injected by child views --}}
            <div class="container-fluid py-3">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- Global PO Modal shared across dashboards --}}
    <div class="modal fade" id="poModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="poModalLabel">PO</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row mb-2">
                <div class="col-md-4"><div class="text-muted">Supplier</div><div id="poModalSupplier"></div></div>
                <div class="col-md-4"><div class="text-muted">Status</div><div id="poModalStatus"></div></div>
                <div class="col-md-4"><div class="text-muted">Total</div><div id="poModalTotals"></div></div>
            </div>
            <div class="mb-2"><div class="text-muted">Purpose</div><div id="poModalPurpose"></div></div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Description</th><th class="text-end">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Total</th></tr></thead>
                    <tbody id="poModalItems"></tbody>
                </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    @stack('scripts')
</body>
</html>


