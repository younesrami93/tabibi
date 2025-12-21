<div class="card-body p-0">
    @if($items->isEmpty())
        <div class="text-center py-5">
            <i class="fa-solid fa-box-open fa-3x text-muted opacity-25 mb-3"></i>
            <h5 class="text-muted">No items found.</h5>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Name</th>
                        @if($type == 'medicine')
                            <th>Form</th>
                            <th>Strength</th>
                            <th>Default Protocol</th>
                        @endif
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">
                                {{ $item->name }}
                                @if(is_null($item->clinic_id))
                                    <span class="badge bg-secondary opacity-50 ms-2" title="System Default">System</span>
                                @endif
                            </td>

                            @if($type == 'medicine')
                                <td><span class="badge bg-light text-dark border">{{ $item->form ?? '-' }}</span></td>
                                <td>{{ $item->strength ?? '-' }}</td>
                                <td class="text-muted small">
                                    @if($item->default_quantity)
                                        <i class="fa-solid fa-clock-rotate-left me-1"></i>
                                        {{ $item->default_quantity }} x {{ $item->default_frequency }} / day
                                        for {{ $item->default_duration }} days
                                    @else
                                        <span class="text-muted opacity-50">-</span>
                                    @endif
                                </td>
                            @endif

                            <td class="text-end pe-4">
                                @if($item->clinic_id == Auth::user()->clinic_id)
                                    <form action="{{ route('catalog.destroy', $item->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Remove this item?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light text-danger"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                @else
                                    <button class="btn btn-sm btn-light text-muted" disabled><i
                                            class="fa-solid fa-lock"></i></button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- Pagination Links --}}
        <div class="p-3">
            {{ $items->links() }}
        </div>
    @endif
</div>