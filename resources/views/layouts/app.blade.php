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
    @vite(['resources/css/app.css','resources/js/app.js'])
    <link rel="stylesheet" href="/css/custom.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @stack('styles')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/modal-manager.js"></script>
    <script src="/js/status-sync.js"></script>
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
                <li class="nav-item"><a class="nav-link @if(request()->is('items*')) active @endif" href="{{ route('items.index') }}"><i class="fas fa-boxes-stacked me-2"></i>Items</a></li>
                @php($auth = session('auth_user'))
                @if($auth && $auth['role']==='requestor')
                    <li class="nav-item"><a class="nav-link @if(request()->is('po*')) active @endif" href="{{ route('po.index') }}"><i class="fas fa-file-invoice-dollar me-2"></i>My Purchase Orders</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('suppliers*')) active @endif" href="{{ route('suppliers.index') }}"><i class="fas fa-truck me-2"></i>Suppliers</a></li>
                @elseif($auth && $auth['role']==='authorized_personnel')
                    <li class="nav-item"><a class="nav-link @if(request()->is('admin/users*')) active @endif" href="{{ route('admin.users.index') }}"><i class="fas fa-users me-2"></i>User Management</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('suppliers*')) active @endif" href="{{ route('suppliers.index') }}"><i class="fas fa-truck me-2"></i>Suppliers</a></li>
                @elseif($auth && $auth['role']==='superadmin')
                    <!-- Superadmin specific navigation -->
                    <li class="nav-item"><a class="nav-link @if(request()->is('dashboard') && request()->get('tab') === 'purchase-orders') active @endif" href="{{ route('dashboard') }}?tab=purchase-orders"><i class="fas fa-file-invoice me-2"></i>Purchase Orders</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('admin/users*') || (request()->is('dashboard') && request()->get('tab') === 'user-management')) active @endif" href="{{ route('dashboard') }}?tab=user-management"><i class="fas fa-users me-2"></i>User Management</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('dashboard') && request()->get('tab') === 'role-management') active @endif" href="{{ route('dashboard') }}?tab=role-management"><i class="fas fa-user-shield me-2"></i>Role Management</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('dashboard') && request()->get('tab') === 'security') active @endif" href="{{ route('dashboard') }}?tab=security"><i class="fas fa-shield-alt me-2"></i>Security</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('dashboard') && request()->get('tab') === 'system') active @endif" href="{{ route('dashboard') }}?tab=system"><i class="fas fa-cogs me-2"></i>System</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('dashboard') && request()->get('tab') === 'database') active @endif" href="{{ route('dashboard') }}?tab=database"><i class="fas fa-database me-2"></i>Database</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('dashboard') && request()->get('tab') === 'logs') active @endif" href="{{ route('dashboard') }}?tab=logs"><i class="fas fa-file-alt me-2"></i>Logs</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('dashboard') && request()->get('tab') === 'branding') active @endif" href="{{ route('dashboard') }}?tab=branding"><i class="fas fa-palette me-2"></i>Branding</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->is('suppliers*')) active @endif" href="{{ route('suppliers.index') }}"><i class="fas fa-truck me-2"></i>Suppliers</a></li>
                @endif
                
                <!-- Settings link for all authenticated users -->
                @if($auth)
                    <li class="nav-item"><a class="nav-link @if(request()->is('settings*')) active @endif" href="{{ route('settings.index') }}"><i class="fas fa-cog me-2"></i>Settings</a></li>
                @endif
                
                <li class="nav-item mt-auto">
                    <form method="POST" action="{{ route('logout') }}" class="p-2">@csrf<button class="btn btn-outline-secondary w-100">Logout</button></form>
                </li>
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
                    <div class="d-none d-lg-block text-end">
                        <div class="fw-semibold">{{ $auth['name'] ?? '' }}</div>
                        <div class="text-muted small">{{ $auth['department'] ?? '' }}</div>
                    </div>
                    <!-- Mobile user info -->
                    <div class="d-lg-none">
                        <div class="dropdown">
                            <button class="btn btn-link text-decoration-none p-0" type="button" data-bs-toggle="dropdown">
                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                    {{ strtoupper(substr($auth['name'] ?? 'U', 0, 1)) }}
                                </div>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">{{ $auth['name'] ?? '' }}</h6></li>
                                <li><span class="dropdown-item-text small text-muted">{{ $auth['department'] ?? '' }}</span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('settings.index') }}"><i class="fas fa-cog me-2"></i>Settings</a></li>
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


