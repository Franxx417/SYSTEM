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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite([
        'resources/css/app.css',
        'resources/css/components/custom.css',
        'resources/js/app.js',
        'resources/js/bootstrap.js',
        'resources/js/components/modal-manager.js',
        'resources/js/components/status-sync.js',
        'resources/js/components/status-management.js'
    ])
    <link rel="stylesheet" href="{{ route('dynamic.status.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @stack('styles')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .sidebar { 
            width: 240px; 
            transition: transform 0.3s ease-in-out;
            z-index: 1050;
        }
        .sidebar .nav-link.active { background: #0d6efd; color: #fff; }
        .content-wrap { margin-left: 0; transition: margin-left 0.3s ease-in-out; }
        
        /* Mobile sidebar overlay */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease-in-out;
        }
        
        /* Profile dropdown styles */
        #profileDropdown:hover .rounded-circle {
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
        }
        
        .dropdown-menu {
            border: 1px solid rgba(0,0,0,.1);
            border-radius: 0.5rem;
            padding: 0.5rem 0;
        }
        
        .dropdown-item {
            border-radius: 0.375rem;
            margin: 0 0.5rem;
            padding: 0.5rem 0.75rem;
            transition: all 0.2s;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
            transform: translateX(2px);
        }
        
        .dropdown-item svg {
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        
        .dropdown-item:hover svg {
            opacity: 1;
        }
        
        .dropdown-divider {
            margin: 0.5rem 0;
        }
        
        .dropdown-header {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
        }
        
        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        /* Mobile hamburger menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #333;
            cursor: pointer;
            padding: 0.5rem;
        }
        
        /* Mobile styles */
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed !important;
                top: 0;
                left: 0;
                height: 100vh;
                transform: translateX(-100%);
                background: white;
                box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .content-wrap {
                margin-left: 0 !important;
            }
            
            .mobile-menu-btn {
                display: inline-block;
            }
            
            .d-lg-block {
                display: none !important;
            }
        }
        
        /* Desktop styles */
        @media (min-width: 992px) { 
            .content-wrap { margin-left: 240px; }
            .sidebar-overlay { display: none !important; }
        }

        textarea {
            field-sizing: content;
            min-height: 300px;
            overflow: hidden;
        }
        
        /* Responsive header */
        .mobile-header {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        /* Responsive tables */
        .table-responsive {
            border-radius: 0.375rem;
        }
        
        @media (max-width: 991.98px) {
            .mobile-header .d-none.d-lg-block {
                display: none !important;
            }
            
            /* Stack cards vertically on mobile */
            .row.g-3 > .col-lg-8,
            .row.g-3 > .col-lg-4,
            .row.g-3 > .col-lg-6,
            .row.g-3 > .col-lg-9,
            .row.g-3 > .col-lg-3 {
                margin-bottom: 1rem;
            }
            
            /* Make buttons smaller on mobile */
            .btn-group-sm .btn {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }
            
            /* Hide some table columns on mobile */
            .table th:nth-child(3),
            .table td:nth-child(3),
            .table th:nth-child(4),
            .table td:nth-child(4) {
                display: none;
            }
            
            /* Make table text smaller */
            .table {
                font-size: 0.875rem;
            }
            
            /* Responsive modal */
            .modal-lg {
                max-width: 95%;
            }
        }
        
        @media (max-width: 767.98px) {
            /* Even smaller screens */
            .container-fluid {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
            
            .card {
                margin-bottom: 1rem;
            }
            
            /* Stack form elements */
            .row.g-3 > .col-md-6 {
                margin-bottom: 0.75rem;
            }
        }
    </style>
</head>
<body class="bg-light">
    {{-- App shell: responsive sidebar + top header; content renders below from child views --}}
    
    <!-- Mobile sidebar overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="d-flex">
        <nav class="sidebar border-end bg-white position-fixed top-0 bottom-0" id="sidebar">
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
                <li class="nav-item"><a class="nav-link @if(request()->is('dashboard') && !request()->has('tab')) active @endif" href="{{ route('dashboard') }}"><i class="fas fa-house me-2"></i>Overview</a></li>
                <li class="nav-item"><a class="nav-link @if(request()->is('items') && !request()->is('items/inventory')) active @endif" href="{{ route('items.index') }}"><i class="fas fa-boxes-stacked me-2"></i>Items</a></li>
                <li class="nav-item"><a class="nav-link @if(request()->is('items/inventory')) active @endif" href="{{ route('items.inventory') }}"><i class="fas fa-warehouse me-2"></i>Inventory Summary</a></li>
                @php($auth = session('auth_user'))
                @if($auth && $auth['role']==='requestor')
                    <li class="nav-item"><a class="nav-link @if(request()->is('po*')) active @endif" href="{{ route('po.index') }}"><i class="fas fa-file-invoice-dollar me-2"></i>My Purchase Orders</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('suppliers*')) active @endif" href="{{ route('suppliers.index') }}"><i class="fas fa-truck me-2"></i>Suppliers</a></li>
                @elseif($auth && $auth['role']==='superadmin')
                    <!-- SUPERADMIN UNRESTRICTED ACCESS - All System Features -->
                    <li class="nav-item">
                        <div class="nav-link text-muted small fw-bold text-uppercase px-2 mb-1">SYSTEM MANAGEMENT</div>
                    </li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('dashboard') && request()->get('tab') === 'purchase-orders') active @endif" href="{{ route('dashboard') }}?tab=purchase-orders"><i class="fas fa-file-invoice me-2"></i>All Purchase Orders</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('dashboard') && request()->get('tab') === 'user-management') active @endif" href="{{ route('dashboard') }}?tab=user-management"><i class="fas fa-users me-2"></i>User Management</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('suppliers*')) active @endif" href="{{ route('suppliers.index') }}"><i class="fas fa-truck me-2"></i>Suppliers</a></li>
                    
                    <li class="nav-item">
                        <div class="nav-link text-muted small fw-bold text-uppercase px-2 mb-1 mt-3">SYSTEM ADMINISTRATION</div>
                    </li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('dashboard') && request()->get('tab') === 'status') active @endif" href="{{ route('dashboard') }}?tab=status"><i class="fas fa-traffic-light me-2"></i>Status Management</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('dashboard') && request()->get('tab') === 'security') active @endif" href="{{ route('dashboard') }}?tab=security"><i class="fas fa-shield-alt me-2"></i>Security & Access</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('dashboard') && request()->get('tab') === 'database') active @endif" href="{{ route('dashboard') }}?tab=database"><i class="fas fa-database me-2"></i>Database Management</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('dashboard') && request()->get('tab') === 'logs') active @endif" href="{{ route('dashboard') }}?tab=logs"><i class="fas fa-file-alt me-2"></i>System Logs</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('dashboard') && request()->get('tab') === 'system') active @endif" href="{{ route('dashboard') }}?tab=system"><i class="fas fa-cogs me-2"></i>System Settings</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('dashboard') && request()->get('tab') === 'branding') active @endif" href="{{ route('dashboard') }}?tab=branding"><i class="fas fa-palette me-2"></i>Branding & UI</a></li>
                @endif
                
                
            </ul>
        </nav>

        <main class="content-wrap w-100">
            <header class="bg-white border-bottom py-3 px-3 px-lg-4">
                <div class="mobile-header justify-content-between align-items-center d-flex">
                    <div class="d-flex align-items-center gap-3">
                        <!-- Mobile menu button -->
                        <button class="mobile-menu-btn" id="mobileMenuBtn" type="button">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div>
                            <div class="h5 mb-0">@yield('page_heading','Dashboard')</div>
                            <div class="text-muted">@yield('page_subheading','Overview')</div>
                        </div>
                    </div>
                    <div class="d-none d-lg-block">
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-end">
                                <div class="fw-semibold">{{ $auth['name'] ?? '' }}</div>
                                <div class="text-muted small">{{ $auth['department'] ?? '' }}</div>
                                @if($auth && $auth['role'] === 'superadmin')
                                    <div class="badge bg-danger small">SUPERADMIN - UNRESTRICTED ACCESS</div>
                                @endif
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-link text-decoration-none p-0" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                                    @if(isset($auth['profile_photo']) && $auth['profile_photo'])
                                        <img src="{{ $auth['profile_photo'] }}" alt="Profile" class="rounded-circle" style="width: 48px; height: 48px; object-fit: cover; border: 2px solid #dee2e6; transition: box-shadow 0.2s;">
                                    @else
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.2rem; font-weight: 600; border: 2px solid #dee2e6; transition: box-shadow 0.2s;">
                                            {{ strtoupper(substr($auth['name'] ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="profileDropdown" style="min-width: 200px;">
                                    <li>
                                        <div class="dropdown-header px-3 py-2">
                                            <div class="fw-semibold">{{ $auth['name'] ?? '' }}</div>
                                            <div class="small text-muted">{{ $auth['email'] ?? $auth['department'] ?? '' }}</div>
                                        </div>
                                    </li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('settings.index') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear me-2" viewBox="0 0 16 16">
                                                <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/>
                                                <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/>
                                            </svg>
                                            @if($auth && $auth['role'] === 'superadmin')
                                                Account Settings
                                            @else
                                                Settings
                                            @endif
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}" class="d-inline w-100">
                                            @csrf
                                            <button class="dropdown-item text-danger py-2" type="submit">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-right me-2" viewBox="0 0 16 16">
                                                    <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/>
                                                    <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
                                                </svg>
                                                Logout
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Mobile user info -->
                    <div class="d-lg-none">
                        <div class="dropdown">
                            <button class="btn btn-link text-decoration-none p-0" type="button" data-bs-toggle="dropdown">
                                @if(isset($auth['profile_photo']) && $auth['profile_photo'])
                                    <img src="{{ $auth['profile_photo'] }}" alt="Profile" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #dee2e6;">
                                @else
                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        {{ strtoupper(substr($auth['name'] ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">{{ $auth['name'] ?? '' }}</h6></li>
                                <li><span class="dropdown-item-text small text-muted">{{ $auth['department'] ?? '' }}</span></li>
                                @if($auth && $auth['role'] === 'superadmin')
                                    <li><span class="dropdown-item-text"><span class="badge bg-danger small">SUPERADMIN ACCESS</span></span></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('settings.index') }}">
                                        <i class="fas fa-cog me-2"></i>
                                        @if($auth && $auth['role'] === 'superadmin')
                                            Account Settings
                                        @else
                                            Settings
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                        @csrf
                                        <button class="dropdown-item text-danger" type="submit">
                                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
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
    
    <!-- Mobile sidebar JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            
            // Toggle sidebar on mobile
            function toggleSidebar() {
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
                document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
            }
            
            // Close sidebar
            function closeSidebar() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.style.overflow = '';
            }
            
            // Event listeners
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', toggleSidebar);
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', closeSidebar);
            }
            
            // Close sidebar when clicking on nav links (mobile)
            const navLinks = sidebar.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        closeSidebar();
                    }
                });
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    closeSidebar();
                }
            });
            
            // Handle escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebar.classList.contains('show')) {
                    closeSidebar();
                }
            });
        });
    </script>
</body>
</html>


