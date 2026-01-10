@extends('layouts.admin')

@section('title', 'Patient Management')
@section('header', 'All Patients')

@section('content')

 <style>
        .custom-tabs {
            display: flex;
            gap: 0.25rem;
            border-bottom: none;
            position: relative;
            margin-bottom: -1px;
            z-index: 10;
        }

        .custom-tabs .nav-link {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-bottom: none;
            padding: .65rem 1.25rem;
            font-weight: 600;
            color: #6c757d;
            transition: all 0.15s ease;
            margin-bottom: 0px !important;
            border-radius: .5rem .5rem 0 0 !important;
        }

        .custom-tabs .nav-link:hover {
            background: #ffffff;
            color: #0d6efd;
        }

        .custom-tabs .nav-link.active {
            background: #ffffff;
            color: #0d6efd;
            border-color: #dee2e6;
            border-bottom: 1px solid white;
            z-index: 11;
        }
    </style>    

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                Patient Management
            </h4>
            <p class="text-muted small mb-0">View patient files, history, and financial status.</p>
        </div>
        
        <div>
        </div>
    </div>


    {{-- 1. SEARCH CARD --}}
    <div class="card  mb-5">
        <div class="card-body p-3">
            <form action="{{ route('patients.index') }}" method="GET">
                @if(request('balance_filter'))
                    <input type="hidden" name="balance_filter" value="{{ request('balance_filter') }}">
                @endif
                
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 ps-3 text-muted">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 bg-white shadow-none" 
                           placeholder="Search by CIN, Name, Phone..." 
                           value="{{ request('search') }}">
                    <button class="btn btn-primary fw-bold px-4" type="submit">Search</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 2. TABS (Upgraded UI) --}}
    <ul class="nav custom-tabs ms-3" role="tablist">

        {{-- ALL PATIENTS --}}
        <li class="nav-item">
            <a href="{{ route('patients.index', array_merge(request()->all(), ['balance_filter' => 'all'])) }}"
               class="nav-link {{ request('balance_filter', 'all') == 'all' ? 'active' : '' }}">
                <i class="fa-solid fa-users me-2"></i>All Patients
            </a>
        </li>

        {{-- DEBT --}}
        <li class="nav-item">
            <a href="{{ route('patients.index', array_merge(request()->all(), ['balance_filter' => 'debt'])) }}"
               class="nav-link {{ request('balance_filter') == 'debt' ? 'active' : '' }}"
               style="{{ request('balance_filter') == 'debt' ? 'color:#dc3545;' : '' }}">
                <i class="fa-solid fa-circle-exclamation me-2"></i>Has Credit / Unpaid
            </a>
        </li>

    </ul>

    {{-- 3. TABLE CARD --}}
    <div class="card border  rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-muted fw-bold small text-uppercase py-3">Patient Name</th>
                            <th class="text-muted fw-bold small text-uppercase py-3">Contact Info</th>
                            <th class="text-muted fw-bold small text-uppercase py-3">Age / Gender</th>
                            <th class="text-muted fw-bold small text-uppercase py-3">Mutuelle</th>
                            <th class="text-muted fw-bold small text-uppercase py-3">Balance</th>
                            <th class="text-muted fw-bold small text-uppercase py-3">Status</th>
                            <th class="text-muted fw-bold small text-uppercase py-3">Next Control</th>
                            <th class="text-end pe-4 text-muted fw-bold small text-uppercase py-3">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($patients as $patient)
                            <tr onclick="window.location='{{ route('patients.show', $patient->id) }}'" style="cursor: pointer;">
                                
                                {{-- NAME --}}
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3 bg-{{ $patient->gender == 'male' ? 'primary' : 'danger' }} bg-opacity-10 text-{{ $patient->gender == 'male' ? 'primary' : 'danger' }} fw-bold d-flex justify-content-center align-items-center rounded-circle border border-{{ $patient->gender == 'male' ? 'primary' : 'danger' }} border-opacity-25"
                                             style="width: 40px; height: 40px; font-size: 0.9rem;">
                                            {{ substr($patient->first_name, 0, 1) }}{{ substr($patient->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $patient->full_name }}</div>
                                            <small class="text-muted" style="font-size: 0.75rem;">File #{{ $patient->id }}</small>
                                        </div>
                                    </div>
                                </td>

                                {{-- CONTACT --}}
                                <td>
                                    <div class="d-flex flex-column small">
                                        <div class="mb-1 text-dark">
                                            <i class="fa-regular fa-id-card me-1 text-muted"></i> {{ $patient->cin ?? '--' }}
                                        </div>
                                        <div class="text-muted">
                                            <i class="fa-solid fa-phone me-1 text-muted"></i> {{ $patient->phone ?? '--' }}
                                        </div>
                                    </div>
                                </td>

                                {{-- AGE / GENDER --}}
                                <td>
                                    <div class="fw-medium text-dark">{{ $patient->age }} Years</div>
                                    <small class="text-muted text-capitalize">{{ $patient->gender }}</small>
                                </td>

                                {{-- MUTUELLE --}}
                                <td>
                                    @if($patient->mutuelle_provider)
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3">
                                            {{ $patient->mutuelle_provider }}
                                        </span>
                                    @else
                                        <span class="text-muted small opacity-50">-</span>
                                    @endif
                                </td>

                                {{-- BALANCE --}}
                                <td>
                                    @if($patient->current_balance > 0)
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger fw-bold rounded-pill shadow-sm"
                                                onclick="event.stopPropagation(); openPaymentModal('{{ route('patients.payment', $patient->id) }}', {{ $patient->current_balance }})"
                                                title="Pay Total Debt">
                                            <i class="fa-solid fa-hand-holding-dollar me-1"></i> 
                                            Pay {{ number_format($patient->current_balance, 2) }} DH
                                        </button>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">
                                            <i class="fa-solid fa-check me-1"></i> Clear
                                        </span>
                                    @endif
                                </td>

                                {{-- STATUS --}}
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-secondary bg-opacity-10 text-dark rounded-pill">
                                            {{ $patient->appointments_count }} Visit(s)
                                        </span>
                                        @if($patient->lastAppointment)
                                            <small class="text-muted" style="font-size: 0.7rem;" title="Last Visit">
                                                {{ $patient->lastAppointment->scheduled_at->diffForHumans(null, true) }} ago
                                            </small>
                                        @endif
                                    </div>
                                </td>

                                {{-- NEXT CONTROL --}}
                                <td>
                                    @if($patient->nextControl)
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <div>
                                                @if($patient->nextControl->scheduled_at->isPast())
                                                    <div class="text-danger fw-bold small" title="Overdue">
                                                        <i class="fa-solid fa-circle-exclamation me-1"></i>
                                                        {{ $patient->nextControl->scheduled_at->format('d/m') }}
                                                    </div>
                                                @else
                                                    <div class="text-primary fw-bold small">
                                                        <i class="fa-regular fa-calendar-check me-1"></i>
                                                        {{ $patient->nextControl->scheduled_at->format('d/m') }}
                                                    </div>
                                                @endif
                                                <small class="text-muted" style="font-size: 0.7rem;">
                                                    {{ $patient->nextControl->scheduled_at->format('H:i') }}
                                                </small>
                                            </div>

                                            <form action="{{ route('appointments.complete_control', $patient->nextControl->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-success bg-opacity-10 text-success border-0 rounded-circle" 
                                                        style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;"
                                                        title="Mark Done Today"
                                                        onclick="event.stopPropagation()">
                                                    <i class="fa-solid fa-check" style="font-size: 0.8rem;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-muted small opacity-50">-</span>
                                    @endif
                                </td>

                                {{-- ACTIONS --}}
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">

                                        <button type="button" class="btn btn-sm btn-light border text-secondary shadow-sm" 
                                                onclick="event.stopPropagation(); openFullModal('{{ route('patients.edit_modal', $patient->id) }}')"
                                                title="Edit Profile">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>

                                        <a href="{{ route('patients.show', $patient->id) }}"
                                           class="btn btn-sm btn-light border text-secondary shadow-sm rounded-circle d-flex align-items-center justify-content-center" 
                                           style="width: 32px; height: 32px;"
                                           title="View Profile"
                                           onclick="event.stopPropagation()">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted opacity-50">
                                        <i class="fa-solid fa-users-slash fa-3x mb-3"></i>
                                        <p class="mb-0">No patients found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>

        @if($patients->hasPages())
            <div class="card-footer bg-white border-top p-3">
                {{ $patients->links() }}
            </div>
        @endif
    </div>

    {{-- MODALS & SCRIPTS --}}
    @include('layouts.partials.payment_modal')

    <div id="dynamic-modal-container"></div>
    <script>
        function openEditPatientModal(url) {
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const container = document.getElementById('dynamic-modal-container');
                    container.innerHTML = html;
                    const modal = new bootstrap.Modal(container.querySelector('.modal'));
                    modal.show();
                });
        }

        function openCreatePatientModal(url) {
             fetch(url)
                .then(response => response.text())
                .then(html => {
                    const container = document.getElementById('dynamic-modal-container');
                    container.innerHTML = html;
                    const modal = new bootstrap.Modal(container.querySelector('.modal'));
                    modal.show();
                });
        }
    </script>

@endsection
