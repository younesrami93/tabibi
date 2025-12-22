<div class="modal fade text-start" id="finishModal-{{ $appt->id }}" tabindex="-1">
    {{-- Force width and centering to ensure large modal --}}
    <div class="modal-dialog modal-dialog-centered mx-auto" style="max-width: 95%; width: 95%;">

        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('appointments.finish', $appt->id) }}" method="POST">
                @csrf @method('PUT')

                {{-- Modal Header --}}
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-user-doctor me-2"></i>Consultation Summary
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body bg-light">
                    <div class="row g-4">

                        {{-- LEFT COLUMN: Clinical Data & Prescriptions --}}
                        <div class="col-lg-8 d-flex flex-column gap-4">

                            {{-- 1. Billable Services --}}
                            <div class="card border-0 shadow-sm">
                                <div
                                    class="card-header bg-white fw-bold small text-uppercase d-flex justify-content-between align-items-center">
                                    <span><i class="fa-solid fa-file-invoice-dollar text-success me-2"></i>Medical
                                        Services</span>
                                    <small class="text-muted">Billable Items</small>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-3">Service Name</th>
                                                    <th width="120">Price (DH)</th>
                                                    <th width="30"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="serviceRows-{{ $appt->id }}">
                                                @foreach($appt->services as $ix => $s)
                                                    <tr class="service-row">
                                                        <td class="ps-3">
                                                            <input type="hidden" name="services[{{ $ix }}][id]"
                                                                value="{{ $s->id }}">
                                                            <input type="text"
                                                                class="form-control form-control-sm bg-light border-0 fw-bold"
                                                                value="{{ $s->name }}" readonly>
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01"
                                                                class="form-control form-control-sm text-end price-input"
                                                                name="services[{{ $ix }}][price]"
                                                                value="{{ $s->pivot->price }}"
                                                                oninput="calculateTotal({{ $appt->id }})">
                                                        </td>
                                                        <td class="text-center">
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
                                    <div class="p-2 bg-light border-top d-flex gap-2">
                                        <div class="input-group input-group-sm">
                                            <select class="form-select" id="newServiceSelect-{{ $appt->id }}">
                                                <option value="">Select Catalog Service...</option>
                                                @foreach($allServices as $srv)
                                                    <option value="{{ $srv->id }}" data-name="{{ $srv->name }}"
                                                        data-price="{{ $srv->price }}">
                                                        {{ $srv->name }} ({{ number_format($srv->price, 2) }} DH)
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-primary px-3"
                                                onclick="addServiceRow({{ $appt->id }})">
                                                <i class="fa-solid fa-plus"></i> Add
                                            </button>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary text-nowrap px-3"
                                            onclick="addCustomServiceRow({{ $appt->id }})">
                                            <i class="fa-solid fa-pen-to-square me-1"></i> Custom Item
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. Prescriptions Manager (Multiple Blocks) --}}
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold small text-uppercase text-primary mb-0">
                                        <i class="fa-solid fa-prescription me-2"></i>Prescriptions
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-primary shadow-sm"
                                        onclick="addNewPrescriptionBlock({{ $appt->id }})">
                                        <i class="fa-solid fa-plus me-1"></i> New Prescription
                                    </button>
                                </div>

                                <div id="prescriptionsContainer-{{ $appt->id }}" class="d-flex flex-column gap-3">
                                    {{--
                                    LOGIC: Handle existing data.
                                    If 'prescription' column has data, it might be the old "flat" format or new "nested"
                                    format.
                                    We normalize it here so the loop works.
                                    --}}
                                    @php
                                        $existingPrescriptions = $appt->prescription ?? [];
                                        // Normalize: If it's a flat array (old style), wrap it in a single block
                                        if (!empty($existingPrescriptions) && isset($existingPrescriptions[0]['name'])) {
                                            $existingPrescriptions = [
                                                ['title' => 'Prescription #1', 'items' => $existingPrescriptions]
                                            ];
                                        }
                                        // If empty, show at least one empty block
                                        if (empty($existingPrescriptions)) {
                                            $existingPrescriptions = [['title' => 'Prescription #1', 'items' => []]];
                                        }
                                    @endphp

                                    @foreach($existingPrescriptions as $pIndex => $block)
                                        <div class="card border shadow-sm prescription-block"
                                            id="pBlock-{{ $appt->id }}-{{ $pIndex }}" data-index="{{ $pIndex }}">
                                            {{-- Block Header --}}
                                            <div
                                                class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="text-muted small"><i
                                                            class="fa-solid fa-file-medical"></i></span>
                                                    <input type="text" name="prescriptions[{{ $pIndex }}][title]"
                                                        class="form-control form-control-sm fw-bold border-0 bg-transparent p-0"
                                                        value="{{ $block['title'] ?? 'Prescription #' . ($pIndex + 1) }}"
                                                        style="width: 200px;" placeholder="Prescription Title">
                                                </div>
                                                <div class="d-flex gap-2">
                                                    {{-- Template Loader for THIS block --}}
                                                    <select class="form-select form-select-sm" style="width: 160px;"
                                                        onchange="loadTemplateToBlock(this, {{ $appt->id }}, {{ $pIndex }})">
                                                        <option value="">Load Template...</option>
                                                        @foreach($templates as $temp)
                                                            <option value="{{ $temp->id }}">{{ $temp->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" class="btn btn-sm btn-outline-danger border-0"
                                                        onclick="removePrescriptionBlock({{ $appt->id }}, {{ $pIndex }})"
                                                        title="Remove Prescription">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Block Items Table --}}
                                            <div class="card-body p-0">
                                                <div class="table-responsive overflow-visible">
                                                    <table class="table table-sm align-middle mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th class="ps-3" width="45%">Item Name</th>
                                                                <th>Dosage / Instructions</th>
                                                                <th width="30"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="pItems-{{ $appt->id }}-{{ $pIndex }}">
                                                            @if(!empty($block['items']))
                                                                @foreach($block['items'] as $iIndex => $item)
                                                                    <tr>
                                                                        <td class="ps-3 position-relative">
                                                                            <input type="hidden"
                                                                                name="prescriptions[{{ $pIndex }}][items][{{ $iIndex }}][catalog_item_id]"
                                                                                value="{{ $item['catalog_item_id'] ?? '' }}">
                                                                            <input type="text"
                                                                                name="prescriptions[{{ $pIndex }}][items][{{ $iIndex }}][name]"
                                                                                class="form-control form-control-sm fw-bold"
                                                                                value="{{ $item['name'] }}" required>
                                                                        </td>
                                                                        <td>
                                                                            <input type="text"
                                                                                name="prescriptions[{{ $pIndex }}][items][{{ $iIndex }}][note]"
                                                                                class="form-control form-control-sm"
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
                                                {{-- Add Item Button --}}
                                                <div class="p-2 text-center border-top bg-light">
                                                    <button type="button" class="btn btn-sm btn-link text-decoration-none"
                                                        onclick="addItemToBlock({{ $appt->id }}, {{ $pIndex }})">
                                                        <i class="fa-solid fa-plus-circle me-1"></i> Add Medicine / Test
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- 3. Notes --}}
                            <div>
                                <label class="form-label small fw-bold text-muted">Consultation Notes /
                                    Diagnosis</label>
                                <textarea name="notes" class="form-control" rows="2"
                                    placeholder="Write clinical notes here...">{{ $appt->notes }}</textarea>
                            </div>

                        </div>

                        {{-- RIGHT COLUMN: Payment --}}
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white fw-bold small text-uppercase">Payment Details</div>
                                <div class="card-body d-flex flex-column">
                                    <div class="mb-3">
                                        <label class="small text-muted fw-bold">Consultation Fee</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" class="form-control fw-bold"
                                                id="basePrice-{{ $appt->id }}" name="price"
                                                value="{{ number_format($appt->price, 2, '.', '') }}"
                                                oninput="calculateTotal({{ $appt->id }})">
                                            <span class="input-group-text">MAD</span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3 align-items-center">
                                        <span class="text-muted small">Services Total:</span>
                                        <span class="fw-bold fs-5"><span id="servicesSum-{{ $appt->id }}">0.00</span>
                                            <small class="fs-6 text-muted">MAD</small></span>
                                    </div>
                                    <hr class="border-dashed my-2">
                                    <div
                                        class="bg-success bg-opacity-10 p-3 rounded text-center mb-4 mt-auto border border-success border-opacity-25">
                                        <small class="text-success fw-bold text-uppercase">Total To Pay</small>
                                        <h2 class="mb-0 text-success fw-bold display-6">
                                            <span id="totalDisplay-{{ $appt->id }}">0.00</span> <small
                                                class="fs-5">MAD</small>
                                        </h2>
                                    </div>
                                    <div>
                                        <label class="small text-muted fw-bold">Amount Received</label>
                                        <input type="number" step="0.01"
                                            class="form-control form-control-lg border-success text-success fw-bold text-end"
                                            name="paid_amount" id="paidAmount-{{ $appt->id }}" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top-0 pb-3">
                                    <button type="submit"
                                        class="btn btn-success w-100 fw-bold shadow-sm py-2 text-uppercase">
                                        <i class="fa-solid fa-check-circle me-2"></i>Complete Appointment
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