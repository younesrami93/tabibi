<div class="modal fade" id="viewModal-{{ $appt->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden text-start">

            {{-- 1. UPDATED HEADER: Patient & Appointment Context --}}
            <div class="modal-header bg-white px-4 py-3 border-bottom position-relative">
                <div class="row w-100 g-0 align-items-center">

                    {{-- LEFT: Patient Details --}}
                    <div class="col-md-7">
                        <div class="d-flex align-items-center gap-3">
                            {{-- Avatar (Initials) --}}
                            <div class="avatar-circle bg-primary bg-opacity-10 text-primary fw-bold rounded-circle flex-shrink-0"
                                style="width: 52px; height: 52px; font-size: 1.25rem;">
                                {{ substr($appt->patient->first_name, 0, 1) }}{{ substr($appt->patient->last_name, 0, 1) }}
                            </div>

                            {{-- Info Block --}}
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h5 class="modal-title fw-bold text-dark mb-0">{{ $appt->patient->full_name }}</h5>
                                    <a href="{{ route('patients.show', $appt->patient->id) }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill x-small fw-bold"
                                        style="height: 20px; line-height: 18px;">
                                        View Profile <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i>
                                    </a>
                                </div>
                                <div
                                    class="d-flex flex-wrap gap-2 text-muted x-small small  text-uppercase tracking-wide">
                                    <span><i
                                            class="fa-solid fa-venus-mars me-1"></i>{{ ucfirst($appt->patient->gender) }}</span>

                                    {{-- Age: Only show if birth_date exists --}}
                                    @if($appt->patient->birth_date)
                                        <span class="opacity-25">•</span>
                                        <span><i class="fa-solid fa-cake-candles me-1"></i>{{ $appt->patient->age }}</span>
                                    @endif

                                    <span class="opacity-25">•</span>
                                    <span><i
                                            class="fa-solid fa-phone me-1"></i>{{ $appt->patient->phone ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT: Appointment Meta --}}
                    {{-- Added pe-5 to prevent overlap with Close Button --}}
                    <div class="col-md-5 ps-md-4 mt-3 mt-md-0 border-start-md pe-5">
                        <div
                            class="d-flex flex-column align-items-start align-items-md-end justify-content-center h-100">

                            {{-- ID & Status Row --}}
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="text-muted small me-1">Appt #{{ $appt->id }}</span>
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
                                    class="px-2 py-1 rounded-pill {{ $style['bg'] }} bg-opacity-10 {{ $style['text'] }}  text-uppercase d-flex align-items-center gap-1 small ">
                                    <i class="fa-solid {{ $style['icon'] }}"></i>
                                    {{ str_replace('_', ' ', $appt->status) }}
                                </div>
                            </div>

                            {{-- Date & Time --}}
                            <div class="text-dark small fw-bold">
                                {{ $appt->scheduled_at->format('l, d M Y') }}
                                <span class="text-muted fw-normal ms-1">at
                                    {{ $appt->scheduled_at->format('H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Close Button (Absolute Top Right) --}}
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body p-0 bg-light">
                <div class="row g-0 h-100">

                    {{-- LEFT COLUMN: Medical Context (65%) --}}
                    <div class="col-lg-8 p-5 bg-white">

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

                            {{-- Prescriptions --}}
                            @if(!empty($appt->prescription) && is_array($appt->prescription))
                                <div>
                                    <h6
                                        class="text-uppercase text-muted small fw-bold mb-3 tracking-wide d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-prescription text-primary"></i> Prescriptions
                                    </h6>

                                    <div class="row g-3">
                                        @foreach($appt->prescription as $index => $block)
                                            @if(!empty($block['items']))
                                                <div class="col-md-6">
                                                    <div class="border rounded-3 overflow-hidden h-100">
                                                        {{-- Prescription Header --}}
                                                        <div
                                                            class="bg-surface-secondary px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                                                            {{-- Left: Title --}}
                                                            <div class="d-flex align-items-center gap-2">
                                                                <i class="fa-solid fa-file-medical text-primary opacity-50"></i>
                                                                <span class="fw-bold text-dark small">
                                                                    {{ $block['title'] ?? 'Prescription #' . ($index + 1) }}
                                                                </span>
                                                            </div>

                                                            {{-- Right: Print Button --}}
                                                            {{-- We use ID 1 for the template as requested, and pass the appointment
                                                            ID via query string --}}
                                                            <a href="{{ route('documents.print.type', 'prescription') }}?model=appointment&id={{ $appt->id }}&rx_index={{ $index }}"
                                                                target="_blank"
                                                                class="btn btn-sm btn-white border shadow-sm py-0 px-2 x-small fw-bold text-secondary"
                                                                title="Print this prescription">
                                                                <i class="fa-solid fa-print me-1"></i> Print
                                                            </a>
                                                        </div> {{-- Items List --}}
                                                        <div class="px-4 py-2">
                                                            <ul class="list-unstyled mb-0">
                                                                @foreach($block['items'] as $item)
                                                                    <li class="py-2 border-bottom border-light last-no-border">
                                                                        <div class="fw-bold text-dark mb-1">{{ $item['name'] }}</div>
                                                                        <div class="small text-muted fst-italic">
                                                                            {{-- Replaced arrow with clock icon --}}
                                                                            <i
                                                                                class="fa-regular fa-clock me-2 opacity-25"></i>{{ $item['note'] ?? 'No instructions' }}
                                                                        </div>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
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

                                    <a href="{{ route('documents.print.type', 'invoice') }}?model=appointment&id={{ $appt->id }}"
                                        target="_blank"
                                        class="btn btn-sm btn-light border py-0 px-2 x-small fw-bold text-primary"
                                        title="Print this invoice">
                                        <i class="fa-solid fa-print me-1"></i> Print
                                    </a>
                                </div>
                                <div class="card-body p-4">
                                    {{-- 1. Consultation Fee --}}
                                    <div class="d-flex justify-content-between mb-1 small text-secondary">
                                        <span>Consultation Fee</span>
                                        <span class="fw-medium text-dark">{{ number_format($appt->price, 2) }}</span>
                                    </div>

                                    {{-- 2. Services List --}}
                                    {{-- Removed "Services Performed" Header text --}}
                                    {{-- Check if invoice items exist (Standard OR Custom) --}}
                                    @if($appt->invoiceItems->isNotEmpty())

                                        @foreach($appt->invoiceItems as $item)
                                            <div class="d-flex justify-content-between mb-1 small text-secondary">

                                                <span>
                                                    {{-- Optional: Add a small icon to distinguish custom entries --}}
                                                    @if(!$item->medical_service_id)
                                                        <i class="fa-solid fa-pen-nib me-1 opacity-50" title="Custom Service"></i>
                                                    @endif

                                                    {{-- Display Name: Uses the getNameAttribute() accessor we added to the
                                                    model --}}
                                                    {{ $item->name }}
                                                </span>

                                                <span class="fw-medium text-dark">
                                                    {{-- Price is directly on the pivot row now --}}
                                                    {{ number_format($item->price, 2) }}
                                                </span>

                                            </div>
                                        @endforeach

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

                            {{-- Print Actions --}}
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

    .x-small {
        font-size: 0.7rem;
    }

    /* Desktop border for header separation */
    @media (min-width: 768px) {
        .border-start-md {
            border-left: 1px solid #dee2e6 !important;
        }
    }
</style>