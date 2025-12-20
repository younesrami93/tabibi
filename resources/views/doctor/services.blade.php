@extends('layouts.admin')

@section('title', 'Medical Services')
@section('header', 'Services Catalog')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 text-secondary">My Services</h4>
            <small class="text-muted">Manage prices and insurance codes.</small>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createServiceModal">
            <i class="fa-solid fa-plus"></i> Add Service
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
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
                                @if($service->code)
                                    <span class="badge bg-dark">{{ $service->code }}</span>
                                @endif
                                <span class="fw-bold ms-1">{{ $service->name }}</span>
                            </td>
                            <td>
                                <small class="text-muted">{{ Str::limit($service->description, 40) ?? '-' }}</small>
                            </td>
                            <td>
                                <small class="text-muted"><i class="fa-regular fa-clock"></i> {{ $service->duration_minutes }}
                                    min</small>
                            </td>
                            <td>
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    {{ number_format($service->price, 2) }} DH
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light border" data-bs-toggle="modal"
                                    data-bs-target="#editServiceModal{{ $service->id }}">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                            </td>
                        </tr>

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
                                                    <label class="form-label">Code (Mutuelle)</label>
                                                    <input type="text" name="code" class="form-control"
                                                        value="{{ $service->code }}" placeholder="e.g. K20">
                                                </div>
                                                <div class="col-md-8">
                                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="name" class="form-control"
                                                        value="{{ $service->name }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Price (DH) <span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" name="price" class="form-control"
                                                        value="{{ $service->price }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Duration (min)</label>
                                                    <input type="number" name="duration_minutes" class="form-control"
                                                        value="{{ $service->duration_minutes }}">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Description / Instructions</label>
                                                    <textarea name="description" class="form-control"
                                                        rows="2">{{ $service->description }}</textarea>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="is_active" {{ $service->is_active ? 'checked' : '' }}>
                                                        <label class="form-check-label">Active</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No services found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

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
                                <label class="form-label">Code (Mutuelle)</label>
                                <input type="text" name="code" class="form-control" placeholder="e.g. C">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Consultation"
                                    required>
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
                                <label class="form-label">Small Description</label>
                                <textarea name="description" class="form-control" rows="2"
                                    placeholder="Instructions for patient..."></textarea>
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