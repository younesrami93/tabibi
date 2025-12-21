<li class="nav-item">
    <a href="{{ route('appointments.index') }}"
        class="nav-link {{ request()->routeIs('appointments.index') ? 'active' : '' }}">
        <i class="fa-solid fa-clipboard-list"></i> Appointments
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('patients.index') }}" class="nav-link {{ request()->routeIs('patients.*') ? 'active' : '' }}">
        <i class="fa-solid fa-users"></i> Patients
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('services.index') }}" class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}">
        <i class="fa-solid fa-briefcase-medical"></i> Services
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('secretaries.index') }}"
        class="nav-link {{ request()->routeIs('secretaries.*') ? 'active' : '' }}">
        <i class="fa-solid fa-id-badge"></i> My Staff
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('catalog.index') }}" class="nav-link {{ request()->routeIs('catalog.*') ? 'active' : '' }}">
        <i class="fa-solid fa-book-medical"></i> Medical Catalog
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('prescriptions_templates.index') }}"
        class="nav-link {{ request()->routeIs('prescriptions_templates.*') ? 'active' : '' }}">
        <i class="fa-solid fa-clipboard-list"></i> Prescription Templates
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