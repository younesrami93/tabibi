@extends('layouts.admin')

@section('content')
    {{-- Load DateRangePicker Dependencies (CDN for speed) --}}
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <div class="container-fluid">

        {{-- 1. MAIN HEADER & DATE FILTER (Alone on top) --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h4 class="fw-bold text-dark mb-1">Financial Manager</h4>
                <p class="text-muted small mb-0">Daily Caisse & Expense Tracking</p>
            </div>

            {{-- CENTRALIZED DATE PICKER --}}
            <div class="d-flex align-items-center bg-white border shadow-sm rounded-pill px-2 py-1"
                style="min-width: 280px;">
                <div class="p-2 text-muted"><i class="fa-regular fa-calendar"></i></div>
                <input type="text" id="reportRange"
                    class="form-control border-0 bg-transparent shadow-none fw-bold text-dark text-center"
                    style="font-size: 0.95rem; cursor: pointer;">
                <div class="p-2 text-muted"><i class="fa-solid fa-chevron-down small"></i></div>
            </div>


        </div>

        {{-- FORM: Hidden Inputs for Filters (Controlled by JS) --}}
        <form action="{{ route('finance.index') }}" method="GET" id="financeFilterForm">
            <input type="hidden" name="date_from" id="inputDateFrom"
                value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}">
            <input type="hidden" name="date_to" id="inputDateTo" value="{{ request('date_to', now()->format('Y-m-d')) }}">

            {{-- 2. STATISTICS CARDS --}}
            <div class="row g-3 mb-4">
                {{-- Income --}}
                <div class="col-md-4">
                    <div class="card  h-100">
                        <div class="card-body p-4 d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-uppercase small fw-bold text-muted mb-1">Total Income</p>
                                <h3 class="fw-bold text-success mb-0">+{{ number_format($stats['income'], 2) }} <small
                                        class="fs-6 text-muted">DH</small></h3>
                            </div>
                            <div class="bg-success bg-opacity-10 text-success rounded-circle p-3">
                                <i class="fa-solid fa-arrow-trend-up fa-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Expenses --}}
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body p-4 d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-uppercase small fw-bold text-muted mb-1">Total Expenses</p>
                                <h3 class="fw-bold text-danger mb-0">-{{ number_format($stats['expense'], 2) }} <small
                                        class="fs-6 text-muted">DH</small></h3>
                            </div>
                            <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-3">
                                <i class="fa-solid fa-arrow-trend-down fa-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Balance --}}
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body p-4 d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-uppercase small fw-bold text-muted-50 mb-1">Net Balance</p>
                                <h3 class="fw-bold text-black mb-0">{{ number_format($stats['balance'], 2) }} <small
                                        class="fs-6 text-muted-50">DH</small></h3>
                            </div>
                            <div class="bg-primary bg-opacity-25 text-primary rounded-circle p-3">
                                <i class="fa-solid fa-wallet fa-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-primary fw-bold shadow-sm px-4 rounded-pill" type="button" data-bs-toggle="modal"
                    data-bs-target="#addTransactionModal">
                    <i class="fa-solid fa-plus me-2"></i> New Transaction
                </button>
            </div>


            {{-- 3. TABLE WITH EMBEDDED FILTERS --}}
            <div class="card overflow-hidden">
                {{-- Table Header: Filters on Top --}}
                <div class="card-header bg-white border-bottom py-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-6">
                            <h6 class="fw-bold m-0 text-dark"><i class="fa-solid fa-list me-2"></i>Transaction History</h6>
                        </div>

                        {{-- Filters --}}
                        <div class="col-md-2">
                            <select name="type" class="form-select form-select-sm bg-light border-0 fw-medium"
                                onchange="this.form.submit()">
                                <option value="all">All Types</option>
                                <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Income Only
                                </option>
                                <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expense Only
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="method" class="form-select form-select-sm bg-light border-0 fw-medium"
                                onchange="this.form.submit()">
                                <option value="all">All Methods</option>
                                <option value="cash" {{ request('method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="card" {{ request('method') == 'card' ? 'selected' : '' }}>Card</option>
                                <option value="check" {{ request('method') == 'check' ? 'selected' : '' }}>Check</option>
                                <option value="transfer" {{ request('method') == 'transfer' ? 'selected' : '' }}>Transfer
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2 text-end">
                            <a href="{{ route('finance.index') }}"
                                class="btn btn-light btn-sm text-muted border fw-bold w-100">
                                <i class="fa-solid fa-rotate-left me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small text-muted">
                            <tr>
                                <th class="ps-4 py-3 border-bottom-0">Date</th>
                                <th class="py-3 border-bottom-0">Category</th>
                                <th class="py-3 border-bottom-0">Description</th>
                                <th class="py-3 border-bottom-0">Method</th>
                                <th class="text-end pe-4 py-3 border-bottom-0">Amount</th>
                                <th class="text-end py-3 border-bottom-0"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $trx)
                                <tr>
                                    <td class="ps-4 border-bottom-0">
                                        <div class="fw-bold text-dark">{{ $trx->transaction_date->format('d M, Y') }}</div>
                                        <div class="small text-muted">{{ $trx->created_at->format('H:i') }}</div>
                                    </td>
                                    <td class="border-bottom-0">
                                        <span class="badge bg-light text-dark border fw-normal">{{ $trx->category }}</span>
                                        @if($trx->billable_type === 'App\Models\Appointment')
                                            <span class="badge bg-primary bg-opacity-10 text-primary ms-1">Appt
                                                #{{ $trx->billable_id }}</span>
                                        @endif
                                    </td>
                                    <td class="border-bottom-0">
                                        <div class="text-truncate" style="max-width: 280px;">
                                            {{ $trx->notes ?: '-' }}
                                            @if($trx->patient)
                                                <div class="small text-muted fw-bold">
                                                    <i class="fa-solid fa-user me-1"></i> {{ $trx->patient->full_name }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="border-bottom-0">
                                        <span class="text-capitalize small"><i class="fa-regular fa-credit-card me-1"></i>
                                            {{ $trx->payment_method }}</span>
                                    </td>
                                    <td class="text-end pe-4 border-bottom-0">
                                        <span
                                            class="d-block fw-bold fs-6 {{ $trx->type === 'income' ? 'text-success' : 'text-danger' }}">
                                            {{ $trx->type === 'income' ? '+' : '-' }} {{ number_format($trx->amount, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end border-bottom-0">
                                        <form action="{{ route('finance.destroy', $trx->id) }}" method="POST"
                                            onsubmit="return confirm('Delete transaction?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm text-muted hover-danger"><i
                                                    class="fa-regular fa-trash-can"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted bg-light">
                                        <i class="fa-solid fa-file-invoice-dollar fa-2x mb-3 opacity-25"></i>
                                        <p class="mb-0">No transactions found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($transactions->hasPages())
                    <div class="card-footer bg-white py-3">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </form>
    </div>

    @include('layouts.partials.add_modal')

    {{-- SCRIPTS: Moment.js + DateRangePicker --}}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        $(function () {
            // 1. Get current values from PHP/URL
            var start = moment("{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}");
            var end = moment("{{ request('date_to', now()->format('Y-m-d')) }}");

            // 2. Callback function: Update Hidden Inputs & Submit Form
            function cb(start, end) {
                $('#reportRange').val(start.format('D MMM, YYYY') + ' - ' + end.format('D MMM, YYYY'));
                $('#inputDateFrom').val(start.format('YYYY-MM-DD'));
                $('#inputDateTo').val(end.format('YYYY-MM-DD'));

                // Auto Submit logic
                // We verify if the values actually changed to prevent loop on load
                // But since this is a clean page load, we just display. 
                // The 'apply.daterangepicker' event below handles the submit.
            }

            // 3. Initialize Picker
            $('#reportRange').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'This Week': [moment().startOf('week'), moment().endOf('week')],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'Last 3 Months': [moment().subtract(3, 'months'), moment()],
                    'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
                },
                opens: 'left',
                buttonClasses: 'btn btn-sm',
                applyButtonClass: 'btn-primary',
                cancelButtonClass: 'btn-light'
            }, cb);

            // 4. Initial Display
            cb(start, end);

            // 5. EVENT: Submit Form on "Apply"
            $('#reportRange').on('apply.daterangepicker', function (ev, picker) {
                // Update hidden inputs
                $('#inputDateFrom').val(picker.startDate.format('YYYY-MM-DD'));
                $('#inputDateTo').val(picker.endDate.format('YYYY-MM-DD'));
                // Submit form
                $('#financeFilterForm').submit();
            });
        });
    </script>
@endsection