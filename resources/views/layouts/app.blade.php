<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Toko Retail') - Toko Retail Modern</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --font-family-sans: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --sidebar-width: 250px;
        }

        body {
            font-family: var(--font-family-sans);
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
        }

        .navbar-custom {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }

        .brand-logo {
            font-weight: 700;
            font-size: 1.25rem;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .brand-logo i {
            color: var(--primary-color);
            font-size: 1.5rem;
        }

        .card-custom {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
            transition: all 0.2s ease-in-out;
        }

        .card-custom:hover {
            box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.07);
        }

        .btn-primary-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            font-weight: 500;
            border-radius: 8px;
            padding: 0.5rem 1rem;
        }

        .btn-primary-custom:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            color: white;
        }

        .table-custom {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .table-custom th {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 0.825rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            padding: 0.85rem 1rem;
            border: none;
        }

        .table-custom td {
            padding: 0.85rem 1rem;
            vertical-align: middle;
            border-top: 1px solid #f1f5f9;
        }

        .badge-role-admin { background-color: #8b5cf6; color: white; }
        .badge-role-gudang { background-color: #0ea5e9; color: white; }
        .badge-role-kasir { background-color: #10b981; color: white; }

        /* Print formatting */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    @auth
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top no-print">
        <div class="container-fluid px-3 px-lg-4">
            <a class="brand-logo" href="{{ url('/') }}">
                <i class="bi bi-shop"></i>
                <span>Toko Retail Modern</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    @if(Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active fw-bold text-primary' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.reports.sales*') ? 'active fw-bold text-primary' : '' }}" href="{{ route('admin.reports.sales') }}">
                                <i class="bi bi-graph-up-arrow me-1"></i> Laporan Penjualan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.reports.profit*') ? 'active fw-bold text-primary' : '' }}" href="{{ route('admin.reports.profit') }}">
                                <i class="bi bi-cash-stack me-1"></i> Laporan Keuntungan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.reports.stock*') ? 'active fw-bold text-primary' : '' }}" href="{{ route('admin.reports.stock') }}">
                                <i class="bi bi-box-seam me-1"></i> Laporan Stok
                            </a>
                        </li>
                    @elseif(Auth::user()->isGudang())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('gudang.dashboard') ? 'active fw-bold text-primary' : '' }}" href="{{ route('gudang.dashboard') }}">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard Gudang
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('gudang.products.*') ? 'active fw-bold text-primary' : '' }}" href="{{ route('gudang.products.index') }}">
                                <i class="bi bi-boxes me-1"></i> Data Barang
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('gudang.receipts.*') ? 'active fw-bold text-primary' : '' }}" href="{{ route('gudang.receipts.index') }}">
                                <i class="bi bi-truck me-1"></i> Barang Masuk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('gudang.adjustments.*') ? 'active fw-bold text-primary' : '' }}" href="{{ route('gudang.adjustments.index') }}">
                                <i class="bi bi-sliders me-1"></i> Penyesuaian Stok
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('gudang.movements.*') ? 'active fw-bold text-primary' : '' }}" href="{{ route('gudang.movements.index') }}">
                                <i class="bi bi-clock-history me-1"></i> Riwayat Mutasi
                            </a>
                        </li>
                    @elseif(Auth::user()->isKasir())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('kasir.dashboard') ? 'active fw-bold text-primary' : '' }}" href="{{ route('kasir.dashboard') }}">
                                <i class="bi bi-calculator me-1"></i> Kasir POS
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('kasir.history') ? 'active fw-bold text-primary' : '' }}" href="{{ route('kasir.history') }}">
                                <i class="bi bi-receipt me-1"></i> Riwayat Transaksi
                            </a>
                        </li>
                    @endif
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <span class="badge rounded-pill px-3 py-2 badge-role-{{ Auth::user()->role }}">
                        <i class="bi bi-person-badge me-1"></i> {{ strtoupper(Auth::user()->role) }}
                    </span>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2 rounded-pill px-3 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5"></i>
                            <span class="d-none d-md-inline fw-semibold">{{ Auth::user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li><span class="dropdown-item-text small text-muted">Signed in as <strong>{{ Auth::user()->username }}</strong></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                        <i class="bi bi-box-arrow-right"></i> Keluar (Logout)
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    @endauth

    <!-- Main Container -->
    <main class="py-4">
        <div class="container-fluid px-3 px-lg-4">
            <!-- Flash Message Alerts -->
            <div class="no-print">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-info-circle-fill fs-5 me-2"></i>
                        <div>{{ session('info') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                        <div class="d-flex align-items-center mb-1">
                            <i class="bi bi-x-circle-fill fs-5 me-2"></i>
                            <strong>Perhatian! Terdapat kesalahan input:</strong>
                        </div>
                        <ul class="mb-0 ps-4">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            </div>

            @yield('content')
        </div>
    </main>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
