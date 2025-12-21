<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tabibi Admin')</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #0d6efd;
            /* Medical Blue */
            --bg-light: #f3f4f6;
            --sidebar-bg: #1e293b;
            /* Slate Dark */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            overflow-x: hidden;
        }

        /* SIDEBAR STYLES */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--sidebar-bg);
            color: #fff;
            z-index: 1000;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }

        .sidebar-logo {
            padding: 20px;
            font-size: 1.5rem;
            font-weight: 700;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-menu {
            padding: 20px 10px;
            flex-grow: 1;
            /* Pushes profile to bottom */
            list-style: none;
        }

        .nav-link {
            color: #cbd5e1;
            /* Light gray text */
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s;
        }

        .nav-link:hover,
        .nav-link.active {
            background-color: var(--primary-color);
            color: #fff;
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        /* USER PROFILE AT BOTTOM LEFT */
        .sidebar-profile {
            padding: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(0, 0, 0, 0.2);
        }

        .sidebar-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* MAIN CONTENT WRAPPER */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* TOP NAVBAR */
        .top-navbar {
            height: 70px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        }

        .page-content {
            padding: 30px;
            flex-grow: 1;
        }

        /* RESPONSIVE TOGGLE */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(var(--sidebar-width) * -1);
            }

            .sidebar.active {
                margin-left: 0;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

    <nav class="sidebar" id="sidebar">
        <a href="#" class="sidebar-logo">
            <i class="fa-solid fa-heart-pulse"></i> Tabibi
        </a>


        <ul class="sidebar-menu p-0">

            <li>
                <a href="{{ route('dashboard') }}"
                    class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge"></i> Dashboard
                </a>
            </li>

            @if(Auth::user()->role === 'super_admin')
                @include('layouts.sidebar.super_admin')

            @elseif(Auth::user()->role === 'doctor')
                @include('layouts.sidebar.doctor')

            @elseif(Auth::user()->role === 'secretary')
                @include('layouts.sidebar.secretary')

            @endif

        </ul>


        <div class="dropdown">
            <a href="#" class="sidebar-profile text-decoration-none text-white dropdown-toggle"
                data-bs-toggle="dropdown">
                <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'Admin' }}&background=0D8ABC&color=fff"
                    alt="Admin">
                <div class="small">
                    <div class="fw-bold">{{ Auth::user()->name ?? 'Super Admin' }}</div>
                    <div class="text-muted" style="font-size: 0.8rem;">View Profile</div>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li><a class="dropdown-item" href="#">Settings</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="dropdown-item" type="submit">Sign out</button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <div class="main-content">
        <header class="top-navbar">
            <button class="btn btn-light d-md-none" id="sidebarToggle">
                <i class="fa-solid fa-bars"></i>
            </button>

            <h5 class="m-0 text-secondary">@yield('header', 'Overview')</h5>

            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light position-relative text-secondary">
                    <i class="fa-regular fa-bell"></i>
                    <span
                        class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                </button>
            </div>
        </header>

        <main class="page-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        // Mobile Sidebar Toggle
        document.getElementById('sidebarToggle').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
</body>

</html>