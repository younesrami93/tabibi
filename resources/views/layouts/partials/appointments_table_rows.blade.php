@forelse($appointments as $appt)
    <tr class="appointment-row {{ $appt->status == 'in_consultation' ? 'bg-success bg-opacity-10' : '' }}">

        {{-- 1. Queue/Time --}}
        <td class="ps-4">
            @if(request('filter_mode', 'today_active') == 'today_active')
                <div class="d-flex align-items-center gap-2">
                    {{-- Use firstItem() to maintain correct numbering across pages --}}
                    <div class="avatar-circle bg-white border shadow-sm fw-bold text-dark d-flex align-items-center justify-content-center rounded-circle"
                        style="width: 35px; height: 35px;">
                        {{ $appointments->firstItem() + $loop->index }}
                    </div>
                    @if($appt->status == 'in_consultation')
                        <span class="badge bg-success animate-pulse">INSIDE</span>
                    @elseif($loop->first && $appointments->currentPage() == 1)
                        <span class="badge bg-primary">NEXT</span>
                    @endif
                </div>
            @else
                <div class="fw-bold text-dark">{{ $appt->scheduled_at->format('H:i') }}</div>
                <small class="text-muted">{{ $appt->scheduled_at->format('d M') }}</small>
            @endif
        </td>

        {{-- 2. Patient --}}
        <td>
            <div class="d-flex align-items-center">
                <div class="avatar-circle me-2 bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center rounded-circle"
                    style="width: 35px; height: 35px; font-size: 0.8rem;">
                    {{ substr($appt->patient->first_name, 0, 1) }}{{ substr($appt->patient->last_name, 0, 1) }}
                </div>
                <div>
                    <div class="fw-bold text-dark">
                        <a href="{{ route('patients.show', $appt->patient->id) }}" target="_blank" class="text-dark">
                            {{ $appt->patient->full_name }}
                            <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i>
                        </a>
                    </div>
                    <div class="small text-muted"><i class="fa-solid fa-phone me-1"
                            style="font-size: 0.7rem;"></i>{{ $appt->patient->phone }}</div>
                </div>
            </div>
        </td>

        {{-- 3. Status --}}
        <td>
            @php
                $statusColors = [
                    'scheduled' => 'secondary',
                    'waiting' => 'primary',
                    'preparing' => 'warning',
                    'in_consultation' => 'success',
                    'pending_payment' => 'info',
                    'finished' => 'dark',
                    'cancelled' => 'danger'
                ];
                $color = $statusColors[$appt->status] ?? 'secondary';
            @endphp
            <span
                class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} border border-{{ $color }} border-opacity-25 rounded-pill px-3">
                {{ str_replace('_', ' ', ucfirst($appt->status)) }}
            </span>
        </td>

        {{-- 4. Type --}}
        <td>
            @if($appt->type == 'urgency')
                <span class="badge bg-danger text-white shadow-sm">URGENCY</span>
            @elseif($appt->type == 'control')
                <span class="badge bg-info text-white shadow-sm">Control</span>
            @else
                <span class="text-muted small">Consultation</span>
            @endif
        </td>

        {{-- 5. Actions --}}
        <td class="text-end pe-4">
            <div class="d-flex justify-content-end gap-2 align-items-center">

                {{-- Status Updates --}}
                @if($appt->status == 'scheduled')
                    <form action="{{ route('appointments.update_status', $appt->id) }}" method="POST">
                        @csrf @method('PUT') <input type="hidden" name="status" value="waiting">
                        <button class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-3">Arrived</button>
                    </form>
                @endif

                @if($appt->status == 'waiting')
                    <form action="{{ route('appointments.update_status', $appt->id) }}" method="POST">
                        @csrf @method('PUT') <input type="hidden" name="status" value="preparing">
                        <button class="btn btn-sm btn-warning text-white fw-bold rounded-pill px-3">Prepare</button>
                    </form>
                @endif

                @if($appt->status == 'preparing')
                    <form action="{{ route('appointments.update_status', $appt->id) }}" method="POST">
                        @csrf @method('PUT') <input type="hidden" name="status" value="in_consultation">
                        <button class="btn btn-sm btn-success text-white fw-bold rounded-pill px-3">Start</button>
                    </form>
                @endif

                {{-- Finishing --}}
                @if($appt->status == 'in_consultation')
                    <button
                        class="btn btn-sm {{ auth()->user()->role === 'doctor' ? 'btn-primary' : 'btn-outline-dark' }} fw-bold rounded-pill px-3 shadow-sm"
                        onclick="openFullModal('{{ route('appointments.finish-modal', $appt->id) }}')">
                        <i class="fa-solid fa-check me-1"></i> Finish
                    </button>
                @endif

                @if($appt->status == 'pending_payment' && auth()->user()->role !== 'doctor')
                    <button class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3 shadow-sm"
                        onclick="openFullModal('{{ route('appointments.finish-modal', $appt->id) }}')">
                        <i class="fa-solid fa-cash-register me-1"></i> Collect Payment

                    </button>
                @endif

                {{-- Cancel --}}
                @if(!in_array($appt->status, ['finished', 'pending_payment', 'cancelled']))
                    <form action="{{ route('appointments.update_status', $appt->id) }}" method="POST"
                        onsubmit="confirmCancel(event)">
                        @csrf @method('PUT') <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="btn btn-sm btn-outline-danger border shadow-sm">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </form>
                @endif

                {{-- View Details --}}
                <button class="btn btn-sm btn-light border shadow-sm text-secondary"
                    onclick="openFullModal('{{ route('appointments.modal', $appt->id) }}')">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center py-5 text-muted">
            <div class="d-flex flex-column align-items-center">
                <i class="fa-regular fa-calendar-xmark fs-3 mb-3 text-secondary"></i>
                <span>No appointments found for this selection.</span>
            </div>
        </td>
    </tr>
@endforelse