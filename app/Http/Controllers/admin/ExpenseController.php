<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExpenseController extends Controller
{
    private function normalizeExpenseFilterDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        foreach (['Y-m-d', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $date)->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    private function resolveBranchId()
    {
        $user               = Auth::user();
        $selectedSubAdminId = session('selectedSubAdminId');

        if ($user->role === 'staff' && $user->branch_id) {
            return $user->branch_id;
        }

        if ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            return $selectedSubAdminId;
        }

        return $user->id; // admin / sub-admin default
    }

    public function create_expense()
    {
        $user     = Auth::user();
        $branchId = $this->resolveBranchId();

        $expenseTypes = ExpenseType::where('isDeleted', 0)
            ->when($user->role === 'staff', function ($q) use ($user) {
                $q->where('created_by', $user->id);
            }, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->get();

        $nextSrNo = Expense::getNextSrNoForBranch($branchId);

        return view('expense.createexpense', compact('expenseTypes', 'nextSrNo'));
    }

    public function edit_expense()
    {

        return view('expense/editexpense');
    }
    public function expense_category()
    {
        $branchId = $this->resolveBranchId();

        $expenseTypes = ExpenseType::where('branch_id', $branchId)
            ->where('isDeleted', 0)
            ->get();

        return view('expense.expensecategory', compact('expenseTypes'));
    }

    public function expense_list()
    {
        $branchId = $this->resolveBranchId();

        $years = Expense::where('branch_id', $branchId)
            ->where('isDeleted', 0)
            ->selectRaw('YEAR(expense_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('expense.expenselist', compact('years'));
    }

    public function expense_report()
    {
        $branchId = $this->resolveBranchId();
        $user     = Auth::user();

        $expenseTypes = ExpenseType::where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->orderBy('type')
            ->get();

        return view('expense.expensereport', compact('expenseTypes'));
    }

    public function expense_report_view($ids)
    {
        $user           = auth()->user();
        $UserID         = $user->id;
        $role           = $user->role;
        $userBranch     = $user->branch_id;
        $selectedBranch = session('selectedSubAdminId');

        // ✅ Decide branch correctly
        if ($role == 'admin' && $selectedBranch) {
            $branchId = $selectedBranch;
        } elseif ($role == 'sub-admin') {
            $branchId = $UserID;
        } elseif ($role == 'staff') {
            $branchId = $userBranch;
        } else {
            $branchId = $UserID;

        }
        $idsArray = explode(',', $ids); // Convert "1,2,3" to [1,2,3]

        $expenses = Expense::with('expenseType')->whereIn('id', $idsArray)->where('branch_id', $branchId)->get();

        if ($expenses->isEmpty()) {
            return redirect()->route('expense.report')->with('error', 'No expense data found.');
        }

        $settings = Setting::where('branch_id', $branchId)->first();

        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        return view('expense.expense_report', compact('expenses', 'currencySymbol', 'settings', 'currencyPosition', 'ids'));
    }

    public function expense_report_pdf($ids)
    {
        $branchId = $this->resolveBranchId();
        $idsArray = explode(',', $ids);

        $expenses = Expense::with('expenseType')->whereIn('id', $idsArray)
            ->where('branch_id', $branchId)
            ->get();

        if ($expenses->isEmpty()) {
            return redirect()->route('expense.report')
                ->with('error', 'No expense data found.');
        }

        $setting = Setting::where('branch_id', $branchId)->first();

        $pdf = PDF::loadView('expense.expense-report-pdf', [
            'expenses'         => $expenses,
            'setting'          => $setting,
            'currencySymbol'   => $setting->currency_symbol ?? '₹',
            'currencyPosition' => $setting->currency_position ?? 'left',
        ])->setPaper('A4', 'portrait');

        return $pdf->download('expense_report.pdf');
    }

    public function show_expense_report_page(Request $request)
    {
        $ids      = $request->query('ids');
        $branchId = $request->query('branch');

        if (! $ids || ! $branchId) {
            return abort(400, 'Missing parameters');
        }

        $idsArray = explode(',', $ids);

        $expenses = Expense::with('expenseType')->whereIn('id', $idsArray)
            ->where('branch_id', $branchId)
            ->get();

        if ($expenses->isEmpty()) {
            return abort(404, 'No expense data found');
        }

        $settings = Setting::where('branch_id', $branchId)->first();

        return view('expense.web_expense_report', [
            'expenses'         => $expenses,
            'settings'         => $settings,
            'currencySymbol'   => $settings->currency_symbol ?? '₹',
            'currencyPosition' => $settings->currency_position ?? 'left',
        ]);
    }

    public function exportExpense(Request $request)
    {
        $branchId = $this->resolveBranchId();
        $user     = Auth::user();

        $year            = $request->query('year');
        $month           = $request->query('month');
        $date            = $this->normalizeExpenseFilterDate($request->query('date'));
        $expense_type_id = $request->query('expense_type_id');

        $query = Expense::with('expenseType')
            ->where('branch_id', $branchId)
            ->where('isDeleted', 0);

        if ($user->role === 'staff') {
            $query->where('created_by', $user->id);
        }

        if (! empty($year)) {
            $query->whereYear('expense_date', $year);
        }
        if (! empty($month)) {
            $query->whereMonth('expense_date', $month);
        }
        if (! empty($date)) {
            $query->whereDate('expense_date', $date);
        }
        if (! empty($expense_type_id)) {
            $query->where('expense_type_id', $expense_type_id);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $headers = [
            'A1' => 'Sr No',
            'B1' => 'Expense Name',
            'C1' => 'Expense Type',
            'D1' => 'Date',
            'E1' => 'Amount',
            'F1' => 'Payment Mode',
            'G1' => 'Expense For',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        $row = 2;
        foreach ($expenses as $expense) {
            $sheet->setCellValue('A' . $row, $expense->sr_no ?? $expense->id);
            $sheet->setCellValue('B' . $row, $expense->expense_name);
            $sheet->setCellValue('C' . $row, $expense->expenseType->type ?? 'N/A');
            $sheet->setCellValue('D' . $row, date('d-M-Y', strtotime($expense->expense_date)));
            $sheet->setCellValue('E' . $row, $expense->amount);
            $sheet->setCellValue('F' . $row, $expense->payment_mode ?? 'N/A');
            $sheet->setCellValue('G' . $row, $expense->description ?? 'N/A');
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'expenses_report_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $writer->save('php://output');
        exit;
    }

    public function exportExpensePdf(Request $request)
    {
        $branchId = $this->resolveBranchId();
        $user     = Auth::user();

        $year            = $request->query('year');
        $month           = $request->query('month');
        $date            = $this->normalizeExpenseFilterDate($request->query('date'));
        $expense_type_id = $request->query('expense_type_id');

        $query = Expense::with('expenseType')
            ->where('branch_id', $branchId)
            ->where('isDeleted', 0);

        if ($user->role === 'staff') {
            $query->where('created_by', $user->id);
        }

        if (! empty($year)) {
            $query->whereYear('expense_date', $year);
        }
        if (! empty($month)) {
            $query->whereMonth('expense_date', $month);
        }
        if (! empty($date)) {
            $query->whereDate('expense_date', $date);
        }
        if (! empty($expense_type_id)) {
            $query->where('expense_type_id', $expense_type_id);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->get();
        // dd($expenses);
        $setting  = Setting::where('branch_id', $branchId)->first();

        $pdf = PDF::loadView('expense.expense-pdf', [
            'expenses'         => $expenses,
            'setting'          => $setting,
            'currencySymbol'   => $setting->currency_symbol ?? '₹',
            'currencyPosition' => $setting->currency_position ?? 'left',
        ])->setPaper('A4', 'portrait');

        return $pdf->download('expenses_report_' . date('Y-m-d') . '.pdf');
    }

    public function downloadSingleExpensePdf($id)
    {
        $branchId = $this->resolveBranchId();
        $user     = Auth::user();

        $query = Expense::with('expenseType')
            ->where('id', $id)
            ->where('branch_id', $branchId)
            ->where('isDeleted', 0);

        if ($user->role === 'staff') {
            $query->where('created_by', $user->id);
        }

        $expense = $query->firstOrFail();
        $setting = Setting::where('branch_id', $branchId)->first();

        $pdf = PDF::loadView('expense.expense-pdf', [
            'expenses'         => collect([$expense]),
            'setting'          => $setting,
            'currencySymbol'   => $setting->currency_symbol ?? '₹',
            'currencyPosition' => $setting->currency_position ?? 'left',
        ])->setPaper('A4', 'portrait');

        return $pdf->download('expense_' . ($expense->sr_no ?? $expense->id) . '.pdf');
    }

}
