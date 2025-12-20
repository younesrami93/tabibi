@extends('layouts.admin')

@section('title', 'Doctor Dashboard')
@section('header', 'Cabinet Overview')

@section('content')

<div class="row g-3 mb-4">
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-uppercase text-muted small mb-0">Waiting Room</h6>
                    <span class="badge bg-warning text-dark"><i class="fa-regular fa-clock"></i> Live</span>
                </div>
                <h2 class="fw-bold mb-0">5 <small class="fs-6 text-muted">Patients</small></h2>
                <small class="text-muted">Avg Wait: 15 mins</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-uppercase text-muted small mb-0">Today's RDV</h6>
                    <i class="fa-solid fa-calendar-day text-primary opacity-50"></i>
                </div>
                <h2 class="fw-bold mb-0">12</h2>
                <div class="progress mt-2" style="height: 5px;">
                    <div class="progress-bar bg-primary" style="width: 40%"></div>
                </div>
                <small class="text-muted">4 Completed / 12 Total</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-uppercase text-muted small mb-0">Caisse du Jour</h6>
                    <button class="btn btn-sm btn-link text-muted p-0" onclick="toggleRevenue()">
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
                <h2 class="fw-bold mb-0 text-success" id="revenueText">2,400 <small class="fs-6">DH</small></h2>
                <small class="text-muted">Cash: 1,800 | Card: 600</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center clickable-card" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#quickAddPatient">
                <i class="fa-solid fa-user-plus fa-2x mb-2"></i>
                <h6 class="fw-bold mb-0">New Patient</h6>
                <small class="opacity-75">Walk-in / Urgent</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fa-solid fa-list-ol text-warning me-2"></i> Current Queue</h5>
                <button class="btn btn-sm btn-outline-primary">View Full List</button>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    
                    <div class="list-group-item p-3 border-start border-4 border-primary bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=Ahmed+Benali&background=random" class="rounded-circle me-3" width="45">
                                <div>
                                    <h6 class="mb-0 fw-bold">Ahmed Benali <span class="badge bg-primary ms-2">IN OFFICE</span></h6>
                                    <small class="text-muted">Reason: Follow-up check</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="text-primary fw-bold">00:15</div>
                                <small class="text-muted">Duration</small>
                            </div>
                            <div class="ms-3">
                                <button class="btn btn-success btn-sm"><i class="fa-solid fa-check"></i> Finish</button>
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="me-3 text-center" style="width: 45px;">
                                    <h5 class="mb-0 fw-bold text-muted">#2</h5>
                                </div>
                                <div>
                                    <h6 class="mb-0">Fatima Zahra</h6>
                                    <small class="text-warning"><i class="fa-regular fa-clock"></i> Waiting: 20 mins</small>
                                </div>
                            </div>
                            <button class="btn btn-outline-primary btn-sm">Call Next <i class="fa-solid fa-arrow-right"></i></button>
                        </div>
                    </div>

                    <div class="list-group-item p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="me-3 text-center" style="width: 45px;">
                                    <h5 class="mb-0 fw-bold text-muted">#3</h5>
                                </div>
                                <div>
                                    <h6 class="mb-0">Karim Tazi</h6>
                                    <small class="text-warning"><i class="fa-regular fa-clock"></i> Waiting: 5 mins</small>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark border">Walk-in</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Agenda Timeline</h5>
            </div>
            <div class="card-body">
                <ul class="timeline list-unstyled">
                    <li class="d-flex mb-4">
                        <div class="me-3 text-end" style="width: 60px;">
                            <span class="fw-bold">09:00</span>
                        </div>
                        <div class="border-start ps-4 border-2 border-success position-relative">
                            <span class="position-absolute top-0 start-0 translate-middle bg-success rounded-circle" style="width: 10px; height: 10px;"></span>
                            <h6 class="mb-1">Morning Consultation</h6>
                            <small class="text-muted">Patient: Sarah L.</small>
                            <span class="badge bg-success bg-opacity-10 text-success d-block w-auto mt-1" style="width: fit-content;">Done</span>
                        </div>
                    </li>

                    <li class="d-flex mb-4">
                        <div class="me-3 text-end" style="width: 60px;">
                            <span class="fw-bold">10:30</span>
                        </div>
                        <div class="border-start ps-4 border-2 border-primary position-relative">
                            <span class="position-absolute top-0 start-0 translate-middle bg-primary rounded-circle" style="width: 10px; height: 10px;"></span>
                            <h6 class="mb-1">Surgery / Procedure</h6>
                            <small class="text-muted">Minor surgery (Room 2)</small>
                        </div>
                    </li>

                    <li class="d-flex mb-4">
                        <div class="me-3 text-end" style="width: 60px;">
                            <span class="fw-bold">12:00</span>
                        </div>
                        <div class="border-start ps-4 border-2 border-secondary position-relative">
                            <span class="position-absolute top-0 start-0 translate-middle bg-secondary rounded-circle" style="width: 10px; height: 10px;"></span>
                            <h6 class="mb-1 text-muted">Break / Lunch</h6>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    // Simple Privacy Toggle for Revenue
    function toggleRevenue() {
        const text = document.getElementById('revenueText');
        const icon = document.getElementById('eyeIcon');
        
        if (text.classList.contains('blur-text')) {
            text.classList.remove('blur-text');
            text.innerText = '2,400 DH';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            text.classList.add('blur-text');
            text.innerText = '**** DH';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }
</script>

@endsection