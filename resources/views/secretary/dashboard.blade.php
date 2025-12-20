@extends('layouts.admin')

@section('title', 'Secretary Dashboard')
@section('header', 'Front Desk')

@section('content')

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="fw-bold text-primary mb-1">👋 Bonjour! Ready to check someone in?</h5>
                <p class="text-muted mb-0">Search for a patient by CIN, Name, or Phone number.</p>
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" placeholder="Search Patient...">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPatientModal">
                        <i class="fa-solid fa-plus"></i> New
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-lg-8">
        
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-warning bg-opacity-10 text-warning h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="fa-solid fa-users fa-2x me-3"></i>
                        <div>
                            <h4 class="fw-bold mb-0 text-dark">5</h4>
                            <small class="text-dark">Waiting Now</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-success bg-opacity-10 text-success h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="fa-solid fa-check-double fa-2x me-3"></i>
                        <div>
                            <h4 class="fw-bold mb-0 text-dark">12</h4>
                            <small class="text-dark">Completed</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-primary bg-opacity-10 text-primary h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="fa-solid fa-calendar-day fa-2x me-3"></i>
                        <div>
                            <h4 class="fw-bold mb-0 text-dark">18</h4>
                            <small class="text-dark">Total RDV Today</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"> Salle d'Attente (Queue)</h5>
                <span class="badge bg-danger animate-pulse">Live Updates</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Patient Name</th>
                            <th>Arrived At</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-primary border-primary border-start border-4">
                            <td class="ps-4 fw-bold">1</td>
                            <td>
                                <div class="fw-bold">Ahmed Benali</div>
                                <small class="text-muted">Follow-up</small>
                            </td>
                            <td>09:15 <small class="text-muted">(45m ago)</small></td>
                            <td><span class="badge bg-primary">In Consultation</span></td>
                            <td>
                                <button class="btn btn-sm btn-light border" disabled>Edit</button>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-4 fw-bold">2</td>
                            <td>
                                <div class="fw-bold">Fatima Zahra</div>
                                <small class="text-muted">New Consultation</small>
                            </td>
                            <td>09:40 <small class="text-muted text-danger">(20m ago)</small></td>
                            <td><span class="badge bg-warning text-dark">Waiting</span></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border" data-bs-toggle="dropdown">Actions</button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">Mark as No-Show</a></li>
                                        <li><a class="dropdown-item" href="#">Edit Details</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#">Cancel</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-4 fw-bold">3</td>
                            <td>
                                <div class="fw-bold">Karim Tazi</div>
                                <small class="text-muted">Urgency</small>
                            </td>
                            <td>09:55 <small class="text-muted text-success">(5m ago)</small></td>
                            <td><span class="badge bg-warning text-dark">Waiting</span></td>
                            <td>
                                <button class="btn btn-sm btn-light border">Actions</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Upcoming Today</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    
                    <div class="list-group-item p-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-center border rounded p-2 bg-light">
                                <div class="fw-bold text-dark">10:30</div>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">Sarah L.</h6>
                                <small class="text-muted">Confirmed</small>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-success">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i> Check-in
                        </button>
                    </div>

                    <div class="list-group-item p-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-center border rounded p-2 bg-light">
                                <div class="fw-bold text-dark">11:00</div>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">Mohamed K.</h6>
                                <small class="text-muted">Not Confirmed</small>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-success">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i> Check-in
                        </button>
                    </div>

                    <div class="list-group-item p-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-center border rounded p-2 bg-light">
                                <div class="fw-bold text-dark">11:30</div>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">Yassine B.</h6>
                                <small class="text-muted">Confirmed</small>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-success">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i> Check-in
                        </button>
                    </div>

                </div>
            </div>
            <div class="card-footer bg-white text-center">
                <a href="#" class="text-decoration-none small fw-bold">View Full Calendar <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createPatientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-5">
                <p class="text-muted">We will build this form next!</p>
            </div>
        </div>
    </div>
</div>

@endsection