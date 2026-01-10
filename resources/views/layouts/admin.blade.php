<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tabibi Admin')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <script src="{{ asset('js/custom-code-scanner.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    
</head>

<body>

    <aside class="sidebar" id="sidebar">
        <div class="d-flex align-items-center px-4 py-4 border-bottom border-light" style="height: 80px;">
            <div class="d-flex align-items-center gap-3 text-primary">
                <div class="icon-box bg-primary text-white shadow-glow">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>
                <span class="fs-4 fw-bold text-dark tracking-tight">Tabibi</span>
            </div>
        </div>

        <ul class="sidebar-menu list-unstyled">
            <li>
                <a href="{{ route('dashboard') }}"
                    class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-layer-group"></i>
                    Dashboard
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

        <div class="p-3 border-top border-light mt-auto">
            <div class="d-flex align-items-center gap-3 px-2">
                <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=eff6ff&color=2563eb"
                    alt="User" class="rounded-circle border" width="40" height="40">
                <div class="flex-grow-1 overflow-hidden">
                    <p class="mb-0 fw-semibold text-dark text-truncate" style="font-size: 0.875rem;">
                        {{ Auth::user()->name }}
                    </p>
                    <p class="mb-0 text-muted text-truncate" style="font-size: 0.75rem;">
                        {{ ucfirst(Auth::user()->role) }}
                    </p>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link text-muted p-0" title="Logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="main-wrapper">

        <header
            class="d-flex align-items-center justify-content-between px-4 py-3 bg-white bg-opacity-75 border-bottom shadow-sm sticky-top"
            style="height: 80px; backdrop-filter: blur(10px);">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-md-none text-muted" id="menuBtn">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1 class="h5 fw-bold text-dark mb-0 d-none d-sm-block">@yield('header', 'Dashboard')</h1>
            </div>

            <div class="d-flex align-items-center gap-2">

                <div class="search-wrapper d-none d-md-flex position-relative w-200 p-0" style="max-width: 400px;">

                    <i class="fa-solid fa-magnifying-glass text-muted small position-absolute"
                        style="left: 15px; top: 50%; transform: translateY(-50%);"></i>

                    <input type="text" id="main-search-input" class="form-control border-0 bg-light ps-5 rounded-pill"
                        placeholder="Search (Name, CIN, ID...)" autocomplete="off"
                        data-route="{{ route('global.search') }}">

                    <div id="global-search-results"
                        class="dropdown-menu shadow-lg border-0 w-100 mt-2 rounded-4 overflow-hidden"
                        style="display: none; position: absolute; top: 100%; left: 0; z-index: 1050;">
                    </div>
                </div>

                {{-- Book Appointment Button --}}
                <button class="btn btn-primary fw-bold shadow-sm d-none d-md-flex align-items-center"
                    data-bs-toggle="modal" data-bs-target="#bookAppointmentModal">
                    <i class="fa-solid fa-plus"></i>
                    <span class="d-none d-lg-inline ps-2">Appointment</span>
                </button>

                <button class="btn btn-primary fw-bold shadow-sm text-nowrap" 
                data-bs-toggle="modal" data-bs-target="#createPatientModal">
                    <i class="fa-solid fa-user-plus me-2"></i>Patient
                </button>

                

                <button class="btn p-2 position-relative text-muted">
                    <i class="fa-regular fa-bell fs-5"></i>
                    <span
                        class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"
                        style="width: 10px; height: 10px; top: 10px !important; left: 75% !important;"></span>
                </button>
            </div>
        </header>

        <main class="flex-grow-1 overflow-auto p-4 p-lg-5">
            <div class="container-fluid p-0">

                <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3"
                    style="z-index: 1055;">
                    @if(session('success'))
                        <div class="toast align-items-center text-bg-success border-0 fade show" role="alert">
                            <div class="d-flex">
                                <div class="toast-body d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <div>{{ session('success') }}</div>
                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                                    data-bs-dismiss="toast"></button>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="px-4 py-2">
                @yield('content')
                </div>

            </div>
        </main>
    </div>

    <div class="mobile-overlay position-fixed top-0 start-0 w-100 h-100 bg-black bg-opacity-50" id="overlay"
        style="z-index: 999; display: none;"></div>

    <script>

        const patientSearchRoute = "{{ route('api.patients.search') }}";

        // Sidebar Toggle Logic
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function toggleSidebar() {
            sidebar.classList.toggle('show');
            if (window.innerWidth <= 768) {
                overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
            }
        }

        if (menuBtn) {
            menuBtn.addEventListener('click', toggleSidebar);
            overlay.addEventListener('click', toggleSidebar);
        }

        // Initialize Toasts
        document.addEventListener("DOMContentLoaded", function () {
            var toastElList = [].slice.call(document.querySelectorAll('.toast'));
            var toastList = toastElList.map(function (toastEl) {
                return new bootstrap.Toast(toastEl).show();
            });
        });
    </script>




    <script src="{{ asset('js/main.js') }}"></script>

    {{-- Include Book Appointment Modal --}}
    @include('layouts.partials.book_modal')

    @include('layouts.partials.create_patient_modal')

    {{-- Book Appointment Modal Scripts --}}


    
    {{-- CREATE MODAL --}}

</body>

</html>