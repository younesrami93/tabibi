<div class="card-body p-0">
    @if($templates->isEmpty())
        <div class="text-center py-5">
            <i class="fa-solid fa-clipboard-list fa-3x text-muted opacity-25 mb-3"></i>
            <h5 class="text-muted">No templates found.</h5>
            <p class="small text-muted">Create your first protocol to speed up your work.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Template Name</th>
                        <th>Type</th>
                        <th>Content</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $t)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">{{ $t->name }}</td>
                            <td>
                                @if($t->type == 'medicine')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Medicine</span>
                                @elseif($t->type == 'test')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">Lab Test</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-dark border border-secondary-subtle">Mixed</span>
                                @endif
                            </td>
                            <td class="text-muted small">
                                {{ count($t->items) }} items
                                @if(isset($t->items[0]['name']))
                                    (Starts with: {{ $t->items[0]['name'] }})
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                {{-- EDIT BUTTON --}}
                                <button class="btn btn-sm btn-light text-primary me-1" onclick="editTemplate({{ $t->id }})">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                {{-- DELETE BUTTON --}}
                                <form action="{{ route('prescriptions_templates.destroy', $t->id) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Delete this template?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light text-danger"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $templates->appends(request()->except('page'))->links() }}
        </div>
    @endif
</div>