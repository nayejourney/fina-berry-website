<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Expense;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request, ReportService $reports)
    {
        [$from, $to] = $reports->range($request->query('dari'), $request->query('sampai'));

        $expenses = Expense::with('user')
            ->whereBetween('spent_on', [$from->toDateString(), $to->toDateString()])
            ->orderByDesc('spent_on')->orderByDesc('id')
            ->get();

        return view('admin.expenses', [
            'expenses' => $expenses,
            'total' => (int) $expenses->sum('amount'),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'spent_on' => ['required', 'date', 'before_or_equal:today'],
            'category' => ['required', 'in:'.implode(',', array_keys(Expense::CATEGORIES))],
            'description' => ['required', 'string', 'max:160'],
            'amount' => ['required', 'integer', 'min:1', 'max:1000000000'],
        ]);

        $expense = Expense::create($data + ['user_id' => $request->user()->id]);
        AuditLog::record('expense.created', $expense, ['amount' => $expense->amount, 'category' => $expense->category]);

        return back()->with('success', 'Biaya operasional dicatat.');
    }

    public function destroy(Expense $expense)
    {
        AuditLog::record('expense.deleted', $expense, [
            'amount' => $expense->amount, 'category' => $expense->category, 'description' => $expense->description,
        ]);
        $expense->delete();

        return back()->with('success', 'Catatan biaya dihapus.');
    }
}
