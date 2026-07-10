<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceSheetController extends Controller
{

    public function index(Request $request)
    {
        $branchId = session('selectedSubAdminId'); // or Auth::user()->branch_id if single branch
        $startDate = $request->start_date ?? \Carbon\Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? \Carbon\Carbon::now()->endOfMonth()->toDateString();

        // ======================
        // 🏷️ Currency Settings & Company Info
        // ======================
        $settings = DB::table('settings')->first();
        $sym      = $settings->currencySymbol ?? '₹';
        $pos      = $settings->currencyPosition ?? 'left';

        $companyName = $settings->name ?? 'Company Name';
        $companyAddress = $settings->address ?? 'Address Line';
        $companyPhone = $settings->phone ?? 'Phone';

        // ======================
        // 🧾 1️⃣ ASSETS
        // ======================

        // Cash (sum of all recorded cash payments)

        $cash = DB::table('payment_store')
            ->join('orders', 'orders.id', '=', 'payment_store.order_id')
            ->where('payment_store.isDeleted', 0)
            ->where('payment_store.payment_method', ['cash', 'Cash'])
            ->where(function ($q) {
                $q->whereNotNull('payment_store.order_id')
                    ->where('payment_store.order_id', '<>', '')
                    ->where('payment_store.order_id', '>', 0);
            })
            ->where(function ($q) {
                $q->whereNull('payment_store.purchase_id')
                    ->orWhere('payment_store.purchase_id', '=', 0)
                    ->orWhere('payment_store.purchase_id', '=', '');
            })
            ->where(function ($q) {
                $q->whereNull('payment_store.custom_invoice_id')
                    ->orWhere('payment_store.custom_invoice_id', '=', 0)
                    ->orWhere('payment_store.custom_invoice_id', '=', '');
            })
            ->when($branchId, fn($q) => $q->where('orders.branch_id', $branchId))
            ->when($startDate, fn($q) => $q->whereDate('payment_store.payment_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('payment_store.payment_date', '<=', $endDate))
            ->sum('payment_store.payment_amount');

            // Bank (UPI / Bank / Online)
        $bank = DB::table('payment_store')
            ->join('orders', 'orders.id', '=', 'payment_store.order_id')
            ->where('payment_store.isDeleted', 0)
            ->whereIn('payment_store.payment_method', ['upi', 'bank', 'online','Online', 'debit card', 'Debit Card', 'Debit card', 'scan', 'Scan'])
            ->where(function ($q) {
                $q->whereNotNull('payment_store.order_id')
                    ->where('payment_store.order_id', '<>', '')
                    ->where('payment_store.order_id', '>', 0);
            })
            ->where(function ($q) {
                $q->whereNull('payment_store.purchase_id')
                    ->orWhere('payment_store.purchase_id', '=', 0)
                    ->orWhere('payment_store.purchase_id', '=', '');
            })
            ->where(function ($q) {
                $q->whereNull('payment_store.custom_invoice_id')
                    ->orWhere('payment_store.custom_invoice_id', '=', 0)
                    ->orWhere('payment_store.custom_invoice_id', '=', '');
            })
            ->when($branchId, fn($q) => $q->where('orders.branch_id', $branchId))
            ->when($startDate, fn($q) => $q->whereDate('payment_store.payment_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('payment_store.payment_date', '<=', $endDate))
            ->sum('payment_store.payment_amount');

        // Inventory value (sum of product quantity * price)
        $inventory = DB::table('products')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum(DB::raw('quantity * price'));

        $assetGroups = [
            'Fixed Assets' => [
                // Add fixed assets queries here when available
            ],
            'Investments' => [
                // Add investments queries here when available
            ],
            'Current Assets' => [
                'Closing Stock' => $inventory,
                'Cash-in-Hand' => $cash,
                'Bank Accounts' => $bank,
            ],
        ];

        // Total Assets
        $totalAssets = $cash + $bank + $inventory;


        // ======================
        // 💸 2️⃣ LIABILITIES
        // ======================

        // Accounts Payable (Unpaid purchase invoices)
        $accountsPayable = DB::table('purchase_invoice')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            // purchase_invoice does not have a purchase_date column; use created_at for filtering.
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->sum('remaining_amount');

        // GST Payable (GST from Orders marked as with GST)
        $gstPayable = DB::table('orders')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('gst_option', 1)
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->sum(DB::raw('(total_amount * 18) / 100')); // change 18 if dynamic tax_id system

        $totalSales = DB::table('orders')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->sum('total_amount');

        $totalPurchases = DB::table('purchase_invoice')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->sum('grand_total');

        $totalExpenses = DB::table('expenses')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($startDate, fn($q) => $q->whereDate('expense_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('expense_date', '<=', $endDate))
            ->sum('amount');

        $totalLiabilities = $accountsPayable + $gstPayable;

        // Retained Earnings = Total Assets - Total Liabilities
        $retainedEarnings = $totalAssets - $totalLiabilities;

        $liabilityGroups = [
            'Capital Account' => [
                // Add capital accounts queries here when available
            ],
            'Loans (Liability)' => [
                // Add loans queries here when available
            ],
            'Current Liabilities' => [
                'Accounts Payable' => $accountsPayable,
            ],
        ];

        if ($gstPayable > 0) {
            $liabilityGroups['Current Liabilities']['Duties & Taxes (GST)'] = $gstPayable;
        }

        $liabilityGroups['Profit & Loss A/c'] = [
            'Current Period' => $retainedEarnings,
        ];

        // ======================
        // 📘 4️⃣ TOTALS
        // ======================
        $totals = [
            'assets'             => $totalAssets,
            'liabilities_equity' => $totalLiabilities + $retainedEarnings,
        ];

        // ======================
        // 📄 5️⃣ RETURN VIEW OR EXPORT
        // ======================
        $data = compact(
            'assetGroups',
            'liabilityGroups',
            'totals',
            'sym',
            'pos',
            'companyName',
            'companyAddress',
            'companyPhone',
            'startDate',
            'endDate',
            'settings'
        );

        if ($request->export === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('accounting.balance-sheet-print', $data);
            return $pdf->download('Balance_Sheet_'.$startDate.'_to_'.$endDate.'.pdf');
        }

        if ($request->export === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\BalanceSheetExport($data), 'Balance_Sheet_'.$startDate.'_to_'.$endDate.'.xlsx');
        }

        return view('accounting.balance-sheet', $data);
    }
}
