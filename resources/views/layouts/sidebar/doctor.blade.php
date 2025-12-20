
<li class="nav-item">
    <a href="#" class="nav-link">
        <i class="fa-solid fa-calendar-days"></i> Agenda
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('services.index') }}" class="nav-link">
        <i class="fa-solid fa-users"></i> Appointments
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('patients.index') }}" class="nav-link">
        <i class="fa-solid fa-users"></i> Patient Search
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('services.index') }}" class="nav-link">
        <i class="fa-solid fa-users"></i> Services
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('secretaries.index') }}" class="nav-link {{ request()->routeIs('secretaries.*') ? 'active' : '' }}">
        <i class="fa-solid fa-id-badge"></i> My Staff
    </a>
</li>

<li class="nav-item">
    <a href="#" class="nav-link">
        <i class="fa-solid fa-clipboard-list"></i> Appointments
    </a>
</li>

<li class="px-3 mt-4 mb-2 text-uppercase text-white small">Finance</li>

<li class="nav-item">
    <a href="#" class="nav-link">
        <i class="fa-solid fa-cash-register"></i> Daily Caisse
    </a>
</li>

<li class="nav-item">
    <a href="#" class="nav-link">
        <i class="fa-solid fa-file-invoice"></i> Expenses
    </a>
</li>
