@extends('layouts.admin')

@section('title', 'My Team')
@section('header', 'Manage Secretaries')

@section('content')

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">My Staff</h4>
            <p class="text-muted small mb-0">Manage secretary accounts and access.</p>
        </div>
        <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#createSecretaryModal">
            <i class="fa-solid fa-user-plus me-2"></i> Add Secretary
        </button>
    </div>

    {{-- FILTER BAR --}}
    <div class="card mb-4">
        <div class="card-body p-3">
            <form action="{{ route('secretaries.index') }}" method="GET" class="row g-3 align-items-center">
                
                {{-- Search Input --}}
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 ps-3 text-muted">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 bg-light shadow-none" 
                               placeholder="Search by name or email..." 
                               value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Status Filter --}}
                <div class="col-md-3">
                    <select name="status" class="form-select bg-light border-0 shadow-none text-secondary fw-bold" onchange="this.form.submit()">
                        <option value="all">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                    </select>
                </div>

                {{-- Reset --}}
                <div class="col-md-2">
                    <a href="{{ route('secretaries.index') }}" class="btn btn-light border w-100 text-muted fw-bold">
                        <i class="fa-solid fa-rotate-left me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-uppercase small text-muted fw-bold">Profile</th>
                        <th class="py-3 text-uppercase small text-muted fw-bold">Contact (Login)</th>
                        <th class="py-3 text-uppercase small text-muted fw-bold">Status</th>
                        <th class="py-3 text-uppercase small text-muted fw-bold">Joined</th>
                        <th class="text-end pe-4 py-3 text-uppercase small text-muted fw-bold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($secretaries as $secretary)
                        <tr>
                            {{-- Profile --}}
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name={{ $secretary->name }}&background=eef2ff&color=4f46e5" 
                                         class="rounded-circle me-3" width="40" height="40" alt="Avatar">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $secretary->name }}</div>
                                        <div class="small text-muted">Secretary</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Contact --}}
                            <td>
                                <div class="text-dark">{{ $secretary->email }}</div>
                            </td>

                            {{-- Status --}}
                            <td>
                                @if($secretary->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                        <i class="fa-solid fa-circle-check me-1"></i> Active
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">
                                        <i class="fa-solid fa-ban me-1"></i> Inactive
                                    </span>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td>
                                <span class="text-muted small">{{ $secretary->created_at->format('d M, Y') }}</span>
                            </td>

                            {{-- Actions --}}
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light border text-secondary shadow-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editSecretaryModal{{ $secretary->id }}">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                            </td>
                        </tr>

                        {{-- EDIT MODAL (Inside Loop for simplicity) --}}
                        <div class="modal fade" id="editSecretaryModal{{ $secretary->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="{{ route('secretaries.update', $secretary->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-content border-0 shadow-lg rounded-4">
                                        <div class="modal-header border-bottom-0">
                                            <h5 class="modal-title fw-bold">Edit Profile</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4 pt-0">
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold text-muted">Full Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $secretary->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold text-muted">Email (Login)</label>
                                                <input type="email" name="email" class="form-control" value="{{ $secretary->email }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold text-muted">New Password</label>
                                                <input type="text" name="password" class="form-control" placeholder="Leave empty to keep current">
                                            </div>
                                            <div class="form-check form-switch p-3 bg-light rounded border">
                                                <input class="form-check-input ms-0 me-3" type="checkbox" name="is_active" 
                                                       id="switch{{ $secretary->id }}" {{ $secretary->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="switch{{ $secretary->id }}">Account Active</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 px-4 pb-4 justify-content-between">
                                            <button type="button" class="btn btn-outline-danger btn-sm border-0"
                                                onclick="if(confirm('Delete this account permanently?')) document.getElementById('del-{{ $secretary->id }}').submit();">
                                                <i class="fa-regular fa-trash-can me-1"></i> Delete
                                            </button>
                                            
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary fw-bold px-4">Save Changes</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <form id="del-{{ $secretary->id }}" action="{{ route('secretaries.destroy', $secretary->id) }}" method="POST" class="d-none">
                                    @csrf @method('DELETE')
                                </form>
                            </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted opacity-50">
                                    <i class="fa-solid fa-users-slash fa-3x mb-3"></i>
                                    <p class="mb-0">No secretaries found matching your filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($secretaries->hasPages())
            <div class="card-footer bg-white py-3 border-top-0">
                {{ $secretaries->links() }}
            </div>
        @endif
    </div>

    {{-- CREATE MODAL (Standard) --}}
    <div class="modal fade" id="createSecretaryModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('secretaries.store') }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold">Add New Secretary</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-primary bg-primary bg-opacity-10 border-0 text-primary small mb-4">
                            <i class="fa-solid fa-circle-info me-2"></i>
                            User will have access to appointment and patients.
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Full Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Sarah Smith">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Email (Login)</label>
                            <input type="email" name="email" class="form-control" required placeholder="name@clinic.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Password</label>
                            <input type="text" name="password" class="form-control" value="123456" required>
                            <div class="form-text">Default password is 123456</div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4">Create Account</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection