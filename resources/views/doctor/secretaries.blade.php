@extends('layouts.admin')

@section('title', 'My Team')
@section('header', 'Manage Secretaries')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 text-secondary">My Staff</h4>
            <small class="text-muted">Create accounts for your assistants</small>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSecretaryModal">
            <i class="fa-solid fa-user-plus"></i> Add Secretary
        </button>
    </div>


    <div class="row g-4">
        @forelse($secretaries as $secretary)
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="position-absolute top-0 end-0 p-3">
                            <span class="badge {{ $secretary->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $secretary->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <img src="https://ui-avatars.com/api/?name={{ $secretary->name }}&background=eef2ff&color=4f46e5" 
                             class="rounded-circle mb-3" width="80" height="80">
                        
                        <h5 class="fw-bold mb-1">{{ $secretary->name }}</h5>
                        <p class="text-muted small mb-3">{{ $secretary->email }}</p>

                        <div class="d-grid gap-2">
                            <button class="btn btn-light btn-sm border" data-bs-toggle="modal" data-bs-target="#editSecretaryModal{{ $secretary->id }}">
                                <i class="fa-solid fa-pen"></i> Edit Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="editSecretaryModal{{ $secretary->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form action="{{ route('secretaries.update', $secretary->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit {{ $secretary->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ $secretary->name }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email (Login)</label>
                                    <input type="email" name="email" class="form-control" value="{{ $secretary->email }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="text" name="password" class="form-control" placeholder="Leave empty to keep current">
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="activeSwitch{{ $secretary->id }}" {{ $secretary->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="activeSwitch{{ $secretary->id }}">Account Active</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-danger me-auto" 
                                        onclick="if(confirm('Are you sure?')) document.getElementById('delete-form-{{ $secretary->id }}').submit();">
                                    Delete
                                </button>
                                
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </form>
                    <form id="delete-form-{{ $secretary->id }}" action="{{ route('secretaries.destroy', $secretary->id) }}" method="POST" class="d-none">
                        @csrf @method('DELETE')
                    </form>
                </div>
            </div>

        @empty
            <div class="col-12 text-center py-5">
                <div class="text-muted mb-3"><i class="fa-solid fa-user-slash fa-3x"></i></div>
                <h5>No secretaries found</h5>
                <p class="text-muted">Add your first assistant to help manage appointments.</p>
            </div>
        @endforelse
    </div>

    <div class="modal fade" id="createSecretaryModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('secretaries.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Secretary</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info small">
                            <i class="fa-solid fa-circle-info me-1"></i> This user will have access to your calendar and patient list.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email (Login) <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="text" name="password" class="form-control" value="123456" required>
                            <small class="text-muted">Default: 123456</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Account</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection