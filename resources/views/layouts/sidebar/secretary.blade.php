<li class="nav-item">
    <a href="#" class="nav-link text-warning">
        <i class="fa-solid fa-stopwatch"></i> Waiting Room
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('appointments.index') }}" 
       class="nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
        <i class="fa-solid fa-calendar-plus"></i> Appointments
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('patients.index') }}" 
       class="nav-link {{ request()->routeIs('patients.*') ? 'active' : '' }}">
        <i class="fa-solid fa-users"></i> Patients
    </a>
</li>

<li class="nav-item">
    <a href="#" class="nav-link">
        <i class="fa-solid fa-box-open"></i> Cashbox (Caisse)
    </a>
</li>