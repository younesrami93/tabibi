
<li class="nav-item">
    <a href="{{ route('clinics.index') }}" class="nav-link {{ request()->routeIs('clinics.*') ? 'active' : '' }}">
        <i class="fa-solid fa-hospital"></i> Clinics
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('doctors.index') }}" class="nav-link {{ request()->routeIs('doctors.*') ? 'active' : '' }}">
        <i class="fa-solid fa-user-doctor"></i> Doctors List
    </a>
</li>

<li class="nav-item">
    <a href="#" class="nav-link">
        <i class="fa-solid fa-money-bill-wave"></i> Revenue & Subs
    </a>
</li>

<li class="nav-item">
    <a href="#" class="nav-link">
        <i class="fa-solid fa-gear"></i> Platform Settings
    </a>
</li>