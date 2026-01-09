<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form action="" method="POST" id="paymentForm">
            @csrf
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                <div class="modal-header bg-success text-white py-2">
                    <h6 class="modal-title fw-bold"><i class="fa-solid fa-hand-holding-dollar me-2"></i>Add Payment</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    
                    <div class="text-center mb-3">
                        <p class="text-muted small mb-1">Remaining Debt</p>
                        <h3 class="fw-bold text-danger" id="displayDueAmount">0.00 DH</h3>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Amount to Pay</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="amount" id="paymentInput" 
                                   class="form-control fw-bold text-center" 
                                   placeholder="0.00" required>
                            <span class="input-group-text fw-bold">DH</span>
                        </div>
                        <div class="form-text text-end cursor-pointer text-primary small" style="cursor: pointer;" onclick="setFullAmount()">
                            Pay Full Amount
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Method</label>
                        <select name="method" class="form-select form-select-sm">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="check">Check</option>
                            <option value="transfer">Transfer</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-bold shadow-sm">
                        Confirm Payment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    let currentMax = 0;

    function openPaymentModal(url, dueAmount) {
        // 1. Set the Form Action
        document.getElementById('paymentForm').action = url;
        
        // 2. Update UI
        currentMax = parseFloat(dueAmount);
        document.getElementById('displayDueAmount').innerText = currentMax.toFixed(2) + ' DH';
        
        // 3. Reset Input
        const input = document.getElementById('paymentInput');
        input.value = '';
        input.max = currentMax; // Browser-level validation
        
        // 4. Show Modal
        new bootstrap.Modal(document.getElementById('paymentModal')).show();
    }

    function setFullAmount() {
        document.getElementById('paymentInput').value = currentMax.toFixed(2);
    }
</script>