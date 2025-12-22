@extends('layouts.admin')

@section('title', 'Doctor Dashboard')
@section('header', 'Cabinet Overview')

@section('content')

{{-- 1. STATS ROW --}}
<div class="row g-3 mb-4">
    
    {{-- Waiting Room Card --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-uppercase text-muted small mb-0 fw-bold">Waiting Room</h6>
                    @if($waitingCount > 0)
                        <span class="badge bg-warning text-dark animate-pulse"><i class="fa-solid fa-circle me-1 small"></i> Live</span>
                    @else
                        <span class="badge bg-light text-muted border">Empty</span>
                    @endif
                </div>
                <h2 class="fw-bold mb-0 text-dark">{{ $waitingCount }} <small class="fs-6 text-muted fw-normal">Patients</small></h2>
                <small class="text-muted">Currently queued</small>
            </div>
        </div>
    </div>

    {{-- Today's Progress Card --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-uppercase text-muted small mb-0 fw-bold">Today's RDV</h6>
                    <i class="fa-solid fa-calendar-day text-primary opacity-50"></i>
                </div>
                <h2 class="fw-bold mb-0 text-dark">{{ $todayTotal }}</h2>
                
                {{-- Progress Bar Calculation --}}
                @php 
                    $percent = $todayTotal > 0 ? ($todayCompleted / $todayTotal) * 100 : 0; 
                @endphp
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-primary rounded-pill" style="width: {{ $percent }}%"></div>
                </div>
                <small class="text-muted">{{ $todayCompleted }} Finished / {{ $todayTotal }} Total</small>
            </div>
        </div>
    </div>

    {{-- Revenue Card (With Privacy Toggle) --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-uppercase text-muted small mb-0 fw-bold">Caisse du Jour</h6>
                    <button class="btn btn-sm btn-link text-muted p-0" onclick="toggleRevenue()" title="Hide/Show">
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
                <h2 class="fw-bold mb-0 text-success" id="revenueText">
                    {{ number_format($dailyRevenue, 0) }} <small class="fs-6 text-muted">DH</small>
                </h2>
                <small class="text-muted">Daily Total</small>
            </div>
        </div>
    </div>

    {{-- Quick Action Card --}}
    <div class="col-md-3">
        <button class="card border-0 shadow-sm bg-primary text-white h-100 w-100 text-start btn btn-primary p-0" 
                data-bs-toggle="modal" data-bs-target="#bookAppointmentModal">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center h-100 transition-transform hover-scale">
                <div class="bg-white bg-opacity-25 rounded-circle mb-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="fa-solid fa-plus fa-lg text-white"></i>
                </div>
                <h6 class="fw-bold mb-0">Book Appointment</h6>
                <small class="opacity-75">Add to Schedule</small>
            </div>
        </button>
    </div>
</div>

<div class="row g-4">
    
    {{-- 2. LIVE QUEUE LIST --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100 overflow-hidden">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="card-title mb-0 fw-bold text-dark">
                    <i class="fa-solid fa-list-ol text-primary me-2"></i>Live Queue
                </h5>
                <a href="{{ route('appointments.index') }}" class="btn btn-sm btn-light border text-muted fw-bold">
                    Manage Queue <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($queue as $index => $appt)
                        <div class="list-group-item p-3 {{ $appt->status == 'in_consultation' ? 'bg-primary bg-opacity-10 border-start border-4 border-primary' : '' }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    {{-- Avatar / Number --}}
                                    <div class="me-3">
                                        @if($appt->status == 'in_consultation')
                                            <div class="position-relative">
                                                <img src="https://ui-avatars.com/api/?name={{ $appt->patient->full_name }}&background=2563eb&color=fff" class="rounded-circle shadow-sm" width="45" height="45">
                                                <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle p-1"></span>
                                            </div>
                                        @else
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center border fw-bold text-muted" style="width: 45px; height: 45px;">
                                                #{{ $index + 1 }}
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Info --}}
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">
                                            {{ $appt->patient->full_name }}
                                            @if($appt->status == 'in_consultation')
                                                <span class="badge bg-primary ms-2 shadow-sm">IN OFFICE</span>
                                            @endif
                                        </h6>
                                        <div class="text-muted small">
                                            @if($appt->status == 'waiting')
                                                <i class="fa-regular fa-clock me-1 text-warning"></i> Waiting since {{ $appt->updated_at->format('H:i') }}
                                            @else
                                                <span class="text-primary fw-bold">Consultation started {{ $appt->updated_at->format('H:i') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Action --}}
                                <div>
                                    @if($appt->status == 'in_consultation')
                                        <form action="{{ route('appointments.update_status', $appt->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT') <input type="hidden" name="status" value="finished">
                                            <button class="btn btn-success btn-sm fw-bold shadow-sm px-3">
                                                <i class="fa-solid fa-check me-1"></i> Finish
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('appointments.update_status', $appt->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT') <input type="hidden" name="status" value="in_consultation">
                                            <button class="btn btn-outline-primary btn-sm fw-bold px-3">
                                                Call <i class="fa-solid fa-bullhorn ms-1"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="mb-3 text-muted opacity-25">
                                <i class="fa-solid fa-mug-hot fa-3x"></i>
                            </div>
                            <h6 class="text-muted fw-bold">Waiting room is empty</h6>
                            <p class="text-muted small mb-0">No patients are currently waiting.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- 3. UPCOMING / SCHEDULE --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-dark">Upcoming</h5>
            </div>
            <div class="card-body">
                @if($nextAppointments->isEmpty())
                    <div class="text-center py-4 text-muted small">
                        No upcoming appointments for today.
                    </div>
                @else
                    <ul class="timeline list-unstyled mb-0">
                        @foreach($nextAppointments as $appt)
                            <li class="d-flex mb-4">
                                <div class="me-3 text-end" style="width: 50px;">
                                    <span class="fw-bold text-dark">{{ $appt->scheduled_at->format('H:i') }}</span>
                                </div>
                                <div class="border-start ps-3 border-2 {{ $loop->last ? 'border-transparent' : 'border-light' }} position-relative pb-1">
                                    <span class="position-absolute top-0 start-0 translate-middle bg-white border border-2 border-primary rounded-circle" style="width: 12px; height: 12px;"></span>
                                    <h6 class="mb-1 fw-bold text-dark" style="font-size: 0.9rem;">{{ $appt->patient->full_name }}</h6>
                                    <div class="text-muted small">
                                        {{ ucfirst($appt->type) }}
                                        @if($appt->notes) 
                                            <br><span class="fst-italic text-muted opacity-75">"{{ Str::limit($appt->notes, 20) }}"</span>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Privacy Toggle Script --}}
<script>
    function toggleRevenue() {
        const text = document.getElementById('revenueText');
        const icon = document.getElementById('eyeIcon');
        const originalValue = '{{ number_format($dailyRevenue, 0) }} <small class="fs-6 text-muted">DH</small>';
        
        if (text.classList.contains('blur-text')) {
            text.classList.remove('blur-text');
            text.innerHTML = originalValue;
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            text.classList.add('blur-text');
            text.innerHTML = '**** <small class="fs-6 text-muted">DH</small>';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }
</script>

<style>
    .blur-text { filter: blur(4px); opacity: 0.6; }
    .hover-scale:hover { transform: scale(1.05); }
    .transition-transform { transition: transform 0.2s ease; }
    @keyframes pulse-green {
        0% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.4); }
        70% { box-shadow: 0 0 0 6px rgba(25, 135, 84, 0); }
        100% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
    }
    .animate-pulse { animation: pulse-green 2s infinite; }
</style>

@endsection