@extends('layouts.admin')

@section('title', 'Appointments')

@section('content')

    {{-- CUSTOM CSS FOR TABS (Integrated) --}}
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
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                Appointments
            </h4>
            <p class="text-muted small mb-0">
                Manage and monitor patient appointments
            </p>
        </div>
    </div>

    {{-- FILTER CONTROLS (Top Card) --}}
    <div class="card overflow-hidden mb-4">
        <div class="card-header bg-white p-3 border-bottom-0">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="input-group border rounded-3 overflow-hidden">
                        <span class="input-group-text bg-light border-0 ps-3"><i
                                class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" id="ajaxSearchInput" class="form-control border-0 bg-light"
                            placeholder="Search patient, status..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse"
                        data-bs-target="#advancedFilters">
                        <i class="fa-solid fa-sliders me-1"></i> Filters
                    </button>
                </div>
            </div>
            
            <div class="collapse mt-3" id="advancedFilters">
                <div class="p-3 bg-light rounded-3 border">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="small text-muted fw-bold mb-1">Status</label>
                            <select id="filterStatus" class="form-select form-select-sm" multiple>
                                <option value="scheduled">Scheduled</option>
                                <option value="waiting">Waiting</option>
                                <option value="preparing">Preparing</option>
                                <option value="in_consultation">In Consultation</option>
                                <option value="pending_payment">Pending Payment</option>
                                <option value="finished">Finished</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="no_show">No Show</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted fw-bold mb-1">Date Range</label>
                            <div class="input-group input-group-sm">
                                <input type="date" id="filterDateFrom" class="form-control" value="{{ request('date_from', now()->format('Y-m-d')) }}">
                                <span class="input-group-text">to</span>
                                <input type="date" id="filterDateTo" class="form-control" value="{{ request('date_to', now()->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button class="btn btn-sm btn-primary w-100 fw-bold" onclick="applyAdvancedFilters()">Apply</button>
                            <button class="btn btn-sm btn-light border w-100 fw-bold" onclick="resetFilters()">Reset</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABS (Above the Table Card) --}}
    <div class="px-3">
        <ul class="nav custom-tabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link {{ request('quick_filter', 'today_active') === 'today_active' ? 'active' : '' }}" 
                        onclick="switchTab(this, 'today_active')">
                    <i class="fa-solid fa-list-check me-2"></i>Today's Queue
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link {{ request('quick_filter') === 'all' ? 'active' : '' }}" 
                        onclick="switchTab(this, 'all')">
                    <i class="fa-solid fa-layer-group me-2"></i>All Records
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link {{ request('quick_filter') === 'finished' ? 'active' : '' }}" 
                        onclick="switchTab(this, 'finished')">
                    <i class="fa-solid fa-check-circle me-2"></i>Finished
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link {{ request('quick_filter') === 'cancelled' ? 'active' : '' }}" 
                        onclick="switchTab(this, 'cancelled')">
                    <i class="fa-solid fa-ban me-2"></i>Cancelled
                </button>
            </li>
        </ul>
    </div>

    {{-- TABLE CARD --}}
    <div class="card border shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-muted small fw-bold text-uppercase">Time / Queue</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Patient</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Status</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Type</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Payment</th>
                            <th class="text-end pe-4 py-3 text-muted small fw-bold text-uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="appointmentListBody">
                        @include('layouts.partials.appointments_table_rows', ['appointments' => $appointments])
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white border-top p-3 d-flex justify-content-end" id="paginationContainer">
            {{ $appointments->links() }}
        </div>
    </div>

    {{-- MODALS & SCRIPTS --}}
    @include('layouts.partials.payment_modal')

    <script>
        const fetchUrl = "{{ route('appointments.fetch') }}";

        function switchTab(clickedBtn, preset) {
            // Remove active class from all tabs
            document.querySelectorAll('.custom-tabs .nav-link').forEach(btn => {
                btn.classList.remove('active');
            });

            // Add active class to clicked tab
            clickedBtn.classList.add('active');

            // Trigger fetch logic
            if (typeof applyPreset === "function") {
                applyPreset(preset);
            }
        }
    </script>

    <script src="{{ asset('js/appointments.js') }}"></script>
    <script src="{{ asset('js/finish_appointment_modal.js') }}"></script>

    @if(isset($flashAppointment))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                openFullModal('{{ route('appointments.modal', $flashAppointment->id) }}');
            });
        </script>
    @endif

@endsection