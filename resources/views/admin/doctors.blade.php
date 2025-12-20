@extends('layouts.admin')

@section('title', 'Manage Doctors')
@section('header', 'Doctors List')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 text-secondary">Registered Doctors</h4>
        <small class="text-muted">Manage user accounts and access</small>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
        <i class="fa-solid fa-user-plus"></i> Add Doctor
    </button>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form action="{{ route('doctors.index') }}" method="GET">
            <div class="input-group">
                <span class="input-group-text bg-white border-0"><i class="fa-solid fa-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-0" placeholder="Search by Doctor Name, Email, or Clinic..." value="{{ request('search') }}">
                <button class="btn btn-light text-primary fw-bold">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Doctor</th>
                    <th>Clinic (Cabinet)</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($doctors as $doctor)
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name={{ $doctor->name }}&background=random" class="rounded-circle me-3" width="40" height="40">
                            <div>
                                <div class="fw-bold">{{ $doctor->name }}</div>
                                <div class="small text-muted">{{ $doctor->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($doctor->clinic)
                            <a href="#" class="text-decoration-none fw-bold text-dark">
                                <i class="fa-solid fa-hospital me-1 text-secondary"></i> {{ $doctor->clinic->name }}
                            </a>
                        @else
                            <span class="badge bg-warning text-dark">Unassigned</span>
                        @endif
                    </td>
                    <td>
                        @if($doctor->is_active)
                            <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger">Banned</span>
                        @endif
                    </td>
                    <td class="text-muted small">
                        {{ $doctor->created_at->format('d M, Y') }}
                    </td>
                    <td class="text-end pe-4">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Edit Details</a></li>
                                <li><a class="dropdown-item text-warning" href="#">Reset Password</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#">Ban Account</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white border-0">
        {{ $doctors->links() }}
    </div>
</div>

<div class="modal fade" id="addDoctorModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('doctors.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Doctor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Assign to Clinic</label>
                        <select name="clinic_id" class="form-select" required>
                            <option value="">Select a Clinic...</option>
                            @foreach(\App\Models\Clinic::all() as $clinic)
                                <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Doctor Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="text" name="password" class="form-control" value="123456">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Doctor</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection