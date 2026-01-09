<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = Auth::user()->clinic_id;
        $query = Transaction::where('clinic_id', $clinicId)
            ->with(['user', 'patient']); // Eager load for performance

        // --- 1. FILTERS ---

        // Date Range (Default to THIS MONTH if empty)
        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        } else {
            // Default: Start of this month
            $query->whereDate('transaction_date', '>=', now()->startOfMonth());
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        // Type Filter
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Payment Method Filter
        if ($request->filled('method') && $request->method !== 'all') {
            $query->where('payment_method', $request->method);
        }

        // --- 2. STATISTICS (Calculated on the Filtered Data) ---
        // We clone the query so the stats match exactly what the user sees in the table
        $statsQuery = clone $query;

        // We can't use 'get()' on the main query yet because of pagination, 
        // but we need aggregates.
        $aggregates = $statsQuery->selectRaw("
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense,
            COUNT(*) as count
        ")->first();

        $stats = [
            'income' => $aggregates->total_income ?? 0,
            'expense' => $aggregates->total_expense ?? 0,
            'balance' => ($aggregates->total_income ?? 0) - ($aggregates->total_expense ?? 0),
            'count' => $aggregates->count,
        ];

        // --- 3. RESULTS ---
        $transactions = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('doctor.finance', compact('transactions', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:100',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cash,card,check,transfer',
        ]);

        Transaction::create([
            'clinic_id' => Auth::user()->clinic_id,
            'user_id' => Auth::id(),
            'type' => $request->type,
            'amount' => $request->amount,
            'category' => $request->category,
            'transaction_date' => $request->transaction_date,
            'payment_method' => $request->payment_method,
            'notes' => $request->description,
            // Manual entries don't link to an appointment automatically, 
            // but the system supports it if we wanted to add it later.
            'billable_type' => null,
            'billable_id' => null,
        ]);

        return back()->with('success', 'Transaction recorded successfully.');
    }

    public function destroy($id)
    {
        $transaction = Transaction::where('id', $id)
            ->where('clinic_id', Auth::user()->clinic_id)
            ->firstOrFail();

        // Optional: Prevent deleting Appointment payments here?
        // if ($transaction->billable_type === 'App\Models\Appointment') {
        //     return back()->with('error', 'Cannot delete appointment payments from here. Manage them in the Appointment.');
        // }

        $transaction->delete();
        return back()->with('success', 'Transaction removed.');
    }
}