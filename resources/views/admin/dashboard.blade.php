@extends('layouts.admin')

@section('title', 'Super Admin Dashboard')
@section('header', 'Dashboard Overview')

@section('content')
<div class="container-fluid p-0">
    
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded text-primary">
                        <i class="fa-solid fa-hospital fa-lg"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-muted mb-0">Total Clinics</h6>
                        <h3 class="fw-bold mb-0">12</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded text-success">
                        <i class="fa-solid fa-user-doctor fa-lg"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-muted mb-0">Active Doctors</h6>
                        <h3 class="fw-bold mb-0">45</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded text-warning">
                        <i class="fa-solid fa-coins fa-lg"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-muted mb-0">Revenue (Dec)</h6>
                        <h3 class="fw-bold mb-0">15k <span class="fs-6 text-muted">DH</span></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">Recently Added Clinics</h5>
        </div>
        <div class="card-body">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Clinic Name</th>
                        <th>Head Doctor</th>
                        <th>Subscription</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Cabinet Al Shifa</td>
                        <td>Dr. Younes</td>
                        <td><span class="badge bg-info text-dark">Pro Plan</span></td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td><button class="btn btn-sm btn-light border">Manage</button></td>
                    </tr>
                    </tbody>
            </table>
        </div>
    </div>

</div>
@endsection