<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tabibi Admin')</title>

    <!-- 1. Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- 2. Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- 3. FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <!-- 4. Custom CSS (Your new file) -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

</head>

<body>

    <!-- SIDEBAR -->
    <nav class="sidebar" id="sidebar">
        <a href="#" class="sidebar-logo">
            <div class="bg-primary text-white rounded p-1 d-flex align-items-center justify-content-center"
                style="width: 32px; height: 32px;">
                <i class="fa-solid fa-heart-pulse" style="font-size: 16px;"></i>
            </div>
            Tabibi.ma
        </a>

        <ul class="sidebar-menu p-0">
            <!-- COMMON LINKS -->
            <li>
                <a href="{{ route('dashboard') }}"
                    class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge"></i> Dashboard
                </a>
            </li>

            <!-- ROLE BASED LINKS -->
            @if(Auth::user()->role === 'super_admin')
                @include('layouts.sidebar.super_admin')
            @elseif(Auth::user()->role === 'doctor')
                @include('layouts.sidebar.doctor')
            @elseif(Auth::user()->role === 'secretary')
                @include('layouts.sidebar.secretary')
            @endif
        </ul>

        <!-- PROFILE DROPDOWN -->
        <div class="dropdown">
            <a href="#" class="sidebar-profile dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'Admin' }}&background=006aff&color=fff"
                    alt="User">
                <div class="small lh-1 text-dark">
                    <div class="fw-bold">{{ Auth::user()->name ?? 'User' }}</div>
                    <span class="text-muted" style="font-size: 11px;">{{ ucfirst(Auth::user()->role) }}</span>
                </div>
            </a>
            <ul class="dropdown-menu shadow border-0" style="width: 220px; margin-left: 10px;">
                <li><a class="dropdown-item py-2" href="#"><i class="fa-solid fa-user-gear me-2 text-muted"></i>
                        Profile</a></li>
                <li><a class="dropdown-item py-2" href="#"><i class="fa-solid fa-sliders me-2 text-muted"></i>
                        Settings</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="dropdown-item py-2 text-danger" type="submit">
                            <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Sign out
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- Header -->
        <header class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-white d-md-none border shadow-sm" id="sidebarToggle">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h5 class="m-0 fw-bold text-dark">@yield('header', 'Dashboard')</h5>
            </div>

            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-white position-relative text-secondary border-0">
                    <i class="fa-regular fa-bell fa-lg"></i>
                    <span
                        class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"
                        style="width: 10px; height: 10px;"></span>
                </button>
            </div>
        </header>

        <!-- Page Content -->
        <main class="page-content">


            <!-- 
                TOAST CONTAINER 
                Positioned at Top End (Right) by default for better visibility 
                Change 'top-0 end-0' to 'top-0 start-0' if you really want Top Left
            -->

            <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1055;">

                {{-- SUCCESS TOAST --}}
                @if(session('success'))
                    <div class="toast align-items-center text-bg-success border-0 fade show" role="alert"
                        aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                        <div class="d-flex">
                            <div class="toast-body d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-check fa-lg"></i>
                                <div>{{ session('success') }}</div>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                                aria-label="Close"></button>
                        </div>
                    </div>
                @endif

                {{-- ERROR TOAST --}}
                @if(session('error'))
                    <div class="toast align-items-center text-bg-danger border-0 fade show" role="alert"
                        aria-live="assertive" aria-atomic="true" data-bs-delay="8000">
                        <div class="d-flex">
                            <div class="toast-body d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-exclamation fa-lg"></i>
                                <div>{{ session('error') }}</div>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                                aria-label="Close"></button>
                        </div>
                    </div>
                @endif

            </div>

            @yield('content')
        </main>
    </div>

    <!-- SCRIPTS -->
    <script>
        // Mobile Sidebar Toggle
        document.getElementById('sidebarToggle').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('active');
        });


        document.addEventListener("DOMContentLoaded", function () {
            var toastElList = [].slice.call(document.querySelectorAll('.toast'));
            var toastList = toastElList.map(function (toastEl) {
                // Initialize Bootstrap Toast with 5s delay (configured in data-bs-delay)
                return new bootstrap.Toast(toastEl).show();
            });
        });

    </script>
</body>

</html>