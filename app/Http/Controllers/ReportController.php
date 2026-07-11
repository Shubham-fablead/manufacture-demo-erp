<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\CachedObjectStorageFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Order;
use App\Models\Expense;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\Purchases;
use App\Models\TaxRate;
use App\Models\User;


class ReportController extends Controller
{
    private function resolveBranchId()
    {
        $user       = auth()->user();
        $role       = strtolower($user->role ?? '');
        $subAdminId = session('selectedSubAdminId');

        return match ($role) {
            'sub-admin' => $user->id,
            'staff'     => $user->branch_id,
            'admin'     => $subAdminId ?: $user->id,
            default     => $user->id,
        };
    }

    private function resolveProfitLossDateRange(Request $request): array
    {
        $timePeriod = $request->input('time_period', 'all_time');

        return match ($timePeriod) {
            'this_week' => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'this_month' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'last_6_months' => [now()->subMonths(5)->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'this_year' => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            'previous_year' => [now()->subYear()->startOfYear()->toDateString(), now()->subYear()->endOfYear()->toDateString()],
            default => [
                $request->filled('from_date') ? $request->input('from_date') : null,
                $request->filled('to_date') ? $request->input('to_date') : null,
            ],
        };
    }

    public function profitLossStatementIndex()
    {
        return view('reports.profit-loss-statement');
    }

    private function getProfitLossDataArray(Request $request)
    {
        $branchId = $this->resolveBranchId();
        [$from, $to] = $this->resolveProfitLossDateRange($request);

        $salesQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.isDeleted', 0)
            ->where('products.isDeleted', 0)
            ->where('orders.payment_status', 'completed')
            ->where('orders.branch_id', $branchId);

        if ($from) {
            $salesQuery->whereDate('orders.created_at', '>=', $from);
        }
        if ($to) {
            $salesQuery->whereDate('orders.created_at', '<=', $to);
        }

        $salesRows = $salesQuery
            ->selectRaw('products.id as product_id, products.name as product_name, SUM(order_items.total_amount) as amount')
            ->groupBy('products.id', 'products.name')
            ->orderBy('products.name')
            ->get();

        $purchaseQuery = DB::table('purchases')
            ->join('purchase_invoice', 'purchases.invoice_id', '=', 'purchase_invoice.id')
            ->join('products', 'purchases.item', '=', 'products.id')
            ->where('purchases.isDeleted', 0)
            ->where('purchase_invoice.isDeleted', 0)
            ->where('purchase_invoice.branch_id', $branchId)
            ->where('products.isDeleted', 0);

        if ($from) {
            // purchase_invoice has no purchase_date column in this app; use created_at instead.
            $purchaseQuery->whereDate('purchase_invoice.created_at', '>=', $from);
        }
        if ($to) {
            $purchaseQuery->whereDate('purchase_invoice.created_at', '<=', $to);
        }

        $purchaseRows = $purchaseQuery
            ->selectRaw('products.id as product_id, products.name as product_name, SUM(COALESCE(NULLIF(purchases.amount_total, 0), purchases.price * purchases.quantity)) as amount, SUM(purchases.discount_amount) as discount_amount')
            ->groupBy('products.id', 'products.name')
            ->orderBy('products.name')
            ->get();

        $expenseQuery = Expense::query()
            ->with('expenseType')
            ->where('branch_id', $branchId)
            ->where('isDeleted', 0);

        if ($from) {
            $expenseQuery->whereDate('expense_date', '>=', $from);
        }
        if ($to) {
            $expenseQuery->whereDate('expense_date', '<=', $to);
        }

        $expenseRows = $expenseQuery
            ->get()
            ->groupBy(fn ($row) => $row->expenseType->type ?? $row->expense_name ?? 'Other');

        $stockCostQuery = Product::query()
            ->leftJoin('product_inventory as pi', 'pi.product_id', '=', 'products.id')
            ->where('products.branch_id', $branchId)
            ->where('products.isDeleted', 0)
            ->select([
                'products.id',
                'products.name',
                DB::raw('COALESCE(products.landing_cost, products.cost_price, products.price, 0) as unit_cost'),
                DB::raw('COALESCE(MAX(pi.current_stock), products.quantity, 0) as current_stock'),
                DB::raw('COALESCE(MAX(pi.initial_stock), products.quantity, 0) as initial_stock'),
            ])
            ->groupBy('products.id', 'products.name', 'products.landing_cost', 'products.cost_price', 'products.price', 'products.quantity');

        $stockRows = $stockCostQuery->get();

        $purchaseDiscount = round($purchaseRows->sum('discount_amount'), 2);

        $openingStock = $stockRows->sum(fn ($row) => (float) $row->initial_stock * (float) $row->unit_cost);
        $closingStock = $stockRows->sum(fn ($row) => (float) $row->current_stock * (float) $row->unit_cost);
        $salesTotal = round($salesRows->sum('amount'), 2);
        
        $purchaseTotal = round($purchaseRows->sum('amount'), 2);
        $purchaseGross = $purchaseTotal + $purchaseDiscount;
        $indirectExpenses = round($expenseRows->flatten(1)->sum('amount'), 2);
        $profitLoss = round($salesTotal + $closingStock - $openingStock - $purchaseTotal - $indirectExpenses, 2);

        $leftRows = collect();
        $leftRows->push(['label' => 'Opening Stock', 'inner_amount' => '', 'outer_amount' => round($openingStock, 2), 'type' => 'strong']);
        $leftRows->push(['label' => '', 'inner_amount' => '', 'outer_amount' => '', 'type' => 'empty']);

        $leftRows->push(['label' => 'Purchase Accounts', 'inner_amount' => '', 'outer_amount' => round($purchaseTotal, 2), 'type' => 'strong']);
        $leftRows->push(['label' => 'Purchase', 'inner_amount' => round($purchaseGross, 2), 'outer_amount' => '', 'type' => 'detail']);
        if ($purchaseDiscount > 0) {
            $leftRows->push(['label' => 'Purchase Discount', 'inner_amount' => -abs($purchaseDiscount), 'outer_amount' => '', 'type' => 'detail', 'is_last' => true]);
        } else {
            $last = $leftRows->pop();
            $last['is_last'] = true;
            $leftRows->push($last);
        }
        $leftRows->push(['label' => '', 'inner_amount' => '', 'outer_amount' => '', 'type' => 'empty']);

        $leftRows->push(['label' => 'Indirect Expenses', 'inner_amount' => '', 'outer_amount' => round($indirectExpenses, 2), 'type' => 'strong']);
        $hasExpenseDetails = false;
        foreach ($expenseRows as $label => $group) {
            $amt = $group->sum('amount');
            if ($amt > 0) {
                $leftRows->push(['label' => $label, 'inner_amount' => round($amt, 2), 'outer_amount' => '', 'type' => 'detail']);
                $hasExpenseDetails = true;
            }
        }
        if ($hasExpenseDetails) {
            $last = $leftRows->pop();
            $last['is_last'] = true;
            $leftRows->push($last);
        }

        $rightRows = collect();
        $rightRows->push(['label' => 'Sales Accounts', 'inner_amount' => '', 'outer_amount' => round($salesTotal, 2), 'type' => 'strong']);
        if ($salesTotal > 0) {
            $rightRows->push(['label' => 'Sales', 'inner_amount' => round($salesTotal, 2), 'outer_amount' => '', 'type' => 'detail', 'is_last' => true]);
        }
        $rightRows->push(['label' => '', 'inner_amount' => '', 'outer_amount' => '', 'type' => 'empty']);
        
        $rightRows->push(['label' => 'Closing Stock', 'inner_amount' => '', 'outer_amount' => round($closingStock, 2), 'type' => 'strong']);
        $rightRows->push(['label' => '', 'inner_amount' => '', 'outer_amount' => '', 'type' => 'empty']);

        $settings = DB::table('settings')->where('branch_id', $branchId)->first();

        return [
            'branch' => $branchId,
            'period' => [
                'from' => $from,
                'to' => $to,
                'time_period' => $request->input('time_period', 'all_time'),
            ],
            'summary' => [
                'opening_stock' => round($openingStock, 2),
                'sales_total' => $salesTotal,
                'purchase_total' => $purchaseTotal,
                'indirect_expenses' => $indirectExpenses,
                'closing_stock' => round($closingStock, 2),
                'profit_loss' => $profitLoss,
                'profit' => $profitLoss > 0 ? $profitLoss : 0,
                'loss' => $profitLoss < 0 ? abs($profitLoss) : 0,
            ],
            'left_rows' => $leftRows->values(),
            'right_rows' => $rightRows->values(),
            'company' => [
                'name' => $settings->name ?? '',
                'address' => $settings->address ?? '',
                'phone' => $settings->phone ?? '',
                'email' => $settings->email ?? '',
                'logo' => $settings->logo ?? '',
            ],
        ];
    }
    public function profitLossStatementPdf(Request $request)
    {
        $data = $this->getProfitLossDataArray($request);
        $startDate = $data['period']['from'] ? date('Y-m-d', strtotime($data['period']['from'])) : 'All_time';
        $endDate = $data['period']['to'] ? date('Y-m-d', strtotime($data['period']['to'])) : 'All_time';
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.profit-loss-statement-pdf', ['data' => $data]);
        return $pdf->download('Profit_Loss_Statement_'.$startDate.'_to_'.$endDate.'.pdf');
    }

    public function profitLossStatementExcel(Request $request)
    {
        $data = $this->getProfitLossDataArray($request);
        $startDate = $data['period']['from'] ? date('Y-m-d', strtotime($data['period']['from'])) : 'All_time';
        $endDate = $data['period']['to'] ? date('Y-m-d', strtotime($data['period']['to'])) : 'All_time';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Worksheet');

        $company = $data['company'] ?? [];
        $summary = $data['summary'] ?? [];
        $leftRows = collect($data['left_rows'] ?? []);
        $rightRows = collect($data['right_rows'] ?? []);

        $formatExcelDate = function ($date) {
            return $date ? date('d-M-y', strtotime($date)) : '';
        };

        $fromLabel = $formatExcelDate($data['period']['from'] ?? null);
        $toLabel = $formatExcelDate($data['period']['to'] ?? null);
        $periodLabel = $fromLabel || $toLabel ? 'From ' . $fromLabel . ' to ' . $toLabel : 'All time';
        $asAtLabel = 'as at ' . ($toLabel ?: $fromLabel ?: date('d-M-y'));

        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        $sheet->mergeCells('A3:F3');
        $sheet->mergeCells('A4:F4');
        $sheet->mergeCells('A5:F5');

        $sheet->setCellValue('A1', strtoupper($company['name'] ?? ''));
        $sheet->setCellValue('A2', $company['address'] ?? '');
        $sheet->setCellValue('A3', 'Contact : ' . trim(($company['phone'] ?? '') . ' | ' . ($company['email'] ?? ''), ' |'));
        $sheet->setCellValue('A4', 'Profit & Loss A/c');
        $sheet->setCellValue('A5', $periodLabel);

        $sheet->setCellValue('A7', 'Particulars');
        $sheet->setCellValue('C7', $asAtLabel);
        $sheet->setCellValue('D7', 'Particulars');
        $sheet->setCellValue('F7', $asAtLabel);

        $row = 8;
        $maxRows = max($leftRows->count(), $rightRows->count());

        for ($i = 0; $i < $maxRows; $i++) {
            $left = $leftRows->get($i, []);
            $right = $rightRows->get($i, []);

            $leftLabel = $left['label'] ?? '';
            $rightLabel = $right['label'] ?? '';

            $sheet->setCellValue('A' . $row, $leftLabel);
            $sheet->setCellValue('B' . $row, $left['inner_amount'] ?? '');
            $sheet->setCellValue('C' . $row, $left['outer_amount'] ?? '');
            $sheet->setCellValue('D' . $row, $rightLabel);
            $sheet->setCellValue('E' . $row, $right['inner_amount'] ?? '');
            $sheet->setCellValue('F' . $row, $right['outer_amount'] ?? '');

            if (($left['type'] ?? '') === 'strong') {
                $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
            }
            if (($right['type'] ?? '') === 'strong') {
                $sheet->getStyle('D' . $row . ':F' . $row)->getFont()->setBold(true);
            }
            if (($left['type'] ?? '') === 'detail') {
                $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
            }
            if (($right['type'] ?? '') === 'detail') {
                $sheet->getStyle('D' . $row)->getFont()->setItalic(true);
            }
            if (!empty($left['is_last'])) {
                $sheet->getStyle('B' . $row)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
            if (!empty($right['is_last'])) {
                $sheet->getStyle('E' . $row)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }

            $row++;
        }

        $netProfit = (float) ($summary['profit_loss'] ?? 0);
        if ($netProfit >= 0) {
            $sheet->setCellValue('A' . $row, 'Nett Profit');
            $sheet->setCellValue('C' . $row, $netProfit);
            $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
            $row++;
        } else {
            $sheet->setCellValue('D' . $row, 'Nett Loss');
            $sheet->setCellValue('F' . $row, abs($netProfit));
            $sheet->getStyle('D' . $row . ':F' . $row)->getFont()->setBold(true);
            $row++;
        }

        $leftTotal = (float) ($summary['opening_stock'] ?? 0)
            + (float) ($summary['purchase_total'] ?? 0)
            + (float) ($summary['indirect_expenses'] ?? 0)
            + max($netProfit, 0);
        $rightTotal = (float) ($summary['sales_total'] ?? 0)
            + (float) ($summary['closing_stock'] ?? 0)
            + max(-$netProfit, 0);

        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->setCellValue('C' . $row, $leftTotal);
        $sheet->setCellValue('D' . $row, 'Total');
        $sheet->setCellValue('F' . $row, $rightTotal);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        foreach (['A' => 34, 'B' => 12, 'C' => 15, 'D' => 34, 'E' => 12, 'F' => 15] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->getStyle('A1:A5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A4')->getFont()->setBold(true)->setUnderline(true)->setSize(12);
        $sheet->getStyle('A7:F7')->getFont()->setBold(true);
        $sheet->getStyle('C7:C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('F7:F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('B8:C' . $row)->getNumberFormat()->setFormatCode('#,##0.00;[Red]-#,##0.00');
        $sheet->getStyle('E8:F' . $row)->getNumberFormat()->setFormatCode('#,##0.00;[Red]-#,##0.00');
        $sheet->getStyle('A7:F7')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A7:F7')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->freezePane('A8');

        $fileName = 'Profit_Loss_Statement_' . $startDate . '_to_' . $endDate . '.xlsx';
        $filePath = storage_path('app/temp/' . uniqid('profit_loss_statement_', true) . '.xlsx');

        if (! is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        (new Xlsx($spreadsheet))->save($filePath);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        return response()->download($filePath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
        ])->deleteFileAfterSend(true);
    }

    public function profitLossStatementData(Request $request)
    {
        return response()->json($this->getProfitLossDataArray($request));
    }




     /**
     * Resolve from_date and to_date from request
     */
    private function resolveDateRange(Request $request): array
    {
        $from = $request->input('from_date')
            ? date('Y-m-d 00:00:00', strtotime($request->input('from_date')))
            : null;

        $to = $request->input('to_date')
            ? date('Y-m-d 23:59:59', strtotime($request->input('to_date')))
            : null;

        return [$from, $to];
    }

    public function exportGstr1Excel(Request $request): StreamedResponse
    {
        // --------------------------------------------
        // 0) Reuse your summary logic from gstr1()
        // --------------------------------------------
        // NOTE: we’ll also refetch orders to build line items
        [$from, $to] = $this->resolveDateRange($request);

        $settings = DB::table('settings')->first();
        $activeTaxes = TaxRate::where('status', 'active')->get();

        $cgstRate = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'CGST')->tax_rate ?? 0;
        $sgstRate = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'SGST')->tax_rate ?? 0;
        $igstRate = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'IGST')->tax_rate ?? 0;
        $totalRatePercent = $activeTaxes->sum('tax_rate');

        // Build orders (only completed)
        $salesQuery = Order::with(['orderItems' => function ($q) {
                $q->where('isDeleted', 0);
            }, 'user'])
            ->where('isDeleted', 0)
            ->where('payment_status', 'completed');

        if ($from && $to) {
            $salesQuery->whereBetween('created_at', [$from, $to]);
        }

        $orders = $salesQuery->get();

        // Compute aggregates (same as your gstr1())
        $b2bAgg = ['invoice_count'=>0,'taxable_value'=>0,'cgst'=>0,'sgst'=>0,'igst'=>0];
        $b2cAgg = ['invoice_count'=>0,'taxable_value'=>0,'cgst'=>0,'sgst'=>0,'igst'=>0];

        // And also build line items for sheets
        $b2bRows = []; // for sheet: b2b,sez,de
        $b2csRows = []; // for sheet: b2cs (OE/UR)
        // (Add more arrays if you later want cdnr, cdnur etc.)

        foreach ($orders as $order) {
            $subtotal = $order->orderItems->sum(fn($item) => (float)$item->price * (float)$item->quantity);
            $discountPercent = (float)($order->discount ?? 0);
            $discountAmount  = ($subtotal * $discountPercent) / 100.0;
            $taxableAmount   = max(0, $subtotal - $discountAmount);

            $cgstAmount = $cgstRate > 0 ? ($taxableAmount * $cgstRate) / 100.0 : 0.0;
            $sgstAmount = $sgstRate > 0 ? ($taxableAmount * $sgstRate) / 100.0 : 0.0;
            $igstAmount = $igstRate > 0 ? ($taxableAmount * $igstRate) / 100.0 : 0.0;

            if ($cgstRate === 0 && $sgstRate === 0 && $igstRate === 0 && $totalRatePercent > 0) {
                $totalTax = ($taxableAmount * $totalRatePercent) / 100.0;
                $cgstAmount = $totalTax / 2.0;
                $sgstAmount = $totalTax / 2.0;
            }

            $isB2B = false;
            $gstNum = '';
            $receiver = '';
            if ($order->relationLoaded('user') && $order->user) {
                $gstNum = trim((string)($order->user->gst_number ?? ''));
                $receiver = trim((string)($order->user->name ?? ''));
                $isB2B = $gstNum !== '';
            }

            // Place of Supply (adjust to your DB fields; fallback to 24-Gujarat as in your screenshot)
            $posCode = $settings->gst_state_code ?? '24';
            $posName = $settings->state_name ?? 'Gujarat';
            $placeOfSupply = "{$posCode}-{$posName}";

            $rateUsed = $igstAmount > 0 ? $igstRate : ($cgstRate + $sgstRate);
            $invoiceValue = round($taxableAmount + $cgstAmount + $sgstAmount + $igstAmount, 2);
            $invoiceDate  = optional($order->created_at)->format('d-M-Y') ?? '';

            if ($isB2B) {
                // --------- b2b,sez,de row format (exact headers below) ----------
                $b2bRows[] = [
                    $gstNum,                  // GSTIN/UIN of Recipient
                    $receiver ?: 'N/A',       // Receiver Name
                    (string)$order->id,       // Invoice Number
                    $invoiceDate,             // Invoice date
                    number_format($invoiceValue, 2, '.', ''), // Invoice Value
                    $placeOfSupply,           // Place Of Supply
                    'N',                      // Reverse Charge (Y/N) – adjust if you handle RC
                    '',                       // Applicable % of Tax Rate (usually blank)
                    'Regular B2B',            // Invoice Type
                    '',                       // E-Commerce GSTIN
                    (float)$rateUsed,         // Rate
                    round($taxableAmount, 2), // Taxable Value
                    0.00,                     // Cess Amount
                ];

                $b2bAgg['invoice_count'] += 1;
                $b2bAgg['taxable_value'] += $taxableAmount;
                $b2bAgg['cgst'] += $cgstAmount;
                $b2bAgg['sgst'] += $sgstAmount;
                $b2bAgg['igst'] += $igstAmount;
            } else {
                // --------- b2cs row format (exact headers below) ----------
                // Type: OE (if through e-com op) or UR (unregistered). Using OE just as example; change as needed.
                $b2csRows[] = [
                    'OE',                      // Type
                    $placeOfSupply,            // Place Of Supply
                    '',                        // Applicable % of Tax Rate
                    (float)$rateUsed,          // Rate
                    round($taxableAmount, 2),  // Taxable Value
                    0.00,                      // Cess Amount
                    '',                        // E-Commerce GSTIN
                ];

                $b2cAgg['invoice_count'] += 1;
                $b2cAgg['taxable_value'] += $taxableAmount;
                $b2cAgg['cgst'] += $cgstAmount;
                $b2cAgg['sgst'] += $sgstAmount;
                $b2cAgg['igst'] += $igstAmount;
            }
        }

        $summary = [
            'total_invoices' => $b2bAgg['invoice_count'] + $b2cAgg['invoice_count'],
            'taxable_value'  => round($b2bAgg['taxable_value'] + $b2cAgg['taxable_value'], 2),
            'cgst'           => round($b2bAgg['cgst'] + $b2cAgg['cgst'], 2),
            'sgst'           => round($b2bAgg['sgst'] + $b2cAgg['sgst'], 2),
            'igst'           => round($b2bAgg['igst'] + $b2cAgg['igst'], 2),
        ];

        // Round aggregates for neatness
        $b2bAgg = array_map(fn($v) => is_numeric($v) ? round($v, 2) : $v, $b2bAgg);
        $b2cAgg = array_map(fn($v) => is_numeric($v) ? round($v, 2) : $v, $b2cAgg);

        // --------------------------------------------
        // 1) Load the TEMPLATE (keeps exact formatting)
        // --------------------------------------------
        $templatePath = storage_path('app/templates/gstr1_template.xlsx');
        if (file_exists($templatePath)) {
            $spreadsheet = IOFactory::load($templatePath);
        } else {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->removeSheetByIndex(0);

            // Create minimal sheets when template is missing
            $sheetB2B = new Worksheet($spreadsheet, 'b2b,sez,de');
            $spreadsheet->addSheet($sheetB2B, 0);
            $sheetB2B->fromArray([
                ['GSTIN/UIN of Recipient','Receiver Name','Invoice Number','Invoice date','Invoice Value','Place Of Supply','Reverse Charge','Applicable % of Tax Rate','Invoice Type','E-Commerce GSTIN','Rate','Taxable Value','Cess Amount']
            ], null, 'A5');

            $sheetB2CS = new Worksheet($spreadsheet, 'b2cs');
            $spreadsheet->addSheet($sheetB2CS, 1);
            $sheetB2CS->fromArray([
                ['Type','Place Of Supply','Applicable % of Tax Rate','Rate','Taxable Value','Cess Amount','E-Commerce GSTIN']
            ], null, 'A5');
        }

        // --------------------------------------------
        // 2) Fill B2B sheet: "b2b,sez,de"
        // --------------------------------------------
        $sheetB2B = $spreadsheet->getSheetByName('b2b,sez,de');
        if ($sheetB2B) {
            // A quick header reminder (matches your screenshot):
            // A: GSTIN/UIN of Recipient
            // B: Receiver Name
            // C: Invoice Number
            // D: Invoice date
            // E: Invoice Value
            // F: Place Of Supply
            // G: Reverse Charge
            // H: Applicable % of Tax Rate
            // I: Invoice Type
            // J: E-Commerce GSTIN
            // K: Rate
            // L: Taxable Value
            // M: Cess Amount

            $startRow = 6; // adjust if your template starts later
            $r = $startRow;
            foreach ($b2bRows as $row) {
                $sheetB2B->fromArray($row, null, "A{$r}");
                $r++;
            }

            // (Optional) If your template has summary cells on top, set them here by address
            // Example (adjust cell addresses as per your template):
            // $sheetB2B->setCellValue('B2', count(array_unique(array_column($b2bRows, 0)))); // No. of Recipients
            // $sheetB2B->setCellValue('D2', $b2bAgg['invoice_count']);                     // No. of Invoices
            // $sheetB2B->setCellValue('H2', array_sum(array_map(fn($x)=> (float)$x[4], $b2bRows))); // Total Invoice Value
            // $sheetB2B->setCellValue('J2', $b2bAgg['taxable_value']);                    // Total Taxable Value
        }

        // --------------------------------------------
        // 3) Fill B2CS sheet: "b2cs"
        // --------------------------------------------
        $sheetB2CS = $spreadsheet->getSheetByName('b2cs');
        if ($sheetB2CS) {
            // Headers you showed:
            // A: Type
            // B: Place Of Supply
            // C: Applicable % of Tax Rate
            // D: Rate
            // E: Taxable Value
            // F: Cess Amount
            // G: E-Commerce GSTIN

            $startRow = 6; // adjust to your template
            $r = $startRow;
            foreach ($b2csRows as $row) {
                $sheetB2CS->fromArray($row, null, "A{$r}");
                $r++;
            }

            // (Optional summary cells on the top band—map them if you want)
            // $sheetB2CS->setCellValue('E2', $b2cAgg['taxable_value']); // Total Taxable Value
        }

        // --------------------------------------------
        // 4) Other sheets
        // --------------------------------------------
        // All other tabs remain in the file with their exact formatting (because we loaded the template).
        // If you later gather rows for b2ba, b2cl, b2cla, cdnr, cdnra, cdnur, cdnura, exp, etc.,
        // just replicate the fromArray() loop with the correct start row & column mapping.

        // --------------------------------------------
        // 5) Download the file
        // --------------------------------------------
        $filename = 'GSTR1_'.now()->format('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            IOFactory::createWriter($spreadsheet, 'Xlsx')->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // Inside your ReportController

    

}
