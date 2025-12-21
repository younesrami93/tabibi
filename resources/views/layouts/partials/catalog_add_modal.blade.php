<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('catalog.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add to Catalog</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    
                    {{-- Type Selector --}}
                    <div class="mb-3">
                        <label class="form-label d-block fw-bold">Item Type</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="type" id="typeMed" value="medicine" checked onchange="toggleFields()">
                            <label class="btn btn-outline-primary" for="typeMed"><i class="fa-solid fa-pills me-2"></i> Medicine</label>

                            <input type="radio" class="btn-check" name="type" id="typeTest" value="test" onchange="toggleFields()">
                            <label class="btn btn-outline-primary" for="typeTest"><i class="fa-solid fa-microscope me-2"></i> Lab Test</label>
                        </div>
                    </div>

                    {{-- Name --}}
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Amoxicillin or CBC Test" required>
                    </div>

                    {{-- MEDICINE SPECIFIC FIELDS (Hidden if Test is selected) --}}
                    <div id="medicineFields">
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Form</label>
                                <select name="form" class="form-select">
                                    <option value="Tablet">Tablet (Comprimé)</option>
                                    <option value="Capsule">Capsule (Gélule)</option>
                                    <option value="Syrup">Syrup (Sirop)</option>
                                    <option value="Sachet">Sachet</option>
                                    <option value="Injection">Injection</option>
                                    <option value="Cream">Cream (Crème)</option>
                                    <option value="Spray">Spray</option>
                                    <option value="Drops">Drops (Gouttes)</option>
                                    <option value="Suppository">Suppository</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Strength</label>
                                <input type="text" name="strength" class="form-control" placeholder="e.g. 500mg, 1g">
                            </div>
                        </div>

                        <div class="p-3 bg-light rounded border border-dashed">
                            <label class="form-label fw-bold small text-uppercase text-muted">Default Dosage (Optional)</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text">Take</span>
                                <input type="number" name="default_quantity" class="form-control" placeholder="1">
                                <span class="input-group-text">unit(s)</span>
                            </div>
                            <div class="input-group mb-2">
                                <input type="number" name="default_frequency" class="form-control" placeholder="3">
                                <span class="input-group-text">times / day</span>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text">For</span>
                                <input type="number" name="default_duration" class="form-control" placeholder="7">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Save Item</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleFields() {
        const isMed = document.getElementById('typeMed').checked;
        const medFields = document.getElementById('medicineFields');
        
        if (isMed) {
            medFields.style.display = 'block';
            // Enable inputs so they are sent to the server
            medFields.querySelectorAll('input, select').forEach(el => el.disabled = false);
        } else {
            medFields.style.display = 'none';
            // Disable inputs so they are sent as NULL (prevents "Tablet" being saved for a Test)
            medFields.querySelectorAll('input, select').forEach(el => el.disabled = true);
        }
    }

    // Run on load to ensure correct state
    document.addEventListener("DOMContentLoaded", toggleFields);
</script>