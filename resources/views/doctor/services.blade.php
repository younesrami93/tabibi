@extends('layouts.admin')

@section('title', 'Medical Services')
@section('header', 'Services List')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 text-secondary">My Services</h4>
            <small class="text-muted">Manage prices and insurance codes.</small>
        </div>
        <div class="d-flex gap-2">
            <!-- Search Form -->
            <form action="{{ route('services.index') }}" method="GET" class="d-flex">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-start-0" placeholder="Search services..." value="{{ request('q') }}">
                </div>
            </form>
            
            <button class="btn btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#createServiceModal">
                <i class="fa-solid fa-plus"></i> Add Service
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Code & Name</th>
                            <th>Description</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $service)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        @if($service->code)
                                            <span class="badge bg-dark me-2">{{ $service->code }}</span>
                                        @endif
                                        <div>
                                            <div class="fw-bold {{ !$service->is_active ? 'text-muted text-decoration-line-through' : '' }}">
                                                {{ $service->name }}
                                            </div>
                                            @if(!$service->is_active)
                                                <small class="text-danger">Inactive</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">{{ Str::limit($service->description, 40) ?? '-' }}</small>
                                </td>
                                <td>
                                    <small class="text-muted"><i class="fa-regular fa-clock"></i> {{ $service->duration_minutes }} min</small>
                                </td>
                                <td>
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        {{ number_format($service->price, 2) }} DH
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#editServiceModal{{ $service->id }}">
                                            <i class="fa-solid fa-pen text-primary"></i>
                                        </button>
                                        
                                        <form action="{{ route('services.destroy', $service->id) }}" method="POST" onsubmit="return confirm('Delete this service?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light border text-danger">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Modal (Inside Loop) -->
                            <div class="modal fade" id="editServiceModal{{ $service->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form action="{{ route('services.update', $service->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Service</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Code</label>
                                                        <input type="text" name="code" class="form-control" value="{{ $service->code }}">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="name" class="form-control" value="{{ $service->name }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Price (DH) <span class="text-danger">*</span></label>
                                                        <input type="number" name="price" class="form-control" value="{{ $service->price }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Duration (min)</label>
                                                        <input type="number" name="duration_minutes" class="form-control" value="{{ $service->duration_minutes }}">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Description</label>
                                                        <textarea name="description" class="form-control" rows="2">{{ $service->description }}</textarea>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="is_active" id="activeSwitch{{ $service->id }}" {{ $service->is_active ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="activeSwitch{{ $service->id }}">Active (Available for booking)</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-notes-medical fa-2x mb-3 opacity-25"></i>
                                    <p>No services found. Add one to get started.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top-0">
            {{ $services->links() }}
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createServiceModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('services.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">New Medical Service</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Code</label>
                                <input type="text" name="code" class="form-control" placeholder="e.g. C">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Consultation" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Price (DH) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control" placeholder="300" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Duration (min)</label>
                                <input type="number" name="duration_minutes" class="form-control" value="30">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Instructions for patient..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Service</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection