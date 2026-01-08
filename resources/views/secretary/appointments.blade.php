@extends('layouts.admin')

@section('title', 'Appointments')

@section('content')

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
      <div>
            <h4 class="mb-1 text-secondary">
                Appointments
            </h4>
            <p class="text-muted small mb-0">
                Manage and monitor patient appointments
            </p>
        </div>
    
    
        <div>
        </div>
    </div>

    {{-- FILTER CONTROLS --}}
    <div class="card overflow-hidden border-0 shadow-sm mb-4">

        {{-- 1. SEARCH & TOGGLE --}}
        <div class="card-header bg-white p-3 border-bottom-0">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="input-group">
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
        </div>

        {{-- 2. QUICK TABS (PRESETS) --}}
        <div class="card-body p-2 border-top d-flex gap-2 overflow-auto" id="quickFilterGroup">
            <button class="btn btn-sm rounded-pill px-3 fw-bold filter-preset active-preset" data-preset="today_active"
                onclick="applyPreset('today_active')">
                <i class="fa-solid fa-list-check me-1"></i> Today's Queue
            </button>

            <button class="btn btn-sm rounded-pill px-3 fw-bold filter-preset text-muted" data-preset="all"
                onclick="applyPreset('all')">
                <i class="fa-solid fa-layer-group me-1"></i> All Records
            </button>

            <button class="btn btn-sm rounded-pill px-3 fw-bold filter-preset text-muted" data-preset="finished"
                onclick="applyPreset('finished')">
                <i class="fa-solid fa-check-circle me-1"></i> Finished
            </button>

            <button class="btn btn-sm rounded-pill px-3 fw-bold filter-preset text-muted" data-preset="cancelled"
                onclick="applyPreset('cancelled')">
                <i class="fa-solid fa-ban me-1"></i> Cancelled
            </button>
        </div>

        {{-- 3. ADVANCED FILTERS (COLLAPSIBLE) --}}
        <div class="collapse {{ request()->anyFilled(['date_from', 'date_to', 'statuses']) ? 'show' : '' }}"
            id="advancedFilters">
            <div class="card-body border-top bg-white p-3">
                <div class="row g-3">
                    {{-- Status Select --}}
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
                        <small class="text-xs text-muted">Hold Ctrl to select multiple</small>
                    </div>

                    {{-- Date Range --}}
                    <div class="col-md-4">
                        <label class="small text-muted fw-bold mb-1">Date Range</label>
                        <div class="input-group input-group-sm">
                            <input type="date" id="filterDateFrom" class="form-control"
                                value="{{ request('date_from', now()->format('Y-m-d')) }}">
                            <span class="input-group-text">to</span>
                            <input type="date" id="filterDateTo" class="form-control"
                                value="{{ request('date_to', now()->format('Y-m-d')) }}">
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button class="btn btn-sm btn-primary w-100" onclick="applyAdvancedFilters()">
                            <i class="fa-solid fa-filter me-1"></i> Apply Filters
                        </button>
                        <button class="btn btn-sm btn-light border w-100" onclick="resetFilters()">
                            <i class="fa-solid fa-rotate-left me-1"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE CONTAINER --}}
    <div class="card overflow-hidden border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-muted small fw-bold text-uppercase">Time / Queue</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase">Patient</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase">Status</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase">Type</th>
                        <th class="text-end pe-4 py-3 text-muted small fw-bold text-uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody id="appointmentListBody">
                    @include('layouts.partials.appointments_table_rows', ['appointments' => $appointments])
                </tbody>
            </table>
        </div>
        <div class="p-3 d-flex justify-content-end" id="paginationContainer">
            {{ $appointments->links() }}
        </div>
    </div>

    {{-- INCLUDE MODALS & SCRIPTS --}}
    <!--@include('layouts.partials.book_modal')-->
    <script>
        const fetchUrl = "{{ route('appointments.fetch') }}";
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