@extends('layouts.admin')

@section('title', 'Patient Management')
@section('header', 'All Patients')

@section('content')

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-1 text-secondary">
                Patient Management
            </h4>
            <p class="text-muted small mb-0">View patient files, history, and schedule controls.</p>
        </div>
        
        <button class="btn btn-primary fw-bold shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#createPatientModal">
            <i class="fa-solid fa-user-plus me-2"></i>Add New Patient
        </button>
    </div>

    {{-- SEARCH CARD --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-2">
            <form action="{{ route('patients.index') }}" method="GET">
                <div class="input-group">
                    <span class="input-group-text bg-white border-0 ps-3">
                        <i class="fa-solid fa-magnifying-glass text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-0 bg-white" 
                           placeholder="Search by CIN, Name, or Phone..." value="{{ request('search') }}">
                    <button class="btn btn-primary fw-bold px-4 rounded-end-3 d-none" type="submit">Search</button>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-white border-bottom">
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
                            <tr>
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
                                <td>
                                    <div class="fw-medium text-dark">{{ $patient->age }} Years</div>
                                    <small class="text-muted text-capitalize">{{ $patient->gender }}</small>
                                </td>
                                <td>
                                    @if($patient->mutuelle_provider)
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3">
                                            {{ $patient->mutuelle_provider }}
                                        </span>
                                    @else
                                        <span class="text-muted small opacity-50">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($patient->current_balance > 0)
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3">
                                            Credit: {{ number_format($patient->current_balance, 2) }}
                                        </span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3">
                                            Clear
                                        </span>
                                    @endif
                                </td>

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
                                                        title="Mark Done Today">
                                                    <i class="fa-solid fa-check" style="font-size: 0.8rem;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-muted small opacity-50">-</span>
                                    @endif
                                </td>

                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button class="btn btn-sm btn-white border shadow-sm text-muted" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editPatientModal{{ $patient->id }}" 
                                                title="Edit Details">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <a href="{{ route('patients.show', $patient->id) }}"
                                            class="btn btn-sm btn-white border shadow-sm text-primary" 
                                            title="View History">
                                            <i class="fa-solid fa-file-medical"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            {{-- EDIT MODAL --}}
                            <div class="modal fade" id="editPatientModal{{ $patient->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <form action="{{ route('patients.update', $patient->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-content border-0 shadow-lg">
                                            <div class="modal-header border-bottom-0 pb-0">
                                                <div>
                                                    <h5 class="modal-title fw-bold">Edit Patient File</h5>
                                                    <p class="text-muted small mb-0">Update information for {{ $patient->full_name }}</p>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="row g-3">
                                                    {{-- Personal Info --}}
                                                    <div class="col-12"><h6 class="fw-bold text-primary small text-uppercase border-bottom pb-2 mb-0">Personal Information</h6></div>
                                                    
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-muted">First Name</label>
                                                        <input type="text" name="first_name" class="form-control" value="{{ $patient->first_name }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-muted">Last Name</label>
                                                        <input type="text" name="last_name" class="form-control" value="{{ $patient->last_name }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-muted">CIN (ID)</label>
                                                        <input type="text" name="cin" class="form-control" value="{{ $patient->cin }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-muted">Phone Number</label>
                                                        <input type="text" name="phone" class="form-control" value="{{ $patient->phone }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-muted">Birth Date</label>
                                                        <input type="date" name="birth_date" class="form-control" value="{{ $patient->birth_date ? $patient->birth_date->format('Y-m-d') : '' }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-muted">Gender</label>
                                                        <select name="gender" class="form-select">
                                                            <option value="male" {{ $patient->gender == 'male' ? 'selected' : '' }}>Male</option>
                                                            <option value="female" {{ $patient->gender == 'female' ? 'selected' : '' }}>Female</option>
                                                        </select>
                                                    </div>

                                                    {{-- Insurance --}}
                                                    <div class="col-12 mt-4"><h6 class="fw-bold text-primary small text-uppercase border-bottom pb-2 mb-0">Insurance (Mutuelle)</h6></div>

                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-muted">Provider</label>
                                                        <select name="mutuelle_provider" class="form-select">
                                                            <option value="">None</option>
                                                            <option value="CNSS" {{ $patient->mutuelle_provider == 'CNSS' ? 'selected' : '' }}>CNSS</option>
                                                            <option value="CNOPS" {{ $patient->mutuelle_provider == 'CNOPS' ? 'selected' : '' }}>CNOPS</option>
                                                            <option value="AXA" {{ $patient->mutuelle_provider == 'AXA' ? 'selected' : '' }}>AXA</option>
                                                            <option value="RMA" {{ $patient->mutuelle_provider == 'RMA' ? 'selected' : '' }}>RMA</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-muted">Registration Number</label>
                                                        <input type="text" name="mutuelle_number" class="form-control" value="{{ $patient->mutuelle_number }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0 pt-0 pe-4 pb-4">
                                                <button type="button" class="btn btn-light text-danger me-auto border shadow-sm"
                                                    onclick="if(confirm('Are you sure you want to archive this patient?')) document.getElementById('delete-pat-{{ $patient->id }}').submit()">
                                                    <i class="fa-solid fa-box-archive me-2"></i>Archive
                                                </button>
                                                
                                                <button type="button" class="btn btn-white border text-muted shadow-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary fw-bold shadow-sm px-4">Save Changes</button>
                                            </div>
                                        </div>
                                    </form>
                                    <form id="delete-pat-{{ $patient->id }}" action="{{ route('patients.destroy', $patient->id) }}" method="POST" class="d-none">
                                        @csrf @method('DELETE')
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted opacity-50">
                                        <i class="fa-solid fa-users-slash fa-3x mb-3"></i>
                                        <p class="mb-0">No patients found. Click "Add New Patient" to get started.</p>
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

    {{-- CREATE MODAL --}}
    <div class="modal fade" id="createPatientModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="{{ route('patients.store') }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-bottom-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-bold">Create New Patient File</h5>
                            <p class="text-muted small mb-0">Fill in the details to register a new patient.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            {{-- Personal Info --}}
                            <div class="col-12"><h6 class="fw-bold text-primary small text-uppercase border-bottom pb-2 mb-0">Personal Information</h6></div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">CIN (ID)</label>
                                <input type="text" name="cin" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Phone Number</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Birth Date</label>
                                <input type="date" name="birth_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Gender <span class="text-danger">*</span></label>
                                <select name="gender" class="form-select" required>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>

                            {{-- Insurance --}}
                            <div class="col-12 mt-4"><h6 class="fw-bold text-primary small text-uppercase border-bottom pb-2 mb-0">Insurance (Optional)</h6></div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Provider</label>
                                <select name="mutuelle_provider" class="form-select">
                                    <option value="">None</option>
                                    <option value="CNSS">CNSS</option>
                                    <option value="CNOPS">CNOPS</option>
                                    <option value="AXA">AXA</option>
                                    <option value="RMA">RMA</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Registration Number</label>
                                <input type="text" name="mutuelle_number" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 pe-4 pb-4">
                        <button type="button" class="btn btn-white border text-muted shadow-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold shadow-sm px-4">Create File</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection