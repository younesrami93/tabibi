<div class="modal fade" id="finishModal-{{ $appt->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden text-start">

            <form action="{{ route('appointments.finish', $appt->id) }}" method="POST" class="d-flex flex-column h-100"
                id="finishForm-{{ $appt->id }}">
                @csrf
                @method('PUT')

                {{-- 1. HEADER --}}
                <div class="modal-header bg-white px-4 py-3 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-circle bg-success bg-opacity-10 text-success fw-bold rounded-circle flex-shrink-0"
                            style="width: 52px; height: 52px; font-size: 1.25rem;">
                            {{ substr($appt->patient->first_name, 0, 1) }}{{ substr($appt->patient->last_name, 0, 1) }}
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">Finish Appointment</h5>
                            <div class="d-flex align-items-center gap-2 text-muted small fw-medium">
                                <span>{{ $appt->patient->full_name }}</span>
                                <span class="opacity-25">•</span>
                                <span>Ref: #{{ $appt->id }}</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-0 bg-light">
                    <div class="row g-0 h-100">

                        {{-- LEFT COLUMN: Medical Data (65%) --}}
                        <div class="col-lg-8 p-5 bg-white overflow-y-auto" style="max-height: 80vh;">
                            <div class="d-flex flex-column gap-5">

                                {{-- A. Diagnosis & Notes --}}
                                <div>
                                    <h6
                                        class="text-uppercase text-muted small fw-bold mb-3 tracking-wide d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-align-left text-success"></i> Diagnosis & Medical Notes
                                    </h6>
                                    {{-- PRE-FILL: We inject $appt->notes here --}}
                                    <textarea name="notes"
                                        class="form-control bg-light border-0 p-4 rounded-3 shadow-sm" rows="3"
                                        placeholder="Enter diagnosis..."
                                        style="resize: none;">{{ $appt->notes }}</textarea>
                                </div>

                                {{-- B. Prescriptions (Existing + New) --}}
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6
                                            class="text-uppercase text-muted small fw-bold tracking-wide mb-0 d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-prescription text-success"></i> Prescriptions
                                        </h6>
                                        <button type="button"
                                            class="btn btn-sm btn-outline-success fw-bold rounded-pill px-3 shadow-sm"
                                            onclick="addNewPrescriptionBlock({{ $appt->id }})">
                                            <i class="fa-solid fa-file-medical me-1"></i> New Prescription
                                        </button>
                                    </div>

                                    <div id="prescriptionsContainer-{{ $appt->id }}" class="d-flex flex-column gap-4">
                                        {{--
                                        LOGIC: Check if we have saved JSON data.
                                        If yes, loop through it. If no, show one empty block.
                                        --}}
                                        @php
                                            $existingPrescriptions = $appt->prescription ?? [];

                                            // Normalization: Ensure it's an array of blocks
                                            // (Handle legacy data if you ever stored flat arrays)
                                            if (!empty($existingPrescriptions) && isset($existingPrescriptions[0]['name'])) {
                                                // Convert old single-block format to multi-block format
                                                $existingPrescriptions = [['title' => 'Prescription #1', 'items' => $existingPrescriptions]];
                                            }

                                            // If completely empty, create one default empty block
                                            if (empty($existingPrescriptions)) {
                                                $existingPrescriptions = [['title' => 'Prescription', 'items' => []]];
                                            }
                                        @endphp

                                        @foreach($existingPrescriptions as $pIndex => $block)
                                            <div class="card overflow-visible border rounded-3 shadow-sm prescription-block"
                                                id="pBlock-{{ $appt->id }}-{{ $pIndex }}">
                                                <div
                                                    class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="fa-solid fa-file-prescription text-success"></i>
                                                        {{-- PRE-FILL: Block Title --}}
                                                        <input type="text" name="prescriptions[{{ $pIndex }}][title]"
                                                            class="form-control form-control-sm border-0 fw-bold p-0 shadow-none"
                                                            value="{{ $block['title'] ?? 'Prescription' }}"
                                                            style="width: 150px;">
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <select
                                                            class="form-select form-select-sm border-0 bg-light text-muted small"
                                                            style="width: 140px;"
                                                            onchange="loadTemplateToBlock(this, {{ $appt->id }}, {{ $pIndex }})">
                                                            <option value="" selected>Load Template...</option>
                                                            @foreach($templates as $temp)
                                                                <option value="{{ $temp->id }}">{{ $temp->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="button" class="btn btn-sm btn-outline-danger border-0"
                                                            onclick="removePrescriptionBlock({{ $appt->id }}, {{ $pIndex }})">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="">
                                                        <table class="table table-sm align-middle mb-0">
                                                            <thead class="bg-light">
                                                                <tr>
                                                                    <th class="ps-3 border-0 text-muted x-small text-uppercase fw-bold"
                                                                        width="50%">Medicine</th>
                                                                    <th
                                                                        class="border-0 text-muted x-small text-uppercase fw-bold">
                                                                        Instructions</th>
                                                                    <th class="border-0" width="30"></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="pItems-{{ $appt->id }}-{{ $pIndex }}">
                                                                {{-- PRE-FILL: Loop Items inside Block --}}
                                                                @if(!empty($block['items']))
                                                                    @foreach($block['items'] as $iIndex => $item)
                                                                        <tr>
                                                                            <td class="ps-3 position-relative">
                                                                                <input type="hidden"
                                                                                    name="prescriptions[{{ $pIndex }}][items][{{ $iIndex }}][catalog_item_id]"
                                                                                    value="{{ $item['catalog_item_id'] ?? '' }}">
                                                                                <input type="text"
                                                                                    name="prescriptions[{{ $pIndex }}][items][{{ $iIndex }}][name]"
                                                                                    class="form-control form-control-sm fw-bold border-0 shadow-none"
                                                                                    value="{{ $item['name'] }}" required>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text"
                                                                                    name="prescriptions[{{ $pIndex }}][items][{{ $iIndex }}][note]"
                                                                                    class="form-control form-control-sm border-0 shadow-none text-muted fst-italic"
                                                                                    value="{{ $item['note'] ?? '' }}">
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <button type="button"
                                                                                    class="btn btn-link text-danger p-0"
                                                                                    onclick="this.closest('tr').remove()">
                                                                                    <i class="fa-solid fa-xmark"></i>
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="p-2 border-top bg-light">
                                                        <button type="button"
                                                            class="btn btn-sm btn-light text-primary w-100 fw-bold border-dashed"
                                                            onclick="addItemToBlock({{ $appt->id }}, {{ $pIndex }})">
                                                            <i class="fa-solid fa-plus me-1"></i> Add Medicine / Test
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- C. Services (Existing + New) --}}
                                <div>
                                    <h6
                                        class="text-uppercase text-muted small fw-bold mb-3 tracking-wide d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-user-md text-success"></i> Services Performed
                                    </h6>

                                    <div class="card border-0 shadow-sm bg-light rounded-3 p-3 mb-3">
                                        <div class="d-flex gap-2">
                                            <select class="form-select form-select-sm border-0 shadow-sm"
                                                id="newServiceSelect-{{ $appt->id }}">
                                                <option value="" selected disabled>Select a service to add...</option>
                                                @foreach($allServices as $service)
                                                    <option value="{{ $service->id }}" data-price="{{ $service->price }}"
                                                        data-name="{{ $service->name }}">
                                                        {{ $service->name }} ({{ number_format($service->price, 2) }} DH)
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-sm btn-success shadow-sm px-3 fw-bold"
                                                onclick="addServiceRow({{ $appt->id }})">
                                                <i class="fa-solid fa-plus"></i>
                                            </button>
                                        </div>
                                        <div class="text-center mt-2">
                                            <span class="text-muted x-small fw-bold text-uppercase">OR</span>
                                        </div>
                                        <button type="button"
                                            class="btn btn-sm btn-outline-secondary dashed-border w-100 mt-2"
                                            onclick="addCustomServiceRow({{ $appt->id }})">
                                            <i class="fa-solid fa-pen-to-square me-2"></i>Add Custom Service
                                        </button>
                                    </div>

                                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                                        <div class="card-body p-0">
                                            <table class="table table-hover align-middle mb-0">
                                                <tbody id="serviceRows-{{ $appt->id }}">
                                                    {{-- PRE-FILL: Loop Existing Services --}}
                                                    @foreach($appt->invoiceItems as $ix => $item)
                                                        <tr class="service-row">
                                                            <td class="ps-3 border-0">
                                                                {{-- LOGIC: Determine how to identify this item --}}
                                                                @if($item->medical_service_id)
                                                                    {{-- Case A: Standard Service (Send ID) --}}
                                                                    <input type="hidden" name="services[{{ $ix }}][id]"
                                                                        value="{{ $item->medical_service_id }}">
                                                                @else
                                                                    {{-- Case B: Custom Service (Send Name) --}}
                                                                    <input type="hidden" name="services[{{ $ix }}][custom_name]"
                                                                        value="{{ $item->custom_name }}">
                                                                @endif

                                                                {{-- Display Name --}}
                                                                <div class="d-flex align-items-center">
                                                                    @if(!$item->medical_service_id)
                                                                        <i class="fa-solid fa-pen-nib text-muted me-2 small opacity-50"
                                                                            title="Custom Service"></i>
                                                                    @endif
                                                                    <input type="text"
                                                                        class="form-control form-control-sm bg-white border-0 fw-bold"
                                                                        value="{{ $item->name }}" readonly>
                                                                </div>
                                                            </td>
                                                            <td class="border-0" width="200">
                                                                <div class="input-group input-group-sm">
                                                                    {{-- Note: use $item->price directly from the pivot row
                                                                    --}}
                                                                    <input type="number" step="0.01"
                                                                        class="form-control text-end price-input border-0 bg-light fw-bold"
                                                                        name="services[{{ $ix }}][price]"
                                                                        value="{{ $item->price }}"
                                                                        oninput="calculateTotal({{ $appt->id }})">
                                                                    <span
                                                                        class="input-group-text border-0 bg-light text-muted small">DH</span>
                                                                </div>
                                                            </td>
                                                            <td class="text-center border-0" width="40">
                                                                <button type="button" class="btn btn-link text-danger p-0"
                                                                    onclick="removeRow(this, {{ $appt->id }})">
                                                                    <i class="fa-solid fa-trash-can"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    {{-- Show "No services" only if count is 0 --}}
                                    <div class="text-muted small fst-italic opacity-50 text-center py-2"
                                        id="no-services-msg-{{ $appt->id }}"
                                        style="{{ $appt->services->count() > 0 ? 'display:none' : '' }}">
                                        No services added yet.
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- RIGHT COLUMN: Invoice & Action (35%) --}}
                        <div class="col-lg-4 bg-light border-start">
                            <div class="p-5 d-flex flex-column h-100">

                                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                                    <div
                                        class="card-header bg-dark text-white py-3 px-4 fw-bold small text-uppercase tracking-wide">
                                        Invoice Summary
                                    </div>
                                    <div class="card-body p-4">

                                        {{-- Base Price --}}
                                        <div class="mb-3">
                                            <label class="small text-secondary mb-1">Consultation Fee</label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" name="price"
                                                    class="form-control fw-bold text-dark border-light bg-light"
                                                    id="basePrice-{{ $appt->id }}"
                                                    value="{{ number_format($appt->price ?? 0, 2, '.', '') }}"
                                                    oninput="calculateTotal({{ $appt->id }})">
                                                <span
                                                    class="input-group-text bg-light border-light text-muted">DH</span>
                                            </div>
                                        </div>

                                        {{-- Services Total --}}
                                        <div class="d-flex justify-content-between mb-3 small text-secondary">
                                            <span>Services Total</span>
                                            <span class="fw-medium text-success">+ <span
                                                    id="servicesSum-{{ $appt->id }}">0.00</span></span>
                                        </div>

                                        {{-- Total Divider --}}
                                        <div class="border-top border-dashed pt-3 mt-2 mb-4">
                                            <div class="d-flex justify-content-between align-items-end">
                                                <span class="small fw-bold text-uppercase text-muted">Total Due</span>
                                                <div class="text-end">
                                                    <span class="h3 mb-0 text-dark fw-bold"
                                                        id="totalDisplay-{{ $appt->id }}">
                                                        {{ number_format($appt->total_price ?? $appt->price, 2) }}
                                                    </span>
                                                    <small class="fs-6 text-muted">DH</small>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Payment Input: ONLY FOR SECRETARY --}}
                                        @if(auth()->user()->isDoctor() == false)
                                            <div
                                                class="bg-warning bg-opacity-10 p-3 rounded-3 border border-warning border-opacity-25">
                                                <label for="paidAmount-{{ $appt->id }}"
                                                    class="form-label small fw-bold text-dark mb-1">
                                                    Amount Received
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" step="0.01" name="paid_amount"
                                                        id="paidAmount-{{ $appt->id }}"
                                                        class="form-control fw-bold fs-5 text-dark border-warning border-opacity-25"
                                                        placeholder="0.00"
                                                        value="{{ number_format($appt->is_paid ? $appt->total_price : ($appt->price + $appt->services->sum('pivot.price')), 2, '.', '') }}">
                                                    <span
                                                        class="input-group-text bg-warning bg-opacity-25 border-warning border-opacity-25 text-dark fw-bold">DH</span>
                                                </div>
                                            </div>
                                        @else
                                            {{-- Doctor sees a message instead --}}
                                            <div class="alert alert-light border text-center text-muted small">
                                                <i class="fa-solid fa-info-circle me-1"></i> Payment will be collected by
                                                the secretary.
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Action Button --}}
                                <div class="mt-auto">
                                    <button type="submit"
                                        class="btn btn-success w-100 py-3 fw-bold shadow-sm text-uppercase tracking-wide">
                                        @if(auth()->user()->role === 'doctor')
                                            <i class="fa-solid fa-paper-plane me-2"></i> Send to Secretary
                                        @else
                                            <i class="fa-solid fa-check-double me-2"></i> Complete & Close
                                        @endif
                                    </button>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Initial calculation on modal load
    document.addEventListener("DOMContentLoaded", function () {
        calculateTotal({{ $appt->id }});
    });
</script>
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