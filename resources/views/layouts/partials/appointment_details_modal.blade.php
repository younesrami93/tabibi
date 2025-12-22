<div class="modal fade" id="viewModal-{{ $appt->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            {{-- 1. HEADER: Clean & Informative --}}
            <div class="modal-header bg-white px-5 py-4 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                        <i class="fa-solid fa-calendar-check fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Appointment Details</h5>
                        <p class="text-muted small mb-0 fw-medium">
                            Ref: #{{ $appt->id }} <span class="mx-2">•</span>
                            {{ $appt->scheduled_at->format('l, d M Y') }} at {{ $appt->scheduled_at->format('H:i') }}
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    {{-- Status Pill --}}
                    @php
                        $statusStyles = [
                            'finished' => ['bg' => 'bg-success', 'text' => 'text-success', 'icon' => 'fa-check-circle'],
                            'scheduled' => ['bg' => 'bg-primary', 'text' => 'text-primary', 'icon' => 'fa-clock'],
                            'cancelled' => ['bg' => 'bg-danger', 'text' => 'text-danger', 'icon' => 'fa-times-circle'],
                            'waiting' => ['bg' => 'bg-warning', 'text' => 'text-warning', 'icon' => 'fa-chair'],
                            'in_consultation' => ['bg' => 'bg-info', 'text' => 'text-info', 'icon' => 'fa-user-doctor'],
                        ];
                        $style = $statusStyles[$appt->status] ?? $statusStyles['scheduled'];
                    @endphp
                    <div
                        class="px-3 py-2 rounded-pill {{ $style['bg'] }} bg-opacity-10 {{ $style['text'] }} fw-bold small text-uppercase d-flex align-items-center gap-2">
                        <i class="fa-solid {{ $style['icon'] }}"></i> {{ str_replace('_', ' ', $appt->status) }}
                    </div>
                    <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-0 bg-light">
                <div class="row g-0 h-100">

                    {{-- LEFT COLUMN: Medical Context (65%) --}}
                    <div class="col-lg-8 p-5 bg-white">

                        {{-- A. Patient Profile --}}
                        <div class="d-flex align-items-start gap-4 mb-5">
                            <div class="pt-1">
                                <h4 class="fw-bold text-dark mb-1">{{ $appt->patient->full_name }}</h4>
                                <div class="d-flex flex-wrap gap-3 text-muted small fw-medium mb-2">
                                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-venus-mars"></i>
                                        {{ ucfirst($appt->patient->gender) }}</span>
                                    <span class="d-flex align-items-center gap-1"><i
                                            class="fa-solid fa-cake-candles"></i> {{ $appt->patient->age }} Years</span>
                                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-phone"></i>
                                        {{ $appt->patient->phone ?? 'N/A' }}</span>
                                </div>
                                <a href="{{ route('patients.show', $appt->patient->id) }}"
                                    class="text-decoration-none small fw-bold text-primary">
                                    View Full Patient Profile <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>

                        <hr class="border-light my-5">

                        {{-- B. Clinical Information --}}
                        <div class="d-flex flex-column gap-5">

                            {{-- Diagnosis / Notes --}}
                            <div>
                                <h6
                                    class="text-uppercase text-muted small fw-bold mb-3 tracking-wide d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-align-left text-primary"></i> Diagnosis & Notes
                                </h6>
                                <div class="p-4 bg-light rounded-3 border border-light text-secondary"
                                    style="min-height: 80px;">
                                    @if($appt->notes)
                                        <p class="mb-0" style="white-space: pre-line; line-height: 1.6;">{{ $appt->notes }}
                                        </p>
                                    @else
                                        <span class="text-muted fst-italic opacity-75">No notes recorded for this
                                            visit.</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Prescriptions (Nested Blocks) --}}
                            @if(!empty($appt->prescription) && is_array($appt->prescription))
                                <div>
                                    <h6
                                        class="text-uppercase text-muted small fw-bold mb-3 tracking-wide d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-prescription text-primary"></i> Prescriptions
                                    </h6>

                                    <div class="d-flex flex-column gap-3">
                                        @foreach($appt->prescription as $index => $block)
                                            @if(!empty($block['items']))
                                                <div class="border rounded-3 overflow-hidden">
                                                    {{-- Prescription Header --}}
                                                    <div
                                                        class="bg-surface-secondary px-4 py-2 border-bottom d-flex justify-content-between align-items-center">
                                                        <span
                                                            class="fw-bold text-dark small">{{ $block['title'] ?? 'Prescription #' . ($index + 1) }}</span>
                                                        <i class="fa-solid fa-file-medical text-muted opacity-25"></i>
                                                    </div>
                                                    {{-- Items List --}}
                                                    <div class="px-4 py-2">
                                                        <ul class="list-unstyled mb-0">
                                                            @foreach($block['items'] as $item)
                                                                <li class="py-2 border-bottom border-light last-no-border">
                                                                    <div class="fw-bold text-dark mb-1">{{ $item['name'] }}</div>
                                                                    <div class="small text-muted fst-italic">
                                                                        <i
                                                                            class="fa-solid fa-arrow-turn-up fa-rotate-90 me-2 opacity-25"></i>{{ $item['note'] ?? 'No instructions' }}
                                                                    </div>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Services Performed --}}
                            @if($appt->services->isNotEmpty())
                                <div>
                                    <h6
                                        class="text-uppercase text-muted small fw-bold mb-3 tracking-wide d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-notes-medical text-primary"></i> Services Performed
                                    </h6>
                                    <div class="border rounded-3 overflow-hidden">
                                        <table class="table table-borderless mb-0">
                                            <thead class="bg-light border-bottom">
                                                <tr>
                                                    <th class="ps-4 small text-muted font-weight-normal py-2">Service Name
                                                    </th>
                                                    <th class="pe-4 text-end small text-muted font-weight-normal py-2">Cost
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($appt->services as $s)
                                                    <tr class="border-bottom border-light last-no-border">
                                                        <td class="ps-4 py-3 fw-medium text-dark">{{ $s->name }}</td>
                                                        <td class="pe-4 py-3 text-end text-muted">
                                                            {{ number_format($s->pivot->price, 2) }} DH
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>

                    {{-- RIGHT COLUMN: Admin & Finance (35%) --}}
                    <div class="col-lg-4 bg-light border-start">
                        <div class="p-5 d-flex flex-column h-100">

                            {{-- Financial Summary Card --}}
                            <div class="card border-0 shadow-sm mb-5 rounded-4 overflow-hidden">
                                <div
                                    class="card-header bg-dark text-white py-3 px-4 fw-bold small text-uppercase tracking-wide">
                                    Invoice Summary
                                </div>
                                <div class="card-body p-4">
                                    {{-- Line Items --}}
                                    <div class="d-flex justify-content-between mb-2 small text-secondary">
                                        <span>Consultation Fee</span>
                                        <span class="fw-medium text-dark">{{ number_format($appt->price, 2) }}</span>
                                    </div>
                                    @if($appt->services->isNotEmpty())
                                        <div class="d-flex justify-content-between mb-3 small text-secondary">
                                            <span>Services Total</span>
                                            <span
                                                class="fw-medium text-dark">{{ number_format($appt->services->sum('pivot.price'), 2) }}</span>
                                        </div>
                                    @endif

                                    {{-- Grand Total --}}
                                    <div class="border-top border-dashed pt-3 mt-2 mb-4">
                                        <div class="d-flex justify-content-between align-items-end">
                                            <span class="small fw-bold text-uppercase text-muted">Total Amount</span>
                                            @php $total = $appt->total_price ?? ($appt->price + $appt->services->sum('pivot.price')); @endphp
                                            <span class="h3 mb-0 text-dark fw-bold">{{ number_format($total, 2) }}
                                                <small class="fs-6 text-muted">DH</small></span>
                                        </div>
                                    </div>

                                    {{-- Payment Status Box --}}
                                    @if($appt->is_paid)
                                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 text-center">
                                            <div class="fw-bold mb-1"><i class="fa-solid fa-check-circle me-2"></i>Paid in
                                                Full</div>
                                            <div class="small opacity-75">No balance due</div>
                                        </div>
                                    @else
                                        <div
                                            class="bg-warning bg-opacity-10 text-warning-emphasis p-3 rounded-3 text-center border border-warning border-opacity-25">
                                            <div class="fw-bold mb-1"><i
                                                    class="fa-solid fa-circle-exclamation me-2"></i>Payment Pending</div>
                                            <div class="small opacity-75">Balance added to patient credit</div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Activity Log --}}
                            <h6 class="text-uppercase text-muted small fw-bold mb-4 tracking-wide">Timeline</h6>
                            <div class="ps-3 border-start border-2 ms-2 mb-4">
                                @foreach($appt->history as $h)
                                    <div class="position-relative mb-4 ps-4">
                                        <span
                                            class="position-absolute top-0 start-0 translate-middle bg-white border border-2 border-secondary rounded-circle"
                                            style="width: 12px; height: 12px; margin-top: 6px; margin-left: -1px;"></span>
                                        <div class="small fw-bold text-dark text-capitalize lh-1 mb-1">
                                            {{ str_replace('_', ' ', $h->status) }}
                                        </div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">
                                            {{ $h->created_at->format('M d, H:i') }}
                                            @if($h->user)
                                                <span class="text-primary opacity-75">•
                                                    {{ explode(' ', $h->user->name)[0] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Print Actions (Stick to bottom) --}}
                            <div class="mt-auto">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-dark fw-bold shadow-sm" onclick="window.print()">
                                        <i class="fa-solid fa-print me-2"></i>Print Summary
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Scoped Styles for this Modal */
    .bg-surface-secondary {
        background-color: #f8fafc;
    }

    .tracking-wide {
        letter-spacing: 0.08em;
    }

    .last-no-border:last-child {
        border-bottom: none !important;
    }

    .avatar-circle {
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
</style>