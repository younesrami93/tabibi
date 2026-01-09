<div class="modal fade" id="addTransactionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('finance.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold text-dark">New Transaction</h5>
                        <p class="text-muted small mb-0">Record manual income or expense</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    
                    {{-- Type Toggle --}}
                    <div class="d-flex justify-content-center mb-4">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="type" id="t_inc" value="income" checked>
                            <label class="btn btn-outline-success py-2 fw-medium" for="t_inc">
                                <i class="fa-solid fa-arrow-down me-2"></i>Income
                            </label>

                            <input type="radio" class="btn-check" name="type" id="t_exp" value="expense">
                            <label class="btn btn-outline-danger py-2 fw-medium" for="t_exp">
                                <i class="fa-solid fa-arrow-up me-2"></i>Expense
                            </label>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Amount</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="amount" class="form-control form-control-lg fw-bold" placeholder="0.00" required>
                                <span class="input-group-text fw-bold text-muted">DH</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Category</label>
                            <input class="form-control" list="catList" name="category" placeholder="Select or type..." required>
                            <datalist id="catList">
                                <option value="Consultation">
                                <option value="Rent">
                                <option value="Salary">
                                <option value="Equipment">
                                <option value="Utilities">
                                <option value="Supplies">
                                <option value="Tax">
                            </datalist>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Date</label>
                            <input type="date" name="transaction_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Payment Method</label>
                            <div class="row g-2">
                                <div class="col-3">
                                    <input type="radio" class="btn-check" name="payment_method" id="pm_cash" value="cash" checked>
                                    <label class="btn btn-outline-secondary w-100 btn-sm" for="pm_cash">Cash</label>
                                </div>
                                <div class="col-3">
                                    <input type="radio" class="btn-check" name="payment_method" id="pm_card" value="card">
                                    <label class="btn btn-outline-secondary w-100 btn-sm" for="pm_card">Card</label>
                                </div>
                                <div class="col-3">
                                    <input type="radio" class="btn-check" name="payment_method" id="pm_check" value="check">
                                    <label class="btn btn-outline-secondary w-100 btn-sm" for="pm_check">Check</label>
                                </div>
                                <div class="col-3">
                                    <input type="radio" class="btn-check" name="payment_method" id="pm_trans" value="transfer">
                                    <label class="btn btn-outline-secondary w-100 btn-sm" for="pm_trans">Transfer</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional details..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 px-4 py-3">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Save Record</button>
                </div>
            </div>
        </form>
    </div>
</div>