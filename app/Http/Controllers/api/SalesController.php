<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\SalesOrderCreatedMail;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentStore;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\Sales_Labour_Items;
use App\Models\SalesReturn;
use App\Models\Setting;
use App\Models\TaxRate;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\WhatsAppMessageTemplate;
use App\Services\MailConfigService;
use App\Services\WhatsAppService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

use App\Models\Unit;

class SalesController extends Controller
{
    private function resolveFinancialYearRange(?string $financialYear): ?array
    {
        if (empty($financialYear)) {
            return null;
        }

        if (!preg_match('/^(\d{4})-(\d{4})$/', trim($financialYear), $matches)) {
            return null;
        }

        $startYear = (int) $matches[1];
        $endYear = (int) $matches[2];

        if ($endYear !== $startYear + 1) {
            return null;
        }

        $from = Carbon::create($startYear, 4, 1)->startOfDay()->toDateTimeString();
        $to = Carbon::create($endYear, 3, 31)->endOfDay()->toDateTimeString();

        return [$from, $to];
    }

    private function applyFinancialYearFilter($query, ?string $financialYear, string $column = 'created_at'): void
    {
        $range = $this->resolveFinancialYearRange($financialYear);
        if (!$range) {
            return;
        }

        $query->whereBetween($column, $range);
    }

    private function resolveSalesSettingsBranchId($user, $selectedSubAdminID): int
    {
        if ($selectedSubAdminID === 'null' || $selectedSubAdminID === 'undefined' || empty($selectedSubAdminID)) {
            $selectedSubAdminID = null;
        }

        if ($user->role === 'staff' && !empty($user->branch_id)) {
            return (int) $user->branch_id;
        }

        if ($user->role === 'admin' && !empty($selectedSubAdminID)) {
            return (int) $selectedSubAdminID;
        }

        return (int) $user->id;
    }

    private function resolveFinancialYearWindowFromDate(Carbon $referenceDate): array
    {
        $reference = $referenceDate->copy()->timezone('Asia/Kolkata');
        $startYear = $reference->month >= 4 ? $reference->year : ($reference->year - 1);

        $from = Carbon::create($startYear, 4, 1, 0, 0, 0, 'Asia/Kolkata');
        $to = Carbon::create($startYear + 1, 3, 31, 23, 59, 59, 'Asia/Kolkata');

        return [$from, $to];
    }

    private function isFinancialYearOrderNumberingEnabled(int $branchId): bool
    {
        $setting = Setting::where('branch_id', $branchId)->first();
        return (bool) ($setting->financial_year ?? false);
    }

    private function getNextFinancialYearOrderSequence(
        int $branchId,
        bool $isQuotation,
        Carbon $referenceDate
    ): int {
        [$from, $to] = $this->resolveFinancialYearWindowFromDate($referenceDate);

        $query = Order::query()
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$from, $to]);

        if ($isQuotation) {
            $maxSequence = (int) ($query
                ->where('quotation_status', 'quotation')
                ->whereRaw("order_number REGEXP '^Q-[0-9]+$'")
                ->selectRaw("MAX(CAST(SUBSTRING(order_number, 3) AS UNSIGNED)) as max_sequence")
                ->value('max_sequence') ?? 0);

            return $maxSequence + 1;
        }

        $maxSequence = (int) ($query
            ->where('quotation_status', 'sales')
            ->whereRaw("order_number REGEXP '^[0-9]+$' AND CHAR_LENGTH(order_number) <= 9")
            ->selectRaw("MAX(CAST(order_number AS UNSIGNED)) as max_sequence")
            ->value('max_sequence') ?? 0);

        return $maxSequence + 1;
    }

    private function generateFinancialYearOrderNumber(
        int $branchId,
        bool $isQuotation,
        Carbon $referenceDate
    ): string {
        $nextSequence = $this->getNextFinancialYearOrderSequence($branchId, $isQuotation, $referenceDate);
        return $isQuotation ? ('Q-' . $nextSequence) : (string) $nextSequence;
    }

    public function getHistory($order_id)
    {
        $history = PaymentStore::where('order_id', $order_id)
            ->where('isDeleted', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        $order = Order::findOrFail($order_id);

        $totalPaid = $history->sum('payment_amount');

        // Calculate Return Amount
        $returnAmount = \App\Models\SalesReturn::where('order_id', $order_id)->sum('total_amount');

        // ✅ Dynamic calculation (NO column)
        $extraPaid = max(0, $totalPaid - ($order->total_amount - $returnAmount));
        $remaining = max(0, ($order->total_amount - $returnAmount) - $totalPaid);

        return response()->json([
            'status' => 'success',
            'data' => $history,
            'summary' => [
                'order_total' => $order->total_amount,
                'total_paid' => $totalPaid,
                'return_amount' => $returnAmount,
                'extra_paid' => $extraPaid,
                'remaining' => $remaining,
            ],
        ]);
    }

    public function getPayment($id)
    {
        $payment = PaymentStore::where('id', $id)
            ->where('isDeleted', 0)
            ->firstOrFail();

        $order = Order::where('id', $payment->order_id)->first();
        $returnAmount = (float) SalesReturn::where('order_id', $payment->order_id)->sum('total_amount');
        $totalPaid = (float) PaymentStore::where('order_id', $payment->order_id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');
        $orderTotal = (float) ($order->total_amount ?? 0);
        $netTotal = max(0, $orderTotal - $returnAmount);
        $remaining = max(0, $netTotal - $totalPaid);

        return response()->json([
            'status' => true,
            'data' => array_merge($payment->toArray(), [
                'order_total' => $orderTotal,
                'remaining_amount' => $remaining,
                'return_amount' => $returnAmount,
            ]),
        ]);
    }

    public function updatePayment(Request $request, $id)
    {
        $validated = $request->validate([
            'payment_amount' => 'required|numeric|min:0',
            'payment_date' => 'required',
            'payment_method' => 'required|string|max:50',
            'payment_type' => 'nullable|string|max:50',
            'bank_id' => 'nullable|integer',
            'remarks' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($validated, $request, $id) {
            $payment = PaymentStore::where('id', $id)
                ->where('isDeleted', 0)
                ->lockForUpdate()
                ->firstOrFail();

            $order = Order::where('id', $payment->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            $paymentDate = $this->normalizePaymentDate($validated['payment_date']);
            $paymentMethod = strtolower(trim($validated['payment_method']));
            $paymentType = strtolower(trim($validated['payment_type'] ?? $payment->payment_type ?? 'fully'));
            $paymentAmount = round((float) $validated['payment_amount'], 2);

            $payment->payment_amount = $paymentAmount;
            $payment->payment_date = $paymentDate->toDateString();
            $payment->payment_method = $paymentMethod;
            $payment->payment_type = $paymentType;
            $payment->bank_id = $validated['bank_id'] ?? null;
            $payment->remarks = $validated['remarks'] ?? null;
            if ($paymentMethod === 'cash') {
                $payment->cash_amount = $paymentAmount;
                $payment->upi_amount = 0;
            } elseif ($paymentMethod === 'online') {
                $payment->cash_amount = 0;
                $payment->upi_amount = $paymentAmount;
            } else {
                $payment->cash_amount = 0;
                $payment->upi_amount = 0;
            }
            $payment->emi_month = $paymentType === 'emi' ? ($request->emi_month ?? $payment->emi_month ?? 1) : $payment->emi_month;
            $payment->save();

            $summary = $this->recalculateSalesOrderPaymentSummary($order);

            return response()->json([
                'status' => true,
                'message' => 'Payment updated successfully.',
                'summary' => $summary,
                'data' => $payment->fresh(),
            ]);
        });
    }

    public function deletePayment(Request $request, $id)
    {
        return DB::transaction(function () use ($id) {
            $payment = PaymentStore::where('id', $id)
                ->where('isDeleted', 0)
                ->lockForUpdate()
                ->firstOrFail();

            $order = Order::where('id', $payment->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            $payment->isDeleted = 1;
            $payment->save();

            $summary = $this->recalculateSalesOrderPaymentSummary($order);

            return response()->json([
                'status' => true,
                'message' => 'Payment deleted successfully.',
                'summary' => $summary,
            ]);
        });
    }

    private function normalizePaymentDate(string $value): Carbon
    {
        $value = trim($value);

        foreach (['Y-m-d', 'd-m-Y', 'Y-m-d H:i:s', Carbon::ATOM] as $format) {
            try {
                return Carbon::createFromFormat($format, $value, 'Asia/Kolkata');
            } catch (\Throwable $e) {
                // Try next format.
            }
        }

        return Carbon::parse($value, 'Asia/Kolkata');
    }

    private function recalculateSalesOrderPaymentSummary(Order $order): array
    {
        $returnAmount = (float) SalesReturn::where('order_id', $order->id)->sum('total_amount');
        $totalPaid = (float) PaymentStore::where('order_id', $order->id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');

        $netTotal = max(0, (float) $order->total_amount - $returnAmount);
        $remaining = max(0, $netTotal - $totalPaid);

        if ($remaining <= 0) {
            $paymentStatus = 'completed';
        } elseif ($totalPaid > 0) {
            $paymentStatus = 'partially';
        } else {
            $paymentStatus = 'pending';
        }

        $latestPaymentMethod = PaymentStore::where('order_id', $order->id)
            ->where('isDeleted', 0)
            ->orderByDesc('created_at')
            ->value('payment_method');

        $order->update([
            'remaining_amount' => $remaining,
            'payment_status' => $paymentStatus,
            'payment_method' => $latestPaymentMethod ? ucfirst(strtolower($latestPaymentMethod)) : $order->payment_method,
        ]);

        return [
            'order_total' => (float) $order->total_amount,
            'total_paid' => $totalPaid,
            'return_amount' => $returnAmount,
            'remaining' => $remaining,
            'extra_paid' => max(0, $totalPaid - $netTotal),
        ];
    }

    public function makePaymentSubmit(Request $request)
    {
        // dd($request->all());
        // $user_id = Auth::id();
        $user_id = Auth::guard('api')->user()->id;

        $request->validate([

            'order_id' => 'nullable|integer',
            'payment_amount' => 'nullable|numeric',
            'payment_date' => 'nullable|date',
            'payment_type' => 'nullable|string',
            'emi_month' => 'nullable|integer',
            'pending_date' => 'nullable|date',
            'new_emi_value' => 'nullable',
            'emi_paid_value' => 'nullable|numeric',
            'bank_id' => 'nullable|integer',
            'remarks' => 'nullable|string|max:500',
        ]);

        if ($request->filled('order_id')) {
            // Resolve payment date: use submitted date or fall back to now (IST)
            $paymentDate = $request->filled('payment_date')
                ? \Carbon\Carbon::createFromFormat('d-m-Y', $request->payment_date, 'Asia/Kolkata')->startOfDay()
                : \Carbon\Carbon::now('Asia/Kolkata');

            // Determine payment amount
            $paymentAmount = $request->emi_total_new ?? $request->emi_total ?? $request->amount ?? $request->upi_online_amount ?? 0;

            if ($request->filled('cash_amount') && $request->filled('online_amount')) {
                $paymentAmount = (float) $request->cash_amount + (float) $request->online_amount;
            } elseif ($request->filled('fully_cash_amount') && $request->filled('full_online_amount')) {
                $paymentAmount = (float) $request->fully_cash_amount + (float) $request->full_online_amount;
            } elseif ($request->cashAmount) {
                $paymentAmount = $request->cashAmount;
            } elseif ($request->upi_online_amount) {
                $paymentAmount = $request->upi_online_amount;
            } elseif ($request->emi_monthly) {
                $paymentAmount = $request->emi_monthly;
            }

            // Determine payment type
            if (
                in_array($request->paid_type, ['cash_partially']) ||
                in_array($request->online_type, ['online_partially']) ||
                in_array($request->cash_online_type, ['cash_online_partially'])
            ) {
                $type = 'partially';
            } elseif (
                in_array($request->paid_type, ['cash_fully']) ||
                in_array($request->online_type, ['online_fully']) ||
                in_array($request->cash_online_type, ['cash_online_fully']) ||
                $request->payment_type === 'fully'

            ) {
                $type = 'fully';
            } elseif (
                in_array($request->emi_type, ['emi'])
            ) {
                $type = 'emi';
            } else {
                $type = 'fully';
            }

            // Fetch Invoice if exists
            $invoice = $request->order_id ? Order::find($request->order_id) : null;

            // Calculate remaining amount
            $currentRemaining = $invoice->remaining_amount ?? 0;
            $newRemaining = max(0, $currentRemaining - $paymentAmount);
            // dd($currentRemaining,$newRemaining);

            // $payment = PaymentStore::create([
            //     'user_id'           => $user_id,
            //     'order_id'          => $request->order_id,
            //     'custom_invoice_id' => 0,
            //     'payment_amount'    => $paymentAmount,
            //     'remaining_amount'  => $newRemaining,
            //     'payment_method'    => $request->payment_method ?? $request->payment_type ?? '',
            //     'payment_date'      => \Carbon\Carbon::now(),
            //     'payment_type'      => $type,
            //     'cash_amount'       => $request->cash_amount,
            //     'upi_amount'        => $request->online_amount,
            //     'emi_month'         => $request->emi_month ?? 1,
            //     'isDeleted'         => 0,
            // ]);
            // Handle cash_online_partially separately
            // ✅ Handle cash_online cases separately
            if ($request->cash_online_type === 'cash_online_partially' || $request->cash_online_type === 'cash_online_fully') {
                $payments = [];

                // 1️⃣ Handle Cash entry (partially or fully)
                $cashValue = $request->cash_online_type === 'cash_online_partially'
                    ? $request->cash_amount
                    : $request->fully_cash_amount;

                if (!empty($cashValue) && $cashValue > 0) {
                    $cashPayment = PaymentStore::create([
                        'user_id' => $user_id,
                        'order_id' => $request->order_id,
                        'custom_invoice_id' => null,
                        'payment_amount' => $cashValue,
                        'remaining_amount' => $newRemaining,
                        'payment_method' => 'cash',
                        'payment_date' => $paymentDate,
                        'payment_type' => ($request->cash_online_type === 'cash_online_partially') ? 'partially' : 'fully',
                        'cash_amount' => $cashValue,
                        'upi_amount' => 0,
                        'emi_month' => $request->emi_month ?? 1,
                        'bank_id' => $request->bank_id,
                        'remarks' => $request->remarks,
                        'status' => 'credit',
                        'isDeleted' => 0,
                    ]);
                    $payments[] = $cashPayment;
                }

                // 2️⃣ Handle Online entry (partially or fully)
                $onlineValue = $request->cash_online_type === 'cash_online_partially'
                    ? $request->online_amount
                    : $request->full_online_amount;

                if (!empty($onlineValue) && $onlineValue > 0) {
                    $onlinePayment = PaymentStore::create([
                        'user_id' => $user_id,
                        'order_id' => $request->order_id,
                        'custom_invoice_id' => null,
                        'payment_amount' => $onlineValue,
                        'remaining_amount' => $newRemaining,
                        'payment_method' => 'online',
                        'payment_date' => $paymentDate,
                        'payment_type' => ($request->cash_online_type === 'cash_online_partially') ? 'partially' : 'fully',
                        'cash_amount' => 0,
                        'upi_amount' => $onlineValue,
                        'emi_month' => $request->emi_month ?? 1,
                        'bank_id' => $request->bank_id,
                        'remarks' => $request->remarks,
                        'status' => 'credit',
                        'isDeleted' => 0,
                    ]);
                    $payments[] = $onlinePayment;
                }

                $payment = $payments; // return both records as array
            } else {
                // 🟢 Default single payment record for all other cases
                $payment = PaymentStore::create([
                    'user_id' => $user_id,
                    'order_id' => $request->order_id,
                    'custom_invoice_id' => null,
                    'payment_amount' => $paymentAmount,
                    'remaining_amount' => $newRemaining,
                    'payment_method' => $request->payment_method ?? $request->payment_type ?? '',
                    'payment_date' => $paymentDate,
                    'payment_type' => $type,
                    'cash_amount' => $request->cash_amount,
                    'upi_amount' => $request->online_amount,
                    'emi_month' => $request->emi_month ?? 1,
                    'bank_id' => $request->bank_id,
                    'remarks' => $request->remarks,
                    'status' => 'credit',
                    'isDeleted' => 0,
                ]);
            }

            // Update invoice
            if ($invoice) {
                // $updateData = ['remaining_amount' => $newRemaining];

                // // ✅ Auto set payment_status when fully paid
                // if ($newRemaining <= 0) {
                //     $updateData['payment_status'] = 'completed';
                // } else {
                //     $updateData['payment_status'] = 'Pending';
                // }
                $updateData = ['remaining_amount' => $newRemaining];
                $totalAmount = $invoice->total_amount ?? 0;
                // ✅ Auto set payment_status when fully paid
                if ($newRemaining <= 0) {
                    // Fully paid
                    $updateData['payment_status'] = 'completed';
                } elseif ($newRemaining < $totalAmount) {
                    // Some amount paid, still balance remaining
                    $updateData['payment_status'] = 'partially';
                } else {
                    // No payment done
                    $updateData['payment_status'] = 'pending';
                }
                // If new EMI is being set
                if ($request->filled('new_emi_value') && $request->payment_method == 'emi' || $request->filled('emi_paid_value')) {
                    //  dd($request->all());
                    $updateData['payment_method'] = 'EMI';
                    $updateData['emi_duration'] = $request->emi_month_new ?? $request->emi_month;
                    $updateData['emi_months'] = $request->emi_total_new ?? 1;
                } else {
                    // dd($request->filled('emi_paid_value'));
                    // Otherwise, keep the actual payment method (cash, online, cash_online)
                    $method = $request->payment_method ?? $request->payment_type ?? $updateData['payment_method'] ?? 'Cash';

                    // Normalize to proper values
                    if (strtolower($method) === 'online' || strtolower($method) === 'upi') {
                        $method = 'Online';
                    } elseif (strtolower($method) === 'cash') {
                        $method = 'Cash';
                    } elseif (strtolower($method) === 'emi') {
                        $method = 'EMI';
                    } else {
                        $method = 'Cash';
                    }

                    $updateData['payment_method'] = $method;
                }

                if ($request->emi_duration) {
                    $updateData['emi_duration'] = $request->emi_duration;
                }

                // Update next pending date if provided
                if ($request->pending_date) {
                    $updateData['next_pending_date'] = $request->pending_date;
                }

                $invoice->update($updateData);
            }
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Payment submitted successfully.',
            'data' => $payment,
        ]);
    }

    // In your controller
    public function getProductsByCategory($categoryId)
    {
        $settings = DB::table('settings')->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        $products = Product::with('category', 'unit')
            ->where('category_id', $categoryId)
            ->where('isDeleted', 0)
            ->where('status', 'active') // ✅ Only active products
            ->get();

        // dd($products);
        return response()->json([
            'status' => true,
            'currency_symbol' => $currencySymbol,
            'currency_position' => $currencyPosition,
            'data' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $product->quantity,
                    'image' => $product->images,
                    'image_url' => $product->image_url,
                    'category_name' => $product->category->name,
                    'categoryId' => $product->category_id,
                    'category_name' => $product->category ? $product->category->name : null,
                    'gst_option' => $product->gst_option,
                    'product_gst' => $product->product_gst,
                    'unit' => $product->unit ? $product->unit->unit_name : null,
                ];
            }),
        ]);
    }

    private function formatLocalBarcodeProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'SKU' => $product->SKU,
            'hsn_code' => $product->hsn_code,
            'barcode' => $product->barcode,
            'description' => $product->description,
            'price' => $product->price,
            'quantity' => $product->quantity,
            'image' => $product->images,
            'category_id' => $product->category_id,
            'brand_id' => $product->brand_id,
            'unit_id' => $product->unit_id,
            'status' => $product->status,
            'availablility' => $product->availablility,
            'gst_option' => $product->gst_option,
            'product_gst' => $product->product_gst,
            'product_gst_ids' => collect(json_decode($product->product_gst, true) ?: [])
                ->pluck('tax_id')
                ->filter()
                ->values(),
            'source' => 'local',
            'source_label' => 'ERP product database',
        ];
    }

    private function fetchOpenFoodFactsProduct(string $barcode): ?array
    {
        $response = Http::timeout(8)
            ->withHeaders([
                'User-Agent' => 'erp-main-demo/1.0',
            ])
            ->get("https://world.openfoodfacts.net/api/v2/product/{$barcode}", [
                'fields' => 'code,product_name,brands,categories,image_url,quantity,generic_name,packaging_text,product_quantity',
            ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        $product = $data['product'] ?? null;

        if (empty($product) || empty($product['product_name'])) {
            return null;
        }

        $descriptionParts = array_filter([
            $product['generic_name'] ?? null,
            $product['packaging_text'] ?? null,
            $product['categories'] ?? null,
        ]);

        return [
            'id' => null,
            'name' => $product['product_name'] ?? null,
            'SKU' => $product['code'] ?? $barcode,
            'hsn_code' => null,
            'barcode' => $product['code'] ?? $barcode,
            'description' => ! empty($descriptionParts) ? implode(' | ', $descriptionParts) : null,
            'price' => null,
            'quantity' => $product['product_quantity'] ?? null,
            'image' => $product['image_url'] ?? null,
            'category_id' => null,
            'brand_id' => null,
            'brand_name' => $product['brands'] ?? null,
            'category_name' => $product['categories'] ?? null,
            'unit_id' => null,
            'status' => 'active',
            'availablility' => 'in_stock',
            'gst_option' => null,
            'product_gst' => null,
            'product_gst_ids' => [],
            'source' => 'external',
            'source_label' => 'Open Food Facts',
        ];
    }

    private function fetchUpcItemDbProduct(string $barcode): ?array
    {
        $response = Http::timeout(8)
            ->get('https://api.upcitemdb.com/prod/trial/lookup', [
                'upc' => $barcode,
            ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        $item = $data['items'][0] ?? null;

        if (empty($item) || empty($item['title'])) {
            return null;
        }

        $descriptionParts = array_filter([
            $item['description'] ?? null,
            $item['category'] ?? null,
        ]);

        return [
            'id' => null,
            'name' => $item['title'] ?? null,
            'SKU' => $item['upc'] ?? $barcode,
            'hsn_code' => null,
            'barcode' => $item['upc'] ?? $barcode,
            'description' => ! empty($descriptionParts) ? implode(' | ', $descriptionParts) : null,
            'price' => null,
            'quantity' => null,
            'image' => $item['images'][0] ?? null,
            'category_id' => null,
            'brand_id' => null,
            'brand_name' => $item['brand'] ?? null,
            'category_name' => $item['category'] ?? null,
            'unit_id' => null,
            'status' => 'active',
            'availablility' => 'in_stock',
            'gst_option' => null,
            'product_gst' => null,
            'product_gst_ids' => [],
            'source' => 'external',
            'source_label' => 'UPCitemdb',
        ];
    }

    private function fetchExternalBarcodeProduct(string $barcode): ?array
    {
        foreach (
            [
                fn() => $this->fetchOpenFoodFactsProduct($barcode),
                fn() => $this->fetchUpcItemDbProduct($barcode),
            ] as $resolver
        ) {
            try {
                $product = $resolver();
                if (! empty($product)) {
                    return $product;
                }
            } catch (\Throwable $exception) {
                Log::warning('External barcode lookup failed', [
                    'barcode' => $barcode,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return null;
    }

    public function getByBarcode($barcode)
    {
        try {
            $product = Product::with('category', 'unit', 'brand')
                ->where('barcode', $barcode)
                ->first();

            if ($product) {
                return response()->json([
                    'status' => true,
                    'product' => $this->formatLocalBarcodeProduct($product),
                ]);
            }

            $externalProduct = $this->fetchExternalBarcodeProduct($barcode);

            if ($externalProduct) {
                return response()->json([
                    'status' => true,
                    'product' => $externalProduct,
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }



    // public function order_sale(Request $request)
    // {
    //     // ✅ Detect quotation
    //     $isQuotation = $request->quotation_status === 'quotation';

    //     // ✅ Validation (payment required ONLY for sales)
    //     $request->validate([
    //         'customer_id' => 'nullable|string|max:255',
    //         'customer_phone' => 'nullable|string|max:255',
    //         'payment_method' => $isQuotation ? 'nullable|string' : 'required|string',
    //         'bank_id'        => 'nullable|integer',
    //         'subtotal'       => 'required|numeric|min:0',
    //         'discount'       => 'nullable|numeric|min:0|max:100',
    //         'remarks'        => 'nullable|string|max:500',
    //         'tax'            => 'nullable|array',
    //         'items'          => 'required|array|min:1',
    //         'labour_items'   => 'nullable|array',
    //         'shipping' => 'nullable|numeric|min:0',
    //         'items.*.product_id' => 'required|integer|exists:products,id',
    //         'items.*.quantity' => 'required|integer|min:1',
    //         'items.*.price' => 'required|numeric|min:0',
    //         'items.*.total' => 'required|numeric|min:0',
    //         'total' => 'required|numeric|min n:0',
    //     ]);





    //     DB::beginTransaction();

    //     try {

    //         $userData = Auth::guard('api')->user();
    //         $userRole = $userData->role;

    //         // Branch logic
    //         if ($userRole === 'staff') {
    //             $branchIdToUse = $userData->branch_id;
    //         } elseif ($userRole === 'admin' && ! empty($request->selectedSubAdminId)) {
    //             $branchIdToUse = $request->selectedSubAdminId;
    //         } else {
    //             $branchIdToUse = $userData->id;
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | STOCK CHECK (ONLY SALES)
    //     |--------------------------------------------------------------------------
    //     */
    //         $productIds = array_column($request->items, 'product_id');
    //         $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

    //         if (!$isQuotation) {
    //             foreach ($request->items as $item) {
    //                 $product = $products->get($item['product_id']);

    //                 if (!$product || $product->quantity < $item['quantity']) {
    //                     DB::rollBack();
    //                     return response()->json([
    //                         'status' => false,
    //                         'message' => "Insufficient stock for product: " .
    //                             ($product->name ?? "ID: {$item['product_id']}"),
    //                     ], 400);
    //                 }
    //             }
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | CUSTOMER
    //     |--------------------------------------------------------------------------
    //     */
    //         if (!is_numeric($request->customer_id) && !empty($request->customer_id)) {

    //             $vendor = User::create([
    //                 'name' => $request->customer_id,
    //                 'phone' => $request->customer_phone,
    //                 'role' => 'customer',
    //                 'status' => 1,
    //                 'branch_id' => $branchIdToUse,
    //                 'created_by' => $userData->id,
    //             ]);

    //             UserDetail::create(['user_id' => $vendor->id]);

    //             $customer_id = $vendor->id;
    //         } elseif (is_numeric($request->customer_id)) {

    //             $customer_id = $request->customer_id;
    //         } else {

    //             $customer_id = User::where('role', 'customer')
    //                 ->where('name', 'Default Customer')
    //                 ->value('id');
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | PAYMENT STATUS
    //     |--------------------------------------------------------------------------
    //     */
    //         $paymentMethod = $isQuotation
    //             ? 'pending'
    //             : $request->payment_method;

    //         $payment_status =
    //             (!$isQuotation && $paymentMethod != 'pending')
    //             ? 'completed'
    //             : 'pending';

    //         $remain =
    //             $payment_status === 'completed'
    //             ? 0
    //             : $request->total;

    //         // order_number is NOT NULL, so generate it before creating the order.
    //         $nextOrderId = (Order::max('id') ?? 0) + 1;
    //         $orderNumber = $isQuotation
    //             ? 'Q-' . $nextOrderId
    //             : now()->format('Ymd') . str_pad($nextOrderId, 5, '0', STR_PAD_LEFT);

    //         /*
    //     |--------------------------------------------------------------------------
    //     | CREATE ORDER
    //     |--------------------------------------------------------------------------
    //     */
    //         $order = Order::create([
    //             'order_number' => $orderNumber,
    //             'user_id' => $customer_id,
    //             'payment_method' => $paymentMethod,
    //             'discount' => $request->discount ?? 0,
    //             'tax_id' => !empty($request->tax) ? json_encode($request->tax) : null,
    //             'gst_option' => $request->gst_option === 'with'
    //                 ? 'with_gst'
    //                 : 'without_gst',
    //             'total_amount' => $request->total,
    //             'remaining_amount' => $remain,
    //             'payment_status' => $payment_status,
    //             'quotation_status' => $isQuotation ? 'quotation' : 'sales',
    //                'shipping'         => $request->shipping ?? 0,
    //             'remarks'          => $request->remarks,
    //             'branch_id'        => $branchIdToUse,
    //             'created_by'       => $userData->id,
    //         ]);

    //         /*
    //     |--------------------------------------------------------------------------
    //     | ITEMS + INVENTORY
    //     |--------------------------------------------------------------------------
    //     */
    //         $orderItemsData = [];
    //         $inventoryData = [];
    //         $productUpdates = [];
    //         $now = now();

    //         $lastInventories = ProductInventory::whereIn('product_id', $productIds)
    //             ->latest()
    //             ->get()
    //             ->groupBy('product_id')
    //             ->map(fn($g) => $g->first());

    //         foreach ($request->items as $item) {

    //             $product = $products->get($item['product_id']);
    //             $quantity = $item['quantity'];
    //             $price = $item['price'];

    //             $discountAmount = $item['discount_amount'] ?? 0;
    //             $finalAmount = ($price * $quantity) - $discountAmount;

    //             $newQuantity = $product->quantity - $quantity;

    //             // ✅ Reduce stock ONLY for SALES
    //             if (!$isQuotation) {
    //                 $productUpdates[$product->id] = ['quantity' => $newQuantity];
    //             }

    //             $orderItemsData[] = [
    //                 'order_id' => $order->id,
    //                 'user_id' => $customer_id,
    //                 'category_id' => $item['categoryId'] ?? null,
    //                 'product_id' => $item['product_id'],
    //                 'quantity'   => $quantity,
    //                 'price'      => $price,
    //                 'discount_percentage' => $item['discount_percentage'] ?? 0,
    //                 'discount_amount' => $discountAmount,
    //                 'product_gst_details' => isset($item['product_gst_details']) ? $item['product_gst_details'] : null,
    //                 'product_gst_total' => $item['product_gst_total'] ?? 0,
    //                 'total_amount' => $finalAmount,
    //                 'branch_id' => $branchIdToUse,
    //                 'created_by' => $userData->id,
    //                 'created_at' => $now,
    //                 'updated_at' => $now,
    //             ];

    //             // ✅ Inventory ONLY for SALES
    //             if (!$isQuotation) {
    //                 $lastInventory = $lastInventories->get($product->id);

    //                 $inventoryData[] = [
    //                     'product_id' => $product->id,
    //                     'initial_stock' => $lastInventory->initial_stock ?? $product->quantity,
    //                     'current_stock' => $newQuantity,
    //                     'branch_id' => $branchIdToUse,
    //                     'create_by' => $userData->id,
    //                     'type' => 'Sale',
    //                     'date' => $now,
    //                     'created_at' => $now,
    //                     'updated_at' => $now,
    //                 ];
    //             }
    //         }

    //         OrderItem::insert($orderItemsData);

    //         // ✅ Add Labour Items (with type for quotations/sales)
    //         if (!empty($request->labour_items)) {
    //             $labourItemsData = [];
    //             foreach ($request->labour_items as $litem) {
    //                 $labourItemsData[] = [
    //                     'order_id' => $order->id,
    //                     'user_id' => $customer_id,
    //                     'labour_item_id' => $litem['labour_item_id'],
    //                     'qty' => $litem['qty'],
    //                     'price' => $litem['price'],
    //                     'created_at' => $now,
    //                     'updated_at' => $now,
    //                 ];
    //             }
    //             Sales_Labour_Items::insert($labourItemsData);
    //         }

    //         if (!$isQuotation) {
    //             foreach ($productUpdates as $id => $data) {
    //                 Product::where('id', $id)->update($data);
    //             }

    //             if (!empty($inventoryData)) {
    //                 ProductInventory::insert($inventoryData);
    //             }
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | PAYMENT (ONLY SALES)
    //     |--------------------------------------------------------------------------
    //     */
    //         if (!$isQuotation && $paymentMethod != 'pending') {

    //             PaymentStore::create([
    //                 'user_id' => $customer_id,
    //                 'order_id' => $order->id,
    //                 'payment_amount' => $request->total,
    //                 'payment_date' => now(),
    //                 'payment_method' => $paymentMethod,
    //                 'payment_type' => 'full',
    //                 'created_at' => now(),
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'message' => $isQuotation
    //                 ? 'Quotation saved successfully!'
    //                 : 'Order placed successfully!',
    //             'order_id' => $order->id,
    //         ], 201);
    //     } catch (\Exception $e) {

    //         DB::rollBack();
    //         Log::error('Order placement error: ' . $e->getMessage());

    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function order_sale(Request $request)
    {
        // ✅ Detect quotation
        $isQuotation = $request->quotation_status === 'quotation';
        $indiaNow = now('Asia/Kolkata');

        // ✅ Validation (payment required ONLY for sales)
        $request->validate([
            'customer_id' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:255',
            'order_date' => 'nullable|date',
            'customer_email' => 'nullable|email|max:255',
            'payment_method' => $isQuotation ? 'nullable|string' : 'required|string',
            'bank_id'        => 'nullable|integer',
            'assign_staff'   => 'nullable|integer|exists:users,id',
            'paid_type' => 'nullable|in:cash_partially,cash_fully',
            'online_type' => 'nullable|in:online_partially,online_fully',
            'cash_online_type' => 'nullable|in:cash_online_partially,cash_online_fully',
            'amount' => 'nullable|numeric|min:0',
            'payment_amount' => 'nullable|numeric|min:0',
            'pending_amount' => 'nullable|numeric|min:0',
            'cash_amount' => 'nullable|numeric|min:0',
            'online_amount' => 'nullable|numeric|min:0',
            'subtotal'       => 'required|numeric|min:0',
            'discount'       => 'nullable|numeric|min:0|max:100',
            'remarks'        => 'nullable|string|max:500',
            'payment_remarks' => 'nullable|string|max:500',
            'tax'            => 'nullable|array',
            'items'          => 'required|array|min:1',
            'labour_items'   => 'nullable|array',
            'shipping' => 'nullable|numeric|min:0',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'tds_percentage' => 'nullable|numeric|min:0|max:100',
            'tds_amount' => 'nullable|numeric|min:0',
        ]);





        DB::beginTransaction();

        try {

            $userData = Auth::guard('api')->user();
            $userRole = $userData->role;

            // Branch logic
            if ($userRole === 'staff') {
                $branchIdToUse = $userData->branch_id;
            } elseif ($userRole === 'admin' && ! empty($request->selectedSubAdminId)) {
                $branchIdToUse = $request->selectedSubAdminId;
            } else {
                $branchIdToUse = $userData->id;
            }

            $branchSetting = Setting::where('branch_id', $branchIdToUse)->first();
            $isTdsEnabled = (bool) ($branchSetting->tds_apply ?? false);

            $shippingAmount = (float) ($request->shipping ?? 0);
            $labourSubtotal = 0;
            if (!empty($request->labour_items) && is_array($request->labour_items)) {
                foreach ($request->labour_items as $labourItem) {
                    $labourSubtotal += ((float) ($labourItem['qty'] ?? 0)) * ((float) ($labourItem['price'] ?? 0));
                }
            }

            $productBaseTotal = 0;
            $productTotalBeforeTds = 0;
            foreach ($request->items as $item) {
                $baseAmount = ((float) ($item['price'] ?? 0)) * ((float) ($item['quantity'] ?? 0));
                $itemGstAmount = (float) ($item['product_gst_total'] ?? 0);
                $itemDiscountAmount = (float) ($item['discount_amount'] ?? 0);
                $productBaseTotal += $baseAmount;
                $productTotalBeforeTds += ($baseAmount + $itemGstAmount - $itemDiscountAmount);
            }

            $preTdsTotal = $productTotalBeforeTds + $shippingAmount + $labourSubtotal;
            $tdsPercentage = $isTdsEnabled ? max(0, min(100, (float) ($request->tds_percentage ?? 0))) : 0;
            $tdsAmount = $isTdsEnabled ? round(($productBaseTotal * $tdsPercentage) / 100, 2) : 0;
            $roundedTotal = max(0, round($preTdsTotal - $tdsAmount));

            /*
        |--------------------------------------------------------------------------
        | STOCK CHECK (ONLY SALES)
        |--------------------------------------------------------------------------
        */
            $productIds = array_column($request->items, 'product_id');
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            if (!$isQuotation) {
                foreach ($request->items as $item) {
                    $product = $products->get($item['product_id']);

                    if (!$product || $product->quantity < $item['quantity']) {
                        DB::rollBack();
                        return response()->json([
                            'status' => false,
                            'message' => "Insufficient stock for product: " .
                                ($product->name ?? "ID: {$item['product_id']}"),
                        ], 400);
                    }
                }
            }

            /*
        |--------------------------------------------------------------------------
        | CUSTOMER
        |--------------------------------------------------------------------------
        */
            if (!is_numeric($request->customer_id) && !empty($request->customer_id)) {

                $vendor = User::create([
                    'name' => $request->customer_id,
                    'phone' => $request->customer_phone,
                    'email' => $request->customer_email,
                    'role' => 'customer',
                    'status' => 1,
                    'branch_id' => $branchIdToUse,
                    'created_by' => $userData->id,
                ]);

                UserDetail::create(['user_id' => $vendor->id]);

                $customer_id = $vendor->id;
            } elseif (is_numeric($request->customer_id)) {

                $customer_id = $request->customer_id;
            } else {

                $customer_id = User::where('role', 'customer')
                    ->where('name', 'Default Customer')
                    ->where('branch_id', $branchIdToUse)
                    ->value('id');
            }

            /*
        |--------------------------------------------------------------------------
        | PAYMENT STATUS
        |--------------------------------------------------------------------------
        */
            $rawPaymentMethod = strtolower(trim((string) $request->payment_method));
            $normalizedPaymentMethod = match ($rawPaymentMethod) {
                'cash+online', 'cash_online', 'cash + online' => 'cash_online',
                'debit card', 'debit', 'scan', 'online', 'upi' => 'online',
                'cash' => 'cash',
                'pending', 'pay later' => 'pending',
                default => $rawPaymentMethod !== '' ? $rawPaymentMethod : 'pending',
            };

            $paymentMethod = $isQuotation ? 'pending' : $normalizedPaymentMethod;

            $paidTypeRaw = strtolower((string) ($request->paid_type ?? $request->online_type ?? $request->cash_online_type ?? ''));
            $isPartiallyPaid = str_contains($paidTypeRaw, 'partially');

            $cashAmount = max(0, (float) ($request->cash_amount ?? 0));
            $onlineAmount = max(0, (float) ($request->online_amount ?? 0));

            $requestedPaidAmount = max(0, (float) ($request->payment_amount ?? $request->amount ?? 0));
            if ($requestedPaidAmount <= 0 && $request->filled('pending_amount')) {
                $requestedPaidAmount = max(0, $roundedTotal - (float) $request->pending_amount);
            }

            if (!$isQuotation && $paymentMethod === 'cash_online') {
                $requestedPaidAmount = $cashAmount + $onlineAmount;
                if ($requestedPaidAmount <= 0) {
                    // Backward compatibility for old payloads: treat as fully paid.
                    $requestedPaidAmount = $roundedTotal;
                    $onlineAmount = $roundedTotal;
                }
            } elseif (
                !$isQuotation &&
                $paymentMethod !== 'pending' &&
                !$request->filled('payment_amount') &&
                !$request->filled('amount') &&
                !$isPartiallyPaid
            ) {
                // Backward compatibility for old payloads: full payment by default.
                $requestedPaidAmount = $roundedTotal;
            }

            // For EMI: paid amount = down payment, remaining = loan amount
            if (!$isQuotation && $normalizedPaymentMethod === 'emi') {
                $emiDownPayment = max(0, (float) ($request->emi_down_payment ?? 0));
                $emiLoanAmount  = max(0, (float) ($request->emi_loan_amount ?? $roundedTotal));
                $paidAmount     = $emiDownPayment;
                $remain         = $emiLoanAmount;
                if ($paidAmount <= 0) {
                    $payment_status = 'pending';
                } elseif ($paidAmount < $roundedTotal) {
                    $payment_status = 'partially';
                } else {
                    $payment_status = 'completed';
                }
            } else {
                $paidAmount = $isQuotation || $paymentMethod === 'pending'
                    ? 0
                    : min($roundedTotal, $requestedPaidAmount);

                if ($paidAmount <= 0) {
                    $payment_status = 'pending';
                } elseif ($paidAmount < $roundedTotal) {
                    $payment_status = 'partially';
                } else {
                    $payment_status = 'completed';
                }

                $remain = max($roundedTotal - $paidAmount, 0);
            }

            $orderCreatedAt = $request->filled('order_date')
                ? Carbon::createFromFormat('Y-m-d', $request->order_date, 'Asia/Kolkata')
                ->setTime(
                    $indiaNow->hour,
                    $indiaNow->minute,
                    $indiaNow->second
                )
                : $indiaNow->copy();

            $financialYearNumberingEnabled = $this->isFinancialYearOrderNumberingEnabled((int) $branchIdToUse);

            // order_number is NOT NULL, so generate it before creating the order.
            if ($financialYearNumberingEnabled) {
                $orderNumber = $this->generateFinancialYearOrderNumber(
                    (int) $branchIdToUse,
                    $isQuotation,
                    $orderCreatedAt->copy()
                );
            } else {
                $nextOrderId = (Order::max('id') ?? 0) + 1;
                $orderNumber = $isQuotation
                    ? 'Q-' . $nextOrderId
                    : now()->format('Ymd') . str_pad($nextOrderId, 5, '0', STR_PAD_LEFT);
            }

            /*
        |--------------------------------------------------------------------------
        | CREATE ORDER
        |--------------------------------------------------------------------------
         */
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $customer_id,
                'staff_id' => $request->filled('assign_staff') ? (int) $request->assign_staff : null,
                'payment_method' => $paymentMethod,
                'discount' => $request->discount ?? 0,
                'tax_id' => !empty($request->tax) ? json_encode($request->tax) : null,
                'gst_option' => $request->gst_option === 'with'
                    ? 'with_gst'
                    : 'without_gst',
                'total_amount' => $roundedTotal,
                'remaining_amount' => $remain,
                'payment_status' => $payment_status,
                'quotation_status' => $isQuotation ? 'quotation' : 'sales',
                'shipping'         => $request->shipping ?? 0,
                'tds_percentage'   => $tdsPercentage,
                'tds_amount'       => $tdsAmount,
                'remarks'          => $request->remarks,
                'order_type'       => $request->order_type,
                'branch_id'        => $branchIdToUse,
                'created_by'       => $userData->id,
                'created_at'       => $orderCreatedAt,
                'updated_at'       => $orderCreatedAt,
                // EMI fields
                'emi_down_payment'  => $normalizedPaymentMethod === 'emi' ? (float) ($request->emi_down_payment ?? 0) : null,
                'emi_loan_amount'   => $normalizedPaymentMethod === 'emi' ? (float) ($request->emi_loan_amount ?? 0) : null,
                'emi_interest_rate' => $normalizedPaymentMethod === 'emi' ? (float) ($request->emi_interest_rate ?? 0) : null,
                'emi_tenure'        => $normalizedPaymentMethod === 'emi' ? ($request->emi_tenure === 'custom' ? $request->emi_custom_tenure : $request->emi_tenure) : null,
                'emi_monthly_amount'=> $normalizedPaymentMethod === 'emi' ? (float) ($request->emi_monthly_amount ?? 0) : null,
                'emi_aadhar_number' => $normalizedPaymentMethod === 'emi' ? ($request->emi_aadhar_number ?? null) : null,
                'emi_do_id'         => $normalizedPaymentMethod === 'emi' ? ($request->emi_do_id ?? null) : null,
                'emi_pan_number'    => $normalizedPaymentMethod === 'emi' ? ($request->emi_pan_number ?? null) : null,
                'emi_guarantor_name'=> $normalizedPaymentMethod === 'emi' ? ($request->emi_guarantor_name ?? null) : null,
                'emi_bank_id'       => $normalizedPaymentMethod === 'emi' ? ($request->bank_id ?? null) : null,
            ]);

            /*
        |--------------------------------------------------------------------------
        | ITEMS + INVENTORY
        |--------------------------------------------------------------------------
        */
            $orderItemsData = [];
            $inventoryData = [];
            $productUpdates = [];
            $now = $orderCreatedAt->copy();

            $lastInventories = ProductInventory::whereIn('product_id', $productIds)
                ->latest()
                ->get()
                ->groupBy('product_id')
                ->map(fn($g) => $g->first());

            foreach ($request->items as $item) {

                $product = $products->get($item['product_id']);
                $quantity = (float) $item['quantity'];
                $price = (float) $item['price'];

                $discountAmount = $item['discount_amount'] ?? 0;
                $itemGstAmount = (float) ($item['product_gst_total'] ?? 0);
                $finalAmount = (($price * $quantity) + $itemGstAmount) - $discountAmount;

                $newQuantity = $product->quantity - $quantity;

                // ✅ Reduce stock ONLY for SALES
                if (!$isQuotation) {
                    $productUpdates[$product->id] = ['quantity' => $newQuantity];
                }

                $orderItemsData[] = [
                    'order_id' => $order->id,
                    'user_id' => $customer_id,
                    'category_id' => $item['categoryId'] ?? null,
                    'product_id' => $item['product_id'],
                    'quantity'   => $quantity,
                    'price'      => $price,
                    'discount_percentage' => $item['discount_percentage'] ?? 0,
                    'discount_amount' => $discountAmount,
                    'product_gst_details' => isset($item['product_gst_details']) ? json_encode($item['product_gst_details']) : null,
                    'product_gst_total' => $item['product_gst_total'] ?? 0,
                    'total_amount' => $finalAmount,
                    'branch_id' => $branchIdToUse,
                    'created_by' => $userData->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // ✅ Inventory ONLY for SALES
                if (!$isQuotation) {
                    $lastInventory = $lastInventories->get($product->id);

                    $inventoryData[] = [
                        'product_id' => $product->id,
                        'initial_stock' => $lastInventory->initial_stock ?? $product->quantity,
                        'current_stock' => $newQuantity,
                        'branch_id' => $branchIdToUse,
                        'create_by' => $userData->id,
                        'type' => 'Sale',
                        'date' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            OrderItem::insert($orderItemsData);

            // ✅ Add Labour Items (with type for quotations/sales)
            if (!empty($request->labour_items)) {
                $labourItemsData = [];
                foreach ($request->labour_items as $litem) {
                    $labourItemsData[] = [
                        'order_id' => $order->id,
                        'user_id' => $customer_id,
                        'labour_item_id' => $litem['labour_item_id'],
                        'qty' => $litem['qty'],
                        'price' => $litem['price'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                Sales_Labour_Items::insert($labourItemsData);
            }

            if (!$isQuotation) {
                foreach ($productUpdates as $id => $data) {
                    Product::where('id', $id)->update($data);
                }

                if (!empty($inventoryData)) {
                    ProductInventory::insert($inventoryData);
                }
            }

            /*
        |--------------------------------------------------------------------------
        | PAYMENT (ONLY SALES)
        |--------------------------------------------------------------------------
        */
            if (!$isQuotation && $paymentMethod !== 'pending' && $paidAmount > 0) {
                $paymentTypeForStore = $payment_status === 'partially' ? 'partially' : 'fully';
                $bankId = $request->bank_id ?: null;

                if ($paymentMethod === 'cash_online') {
                    $cashToStore = 0.0;
                    $onlineToStore = 0.0;

                    if (($cashAmount + $onlineAmount) > 0) {
                        $cashToStore = min($cashAmount, $paidAmount);
                        $onlineToStore = min($onlineAmount, max($paidAmount - $cashToStore, 0));

                        $remainingSplit = max($paidAmount - ($cashToStore + $onlineToStore), 0);
                        if ($remainingSplit > 0) {
                            $onlineToStore += $remainingSplit;
                        }
                    } else {
                        $onlineToStore = $paidAmount;
                    }

                    if ($cashToStore > 0) {
                        PaymentStore::create([
                            'user_id' => $customer_id,
                            'order_id' => $order->id,
                            'payment_amount' => $cashToStore,
                            'payment_date' => now(),
                            'payment_method' => 'cash',
                            'payment_type' => $paymentTypeForStore,
                            'cash_amount' => $cashToStore,
                            'upi_amount' => 0,
                            'remaining_amount' => $remain,
                            'bank_id' => $bankId,
                            'remarks' => $request->payment_remarks ?? $request->remarks,
                            'created_at' => now(),
                        ]);
                    }

                    if ($onlineToStore > 0) {
                        PaymentStore::create([
                            'user_id' => $customer_id,
                            'order_id' => $order->id,
                            'payment_amount' => $onlineToStore,
                            'payment_date' => now(),
                            'payment_method' => 'online',
                            'payment_type' => $paymentTypeForStore,
                            'cash_amount' => 0,
                            'upi_amount' => $onlineToStore,
                            'remaining_amount' => $remain,
                            'bank_id' => $bankId,
                            'remarks' => $request->payment_remarks ?? $request->remarks,
                            'created_at' => now(),
                        ]);
                    }
                } elseif ($paymentMethod === 'emi') {
                    // For EMI, store the down payment as the initial payment
                    PaymentStore::create([
                        'user_id' => $customer_id,
                        'order_id' => $order->id,
                        'payment_amount' => $paidAmount, // down payment
                        'payment_date' => now(),
                        'payment_method' => 'emi',
                        'payment_type' => 'partially',
                        'cash_amount' => $paidAmount,
                        'upi_amount' => 0,
                        'remaining_amount' => $remain,
                        'bank_id' => $bankId,
                        'remarks' => $request->payment_remarks ?? $request->remarks,
                        'created_at' => now(),
                    ]);
                } else {
                    PaymentStore::create([
                        'user_id' => $customer_id,
                        'order_id' => $order->id,
                        'payment_amount' => $paidAmount,
                        'payment_date' => now(),
                        'payment_method' => $paymentMethod,
                        'payment_type' => $paymentTypeForStore,
                        'cash_amount' => $paymentMethod === 'cash' ? $paidAmount : 0,
                        'upi_amount' => $paymentMethod === 'online' ? $paidAmount : 0,
                        'remaining_amount' => $remain,
                        'bank_id' => $bankId,
                        'remarks' => $request->payment_remarks ?? $request->remarks,
                        'created_at' => now(),
                    ]);
                }
            }


            // ==============================================
            // 🔔 CREATE NOTIFICATIONS
            // ==============================================

            // Get customer details
            $customer = User::find($customer_id);

            // 1. Notification for the admin/staff who created the order
            $creatorNotificationTitle = $isQuotation ? 'Quotation Created Successfully' : 'Order Placed Successfully';
            $creatorNotificationMessage = $isQuotation
                ? "Quotation #{$order->order_number} has been created successfully for customer: " . ($customer->name ?? 'N/A')
                : "Order #{$order->order_number} has been placed successfully for customer: " . ($customer->name ?? 'N/A') . ". Total: {$roundedTotal}";

            Notification::create([
                'user_id'   => $userData->id,
                'type'      => $isQuotation ? 'quotation_created' : 'order_created',
                'title'     => $creatorNotificationTitle,
                'message'   => $creatorNotificationMessage,
                'link'      => $isQuotation ? '/sales-invoice/' . $order->id : '/sales-invoice/' . $order->id,
                'is_read'   => 0,
                'is_sound'  => 0,
                'branch_id' => $branchIdToUse,
            ]);



            DB::commit();

            $subscriptionInvoiceMeta = null;
            if (!$isQuotation) {
                $subscriptionInvoiceMeta = $this->generateAndSendSubscriptionInvoice(
                    (int) $order->id,
                    (int) $customer_id,
                    (int) $branchIdToUse
                );
            }
            try {
                $orderTemplateWhatsAppSent = false;
                // ✅ OPTIMIZED: Only load customer if needed
                $customer = User::select('id', 'name', 'phone', 'email')->find($customer_id);
                if ($customer && !empty($customer->phone)) {
                    $waSetting = Setting::select('customer_whatsapp_message')
                        ->where('branch_id', $branchIdToUse)
                        ->first();
                    $isCustomerWhatsAppEnabled = (int) ($waSetting->customer_whatsapp_message ?? 0) === 1;

                    if (! $isCustomerWhatsAppEnabled) {
                        Log::info('Customer WhatsApp disabled in settings; skipping order WhatsApp send', [
                            'order_id' => $order->id,
                            'branch_id' => $branchIdToUse,
                        ]);
                    }

                    $phoneNumber = preg_replace('/[^0-9]/', '', $customer->phone);

                    if (!empty($phoneNumber) && $isCustomerWhatsAppEnabled) {
                        $templateName = null;
                        $templateParams = [];
                        $templateLanguage = 'en'; // Default language code
                        $templateExtraComponents = [];
                        $invoiceFileUrl = (string) data_get($subscriptionInvoiceMeta, 'file_url', '');
                        $invoiceFileName = (string) data_get($subscriptionInvoiceMeta, 'file_name', 'subscription_invoice.pdf');

                        $hasSubscriptionInvoicePdf = is_array($subscriptionInvoiceMeta)
                            && (($subscriptionInvoiceMeta['status'] ?? true) !== false)
                            && $invoiceFileUrl !== '';

                        $subscriptionUseFor = null;
                        if ($hasSubscriptionInvoicePdf) {
                            $subscriptionUseFor = match ($payment_status) {
                                'completed' => 'Subscription complete',
                                'pending', 'partially' => 'Subscription pending',
                                default => null,
                            };
                        }

                        $genericUseFor = $payment_status === 'completed'
                            ? 'Complete order'
                            : (($payment_status === 'pending' || $payment_status === 'partially') ? 'Pending order' : null);

                        $template = null;
                        $useForTemplate = null;

                        if ($subscriptionUseFor) {
                            $useForTemplate = $subscriptionUseFor;
                            $template = $this->findWhatsAppOrderTemplate($branchIdToUse, [$useForTemplate]);
                        }

                        if (! $template && $genericUseFor) {
                            $useForTemplate = $genericUseFor;
                            $template = $this->findWhatsAppOrderTemplate($branchIdToUse, [$useForTemplate]);
                            if ($template && $subscriptionUseFor) {
                                Log::info('WhatsApp: subscription PDF present but no template for subscription use_for; using generic order template', [
                                    'branch_id' => $branchIdToUse,
                                    'order_id' => $order->id,
                                    'subscription_use_for' => $subscriptionUseFor,
                                    'fallback_use_for' => $genericUseFor,
                                ]);
                            }
                        }

                        // Pay later / partial: many sites only map the invoice PDF template to "Subscription complete".
                        // If "Subscription pending" and "Pending order" are unset, reuse the complete-slot template and
                        // fill {{4}} (status) from actual payment_status.
                        if (! $template && $subscriptionUseFor === 'Subscription pending') {
                            foreach (['Subscription complete', 'Complete order'] as $completeSlot) {
                                $useForTemplate = $completeSlot;
                                $template = $this->findWhatsAppOrderTemplate($branchIdToUse, [$completeSlot]);
                                if ($template) {
                                    Log::info('WhatsApp: pending payment + invoice PDF — no Subscription pending/Pending order template; using complete-slot template for WhatsApp.', [
                                        'branch_id' => $branchIdToUse,
                                        'order_id' => $order->id,
                                        'fallback_use_for' => $completeSlot,
                                        'payment_status' => $payment_status,
                                    ]);
                                    break;
                                }
                            }
                        }

                        if ($template) {
                            // Check if template is APPROVED
                            if ($template->status !== 'APPROVED') {
                                Log::error("WhatsApp template not approved", [
                                    'branch_id' => $branchIdToUse,
                                    'use_for_template' => $useForTemplate,
                                    'template_name' => $template->name ?? 'N/A',
                                    'template_status' => $template->status ?? 'UNKNOWN',
                                    'message' => 'Template must be APPROVED in Meta Business Manager to send messages. Current status: ' . ($template->status ?? 'UNKNOWN')
                                ]);
                                $templateName = null; // Don't send if not approved
                            } elseif (empty($template->name)) {
                                Log::warning("WhatsApp template found but name is empty", [
                                    'branch_id' => $branchIdToUse,
                                    'use_for_template' => $useForTemplate,
                                    'template_id' => $template->id ?? null
                                ]);
                            } else {
                                $templateName = trim($template->name);
                                // Get language code from template, default to 'en' if not set
                                $templateLanguage = !empty($template->language) ? $template->language : 'en';

                                // Warn if MARKETING category (but still allow sending)
                                if ($template->category === 'MARKETING') {
                                    Log::warning("WhatsApp MARKETING template detected - may have delivery restrictions", [
                                        'branch_id' => $branchIdToUse,
                                        'use_for_template' => $useForTemplate,
                                        'template_name' => $templateName,
                                        'template_category' => $template->category,
                                        'message' => 'MARKETING templates require user opt-in and have 24-hour messaging window restrictions. Consider changing template category to UTILITY for order notifications.'
                                    ]);
                                }

                                // Validate template name matches use_for_template to prevent mismatches
                                $templateNameLower = strtolower($templateName);
                                $useForTemplateLower = strtolower($useForTemplate);

                                // Check for obvious mismatches (e.g., "complete" template name used for "Pending order")
                                if (($useForTemplate === 'Pending order' || $useForTemplate === 'Subscription pending')
                                    && (strpos($templateNameLower, 'complete') !== false || strpos($templateNameLower, 'order_complete') !== false)
                                ) {
                                    Log::error("WhatsApp template name mismatch detected", [
                                        'branch_id' => $branchIdToUse,
                                        'use_for_template' => $useForTemplate,
                                        'template_name' => $templateName,
                                        'payment_status' => $payment_status,
                                        'message' => 'Template name contains "complete" but use_for_template is a pending-type slot. Please check your template configuration.'
                                    ]);
                                    $templateName = null; // Prevent sending with wrong template
                                } elseif (($useForTemplate === 'Complete order' || $useForTemplate === 'Subscription complete')
                                    && (strpos($templateNameLower, 'pending') !== false && strpos($templateNameLower, 'complete') === false)
                                ) {
                                    Log::error("WhatsApp template name mismatch detected", [
                                        'branch_id' => $branchIdToUse,
                                        'use_for_template' => $useForTemplate,
                                        'template_name' => $templateName,
                                        'payment_status' => $payment_status,
                                        'message' => 'Template name contains "pending" but use_for_template is a complete-type slot. Please check your template configuration.'
                                    ]);
                                    $templateName = null; // Prevent sending with wrong template
                                } else {
                                    // Log template details for debugging
                                    Log::info("WhatsApp template found for order notification", [
                                        'branch_id' => $branchIdToUse,
                                        'use_for_template' => $useForTemplate,
                                        'template_name' => $templateName,
                                        'template_status' => $template->status ?? 'UNKNOWN',
                                        'template_category' => $template->category ?? 'UNKNOWN',
                                        'payment_status' => $payment_status,
                                        'note' => $template->category === 'MARKETING'
                                            ? 'MARKETING category - requires opt-in and has 24-hour window. Consider using UTILITY category for order notifications.'
                                            : 'Template status must be APPROVED for messages to be delivered'
                                    ]);
                                }

                                // Only process components if template name is valid
                                if ($templateName) {
                                    $components = is_string($template->components) ? json_decode($template->components, true) : $template->components;

                                    if (is_array($components)) {
                                        foreach ($components as $component) {
                                            $componentType = strtoupper((string) ($component['type'] ?? ''));

                                            if ($componentType === 'HEADER' && strtoupper((string) ($component['format'] ?? '')) === 'DOCUMENT') {
                                                if (! empty($invoiceFileUrl)) {
                                                    $templateExtraComponents[] = [
                                                        'type' => 'header',
                                                        'parameters' => [
                                                            [
                                                                'type' => 'document',
                                                                'document' => [
                                                                    'link' => $invoiceFileUrl,
                                                                    'filename' => $invoiceFileName,
                                                                ],
                                                            ],
                                                        ],
                                                    ];
                                                } else {
                                                    Log::warning('WhatsApp template requires HEADER DOCUMENT but invoice file_url is empty', [
                                                        'order_id' => $order->id,
                                                        'branch_id' => $branchIdToUse,
                                                        'template_name' => $templateName,
                                                    ]);
                                                }
                                            }

                                            if ($componentType === 'BODY' && isset($component['text'])) {
                                                preg_match_all('/\{\{(\d+)\}\}/', $component['text'], $matches);

                                                if (!empty($matches[1])) {
                                                    // ✅ OPTIMIZED: Cache setting query
                                                    $setting = cache()->remember("setting_branch_{$branchIdToUse}", 300, function () use ($branchIdToUse) {
                                                        return Setting::select('name')->where('branch_id', $branchIdToUse)->first();
                                                    });

                                                    $customerName = $customer->name ?? 'Customer';
                                                    $orderNumber = $order->order_number ?? '';
                                                    $totalAmount = number_format($order->total_amount ?? 0, 2);
                                                    $companyName = $setting->name ?? 'Company';

                                                    $bodyVarNums = array_values(array_unique(array_map('intval', $matches[1])));
                                                    sort($bodyVarNums, SORT_NUMERIC);

                                                    // Different parameter mappings for Pending vs Complete order templates
                                                    // {{7}} = public subscription invoice PDF URL (same as API response subscription_invoice.file_url)
                                                    if ($useForTemplate === 'Complete order' || $useForTemplate === 'Subscription complete') {
                                                        // Complete order template structure:
                                                        // {{1}} Customer Name, {{2}} Order ID, {{3}} Company Name,
                                                        // {{4}} Order Status, {{5}} Order ID, {{6}} Amount, {{7}} Invoice PDF URL
                                                        $statusForTemplate = match ($payment_status) {
                                                            'completed' => 'completed',
                                                            'partially' => 'partially',
                                                            default => 'pending',
                                                        };
                                                        foreach ($bodyVarNums as $varNum) {
                                                            $templateParams[] = match ((int)$varNum) {
                                                                1 => $customerName,           // {{1}}: Customer Name
                                                                2 => $orderNumber,            // {{2}}: Order ID
                                                                3 => $companyName,            // {{3}}: Company Name
                                                                4 => $statusForTemplate,      // {{4}}: Order status (matches payment when using same Meta template for Pay later)
                                                                5 => $orderNumber,            // {{5}}: Order ID
                                                                6 => $totalAmount,            // {{6}}: Amount
                                                                7 => $invoiceFileUrl,         // {{7}}: Subscription invoice PDF link
                                                                default => ''
                                                            };
                                                        }

                                                        Log::info("Complete order template parameters built", [
                                                            'variables_found' => $bodyVarNums,
                                                            'params_count' => count($templateParams),
                                                            'params' => $templateParams
                                                        ]);
                                                    } elseif ($useForTemplate === 'Pending order' || $useForTemplate === 'Subscription pending') {
                                                        // Pending order template structure:
                                                        // {{1}} Customer Name, {{2}} Order ID, {{3}} Company Name,
                                                        // {{4}} Order ID, {{5}} Amount, {{6}} Status, {{7}} Invoice PDF URL
                                                        foreach ($bodyVarNums as $varNum) {
                                                            $templateParams[] = match ((int)$varNum) {
                                                                1 => $customerName,           // {{1}}: Customer Name
                                                                2 => $orderNumber,            // {{2}}: Order ID
                                                                3 => $companyName,            // {{3}}: Company Name
                                                                4 => $orderNumber,            // {{4}}: Order ID
                                                                5 => $totalAmount,            // {{5}}: Amount
                                                                6 => 'pending',               // {{6}}: Order Status (pending)
                                                                7 => $invoiceFileUrl,         // {{7}}: Subscription invoice PDF link
                                                                default => ''
                                                            };
                                                        }

                                                        Log::info("Pending order template parameters built", [
                                                            'variables_found' => $bodyVarNums,
                                                            'params_count' => count($templateParams),
                                                            'params' => $templateParams
                                                        ]);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            Log::warning("WhatsApp template not found", [
                                'branch_id' => $branchIdToUse,
                                'tried_subscription_use_for' => $subscriptionUseFor,
                                'tried_generic_use_for' => $genericUseFor,
                                'payment_status' => $payment_status,
                            ]);
                        }

                        if ($templateName) {
                            // Log before sending
                            Log::info("Sending WhatsApp message", [
                                'branch_id' => $branchIdToUse,
                                'phone' => $phoneNumber,
                                'template_name' => $templateName,
                                'template_params_count' => count($templateParams),
                                'template_extra_components_count' => count($templateExtraComponents),
                                'template_params' => $templateParams,
                                'language' => $templateLanguage,
                                'use_for_template' => $useForTemplate
                            ]);

                            // Send WhatsApp message asynchronously (non-blocking)
                            // Use try-catch to prevent blocking order creation
                            try {
                                $result = WhatsAppService::sendTemplateMessage(
                                    $branchIdToUse,
                                    $phoneNumber,
                                    $templateName,
                                    $templateParams,
                                    $templateLanguage,
                                    $templateExtraComponents
                                );

                                if ($result['success']) {
                                    $orderTemplateWhatsAppSent = true;
                                    // Check if template is MARKETING and warn about delivery restrictions
                                    $deliveryWarning = '';
                                    if ($template->category === 'MARKETING') {
                                        $deliveryWarning = ' ⚠️ MARKETING template - Message may not be delivered due to WhatsApp restrictions (requires opt-in and 24-hour window). Change template category to UTILITY for reliable delivery.';
                                        Log::warning("WhatsApp MARKETING template message sent but may not be delivered", [
                                            'branch_id' => $branchIdToUse,
                                            'phone' => $phoneNumber,
                                            'template_name' => $templateName,
                                            'template_category' => $template->category,
                                            'message_id' => $result['message_id'] ?? null,
                                            'warning' => 'MARKETING templates have delivery restrictions. Change to UTILITY category in Meta Business Manager for order notifications.'
                                        ]);
                                    }

                                    Log::info("WhatsApp message sent successfully" . $deliveryWarning, [
                                        'branch_id' => $branchIdToUse,
                                        'phone' => $phoneNumber,
                                        'template_name' => $templateName,
                                        'template_category' => $template->category ?? 'UNKNOWN',
                                        'message_id' => $result['message_id'] ?? null,
                                        'message_status' => $result['message_status'] ?? 'unknown'
                                    ]);
                                } else {
                                    Log::error("WhatsApp message failed to send", [
                                        'branch_id' => $branchIdToUse,
                                        'phone' => $phoneNumber,
                                        'template_name' => $templateName,
                                        'error' => $result['message'] ?? 'Unknown error',
                                        'error_code' => $result['error_code'] ?? null
                                    ]);
                                }
                            } catch (\Throwable $e) {
                                // Log error but don't block order creation
                                Log::error("WhatsApp sending exception (non-blocking)", [
                                    'branch_id' => $branchIdToUse,
                                    'phone' => $phoneNumber,
                                    'template_name' => $templateName,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        } else {
                            Log::warning("WhatsApp message not sent - template name is null", [
                                'branch_id' => $branchIdToUse,
                                'phone' => $phoneNumber,
                                'use_for_template' => $useForTemplate
                            ]);
                        }
                    }

                    // Do not send legacy text + separate PDF — that duplicated chats. Subscription WhatsApp must use the Meta template only.
                    if (
                        ! $isQuotation
                        && $isCustomerWhatsAppEnabled
                        && ! empty($phoneNumber)
                        && ! $orderTemplateWhatsAppSent
                        && is_array($subscriptionInvoiceMeta)
                        && ! empty($subscriptionInvoiceMeta['file_url'] ?? null)
                    ) {
                        Log::warning('Subscription WhatsApp: no template message was delivered; legacy text+PDF is disabled.', [
                            'order_id' => $order->id,
                            'branch_id' => $branchIdToUse,
                            'payment_status' => $payment_status,
                            'hint' => 'Assign an APPROVED template to "Subscription pending" or "Pending order" for Pay later, or to "Subscription complete" for paid orders (HEADER document + body {{1}}-{{7}}). Re-sync from Meta after APPROVED.',
                        ]);
                        $subscriptionInvoiceMeta['whatsapp'] = [
                            'success' => false,
                            'message' => 'Template was not sent; configure Subscription complete/pending with APPROVED status. Separate text+PDF fallback removed.',
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::error("WhatsApp message exception for order", [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }

            $mailSent = $this->sendOrderOrQuotationMail(
                $customer,
                $order,
                $isQuotation,
                $branchIdToUse
            );

            return response()->json([
                'status' => true,
                'message' => $isQuotation
                    ? 'Quotation saved successfully!'
                    : 'Order placed successfully!',
                'order_id' => $order->id,
                'mail_sent' => $mailSent,
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Order placement error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    private function generateAndSendSubscriptionInvoice(int $orderId, int $customerId, int $branchId): ?array
    {
        try {
            Log::info('Subscription invoice flow started', [
                'order_id' => $orderId,
                'customer_id' => $customerId,
                'branch_id' => $branchId,
            ]);

            $order = Order::with(['orderItems.product'])->find($orderId);
            if (! $order) {
                return null;
            }

            $planOrderItem = $order->orderItems->first(function ($item) {
                return (bool) optional($item->product)->is_plan;
            });

            if (! $planOrderItem || ! $planOrderItem->product) {
                return null;
            }

            $user = User::select('id', 'name', 'phone', 'age', 'gender', 'distance_from_shop')
                ->find($customerId);
            if (! $user) {
                return [
                    'status' => false,
                    'message' => 'Customer not found for subscription invoice generation.',
                ];
            }

            $userDetails = UserDetail::where('user_id', $customerId)->first();
            $product = $planOrderItem->product;

            $normalizedPlanItems = collect($product->plan_items ?? [])
                ->map(function ($item) {
                    if (is_array($item)) {
                        $itemId = (int) ($item['item_id'] ?? $item['product_id'] ?? $item['id'] ?? 0);
                        $quantity = (int) ($item['quantity'] ?? 1);
                    } elseif (is_numeric($item)) {
                        $itemId = (int) $item;
                        $quantity = 1;
                    } else {
                        $itemId = 0;
                        $quantity = 0;
                    }

                    if ($itemId <= 0) {
                        return null;
                    }

                    return [
                        'item_id' => $itemId,
                        'quantity' => max(1, $quantity),
                    ];
                })
                ->filter()
                ->values();

            $planItemIds = $normalizedPlanItems
                ->pluck('item_id')
                ->unique()
                ->values();

            $planProducts = $planItemIds->isEmpty()
                ? collect()
                : Product::select('id', 'name', 'price')
                ->whereIn('id', $planItemIds)
                ->get()
                ->keyBy('id');

            $planItemsDetailed = $normalizedPlanItems
                ->map(function ($item) use ($planProducts) {
                    $meal = $planProducts->get((int) $item['item_id']);
                    if (! $meal) {
                        return null;
                    }

                    $quantity = (int) ($item['quantity'] ?? 1);
                    $price = (float) ($meal->price ?? 0);

                    return [
                        'name' => (string) $meal->name,
                        'price' => $price,
                        'quantity' => max(1, $quantity),
                        'line_total' => $price * max(1, $quantity),
                    ];
                })
                ->filter()
                ->values();

            $planItems = $planItemsDetailed
                ->map(fn($item) => $this->normalizePlanMealName((string) ($item['name'] ?? '')))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $totalSubscriptionAmount = (float) $planItemsDetailed->sum(function ($item) {
                if (isset($item['line_total'])) {
                    return (float) $item['line_total'];
                }

                return (float) ($item['price'] ?? 0);
            });
            if ($totalSubscriptionAmount <= 0) {
                $totalSubscriptionAmount = (float) ($planOrderItem->total_amount ?? 0);
            }

            $distanceFromShop = (float) ($user->distance_from_shop ?? 0);
            $deliveryCharge = (float) ($order->extra_charges ?? 0);

            $setting = Setting::where('branch_id', $branchId)->first();

            // Build base64 logo for PDF (external URLs don't load in PDF renderers)
            $logoBase64 = null;
            $logoMime   = 'image/png';
            if ($setting && !empty($setting->logo)) {
                $logoPath = storage_path('app/public/' . $setting->logo);
                if (file_exists($logoPath)) {
                    $logoBase64 = base64_encode(file_get_contents($logoPath));
                    $logoMime   = mime_content_type($logoPath);
                }
            }

            $pdf = Pdf::loadView('sales.subscription_invoice', [
                'order' => $order,
                'user' => $user,
                'userDetails' => $userDetails,
                'product' => $product,
                'planItems' => $planItems,
                'planItemsDetailed' => $planItemsDetailed,
                'totalSubscriptionAmount' => $totalSubscriptionAmount,
                'deliveryCharge' => $deliveryCharge,
                'distanceFromShop' => $distanceFromShop,
                'setting' => $setting,
                'logoBase64' => $logoBase64,
                'logoMime' => $logoMime,
            ])->setPaper('A4', 'portrait')->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
            ]);

            Storage::disk('public')->makeDirectory('subscriptions');

            $fileName = 'subscription_invoice_' . $order->id . '_' . now()->format('Ymd_His') . '.pdf';
            $relativePath = 'subscriptions/' . $fileName;
            Storage::disk('public')->put($relativePath, $pdf->output());
            $absolutePath = Storage::disk('public')->path($relativePath);

            $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

            // WhatsApp is sent after order save: "Subscription complete" / "Subscription pending" when a subscription PDF exists,
            // otherwise "Complete order" / "Pending order"; or plain text + document fallback if no template sends.

            return [
                'status' => true,
                'file_url' => $fileUrl,
                'file_name' => $fileName,
                'relative_path' => $relativePath,
                'local_path' => $absolutePath,
                'whatsapp' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('Subscription invoice generation failed', [
                'order_id' => $orderId,
                'customer_id' => $customerId,
                'branch_id' => $branchId,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => false,
                'message' => 'Failed to generate/send subscription invoice: ' . $e->getMessage(),
            ];
        }
    }

    private function findWhatsAppOrderTemplate(int $branchId, array $useForCandidates): ?WhatsAppMessageTemplate
    {
        $templates = WhatsAppMessageTemplate::select(
            'id',
            'branch_id',
            'name',
            'components',
            'language',
            'status',
            'category',
            'use_for_template',
            'on_off',
            'isDeleted'
        )
            ->where('branch_id', $branchId)
            ->where('on_off', 'active')
            ->where('isDeleted', 0)
            ->get();

        if ($templates->isEmpty()) {
            return null;
        }

        $normalize = static function ($value): string {
            return trim(mb_strtolower((string) $value));
        };

        $candidates = collect($useForCandidates)
            ->filter(fn($value) => trim((string) $value) !== '')
            ->map(fn($value) => trim((string) $value))
            ->unique()
            ->values();

        foreach ($candidates as $candidate) {
            $normalizedCandidate = $normalize($candidate);

            $template = $templates->first(function ($template) use ($normalize, $normalizedCandidate) {
                return $normalize($template->use_for_template ?? '') === $normalizedCandidate;
            });

            if ($template) {
                return $template;
            }
        }

        $aliasMap = [
            'pending order' => [
                'pending order',
                'subscription pending',
                'order_pending_notification',
                'order_panding_notification',
                'order_pending',
            ],
            'subscription pending' => [
                'subscription pending',
                'pending order',
                'order_pending_notification',
                'order_panding_notification',
                'order_pending',
            ],
            'complete order' => [
                'complete order',
                'subscription complete',
                'order_complete_notification',
                'order_confirmation',
                'order_conform',
            ],
            'subscription complete' => [
                'subscription complete',
                'complete order',
                'order_complete_notification',
                'order_confirmation',
                'order_conform',
            ],
        ];

        $aliasCandidates = [];
        foreach ($candidates as $candidate) {
            $aliasCandidates = array_merge($aliasCandidates, $aliasMap[$normalize($candidate)] ?? [$candidate]);
        }

        $aliasCandidates = array_values(array_unique(array_map($normalize, $aliasCandidates)));

        return $templates->first(function ($template) use ($normalize, $aliasCandidates) {
            return in_array($normalize($template->use_for_template ?? ''), $aliasCandidates, true)
                || in_array($normalize($template->name ?? ''), $aliasCandidates, true);
        });
    }

    private function sendOrderOrQuotationMail(?User $customer, Order $order, bool $isQuotation, $branchId): bool
    {
        if (!$customer || empty($customer->email)) {
            Log::info(
                ($isQuotation ? 'Quotation' : 'Order')
                    . " email skipped for order #{$order->order_number}: customer email not found."
            );
            return false;
        }

        $setting = Setting::where('branch_id', $branchId)->first();
        $sendMailEnabled = is_null($setting?->send_mail) ? true : (bool) $setting->send_mail;

        if (!$sendMailEnabled) {
            Log::info(
                ($isQuotation ? 'Quotation' : 'Order')
                    . " email skipped for order #{$order->order_number}: send_mail is off for branch {$branchId}."
            );
            return false;
        }

        try {
            MailConfigService::setSMTP($branchId);
        } catch (\Throwable $e) {
            // Keep fallback behavior: if branch SMTP fails, default mail config is still usable.
            Log::warning(
                "SMTP setup failed for order #{$order->order_number} (branch: {$branchId}): "
                    . $e->getMessage()
            );
        }

        try {
            Mail::to($customer->email)->send(new SalesOrderCreatedMail($order, $customer, $isQuotation));
            Log::info(
                ($isQuotation ? 'Quotation' : 'Order')
                    . " email sent for order #{$order->order_number} to {$customer->email}."
            );
            return true;
        } catch (\Throwable $e) {
            Log::error(
                "Failed to send " . ($isQuotation ? 'quotation' : 'order')
                    . " email for order #{$order->order_number}: "
                    . $e->getMessage()
            );
            return false;
        }
    }







    public function get_orders(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $userId = $user->id;
        $role = $user->role;

        // ✅ Prefer request input over route param
        $selectedSubAdminID = $request->input('selectedSubAdminId');
        if ($selectedSubAdminID === 'null' || $selectedSubAdminID === 'undefined' || empty($selectedSubAdminID)) {
            $selectedSubAdminID = null;
        }

        // ✅ Build the query
        $query = Order::select('orders.*') // 👈 ensure created_by is included
            ->with([
                'user:id,name,phone',
                'orderItems:id,order_id',
                'creator:id,name,role',
            ])
            ->where('isDeleted', 0)

            // ✅ COUNT payments (for buttons / permissions)
            ->withCount([
                'payments as has_payment' => function ($q) {
                    $q->where('isDeleted', 0);
                },
            ])

            // ✅ SUM payments (for remaining / extra paid)
            ->withSum([
                'payments as total_paid' => function ($q) {
                    $q->where('isDeleted', 0);
                },
            ], 'payment_amount')
            ->withSum('returns as total_return', 'total_amount');

        if ($role === 'sub-admin') {
            $query->where('branch_id', $userId);
        } elseif ($role === 'admin' && $selectedSubAdminID) {
            $query->where('branch_id', $selectedSubAdminID);
        } elseif ($role === 'staff') {
            $query->where('created_by', $userId);
        } else {
            $query->where('branch_id', $userId);
        }
        // ✅ Apply date filter
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            if ($request->filled('year')) {
                $query->whereYear('created_at', $request->year);
            }

            if ($request->filled('month')) {
                $query->whereMonth('created_at', $request->month);
            }
        }

        $this->applyFinancialYearFilter($query, $request->input('financial_year'), 'created_at');

        $orderType = strtolower(trim((string) $request->input('order_type', '')));
        if ($orderType !== '' && $orderType !== 'all') {
            $query->whereRaw('LOWER(order_type) = ?', [$orderType]);
        }

        $orderSort = strtolower(trim((string) $request->input('order_sort', 'latest')));
        switch ($orderSort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'order_no_asc':
                $query->orderBy('order_number', 'asc');
                break;
            case 'order_no_desc':
                $query->orderBy('order_number', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        if ($request->filled('customerId')) {
            $query->where('user_id', $request->customerId);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('payment_status', 'like', "%{$search}%")
                    ->orWhere('payment_method', 'like', "%{$search}%")
                    ->orWhere('quotation_status', 'like', "%{$search}%")
                    ->orWhere('total_amount', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('creator', function ($creatorQuery) use ($search) {
                        $creatorQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // ✅ Apply sorting
        $query->orderBy('created_at', 'desc');

        $summaryOrders = (clone $query)->get();
        $totalAmount = 0;
        $totalPendingAmount = 0;
        $totalPaidAmount = 0;

        foreach ($summaryOrders as $summaryOrder) {
            $orderTotal = (float) ($summaryOrder->total_amount ?? 0);
            $totalPaid = (float) ($summaryOrder->total_paid ?? 0);
            $totalReturn = (float) ($summaryOrder->total_return ?? 0);
            $actualTotal = max(0, $orderTotal - $totalReturn);
            $remaining = max(0, $actualTotal - $totalPaid);
            $effectivePaid = max(0, min($orderTotal, $orderTotal - $remaining));

            $totalAmount += $orderTotal;
            $totalPendingAmount += $remaining;
            $totalPaidAmount += $effectivePaid;
        }

        $perPage = (int) $request->input('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;
        $get_orders = $query->paginate($perPage);
        $ordersCollection = collect($get_orders->items());
        $ordersCollection->transform(function ($order) {

            $orderTotal = (float) ($order->total_amount ?? 0);
            $totalPaid = (float) ($order->total_paid ?? 0);
            $totalReturn = (float) ($order->total_return ?? 0);

            // ✅ Actual total after returns
            $actualTotal = $orderTotal - $totalReturn;

            // ✅ Remaining
            $remaining = max(0, $actualTotal - $totalPaid);

            // ✅ Extra Paid (Overpayment after returns)
            $extraPaid = max(0, $totalPaid - $actualTotal);

            $order->remaining_amount = $remaining;
            $order->extra_paid = $extraPaid;

            return $order;
        });

        // ✅ Add created_date (formatted date from orders.created_at)
        $ordersCollection->transform(function ($order) {
            $order->created_date = $order->created_at ? $order->created_at->format('Y-m-d') : null;
            return $order;
        });
        $settingsBranchId = $this->resolveSalesSettingsBranchId($user, $selectedSubAdminID);

        $settings = DB::table('settings')->where('branch_id', $settingsBranchId)->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';
        $financialYearEnabled = (bool) ($settings->financial_year ?? true);

        $ordersCollection->transform(function ($order) {
            // Convert UTC timestamp to IST and format
            $order->created_date = $order->created_at
                ? $order->created_at->format('d-M-Y')
                : null;
            // ✅ Biller logic
            if ($order->created_by && $order->creator) {
                $order->biller = $order->creator->name;
            } else {
                $order->biller = 'Admin';
            }
            $order->invoice_pdf_url = url("/sales/invoice/pdf/" . $order->id);

            // Add biller name from creator relationship
            $order->biller = $order->creator->name ?? 'Admin';

            return $order;
        });

        return response()->json([
            'status' => true,
            'message' => 'Orders fetched successfully',
            'currency_symbol' => $currencySymbol,
            'currency_position' => $currencyPosition,
            'financial_year_enabled' => $financialYearEnabled,
            'total_amount' => $totalAmount,
            'total_pending_amount' => round($totalPendingAmount, 2),
            'total_paid_amount' => round($totalPaidAmount, 2),
            'data' => $ordersCollection,
            'pagination' => [
                'current_page' => $get_orders->currentPage(),
                'last_page' => $get_orders->lastPage(),
                'per_page' => $get_orders->perPage(),
                'total' => $get_orders->total(),
            ],
        ]);
    }

    public function get_salse_detail(Request $request) {}

    public function getsalseById($id, Request $request)
    {

        $authUser = Auth::guard('api')->user();
        // $subAdminId = session('selectedSubAdminId') ?? $authUser->id;
        $selectedSubAdminID = $request->input('selectedSubAdminId');

        if ($authUser->role === 'staff' && $authUser->branch_id) {
            $branchIdToUse = $authUser->branch_id;
        } elseif ($authUser->role === 'admin' && !empty($selectedSubAdminID)) {
            $branchIdToUse = $selectedSubAdminID;
        } else {
            $branchIdToUse = $authUser->id;
        }
        // $setting    = Setting::where('branch_id', $subAdminId)->first();

        // ✅ OPTIMIZED: Eager load all relationships in single query
        $sales = Order::with(['orderItems.product.unit', 'user:id,name,phone,email,gst_number,pan_number'])->find($id);

        if (!$sales) {
            return response()->json(['status' => false, 'error' => 'Sale not found'], 404);
        }
        $paidAmount = PaymentStore::where('order_id', $sales->id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');

        // ✅ Calculate total return amount
        $totalReturn = SalesReturn::where('order_id', $sales->id)->sum('total_amount');
        $actualTotal = max(0, $sales->total_amount - $totalReturn);

        // ✅ Extra Paid calculation (using actual total after returns)
        $extraPaid = max(0, $paidAmount - $actualTotal);

        // ✅ Pending amount calculation (dynamic to fix database inconsistencies)
        $pendingAmount = max(0, $actualTotal - $paidAmount);

        // ✅ OPTIMIZED: Cache company info
        $companyInfo = Setting::where('branch_id', $branchIdToUse)->first()
            ?? Setting::first()
            ?? new Setting([
                'name' => 'Fablead Developer & Technolab',
                'email' => 'info@gmail.com',
                'phone' => '1234567890',
                'address' => 'Adajan Surat',
                'logo' => 'admin/assets/img/logo-image.jpg',
                'currency_symbol' => '₹',
                'currency_position' => 'left',
            ]);
        // dd($companyInfo);
        $taxIds = json_decode($sales->tax_id, true) ?? [];

        // ✅ OPTIMIZED: Only query taxes if tax IDs exist
        $taxDetails = !empty($taxIds)
            ? TaxRate::whereIn('id', $taxIds)
            ->where('isDeleted', 0)
            ->where('status', 'active')
            ->get(['id', 'tax_name', 'tax_rate'])
            : collect();

        $formattedCreatedAt = $sales->created_at?->format('d-m-Y');

        $labourItems = Sales_Labour_Items::where('order_id', $id)
            ->with('labourItem')
            ->get();


        $labourCost = 0;
        if ($labourItems && $labourItems->isNotEmpty()) {
            foreach ($labourItems as $labourItem) {
                $labourCost += ($labourItem->qty ?? 0) * ($labourItem->price ?? 0);
            }
        }


        // ✅ OPTIMIZED: Use already loaded relationship instead of new query
        $orderItems = $sales->orderItems->map(function ($item) {
            return [
                'id' => $item->id,
                'product' => $item->product,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'discount_percentage' => $item->discount_percentage,
                'discount_amount' => $item->discount_amount,
                'product_tax' => $this->normalizeGstDetails($item->product_gst_details),
                'date' => $item->created_at?->timezone('Asia/Kolkata')->format('Y-m-d h:i A'),
            ];
        });

        // ✅ Fetch all returns for this order
        $returns = \App\Models\SalesReturn::with(['items.product'])
            ->where('order_id', $sales->id)
            ->get();

        return response()->json([
            'status' => true,
            'sales' => [
                ...$sales->toArray(),
                'created_at' => $formattedCreatedAt,
                'user_name' => $sales->user->name ?? 'walk-in-customer',
                'user_phone' => $sales->user->phone ?? 'N/A',
                'user_gst_number' => $sales->user->gst_number ?? null,
                'user_pan_number' => $sales->user->pan_number ?? null,
                'taxes' => $taxDetails,
                'paid_amount' => $paidAmount,
                'pending_amount' => $pendingAmount,
                'extra_paid' => $extraPaid,
            ],
            'labour_items' => $labourItems && $labourItems->isNotEmpty() ? $labourItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'order_id' => $item->order_id,
                    'user_id' => $item->user_id,
                    'labour_item_id' => $item->labour_item_id,
                    'labour_item_name' => $item->labourItem->item_name,
                    'qty' => $item->qty ?? 0,
                    'price' => $item->price ?? 0,
                    'labourItem' => $item->labourItem ? [
                        'id' => $item->labourItem->id,
                        'item_name' => $item->labourItem->item_name ?? 'Labour Item',
                        'price' => $item->labourItem->price ?? 0,
                    ] : null,
                    'created_at' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : null,
                    'updated_at' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : null,
                ];
            })->toArray() : [],
            'order_items' => $orderItems,
            'returns' => $returns,
            'company_info' => $companyInfo,
            'currency_symbol' => $companyInfo->currency_symbol ?? '₹',
            'currency_position' => $companyInfo->currency_position ?? 'left',
        ], 200);
    }

    public function delete($id)
    {
        // Find the order
        $order = Order::find($id);

        // If order not found, return error
        if (!$order) {
            return response()->json([
                'status' => false,
                'error' => 'Order not found',
            ], 404);
        }

        // ❌ Prevent deletion if payment is pending
        // if ($order->payment_status === 'pending') {
        //     return response()->json([
        //         'status' => false,
        //         'error' => 'This order cannot be deleted because its payment status is pending.',
        //     ], 400);
        // }
        // Soft delete: set isDeleted = 1
        $order->isDeleted = 1;
        $order->save();

        // Soft delete related order items
        OrderItem::where('order_id', $order->id)->update(['isDeleted' => 1]);

        return response()->json([
            'status' => true,
            'message' => 'Order deleted successfully.',
        ]);
    }


    // public function update_sale(Request $request)
    // {

    // dd($request->all());
    //     $validator = Validator::make($request->all(), [
    //         'update_id' => 'required|exists:orders,id',
    //         'customer_id' => 'nullable',
    //         'order_number' => 'nullable|string',
    //         'customer_phone' => 'nullable|string',
    //         'order_date'     => 'nullable|date',
    //         'product_ids'    => 'required|array',
    //         'product_ids.*'  => 'exists:products,id',
    //         'quantities'     => 'required|array',
    //         'quantities.*'   => 'numeric|min:0',
    //         'prices'         => 'nullable|array',
    //         'prices.*'       => 'numeric|min:0',
    //         'discount'       => 'numeric|min:0|max:100',
    //         'grand_total'    => 'required|numeric|min:0',
    //         'shipping'       => 'nullable|numeric|min:0',
    //         'labour_item_ids' => 'nullable|array',
    //         'labour_qtys'     => 'nullable|array',
    //         'labour_prices'   => 'nullable|array',
    //         'tds_percentage'  => 'nullable|numeric|min:0|max:100',
    //         'tds_amount'      => 'nullable|numeric|min:0',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation errors',
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     try {
    //         DB::beginTransaction();

    //         $order = Order::findOrFail($request->update_id);
    //         $products = Product::whereIn('id', $request->product_ids)->get();
    //         $updatedQuotationStatus = $request->quotation_status ?? $order->quotation_status;
    //         $isConvertingQuotationToSale = ($order->quotation_status ?? '') === 'quotation'
    //             && $updatedQuotationStatus === 'sales';
    //         $orderUpdatedAt = now('Asia/Kolkata');
    //         $orderCreatedAt = $request->filled('order_date')
    //             ? Carbon::createFromFormat('Y-m-d', $request->order_date, 'Asia/Kolkata')
    //                 ->setTime(
    //                     optional($order->created_at)->timezone('Asia/Kolkata')->hour ?? $orderUpdatedAt->hour,
    //                     optional($order->created_at)->timezone('Asia/Kolkata')->minute ?? $orderUpdatedAt->minute,
    //                     optional($order->created_at)->timezone('Asia/Kolkata')->second ?? $orderUpdatedAt->second
    //                 )
    //             : $order->created_at;

    //         // Fetch old order items
    //         $oldItems = OrderItem::where('order_id', $order->id)->get();
    //         $oldQuantities = $oldItems->pluck('quantity', 'product_id')->toArray();

    //         // Calculate subtotal & product-wise GST & adjust stock
    //         $subtotal = 0;
    //         $productWiseGstTotal = 0;
    //         $hasProductSpecificGst = false;

    //         foreach ($request->product_ids as $productId) {
    //             $product = $products->firstWhere('id', $productId);
    //             if ($product) {
    //                 $quantity = $request->quantities[$productId] ?? 0;
    //                 $oldQty = $oldQuantities[$productId] ?? 0;

    //                 $difference = $quantity - $oldQty;

    //                 // Stock adjustment (ONLY if NOT quotation)
    //                 if ($isConvertingQuotationToSale) {
    //                     if ($quantity > 0 && $product->quantity < $quantity) {
    //                         DB::rollBack();
    //                         return response()->json([
    //                             'success' => false,
    //                             'message' => "Stock Quantity Exceeded. Only {$product->quantity} quantity are available for '{$product->name}'.",
    //                         ], 422);
    //                     }

    //                     if ($quantity > 0) {
    //                         $product->decrement('quantity', $quantity);
    //                     }
    //                 } elseif ($order->quotation_status !== 'quotation') {
    //                     // Check if stock is available for increment
    //                     if ($difference > 0 && $product->quantity < $difference) {
    //                         DB::rollBack();
    //                         return response()->json([
    //                             'success' => false,
    //                             'message' => "Stock Quantity Exceeded. Only {$product->quantity} quantity are available for '{$product->name}'.",
    //                         ], 422);
    //                     }

    //                     if ($difference > 0) {
    //                         $product->decrement('quantity', $difference);
    //                     } elseif ($difference < 0) {
    //                         $product->increment('quantity', abs($difference));
    //                     }
    //                 }

    //                 // Calculate product total
    //                 $quantity = $request->quantities[$productId] ?? 0;
    //                 $discountPercent = $request->discounts[$productId] ?? 0;

    //                 $price = floatval($request->prices[$productId] ?? $product->price);
    //                 $baseProductTotal = $price * $quantity;

    //                 // Calculate product-wise GST if global option is with_gst
    //                 $itemGstTotal = 0;
    //                 if ($request->gst_option === 'with_gst' && $product->gst_option === 'with_gst' && $product->product_gst) {
    //                     $hasProductSpecificGst = true;
    //                     try {
    //                         $gstData = json_decode($product->product_gst, true);
    //                         if (is_array($gstData)) {
    //                             foreach ($gstData as $tax) {
    //                                 $taxRate = floatval($tax['tax_rate'] ?? 0) / 100;
    //                                 $itemGstTotal += $baseProductTotal * $taxRate;
    //                             }
    //                         }
    //                     } catch (\Exception $e) {
    //                         // Log error but continue
    //                         Log::error('Error calculating product GST: ' . $e->getMessage());
    //                     }
    //                 }

    //                 $itemDiscountAmount = ($baseProductTotal + $itemGstTotal) * ($discountPercent / 100);
    //                 $productTotal = ($baseProductTotal + $itemGstTotal) - $itemDiscountAmount;

    //                 $subtotal += $baseProductTotal;
    //                 $productWiseGstTotal += $itemGstTotal;
    //             }
    //         }

    //         $userData = Auth::guard('api')->user();
    //         $userRole = $userData->role;
    //         if ($userRole == 'staff') {
    //             $branchIdToUse = $userData->branch_id;
    //         } elseif (!empty($request->selectedSubAdminId) && $userRole == 'admin') {
    //             $branchIdToUse = $request->selectedSubAdminId ?? null;
    //         } else {
    //             $branchIdToUse = $userData->id;
    //         }

    //         // Tax & discount calculations
    //         // Products total after product-level discounts
    //         $totalProductsAfterProductDiscount = ($subtotal + $productWiseGstTotal) - $request->sum_product_discounts; // Wait, I should calculate it here

    //         // Actually, let's recalculate the whole products part to be safe and match JS
    //         $totalProductsWithGstAndDiscount = 0;
    //         $totalItemDiscounts = 0;
    //         foreach ($request->product_ids as $productId) {
    //             $product = $products->firstWhere('id', $productId);
    //             if ($product) {
    //                 $quantity = $request->quantities[$productId] ?? 0;
    //                 $discountPercent = $request->discounts[$productId] ?? 0;
    //                 $price = floatval($request->prices[$productId] ?? $product->price);
    //                 $baseProductTotal = $price * $quantity;

    //                 $itemGst = 0;
    //                 if ($request->gst_option === 'with_gst' && $product->gst_option === 'with_gst' && $product->product_gst) {
    //                     $gstData = json_decode($product->product_gst, true);
    //                     if (is_array($gstData)) {
    //                         foreach ($gstData as $tax) {
    //                             $itemGst += $baseProductTotal * (floatval($tax['tax_rate'] ?? 0) / 100);
    //                         }
    //                     }
    //                 }
    //                 $rowWithGst = $baseProductTotal + $itemGst;
    //                 $rowDiscount = $rowWithGst * ($discountPercent / 100);
    //                 $totalProductsWithGstAndDiscount += ($rowWithGst - $rowDiscount);
    //                 $totalItemDiscounts += $rowDiscount;
    //             }
    //         }

    //         $discountAmount = $totalProductsWithGstAndDiscount * ($request->discount / 100);

    //         // Add labour subtotal
    //         $labourSubtotal = 0;
    //         if ($request->has('labour_item_ids')) {
    //             foreach ($request->labour_item_ids as $index => $labourItemId) {
    //                 $qty = floatval($request->labour_qtys[$index] ?? 0);
    //                 $price = floatval($request->labour_prices[$index] ?? 0);
    //                 $labourSubtotal += $qty * $price;
    //             }
    //         }
    //         $preTdsGrandTotal = ($totalProductsWithGstAndDiscount - $discountAmount) + ($request->shipping ?? 0) + $labourSubtotal;
    //         $settings = Setting::where('branch_id', $branchIdToUse)->first();
    //         $isTdsEnabled = (bool) ($settings->tds_apply ?? false);
    //         $tdsPercentage = $isTdsEnabled ? max(0, min(100, (float) ($request->tds_percentage ?? 0))) : 0;
    //         $tdsAmount = $isTdsEnabled ? round(($preTdsGrandTotal * $tdsPercentage) / 100, 2) : 0;
    //         $grandTotal = max(0, round($preTdsGrandTotal - $tdsAmount));

    //         $totalPaid  = $order->total_amount - $order->remaining_amount;

    //         if ($totalPaid >= $grandTotal) {
    //             $remainingAmount = 0;
    //         } else {
    //             $remainingAmount = $grandTotal - $totalPaid;
    //         }

    //         // Payment status
    //         if ($remainingAmount == 0 && $totalPaid > 0) {
    //             $paymentStatus = 'completed';
    //         } elseif ($totalPaid > 0) {
    //             $paymentStatus = 'partially';
    //         } else {
    //             $paymentStatus = 'pending';
    //         }

    //         $updatedOrderNumber = $order->order_number;
    //         if ($isConvertingQuotationToSale) {
    //             $financialYearNumberingEnabled = $this->isFinancialYearOrderNumberingEnabled((int) $branchIdToUse);
    //             if ($financialYearNumberingEnabled) {
    //                 $updatedOrderNumber = $this->generateFinancialYearOrderNumber(
    //                     (int) $branchIdToUse,
    //                     false,
    //                     ($orderCreatedAt instanceof Carbon) ? $orderCreatedAt->copy() : now('Asia/Kolkata')
    //                 );
    //             } else {
    //                 $updatedOrderNumber = now()->format('Ymd') . str_pad($order->id, 5, '0', STR_PAD_LEFT);
    //             }
    //         }

    //         // Update Order
    //         $order->update([
    //             'user_id' => $request->customer_id,
    //             'user_phone' => $request->customer_phone,
    //             'payment_method' => $order->payment_method,
    //             'discount' => $request->discount,
    //             'discount_amount' => $totalItemDiscounts + $discountAmount,
    //             'gst_option' => $request->gst_option === 'with_gst' ? 'with_gst' : 'without_gst',
    //             'tax_id' => json_encode([]), // Empty array since no TaxRate
    //             'tax_amount' => $productWiseGstTotal,
    //             'subtotal' => $subtotal,
    //             'total_amount' => $grandTotal,
    //             'remaining_amount' => $remainingAmount,
    //             'payment_status'   => $paymentStatus,
    //             'quotation_status' => $updatedQuotationStatus,
    //             'order_number'     => $updatedOrderNumber,
    //             'shipping'         => $request->shipping ?? 0,
    //             'tds_percentage'   => $tdsPercentage,
    //             'tds_amount'       => $tdsAmount,
    //             'remarks'          => $request->remarks ?? $order->remarks,
    //             'created_at'       => $orderCreatedAt,
    //         ]);

    //         // Remove old items
    //         OrderItem::where('order_id', $order->id)->delete();
    //         Sales_Labour_Items::where('order_id', $order->id)->delete();

    //         // Reinsert updated order items with GST details
    //         foreach ($request->product_ids as $productId) {
    //             $product = $products->firstWhere('id', $productId);
    //             if ($product) {
    //                 $quantity     = $request->quantities[$productId] ?? 0;
    //                 $discountPercentage = $request->discounts[$productId] ?? 0;
    //                 $price        = floatval($request->prices[$productId] ?? $product->price);

    //                 $baseProductTotal = $price * $quantity;

    //                 // Calculate product GST details (based on base price)
    //                 $productGstDetails = [];
    //                 $productGstTotal = 0;

    //                 if ($request->gst_option === 'with_gst' && $product->gst_option === 'with_gst' && $product->product_gst) {
    //                     try {
    //                         $gstData = json_decode($product->product_gst, true);
    //                         if (is_array($gstData)) {
    //                             foreach ($gstData as $tax) {
    //                                 $taxRate = floatval($tax['tax_rate'] ?? 0) / 100;
    //                                 $taxAmount = $baseProductTotal * $taxRate;
    //                                 $productGstTotal += $taxAmount;

    //                                 $productGstDetails[] = [
    //                                     'tax_name' => $tax['tax_name'] ?? 'GST',
    //                                     'tax_rate' => $tax['tax_rate'] ?? 0,
    //                                     'tax_amount' => $taxAmount,
    //                                 ];
    //                             }
    //                         }
    //                     } catch (\Exception $e) {
    //                         // Log error
    //                         Log::error('Error parsing product GST: ' . $e->getMessage());
    //                     }
    //                 }

    //                 // Calculate discount on (Base + GST)
    //                 $discountAmount = ($baseProductTotal + $productGstTotal) * ($discountPercentage / 100);
    //                 $productTotal = ($baseProductTotal + $productGstTotal) - $discountAmount;

    //                 OrderItem::create([
    //                     'order_id'            => $order->id,
    //                     'user_id'             => $order->user_id ?? null,
    //                     'category_id'         => $product->category_id ?? null,
    //                     'product_id'          => $productId,
    //                     'quantity'            => $quantity,
    //                     'price'               => $price,
    //                     'discount_percentage' => $discountPercentage,
    //                     'discount_amount'     => $discountAmount,
    //                     'total_amount'        => $productTotal,
    //                     'product_gst_details' => ! empty($productGstDetails) ? json_encode($productGstDetails) : null,
    //                     'product_gst_total'   => $productGstTotal,
    //                     'branch_id'           => $branchIdToUse,
    //                     'created_by'          => $userData->id,
    //                 ]);

    //                 if ($order->quotation_status !== 'quotation') {
    //                     $lastInventory = ProductInventory::where('product_id', $productId)
    //                         ->orderBy('id', 'desc')
    //                         ->first();

    //                     ProductInventory::create([
    //                         'product_id'    => $productId,
    //                         'initial_stock' => $lastInventory->initial_stock ?? $product->quantity,
    //                         'current_stock' => $product->quantity,
    //                         'branch_id'     => $order->branch_id,
    //                         'create_by'     => Auth::id(),
    //                         'type'          => 'Update Sale',
    //                         'date'          => now(),
    //                     ]);
    //                 }
    //             }
    //         }

    //         // Reinsert labour items
    //         if ($request->has('labour_item_ids')) {
    //             foreach ($request->labour_item_ids as $index => $labourItemId) {
    //                 Sales_Labour_Items::create([
    //                     'order_id' => $order->id,
    //                     'user_id' => $order->user_id,
    //                     'labour_item_id' => $labourItemId,
    //                     'qty' => floatval($request->labour_qtys[$index] ?? 0),
    //                     'price' => floatval($request->labour_prices[$index] ?? 0),
    //                 ]);
    //             }
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Order updated successfully',
    //             'data' => $order,
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to update order',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function update_sale(Request $request)
    {
        $userData = Auth::guard('api')->user();
        $userRole = $userData->role ?? null;

        if ($userRole == 'staff') {
            $branchIdToUse = $userData->branch_id;
        } elseif (!empty($request->selectedSubAdminId) && $userRole == 'admin') {
            $branchIdToUse = $request->selectedSubAdminId ?? null;
        } else {
            $branchIdToUse = $userData->id ?? null;
        }

        $validator = Validator::make($request->all(), [
            'update_id' => 'required|exists:orders,id',
            'customer_id' => 'nullable',
            'order_number' => [
                'nullable',
                'string',
                Rule::unique('orders', 'order_number')
                    ->ignore($request->update_id)
                    ->where(function ($query) use ($branchIdToUse) {
                        $query->where('isDeleted', 0);

                        if (!empty($branchIdToUse)) {
                            $query->where('branch_id', $branchIdToUse);
                        }

                        return $query;
                    }),
            ],
            'customer_phone' => 'nullable|string',
            'order_date'     => 'nullable|date',
            'product_ids'    => 'required|array',
            'product_ids.*'  => 'exists:products,id',
            'quantities'     => 'required|array',
            'quantities.*'   => 'numeric|min:0',
            'prices'         => 'nullable|array',
            'prices.*'       => 'numeric|min:0',
            'discount'       => 'numeric|min:0|max:100',
            'grand_total'    => 'required|numeric|min:0',
            'shipping'       => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'bank_id'        => 'nullable|required_if:payment_method,online,cash+online,emi|integer|exists:bank_master,id',
            'emi_bank_id'    => 'nullable|integer|exists:bank_master,id',
            'emi_down_payment'  => 'nullable|numeric|min:0',
            'emi_loan_amount'   => 'nullable|numeric|min:0',
            'emi_interest_rate' => 'nullable|numeric|min:0',
            'emi_tenure'        => 'nullable|string',
            'emi_custom_tenure' => 'nullable|integer|min:1',
            'emi_monthly_amount'=> 'nullable|numeric|min:0',
            'emi_aadhar_number' => 'nullable|string|max:50',
            'emi_do_id'         => 'nullable|string|max:50',
            'emi_pan_number'    => 'nullable|string|max:50',
            'emi_guarantor_name'=> 'nullable|string|max:255',
            'paid_type'      => 'nullable|in:full,partial',
            'payment_amount' => 'nullable|numeric|min:0',
            'pending_amount' => 'nullable|numeric|min:0',
            'cash_amount'    => 'nullable|numeric|min:0',
            'online_amount'  => 'nullable|numeric|min:0',
            'labour_item_ids' => 'nullable|array',
            'labour_qtys'     => 'nullable|array',
            'labour_prices'   => 'nullable|array',
            'tds_percentage'  => 'nullable|numeric|min:0|max:100',
            'tds_amount'      => 'nullable|numeric|min:0',
        ], [
            'order_number.unique' => 'This order number already exists.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $order = Order::findOrFail($request->update_id);
            $products = Product::whereIn('id', $request->product_ids)->get();
            $updatedQuotationStatus = $request->quotation_status ?? $order->quotation_status;
            $isConvertingQuotationToSale = ($order->quotation_status ?? '') === 'quotation'
                && $updatedQuotationStatus === 'sales';
            $orderUpdatedAt = now('Asia/Kolkata');
            $orderCreatedAt = $request->filled('order_date')
                ? Carbon::createFromFormat('Y-m-d', $request->order_date, 'Asia/Kolkata')
                ->setTime(
                    optional($order->created_at)->timezone('Asia/Kolkata')->hour ?? $orderUpdatedAt->hour,
                    optional($order->created_at)->timezone('Asia/Kolkata')->minute ?? $orderUpdatedAt->minute,
                    optional($order->created_at)->timezone('Asia/Kolkata')->second ?? $orderUpdatedAt->second
                )
                : $order->created_at;

            // Fetch old order items
            $oldItems = OrderItem::where('order_id', $order->id)->get();
            $oldQuantities = $oldItems->pluck('quantity', 'product_id')->toArray();

            // Calculate subtotal & product-wise GST & adjust stock
            $subtotal = 0;
            $productWiseGstTotal = 0;
            $hasProductSpecificGst = false;

            foreach ($request->product_ids as $productId) {
                $product = $products->firstWhere('id', $productId);
                if ($product) {
                    $quantity = $request->quantities[$productId] ?? 0;
                    $oldQty = $oldQuantities[$productId] ?? 0;

                    $difference = $quantity - $oldQty;

                    // Stock adjustment (ONLY if NOT quotation)
                    if ($isConvertingQuotationToSale) {
                        if ($quantity > 0 && $product->quantity < $quantity) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => "Stock Quantity Exceeded. Only {$product->quantity} quantity are available for '{$product->name}'.",
                            ], 422);
                        }

                        if ($quantity > 0) {
                            $product->decrement('quantity', $quantity);
                        }
                    } elseif ($order->quotation_status !== 'quotation') {
                        // Check if stock is available for increment
                        if ($difference > 0 && $product->quantity < $difference) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => "Stock Quantity Exceeded. Only {$product->quantity} quantity are available for '{$product->name}'.",
                            ], 422);
                        }

                        if ($difference > 0) {
                            $product->decrement('quantity', $difference);
                        } elseif ($difference < 0) {
                            $product->increment('quantity', abs($difference));
                        }
                    }

                    // Calculate product total
                    $quantity = $request->quantities[$productId] ?? 0;
                    $discountPercent = $request->discounts[$productId] ?? 0;

                    $price = floatval($request->prices[$productId] ?? $product->price);
                    $baseProductTotal = $price * $quantity;

                    // Calculate product-wise GST if global option is with_gst
                    $itemGstTotal = 0;
                    if ($request->gst_option === 'with_gst' && $product->gst_option === 'with_gst' && $product->product_gst) {
                        $hasProductSpecificGst = true;
                        try {
                            $gstData = json_decode($product->product_gst, true);
                            if (is_array($gstData)) {
                                foreach ($gstData as $tax) {
                                    $taxRate = floatval($tax['tax_rate'] ?? 0) / 100;
                                    $itemGstTotal += $baseProductTotal * $taxRate;
                                }
                            }
                        } catch (\Exception $e) {
                            Log::error('Error calculating product GST: ' . $e->getMessage());
                        }
                    }

                    $itemDiscountAmount = ($baseProductTotal + $itemGstTotal) * ($discountPercent / 100);
                    $productTotal = ($baseProductTotal + $itemGstTotal) - $itemDiscountAmount;

                    $subtotal += $baseProductTotal;
                    $productWiseGstTotal += $itemGstTotal;
                }
            }

            // Tax & discount calculations
            $totalProductsWithGstAndDiscount = 0;
            $totalItemDiscounts = 0;
            foreach ($request->product_ids as $productId) {
                $product = $products->firstWhere('id', $productId);
                if ($product) {
                    $quantity = $request->quantities[$productId] ?? 0;
                    $discountPercent = $request->discounts[$productId] ?? 0;
                    $price = floatval($request->prices[$productId] ?? $product->price);
                    $baseProductTotal = $price * $quantity;

                    $itemGst = 0;
                    if ($request->gst_option === 'with_gst' && $product->gst_option === 'with_gst' && $product->product_gst) {
                        $gstData = json_decode($product->product_gst, true);
                        if (is_array($gstData)) {
                            foreach ($gstData as $tax) {
                                $itemGst += $baseProductTotal * (floatval($tax['tax_rate'] ?? 0) / 100);
                            }
                        }
                    }
                    $rowWithGst = $baseProductTotal + $itemGst;
                    $rowDiscount = $rowWithGst * ($discountPercent / 100);
                    $totalProductsWithGstAndDiscount += ($rowWithGst - $rowDiscount);
                    $totalItemDiscounts += $rowDiscount;
                }
            }

            $discountAmount = $totalProductsWithGstAndDiscount * ($request->discount / 100);

            // Add labour subtotal
            $labourSubtotal = 0;
            if ($request->has('labour_item_ids')) {
                foreach ($request->labour_item_ids as $index => $labourItemId) {
                    $qty = floatval($request->labour_qtys[$index] ?? 0);
                    $price = floatval($request->labour_prices[$index] ?? 0);
                    $labourSubtotal += $qty * $price;
                }
            }

        // TDS is calculated only from the product base amount, excluding GST, shipping, and labour.
        $tdsBaseAmount = max(0, $subtotal);
        $preTdsGrandTotal = $totalProductsWithGstAndDiscount - $discountAmount + ($request->shipping ?? 0) + $labourSubtotal;
        $settings = Setting::where('branch_id', $branchIdToUse)->first();
        $isTdsEnabled = (bool) ($settings->tds_apply ?? false);
        $tdsPercentage = $isTdsEnabled ? max(0, min(100, (float) ($request->tds_percentage ?? 0))) : 0;
        $tdsAmount = $isTdsEnabled ? round(($tdsBaseAmount * $tdsPercentage) / 100, 2) : 0;
        $grandTotal = max(0, round($preTdsGrandTotal - $tdsAmount));

        $isQuotationOrder = $updatedQuotationStatus === 'quotation';
        $rawPaymentMethod = strtolower(trim((string) ($request->payment_method ?? $order->payment_method ?? 'pending')));
        $normalizedPaymentMethod = match ($rawPaymentMethod) {
            'cash+online', 'cash_online', 'cash + online', 'cash+bank', 'cash_bank', 'cash + bank' => 'cash_online',
            'debit', 'debit card', 'scan', 'online', 'upi' => 'online',
            'cash' => 'cash',
            'pending', 'pay later', '' => 'pending',
            default => $rawPaymentMethod,
        };

        $paymentMethodForOrder = $isQuotationOrder ? 'pending' : $normalizedPaymentMethod;
        $paidType = strtolower(trim((string) ($request->paid_type ?? '')));
        $cashAmountInput = max(0, (float) ($request->cash_amount ?? 0));
        $onlineAmountInput = max(0, (float) ($request->online_amount ?? 0));
        $pendingAmountInput = max(0, (float) ($request->pending_amount ?? 0));

        $activePayments = PaymentStore::where('order_id', $order->id)
            ->where(function ($query) {
                $query->whereNull('isDeleted')->orWhere('isDeleted', 0);
            })
            ->get();

        $existingPaidAmount = min($grandTotal, (float) $activePayments->sum('payment_amount'));
        $outstandingAmount = max(0, $grandTotal - $existingPaidAmount);

        $requestedPaidAmount = max(0, (float) ($request->payment_amount ?? 0));
        if ($request->filled('pending_amount')) {
            $requestedPaidAmount = max(0, $outstandingAmount - min($pendingAmountInput, $outstandingAmount));
        }

        if ($paymentMethodForOrder === 'cash_online') {
            $splitPaidAmount = $cashAmountInput + $onlineAmountInput;
            if ($splitPaidAmount > 0) {
                $requestedPaidAmount = $splitPaidAmount;
            }
        } elseif ($paymentMethodForOrder === 'cash' && $cashAmountInput > 0) {
            $requestedPaidAmount = $cashAmountInput;
        } elseif ($paymentMethodForOrder === 'online' && $onlineAmountInput > 0) {
            $requestedPaidAmount = $onlineAmountInput;
        }

        if (!$isQuotationOrder && $paymentMethodForOrder !== 'pending' && $paidType === 'full') {
            $requestedPaidAmount = $outstandingAmount;
        }

        $additionalPaidAmount = $isQuotationOrder || $paymentMethodForOrder === 'pending'
            ? 0
            : min($outstandingAmount, $requestedPaidAmount);
        $totalPaid = min($grandTotal, $existingPaidAmount + $additionalPaidAmount);
        $remainingAmount = max($grandTotal - $totalPaid, 0);

        if ($remainingAmount <= 0 && $totalPaid > 0) {
            $paymentStatus = 'completed';
        } elseif ($totalPaid > 0) {
            $paymentStatus = 'partially';
        } else {
            $paymentStatus = 'pending';
        }

        // ✅ FIXED: Proper order number handling
        $updatedOrderNumber = $order->order_number;

        // Check if we should update the order number
        if ($request->filled('order_number') && !empty($request->order_number)) {
            // Use the order number sent from the frontend
            $updatedOrderNumber = $request->order_number;
        } elseif ($isConvertingQuotationToSale) {
            // Converting from QUOTATION to SALE - generate new sales order number
            $financialYearNumberingEnabled = $this->isFinancialYearOrderNumberingEnabled((int) $branchIdToUse);
            if ($financialYearNumberingEnabled) {
                $updatedOrderNumber = $this->generateFinancialYearOrderNumber(
                    (int) $branchIdToUse,
                    false,
                    ($orderCreatedAt instanceof Carbon) ? $orderCreatedAt->copy() : now('Asia/Kolkata')
                );
            } else {
                $lastSalesOrder = Order::where('branch_id', $branchIdToUse)
                    ->where('quotation_status', 'sales')
                    ->where('isDeleted', 0)
                    ->latest('id')
                    ->first();

                $nextId = ($lastSalesOrder ? $lastSalesOrder->id + 1 : $order->id);
                $updatedOrderNumber = now()->format('Ymd') . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        }
        // For regular updates, keep the existing order number (don't change it)

        // Update Order
        $storedPaymentMethod = $isQuotationOrder
            ? 'pending'
            : ($additionalPaidAmount > 0 ? $paymentMethodForOrder : ($remainingAmount > 0 ? 'pending' : $order->payment_method));

        $order->update([
            'user_id' => $request->customer_id,
            'user_phone' => $request->customer_phone,
            'payment_method' => $storedPaymentMethod,
            'discount' => $request->discount,
            'discount_amount' => $totalItemDiscounts + $discountAmount,
            'gst_option' => $request->gst_option === 'with_gst' ? 'with_gst' : 'without_gst',
            'tax_id' => json_encode([]),
            'tax_amount' => $productWiseGstTotal,
            'subtotal' => $subtotal,
            'total_amount' => $grandTotal,
            'remaining_amount' => $remainingAmount,
            'payment_status'   => $paymentStatus,
            'quotation_status' => $updatedQuotationStatus,
            'order_number'     => $updatedOrderNumber,
            'shipping'         => $request->shipping ?? 0,
            'tds_percentage'   => $tdsPercentage,
            'tds_amount'       => $tdsAmount,
            'remarks'          => $request->remarks ?? $order->remarks,
            'order_type'       => $request->order_type ?? $order->order_type,
            'staff_id'         => $request->filled('assign_staff') ? (int) $request->assign_staff : null,
            'emi_down_payment' => $normalizedPaymentMethod === 'emi' ? (float) ($request->emi_down_payment ?? 0) : null,
            'emi_loan_amount'  => $normalizedPaymentMethod === 'emi' ? (float) ($request->emi_loan_amount ?? 0) : null,
            'emi_interest_rate'=> $normalizedPaymentMethod === 'emi' ? (float) ($request->emi_interest_rate ?? 0) : null,
            'emi_tenure'       => $normalizedPaymentMethod === 'emi'
                ? ($request->emi_tenure === 'custom' ? $request->emi_custom_tenure : $request->emi_tenure)
                : null,
            'emi_monthly_amount' => $normalizedPaymentMethod === 'emi' ? (float) ($request->emi_monthly_amount ?? 0) : null,
            'emi_aadhar_number'  => $normalizedPaymentMethod === 'emi' ? ($request->emi_aadhar_number ?? null) : null,
            'emi_do_id'          => $normalizedPaymentMethod === 'emi' ? ($request->emi_do_id ?? null) : null,
            'emi_pan_number'     => $normalizedPaymentMethod === 'emi' ? ($request->emi_pan_number ?? null) : null,
            'emi_guarantor_name' => $normalizedPaymentMethod === 'emi' ? ($request->emi_guarantor_name ?? null) : null,
            'emi_bank_id'        => $normalizedPaymentMethod === 'emi' ? ($request->bank_id ?: null) : null,
            'created_at'       => $orderCreatedAt,
        ]);



            if (!$isQuotationOrder && $paymentMethodForOrder !== 'pending' && $additionalPaidAmount > 0) {
                $paymentTypeForStore = $paymentStatus === 'completed' ? 'fully' : 'partially';
                $bankId = $paymentMethodForOrder === 'cash' ? null : ($request->bank_id ?: null);

                if ($paymentMethodForOrder === 'cash_online') {
                    $cashToStore = min($cashAmountInput, $additionalPaidAmount);
                    $onlineToStore = min($onlineAmountInput, max($additionalPaidAmount - $cashToStore, 0));

                    if (($cashToStore + $onlineToStore) <= 0) {
                        $onlineToStore = $additionalPaidAmount;
                    } elseif (($cashToStore + $onlineToStore) < $additionalPaidAmount) {
                        $onlineToStore += ($additionalPaidAmount - ($cashToStore + $onlineToStore));
                    }

                    if ($cashToStore > 0) {
                        PaymentStore::create([
                            'user_id' => $order->user_id,
                            'order_id' => $order->id,
                            'payment_amount' => $cashToStore,
                            'payment_date' => now(),
                            'payment_method' => 'cash',
                            'payment_type' => $paymentTypeForStore,
                            'cash_amount' => $cashToStore,
                            'upi_amount' => 0,
                            'remaining_amount' => $remainingAmount,
                            'bank_id' => $bankId,
                            'remarks' => $request->remarks,
                        ]);
                    }

                    if ($onlineToStore > 0) {
                        PaymentStore::create([
                            'user_id' => $order->user_id,
                            'order_id' => $order->id,
                            'payment_amount' => $onlineToStore,
                            'payment_date' => now(),
                            'payment_method' => 'online',
                            'payment_type' => $paymentTypeForStore,
                            'cash_amount' => 0,
                            'upi_amount' => $onlineToStore,
                            'remaining_amount' => $remainingAmount,
                            'bank_id' => $bankId,
                            'remarks' => $request->remarks,
                        ]);
                    }
                } else {
                    PaymentStore::create([
                        'user_id' => $order->user_id,
                        'order_id' => $order->id,
                        'payment_amount' => $additionalPaidAmount,
                        'payment_date' => now(),
                        'payment_method' => $paymentMethodForOrder,
                        'payment_type' => $paymentTypeForStore,
                        'cash_amount' => $paymentMethodForOrder === 'cash' ? $additionalPaidAmount : 0,
                        'upi_amount' => $paymentMethodForOrder === 'online' ? $additionalPaidAmount : 0,
                        'remaining_amount' => $remainingAmount,
                        'bank_id' => $bankId,
                        'remarks' => $request->remarks,
                    ]);
                }
            }

            // Remove old items
            OrderItem::where('order_id', $order->id)->delete();
            Sales_Labour_Items::where('order_id', $order->id)->delete();

            // Reinsert updated order items with GST details
            foreach ($request->product_ids as $productId) {
                $product = $products->firstWhere('id', $productId);
                if ($product) {
                    $quantity     = $request->quantities[$productId] ?? 0;
                    $discountPercentage = $request->discounts[$productId] ?? 0;
                    $price        = floatval($request->prices[$productId] ?? $product->price);

                    $baseProductTotal = $price * $quantity;

                    $productGstDetails = [];
                    $productGstTotal = 0;

                    if ($request->gst_option === 'with_gst' && $product->gst_option === 'with_gst' && $product->product_gst) {
                        try {
                            $gstData = json_decode($product->product_gst, true);
                            if (is_array($gstData)) {
                                foreach ($gstData as $tax) {
                                    $taxRate = floatval($tax['tax_rate'] ?? 0) / 100;
                                    $taxAmount = $baseProductTotal * $taxRate;
                                    $productGstTotal += $taxAmount;

                                    $productGstDetails[] = [
                                        'tax_name' => $tax['tax_name'] ?? 'GST',
                                        'tax_rate' => $tax['tax_rate'] ?? 0,
                                        'tax_amount' => $taxAmount,
                                    ];
                                }
                            }
                        } catch (\Exception $e) {
                            Log::error('Error parsing product GST: ' . $e->getMessage());
                        }
                    }

                    $discountAmount = ($baseProductTotal + $productGstTotal) * ($discountPercentage / 100);
                    $productTotal = ($baseProductTotal + $productGstTotal) - $discountAmount;

                    OrderItem::create([
                        'order_id'            => $order->id,
                        'user_id'             => $order->user_id ?? null,
                        'category_id'         => $product->category_id ?? null,
                        'product_id'          => $productId,
                        'quantity'            => $quantity,
                        'price'               => $price,
                        'discount_percentage' => $discountPercentage,
                        'discount_amount'     => $discountAmount,
                        'total_amount'        => $productTotal,
                        'product_gst_details' => ! empty($productGstDetails) ? json_encode($productGstDetails) : null,
                        'product_gst_total'   => $productGstTotal,
                        'branch_id'           => $branchIdToUse,
                        'created_by'          => $userData->id,
                    ]);

                    if ($order->quotation_status !== 'quotation') {
                        $lastInventory = ProductInventory::where('product_id', $productId)
                            ->orderBy('id', 'desc')
                            ->first();

                        ProductInventory::create([
                            'product_id'    => $productId,
                            'initial_stock' => $lastInventory->initial_stock ?? $product->quantity,
                            'current_stock' => $product->quantity,
                            'branch_id'     => $order->branch_id,
                            'create_by'     => Auth::id(),
                            'type'          => 'Update Sale',
                            'date'          => now(),
                        ]);
                    }
                }
            }

            // Reinsert labour items
            if ($request->has('labour_item_ids')) {
                foreach ($request->labour_item_ids as $index => $labourItemId) {
                    Sales_Labour_Items::create([
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                        'labour_item_id' => $labourItemId,
                        'qty' => floatval($request->labour_qtys[$index] ?? 0),
                        'price' => floatval($request->labour_prices[$index] ?? 0),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function convertQuotationToSale($id)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated access'
            ], 401);
        }

        try {
            DB::beginTransaction();

            $order = Order::with('orderItems')->findOrFail($id);

            if (($order->quotation_status ?? '') !== 'quotation') {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Only quotation orders can be converted.'
                ], 422);
            }

            if ($order->orderItems->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'No order items found for this quotation.'
                ], 422);
            }

            foreach ($order->orderItems as $item) {
                $product = Product::find($item->product_id);
                if (!$product) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => "Product not found for item ID {$item->id}."
                    ], 422);
                }

                $requiredQty = (float) ($item->quantity ?? 0);
                if ($requiredQty > (float) $product->quantity) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => "Insufficient stock for '{$product->name}'. Available: {$product->quantity}, required: {$requiredQty}."
                    ], 422);
                }
            }

            foreach ($order->orderItems as $item) {
                $product = Product::findOrFail($item->product_id);
                $deductQty = (float) ($item->quantity ?? 0);

                if ($deductQty <= 0) {
                    continue;
                }

                $product->decrement('quantity', $deductQty);
                $product->refresh();

                $lastInventory = ProductInventory::where('product_id', $item->product_id)
                    ->orderBy('id', 'desc')
                    ->first();

                ProductInventory::create([
                    'product_id' => $item->product_id,
                    'initial_stock' => $lastInventory->initial_stock ?? $product->quantity,
                    'current_stock' => $product->quantity,
                    'branch_id' => $order->branch_id,
                    'create_by' => $user->id,
                    'type' => 'Convert Quotation to Sale',
                    'date' => now(),
                ]);
            }

            $branchIdForNumbering = (int) ($order->branch_id ?: ($user->branch_id ?: $user->id));
            $financialYearNumberingEnabled = $this->isFinancialYearOrderNumberingEnabled($branchIdForNumbering);
            if ($financialYearNumberingEnabled) {
                $salesOrderNumber = $this->generateFinancialYearOrderNumber(
                    $branchIdForNumbering,
                    false,
                    now('Asia/Kolkata')
                );
            } else {
                $salesOrderNumber = now()->format('Ymd') . str_pad($order->id, 5, '0', STR_PAD_LEFT);
            }
            $order->quotation_status = 'sales';
            $order->order_number = $salesOrderNumber;
            $order->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Quotation converted to sales successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quotation conversion failed: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to convert quotation.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function normalizeGstDetails($gstDetails): array
    {
        if (empty($gstDetails)) {
            return [];
        }

        if (is_string($gstDetails)) {
            $decoded = json_decode($gstDetails, true);

            // Handle double-encoded JSON string
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }

            $gstDetails = $decoded;
        }

        if (!is_array($gstDetails)) {
            return [];
        }

        // Handle single GST object shape
        if (isset($gstDetails['tax_name']) || isset($gstDetails['tax_rate'])) {
            return [$gstDetails];
        }

        return array_values(array_filter($gstDetails, function ($tax) {
            return is_array($tax);
        }));
    }

    public function orderReport(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated access'], 401);
        }

        $userId = $user->id;
        $role = $user->role;
        $branchId = $user->branch_id;

        $effectiveBranchId = ($role === 'staff')
            ? $branchId
            : ($request->input('selectedSubAdminId') ?? $userId);

        $filter = $request->query('filter');
        $month = $request->query('month');
        $year = $request->query('year');
        $customerId = $request->query('customer_id');
        $categoryId = $request->query('category_id');
        $search = $request->query('search'); // ✅ Add search parameter

        $query = OrderItem::with(['product.category', 'product.brand', 'order'])
            ->whereHas('order', function ($q) use ($filter, $month, $year, $customerId, $role, $effectiveBranchId, $userId) {
                $q->where('isDeleted', 0);

                // ✅ Role-based filtering
                if ($role === 'staff') {
                    $q->where('created_by', $userId);
                } else {
                    $q->where('branch_id', $effectiveBranchId);
                }

                // ✅ Date filters
                if ($filter) {
                    $today = Carbon::today();
                    switch ($filter) {
                        case 'this_week':
                            $q->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                            break;
                        case 'this_month':
                            $q->whereMonth('created_at', $today->month)->whereYear('created_at', $today->year);
                            break;
                        case 'last_6_months':
                            $q->whereBetween('created_at', [Carbon::now()->subMonths(6), Carbon::now()]);
                            break;
                        case 'this_year':
                            $q->whereYear('created_at', $today->year);
                            break;
                        case 'previous_year':
                            $q->whereYear('created_at', Carbon::now()->subYear()->year);
                            break;
                    }
                }

                // ✅ Month filter
                if ($month) {
                    $q->whereMonth('created_at', $month);
                }

                // ✅ Year filter
                if ($year) {
                    $q->whereYear('created_at', $year);
                }

                // ✅ Customer filter
                if ($customerId) {
                    $q->where('user_id', $customerId);
                }
            });

        // ✅ Category filter (via product relation)
        if ($categoryId) {
            $query->whereHas('product', function ($p) use ($categoryId) {
                $p->where('category_id', $categoryId);
            });
        }

        // ✅ Search filter (by product name or SKU)
        if (!empty($search)) {
            $query->whereHas('product', function ($p) use ($search) {
                $p->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('SKU', 'LIKE', "%{$search}%");
            });
        }

        // 🔹 Calculate total for summary (before pagination)
        $totalSoldAmountQuery = clone $query;
        $totalSoldAmount = $totalSoldAmountQuery->get()->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // 🔹 Apply Pagination
        $perPage = (int) $request->input('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 10) : 10;
        $orderItemsPaginated = $query->latest('id')->paginate($perPage);

        $orderItems = collect($orderItemsPaginated->items())->map(function ($item) {
            $product = $item->product;
            $soldQty = $item->quantity;
            $soldAmount = $item->price * $soldQty;

            // Decode product images
            $decodedImages = json_decode($product->images, true);
            $firstImage = $decodedImages[0] ?? 'admin/assets/img/product/noimage.png';

            return [
                'id' => $item->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'SKU' => $product->SKU,
                'category' => $product->category->name ?? 'N/A',
                'brand' => $product->brand->name ?? 'N/A',
                'sold_qty' => $soldQty,
                'sold_amount' => number_format($soldAmount, 2),
                // Simple image path
                'image' => 'storage/' . $firstImage,
                // Uses accessor (returns full URLs)
                'image_url' => $product->image_url,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $orderItems,
            'total_sold_amount' => number_format($totalSoldAmount, 2),
            'pagination' => [
                'current_page' => $orderItemsPaginated->currentPage(),
                'last_page' => $orderItemsPaginated->lastPage(),
                'per_page' => $orderItemsPaginated->perPage(),
                'total' => $orderItemsPaginated->total(),
                'from' => $orderItemsPaginated->firstItem(),
                'to' => $orderItemsPaginated->lastItem(),
                'next_page_url' => $orderItemsPaginated->nextPageUrl(),
                'prev_page_url' => $orderItemsPaginated->previousPageUrl(),
            ]
        ]);
    }

    public function getFilteredOrders(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $userId = $user->id;
        $role = $user->role;

        // ✅ Prefer request input over route param
        $selectedSubAdminID = $request->input('selectedSubAdminId');

        // ✅ Build the query
        $query = Order::select('orders.*') // 👈 ensure created_by is included
            ->with([
                'user:id,name,phone',
                'orderItems:id,order_id',
                'creator:id,name,role',
            ])
            ->where('isDeleted', 0)

            // ✅ COUNT payments (for buttons / permissions)
            ->withCount([
                'payments as has_payment' => function ($q) {
                    $q->where('isDeleted', 0);
                },
            ])

            // ✅ SUM payments (for remaining / extra paid)
            ->withSum([
                'payments as total_paid' => function ($q) {
                    $q->where('isDeleted', 0);
                },
            ], 'payment_amount')
            ->withSum('returns as total_return', 'total_amount');

        // ✅ Apply role-based filtering (same as get_orders)
        if ($role === 'sub-admin') {
            $query->where('branch_id', $userId);
        } elseif ($role === 'admin' && $selectedSubAdminID) {
            $query->where('branch_id', $selectedSubAdminID);
        } elseif ($role === 'staff') {
            $query->where('created_by', $userId);
        } else {
            $query->where('branch_id', $userId);
        }

        // ✅ Apply date filter (similar to get_orders)
        if ($request->filled('date')) {
            // If specific date is provided
            $query->whereDate('created_at', $request->date);
        } else {
            // Apply year filter if provided
            if ($request->filled('year')) {
                $query->whereYear('created_at', $request->year);
            }

            // Apply month filter if provided
            if ($request->filled('month')) {
                $query->whereMonth('created_at', $request->month);
            }
        }

        $this->applyFinancialYearFilter($query, $request->input('financial_year'), 'created_at');

        $orderType = strtolower(trim((string) $request->input('order_type', '')));
        if ($orderType !== '' && $orderType !== 'all') {
            $query->whereRaw('LOWER(order_type) = ?', [$orderType]);
        }

        $orderSort = strtolower(trim((string) $request->input('order_sort', 'latest')));
        switch ($orderSort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'order_no_asc':
                $query->orderBy('order_number', 'asc');
                break;
            case 'order_no_desc':
                $query->orderBy('order_number', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // ✅ Apply customer filter if provided
        if ($request->filled('customerId')) {
            $query->where('user_id', $request->customerId);
        }

        // ✅ Apply sorting
        $query->orderBy('created_at', 'desc');

        try {
            // ✅ Get the results
            $orders = $query->get();

            // ✅ Transform data to calculate remaining/extra paid and format dates
            $orders->transform(function ($order) {
                $orderTotal = (float) ($order->total_amount ?? 0);
                $totalPaid = (float) ($order->total_paid ?? 0);

                // ✅ Remaining
                $remaining = max(0, $orderTotal - $totalPaid);

                // ✅ Extra Paid
                $extraPaid = max(0, $totalPaid - $orderTotal);

                $order->remaining_amount = $remaining;
                $order->extra_paid = $extraPaid;

                // ✅ Format date and add biller info
                $order->created_date = $order->created_at
                    ? $order->created_at->format('d-M-Y h:i A')
                    : null;

                // ✅ Biller logic
                if ($order->created_by && $order->creator) {
                    $order->biller = $order->creator->name;
                } else {
                    $order->biller = 'Admin';
                }

                // ✅ Invoice URL
                $order->invoice_pdf_url = url("/sales/invoice/pdf/" . $order->id);

                return $order;
            });

            // ✅ Get currency settings (optimized)
            $branchIdForSettings = $this->resolveSalesSettingsBranchId($user, $selectedSubAdminID);

            $settings = DB::table('settings')->where('branch_id', $branchIdForSettings)->first();

            $currencySymbol = $settings->currency_symbol ?? '₹';
            $currencyPosition = $settings->currency_position ?? 'left';
            $financialYearEnabled = (bool) ($settings->financial_year ?? true);

            return response()->json([
                'status' => true,
                'data' => $orders,
                'currency_symbol' => $currencySymbol,
                'currency_position' => $currencyPosition,
                'financial_year_enabled' => $financialYearEnabled,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
                'currency_symbol' => '₹',
                'currency_position' => 'left',
            ], 500);
        }
    }

    public function getHistory1($order_id)
    {
        $history = PaymentStore::where('order_id', $order_id)
            ->where('isDeleted', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        $order = Order::findOrFail($order_id);

        $totalPaid = $history->sum('payment_amount');

        // Calculate Return Amount
        $returnAmount = \App\Models\SalesReturn::where('order_id', $order_id)->sum('total_amount');

        // ✅ Dynamic calculation (NO column)
        $extraPaid = max(0, $totalPaid - ($order->total_amount - $returnAmount));
        $remaining = max(0, ($order->total_amount - $returnAmount) - $totalPaid);

        return response()->json([
            'status' => 'success',
            'data' => $history,
            'summary' => [
                'order_total' => $order->total_amount,
                'total_paid' => $totalPaid,
                'return_amount' => $returnAmount,
                'extra_paid' => $extraPaid,
                'remaining' => $remaining,
            ],
        ]);
    }

    public function exportOrders(Request $request)
    {
        // $user = auth()->user();
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $userId = $user->id;
        $role = $user->role;
        // $selectedSubAdminId = $request->query('selectedSubAdminId');
        // Sub-admin id (from dropdown or session fallback)
        $selectedSubAdminId = $request->query('selectedSubAdminId') ?? session('selectedSubAdminId') ?? $user->id;

        $year = $request->query('year');
        $month = $request->query('month');
        $date = $request->query('date');
        $customerId = $request->query('customerId');
        $formatCurrency = $request->query('format_currency');

        $settings = DB::table('settings')->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        try {
            $query = Order::with(['user:id,name,phone'])
                ->where('isDeleted', 0);
            // ->where('type', 'Sales');

            // 🔹 Year filter
            if (!empty($year)) {
                $query->whereYear('created_at', $year);
            }

            // 🔹 Month filter
            if (!empty($month)) {
                $query->whereMonth('created_at', $month);
            }

            // 🔹 Exact Date filter
            if (!empty($date)) {
                // Convert DD-MM-YYYY to YYYY-MM-DD
                $dateParts = explode('-', $date);
                if (count($dateParts) === 3) {
                    $formattedDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
                    $query->whereDate('created_at', $formattedDate);
                }
            }

            // 🔹 Customer filter
            if ($request->filled('customerId')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', $request->customerId);
                });
            }

            // 🔹 Role wise filter
            if ($role === 'sub-admin') {
                $query->where('branch_id', $userId);
            } elseif ($role === 'admin' && $selectedSubAdminId) {
                $query->where('branch_id', $selectedSubAdminId);
            } elseif ($role === 'staff') {
                $query->where('created_by', $userId);
            } else {
                $query->where('branch_id', $userId);
            }

            $orders = $query->orderBy('created_at', 'desc')->get();

            $formatIndian = function ($num) {
                $num = (float)$num;
                $explode = explode(".", number_format($num, 2, '.', ''));
                $whole = $explode[0];
                $decimal = $explode[1];

                $lastThree = substr($whole, -3);
                $restUnits = substr($whole, 0, -3);
                if ($restUnits != '') {
                    $restUnits = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $restUnits);
                    $whole = $restUnits . "," . $lastThree;
                }
                return $whole . "." . $decimal;
            };

            // Generate Excel same as before...
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'Order Number');
            $sheet->setCellValue('B1', 'Date');
            $sheet->setCellValue('C1', 'Customer Name');
            $sheet->setCellValue('D1', 'Total Amount');
            $sheet->setCellValue('E1', 'Remaining Amount');
            $sheet->setCellValue('F1', 'Payment Status');
            $sheet->setCellValue('G1', 'Payment Method');
            $sheet->getStyle('A1:G1')->getFont()->setBold(true);

            $row = 2;
            foreach ($orders as $order) {
                $sheet->setCellValue('A' . $row, $order->order_number ?? 'N/A');
                $sheet->setCellValue('B' . $row, $order->created_at->format('d-m-Y'));
                $sheet->setCellValue('C' . $row, $order->user->name ?? 'N/A');
                // $sheet->setCellValue('D' . $row, $order->total_amount ?? 0);
                // $sheet->setCellValue('E' . $row, $order->remaining_amount ?? 0);
                if ($formatCurrency === 'indian') {
                    $sheet->setCellValue('D' . $row, $formatIndian($order->total_amount ?? 0));
                    $sheet->setCellValue('E' . $row, $formatIndian($order->remaining_amount ?? 0));

                    // Set these columns as text to preserve the formatting
                    $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
                    $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
                } else {
                    $sheet->setCellValue('D' . $row, $order->total_amount ?? 0);
                    $sheet->setCellValue('E' . $row, $order->remaining_amount ?? 0);

                    // Set as number format
                    $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                }
                $sheet->setCellValue('F' . $row, $order->payment_status ?? 'N/A');
                $sheet->setCellValue('G' . $row, $order->payment_method ?? 'N/A');
                $row++;
            }
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // $writer   = new Xlsx($spreadsheet);
            // $fileName = 'Sales_' . date('Ymd_His') . '.xlsx';

            // return response()->streamDownload(function () use ($writer) {
            //     $writer->save('php://output');
            // }, $fileName, [
            //     'Content-Type'                  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            //     'Access-Control-Expose-Headers' => 'Content-Disposition',
            // ]);

            // Save to public storage
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'Sales_' . date('Ymd_His') . '.xlsx';
            $relativePath = 'exports/' . $filename;

            // Save temporary file
            $temp_file = tempnam(sys_get_temp_dir(), 'excel');
            $writer->save($temp_file);
            Storage::disk('public')->put($relativePath, file_get_contents($temp_file));
            unlink($temp_file);

            // Generate public URL

            $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

            return response()->json([
                'status' => true,
                'message' => 'Sales Excel generated successfully.',
                'file_url' => $fileUrl,
                'file_name' => $filename,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function salse_invoice_pdf_download($id)
    {
        $view_id = $id;
        $sales = Order::find($view_id);
        $authUser = Auth::guard('api')->user();
        $subAdminId = session('selectedSubAdminId') ?? $authUser->id;
        $setting = Setting::where('branch_id', $subAdminId)->first();

        if (!$sales) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $labourItems = Sales_Labour_Items::where('order_id', $id)
            ->with('labourItem')
            ->get();
        // dd($labourItems);

        $labourCost = 0;
        if ($labourItems && $labourItems->isNotEmpty()) {
            foreach ($labourItems as $labourItem) {
                $labourCost += ($labourItem->qty ?? 0) * ($labourItem->price ?? 0);
            }
        }

        // Fetch user data (order belongs to which user)
        $user = $sales->user_id ? User::find($sales->user_id) : null;

        // Helper for currency formatting
        $formatCurrency = function ($amount) use ($setting) {
            $amount = number_format($amount, 2);
            return $setting->currency_position === 'right'
                ? $amount . $setting->currency_symbol
                : $setting->currency_symbol . $amount;
        };

        // ✅ Subtotal
        $orderItems = OrderItem::where('order_id', $view_id)->get();
        $subtotal = $orderItems->sum('total_amount');

        // ✅ Discount
        $discountPercent = $sales->discount ?? 0;
        $discountAmount = ($discountPercent / 100) * $subtotal;
        $afterDiscount = $subtotal - $discountAmount;

        // ✅ Tax calculation
        $taxDetails = [];
        $totalTaxAmount = 0;
        if ($sales->gst_option === 'with_gst') {
            $taxIds = json_decode($sales->tax_id, true) ?? [];
            if (!empty($taxIds)) {
                $taxes = TaxRate::whereIn('id', $taxIds)->get();
                foreach ($taxes as $tax) {
                    $taxAmount = ($tax->tax_rate / 100) * $afterDiscount;
                    $totalTaxAmount += $taxAmount;
                    $taxDetails[] = [
                        'name' => $tax->tax_name,
                        'rate' => $tax->tax_rate,
                        'amount' => $taxAmount,
                        'formatted_amount' => $formatCurrency($taxAmount),
                    ];
                }
            }
        }

        // ✅ Final total
        $finalTotal =
            $afterDiscount +
            $totalTaxAmount +
            $labourCost;

        $paidAmount    = $sales->paid_amount ?? 0;
        $pendingAmount = $finalTotal - $paidAmount;

        // ✅ Prepare formatted values
        $formattedSubtotal = $formatCurrency($subtotal);
        $formattedDiscountAmount = $formatCurrency($discountAmount);
        $formattedAfterDiscount = $formatCurrency($afterDiscount);
        $formattedLabourCost = $formatCurrency($labourCost);
        $formattedFinalTotal = $formatCurrency($finalTotal);
        $formattedPaidAmount = $formatCurrency($paidAmount);
        $formattedPendingAmount = $formatCurrency($pendingAmount);

        // ✅ Prepare data for PDF view
        $pdfData = [
            'view_id' => $view_id,
            'sales' => $sales,
            'setting' => $setting,
            'orderItems' => $orderItems,
            'salesItems' => $orderItems,
            'labourItems' => $labourItems,
            'customer' => [
                'name' => $user->name ?? 'walk-in-customer',
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? '',
                'address' => optional($user->userDetail)->address ?? 'arga',
            ],
            'subtotal' => $formattedSubtotal,
            'discountPercent' => $discountPercent,
            'discountAmount' => $formattedDiscountAmount,
            'afterDiscount' => $formattedAfterDiscount,
            'labourCost' => $formattedLabourCost,
            'finalTotal' => $formattedFinalTotal,
            'paidAmount' => $formattedPaidAmount,
            'pendingAmount' => $formattedPendingAmount,
            'taxDetails' => $taxDetails,
        ];

        // ========== NEW PART: INVOICE SIZE SELECTION ==========
        // Determine which view to use based on the saved invoice size
        // ========== INVOICE SIZE SELECTION ==========
        if ($setting && $setting->invoice_size === 'small') {

            // 80mm Thermal Paper Size
            // 1mm = 2.83465 points
            // 80mm = 226.77 pt

            $customPaper = [0, 0, 226.77, 1000]; // width, height(auto large)

            $pdf = PDF::loadView('sales.salse-invoice-small-pdf', $pdfData)
                ->setPaper($customPaper, 'portrait');
        } else {

            // Normal A4 Invoice
            $pdf = PDF::loadView('sales.salse-invoice-pdf', $pdfData)
                ->setPaper('A4', 'portrait');
        }
        // ============================================
        // =======================================================


        // ✅ Generate PDF
        // $pdf = PDF::loadView('sales.salse-invoice-pdf', $pdfData);

        // ✅ Save PDF to storage (public folder)
        $fileName = 'invoice_' . $view_id . '.pdf';
        // $filePath = '/storage/app/public/sales-invoices/' . $fileName;

        // Ensure directory exists
        // if (!file_exists(storage_path('/storage/app/public/sales-invoices/'))) {
        //     mkdir(storage_path('/storage/app/public/sales-invoices/'), 0777, true);
        // }

        $relativePath = 'sales-invoices/' . $fileName;
        Storage::disk('public')->put($relativePath, $pdf->output());

        // Public URL

        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        return response()->json([
            'status' => true,
            'message' => 'Sales Invoice PDF generated successfully.',
            'file_url' => $fileUrl,
            'file_name' => $fileName,
        ]);
    }

    public function updateStaff(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found.'], 404);
        }
        $order->staff_id = $request->staff_id ?: null;
        $order->save();
        return response()->json(['status' => true, 'message' => 'Staff assigned successfully.']);
    }

    public function updateOrderType(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found.'], 404);
        }
        $order->order_type = $request->order_type;
        $order->save();
        return response()->json(['status' => true, 'message' => 'Order type updated successfully.']);
    }

    public function exportOrdersPDF(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $userId = $user->id;
        $role = $user->role;
        $userBranchId = $user->branch_id;
        $selectedSubAdminId = $request->query('selectedSubAdminId') ?? $userId;

        // 🔹 Fetch branch-wise setting
        // $setting = Setting::where('branch_id', $selectedSubAdminId)->first();
        if ($role === 'staff' && $userBranchId) {
            $branchIdToUse = $userBranchId;
        } elseif ($role === 'admin' && !empty($selectedSubAdminId)) {
            $branchIdToUse = $selectedSubAdminId;
        } elseif ($role === 'sub-admin') {
            $branchIdToUse = $userId;
        } else {
            $branchIdToUse = $userId;
        }

        // 🔹 Fetch settings branch-wise
        $setting = Setting::where('branch_id', $branchIdToUse)->first();

        try {
            $query = Order::with(['user:id,name,phone'])
                ->where('isDeleted', 0);

            // 🔹 Apply filters
            if ($request->filled('year')) {
                $query->whereYear('created_at', $request->year);
            }

            if ($request->filled('month')) {
                $query->whereMonth('created_at', $request->month);
            }

            if ($request->filled('date')) {
                $inputDate = trim($request->date);
                $formattedDate = preg_match('/^\d{2}-\d{2}-\d{4}$/', $inputDate)
                    ? implode('-', array_reverse(explode('-', $inputDate)))
                    : $inputDate;
                $query->whereDate('created_at', $formattedDate);
            }
            if ($request->filled('customerId')) {
                $query->whereHas('user', fn($q) => $q->where('name', $request->customerId));
            }

            // 🔹 Role-based branch filtering
            if ($role === 'sub-admin') {
                $query->where('branch_id', $userId);
            } elseif ($role === 'admin' && $selectedSubAdminId) {
                $query->where('branch_id', $selectedSubAdminId);
            } elseif ($role === 'staff') {
                $query->where('created_by', $userId);
            } else {
                $query->where('branch_id', $userId);
            }

            $orders = $query->orderBy('created_at', 'desc')->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No orders found for the given filters.',
                ], 404);
            }

            // 🔹 Generate PDF
            $pdf = Pdf::loadView('sales.orders_pdf', [
                'orders' => $orders,
                'setting' => $setting,
            ])->setPaper('A4', 'landscape');

            // 🔹 Save PDF to storage
            $fileName = 'sales_report_' . now()->format('Ymd_His') . '.pdf';
            $relativePath = 'sales-reports/' . $fileName;
            Storage::disk('public')->put($relativePath, $pdf->output());

            // 🔹 Generate full URL

            $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

            // 🔹 Return JSON response
            return response()->json([
                'status' => true,
                'message' => 'Sales report PDF generated successfully.',
                'file_url' => $fileUrl,
                'file_name' => $fileName,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while generating PDF.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function export_sales_report_pdf_api(Request $request)
    {
        try {
            $authUser = Auth::guard('api')->user();
            $selectedSubAdminId = $request->selectedSubAdminId ?? null;

            // ✅ OPTIMIZED: Determine branch_id based on role
            if ($authUser->role === 'staff' && $authUser->branch_id) {
                $branchIdToUse = $authUser->branch_id;
            } elseif ($authUser->role === 'admin' && !empty($selectedSubAdminId)) {
                $branchIdToUse = $selectedSubAdminId;
            } else {
                $branchIdToUse = $authUser->id;
            }

            $idsArray = $request->input('ids', []);
            $idsString = implode(',', $idsArray);

            if (empty($idsArray)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No sales IDs provided.',
                ]);
            }

            // ✅ OPTIMIZED: Eager load all relationships in single query
            $sales = OrderItem::with([
                'product:id,name,price,category_id',
                'product.category:id,name',
                'invoice:id,order_id,discount,gst_option,tax_id',
                'user:id,name',
                'user.userDetail:id,user_id,address',
            ])
                ->whereIn('id', $idsArray)
                ->get();

            if ($sales->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No sales data found.',
                ]);
            }

            // ✅ OPTIMIZED: Cache settings
            $setting = cache()->remember("setting_branch_{$branchIdToUse}", 300, function () use ($branchIdToUse) {
                return Setting::select('name', 'currency_symbol', 'currency_position')
                    ->where('branch_id', $branchIdToUse)
                    ->first();
            });

            $currencySymbol = $setting->currency_symbol ?? '₹';
            $currencyPosition = $setting->currency_position ?? 'left';

            // ✅ OPTIMIZED: Collect all unique tax IDs first (single query instead of N queries)
            $allTaxIds = [];
            $invoices = [];

            foreach ($sales as $sale) {
                if ($sale->invoice) {
                    $invoiceId = $sale->invoice->id;
                    if (!isset($invoices[$invoiceId])) {
                        $invoices[$invoiceId] = $sale->invoice;
                        $rowTaxIds = json_decode($sale->invoice->tax_id ?? '[]', true) ?: [];
                        if (!empty($rowTaxIds) && ($sale->invoice->gst_option ?? 'without_gst') === 'with_gst') {
                            $allTaxIds = array_merge($allTaxIds, $rowTaxIds);
                        }
                    }
                }
            }

            // ✅ OPTIMIZED: Load all tax rates in ONE query
            $allTaxIds = array_unique($allTaxIds);
            $taxRatesMap = [];
            if (!empty($allTaxIds)) {
                $taxRates = TaxRate::where('status', 'active')
                    ->where('branch_id', $branchIdToUse)
                    ->where('isDeleted', 0)
                    ->whereIn('id', $allTaxIds)
                    ->get(['id', 'tax_name', 'tax_rate'])
                    ->keyBy('id');

                $taxRatesMap = $taxRates->toArray();
            }

            // ✅ OPTIMIZED: Single pass calculation
            $subtotal = 0;
            $discountAmount = 0;
            $taxDetails = [];
            $totalTaxAmount = 0;

            foreach ($sales as $sale) {
                $subtotal += $sale->total_amount;

                // Discount calculation
                if ($sale->invoice && $sale->invoice->discount) {
                    $discountPercent = $sale->invoice->discount;
                    $discountAmount += ($sale->total_amount * $discountPercent) / 100;
                }

                // Tax calculation (using pre-loaded tax rates)
                $rowTaxAmount = 0;
                $unitPrice = $sale->price;

                // Apply discount per unit
                if ($sale->invoice && $sale->invoice->discount) {
                    $discountPercent = $sale->invoice->discount;
                    $unitPrice -= ($unitPrice * $discountPercent) / 100;
                }

                if ($sale->invoice && ($sale->invoice->gst_option ?? 'without_gst') === 'with_gst') {
                    $rowTaxIds = json_decode($sale->invoice->tax_id ?? '[]', true) ?: [];

                    foreach ($rowTaxIds as $taxId) {
                        if (isset($taxRatesMap[$taxId])) {
                            $tax = $taxRatesMap[$taxId];
                            $taxBase = $unitPrice * $sale->quantity;
                            $amount = $taxBase * ($tax['tax_rate'] / 100);
                            $rowTaxAmount += $amount;

                            if (!isset($taxDetails[$taxId])) {
                                $taxDetails[$taxId] = [
                                    'name' => $tax['tax_name'],
                                    'rate' => $tax['tax_rate'],
                                    'amount' => 0,
                                ];
                            }
                            $taxDetails[$taxId]['amount'] += $amount;
                        }
                    }
                }

                $sale->rowFinalTotal = ($unitPrice * $sale->quantity) + $rowTaxAmount;
                $totalTaxAmount += $rowTaxAmount;
            }

            $subtotalAfterDiscount = $subtotal - $discountAmount;
            $totalAmount = $sales->sum('rowFinalTotal');

            // 🔹 Prepare PDF data
            $pdfData = [
                'sales' => $sales,
                'setting' => $setting,
                'currencySymbol' => $currencySymbol,
                'currencyPosition' => $currencyPosition,
                'subtotal' => $subtotal,
                'discountAmount' => $discountAmount,
                'taxDetails' => $taxDetails,
                'totalTaxAmount' => $totalTaxAmount,
                'totalAmount' => $totalAmount,
            ];

            // 🔹 Generate PDF
            $pdf = PDF::loadView('sales.sales-invoice-report-pdf', $pdfData)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                ]);

            // 🔹 Save PDF file in storage
            $fileName = 'sales_report_' . now()->format('Ymd_His') . '.pdf';
            $relativePath = 'sales-reports/' . $fileName;

            Storage::disk('public')->put($relativePath, $pdf->output());

            // 🔹 Get full URL
            $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

            return response()->json([
                'status' => true,
                'message' => 'Sales Report PDF generated successfully.',
                'file_url' => $fileUrl,
                'file_name' => $fileName,
                'ids_used' => $idsString, // 👈 for debug
            ]);
        } catch (\Throwable $e) {
            Log::error('Sales PDF Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to generate Sales Report PDF.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function view_sales_report(Request $request)
    {
        try {
            $ids = $request->input('ids'); // can be array or comma-separated string
            $selectedSubAdminId = $request->input('selectedSubAdminId');

            if (empty($ids)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No sales selected for report view.',
                ], 400);
            }

            // Convert to array if comma-separated
            $idsArray = is_array($ids) ? $ids : explode(',', $ids);

            // 🔹 Store IDs in a comma-separated string for URL
            $idsString = implode(',', $idsArray);

            // 🔹 Determine branch_id (safe fallback)
            // If user is logged in, we can still read role info
            $authUser = Auth::guard('api')->user();
            if ($authUser) {
                if ($authUser->role === 'staff' && $authUser->branch_id) {
                    $branchIdToUse = $authUser->branch_id;
                } elseif ($authUser->role === 'admin' && !empty($selectedSubAdminId)) {
                    $branchIdToUse = $selectedSubAdminId;
                } else {
                    $branchIdToUse = $authUser->id;
                }
            } else {
                // 🔹 No authentication — require branch ID from frontend
                if (!empty($selectedSubAdminId)) {
                    $branchIdToUse = $selectedSubAdminId;
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Branch ID missing (unauthenticated request).',
                    ], 400);
                }
            }

            // ✅ Generate the report URL
            // $reportUrl = url('sales/report/view-page?ids=' . urlencode($idsString) . '&branch=' . $branchIdToUse);
            $reportUrl = url('sales/report/view-page?ids=' . $idsString . '&branch=' . $branchIdToUse);

            return response()->json([
                'status' => true,
                'message' => 'Sales report link generated successfully.',
                'view_link' => $reportUrl,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to generate sales report link.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function exportTdsOrderReportPdf(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated access'], 401);
        }

        $query = $this->buildTdsOrderReportQuery($request, $user);
        $summary = $this->getTdsOrderReportSummary($query);
        $orders = (clone $query)->latest('id')->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No TDS records found for the selected filters.',
            ], 404);
        }

        $branchId = $this->resolveBranchIdForTdsReport($user, $request->input('selectedSubAdminId'));
        $setting = Setting::where('branch_id', $branchId)->first() ?? Setting::first();
        [$currencySymbol, $currencyPosition] = $this->getTdsCurrencySettings($setting);

        $customerLabel = 'All Customers';
        if ($request->filled('customer_id')) {
            $customer = User::select('name')->find((int) $request->query('customer_id'));
            $customerLabel = $customer->name ?? 'Selected Customer';
        }

        $reportFilters = [
            'date' => $this->getTdsDateFilterLabel($request->query('filter')),
            'month' => $this->getTdsMonthLabel($request->query('month')),
            'year' => $this->getTdsYearLabel($request->query('year')),
            'customer' => $customerLabel,
            'search' => trim((string) $request->query('search', '')),
        ];

        $reportRows = $orders
            ->map(fn($order) => $this->transformTdsOrderRow($order))
            ->values();

        $pdf = Pdf::loadView('sales.tds_report_pdf', [
            'orders' => $reportRows,
            'summary' => $summary,
            'settings' => $setting,
            'currencySymbol' => $currencySymbol,
            'currencyPosition' => $currencyPosition,
            'reportFilters' => $reportFilters,
            'generatedAt' => now()->format('d-m-Y h:i A'),
        ])->setPaper('A4', 'portrait');

        $fileName = 'tds_report_' . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName, [
            'Content-Type' => 'application/pdf',
            'Access-Control-Expose-Headers' => 'Content-Disposition',
        ]);
    }

    public function exportTdsOrderReportExcel(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated access'], 401);
        }

        $query = $this->buildTdsOrderReportQuery($request, $user);
        $summary = $this->getTdsOrderReportSummary($query);
        $orders = (clone $query)->latest('id')->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No TDS records found for the selected filters.',
            ], 404);
        }

        $branchId = $this->resolveBranchIdForTdsReport($user, $request->input('selectedSubAdminId'));
        $setting = Setting::where('branch_id', $branchId)->first() ?? Setting::first();
        [$currencySymbol, $currencyPosition] = $this->getTdsCurrencySettings($setting);

        $formatAmount = function (float $value) use ($currencySymbol, $currencyPosition): string {
            $formatted = number_format($value, 2, '.', ',');
            return $currencyPosition === 'right' ? $formatted . $currencySymbol : $currencySymbol . $formatted;
        };

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'TDS Report');
        $sheet->setCellValue('A2', 'Generated On: ' . now()->format('d-m-Y h:i A'));
        $sheet->setCellValue(
            'A3',
            'Filters: ' .
                $this->getTdsDateFilterLabel($request->query('filter')) . ' | ' .
                $this->getTdsMonthLabel($request->query('month')) . ' | ' .
                $this->getTdsYearLabel($request->query('year'))
        );

        $headers = ['Sr No', 'Order No.', 'Order Date', 'Customer', 'Total Amount', 'TDS %', 'TDS Amount', 'Payment Status'];
        $sheet->fromArray($headers, null, 'A5');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A5:H5')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A5:H5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF9F43');

        $row = 6;
        foreach ($orders as $index => $order) {
            $mapped = $this->transformTdsOrderRow($order);

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $mapped['order_number']);
            $sheet->setCellValue('C' . $row, $mapped['order_date']);
            $sheet->setCellValue('D' . $row, trim($mapped['customer_name'] . ' ' . $mapped['customer_phone']));
            $sheet->setCellValue('E' . $row, $formatAmount((float) $mapped['total_amount']));
            $sheet->setCellValue('F' . $row, $mapped['tds_percentage_display'] . '%');
            $sheet->setCellValue('G' . $row, $formatAmount((float) $mapped['tds_amount']));
            $sheet->setCellValue('H' . $row, ucfirst($mapped['payment_status'] ?: '-'));

            $sheet->getStyle('E' . $row)->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            $sheet->getStyle('G' . $row)->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

            $row++;
        }

        $summaryRow = $row + 1;
        $sheet->setCellValue('A' . $summaryRow, 'Total Orders');
        $sheet->setCellValue('B' . $summaryRow, (int) $summary['total_orders']);
        $sheet->setCellValue('D' . $summaryRow, 'Total TDS Amount');
        $sheet->setCellValue('E' . $summaryRow, $formatAmount((float) $summary['total_tds_amount']));
        $sheet->setCellValue('G' . $summaryRow, 'Avg TDS %');
        $sheet->setCellValue('H' . $summaryRow, number_format((float) $summary['average_tds_percentage'], 2) . '%');
        $sheet->getStyle('A' . $summaryRow . ':H' . $summaryRow)->getFont()->setBold(true);

        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $fileName = 'tds_report_' . now()->format('Ymd_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Access-Control-Expose-Headers' => 'Content-Disposition',
        ]);
    }

    public function tdsOrderReport(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated access'], 401);
        }

        $query = $this->buildTdsOrderReportQuery($request, $user);
        $summary = $this->getTdsOrderReportSummary($query);

        $perPage = (int) $request->input('per_page', 10);
        $perPage = max(10, min($perPage, 100));

        $ordersPaginated = $query->latest('id')->paginate($perPage);
        $orders = collect($ordersPaginated->items())
            ->map(fn($order) => $this->transformTdsOrderRow($order))
            ->values();

        return response()->json([
            'status' => true,
            'data' => $orders,
            'summary' => $summary,
            'pagination' => [
                'current_page' => $ordersPaginated->currentPage(),
                'last_page' => $ordersPaginated->lastPage(),
                'per_page' => $ordersPaginated->perPage(),
                'total' => $ordersPaginated->total(),
                'from' => $ordersPaginated->firstItem(),
                'to' => $ordersPaginated->lastItem(),
                'next_page_url' => $ordersPaginated->nextPageUrl(),
                'prev_page_url' => $ordersPaginated->previousPageUrl(),
            ],
        ]);
    }

    private function transformTdsOrderRow($order): array
    {
        $tdsPercentage = (float) ($order->tds_percentage ?? 0);
        $tdsAmount = (float) ($order->tds_amount ?? 0);
        $totalAmount = (float) ($order->total_amount ?? 0);

        return [
            'id' => $order->id,
            'order_number' => $order->order_number ?: ('ORD-' . $order->id),
            'order_date' => optional($order->created_at)->format('d-m-Y'),
            'customer_name' => optional($order->user)->name ?? 'Walk-in Customer',
            'customer_phone' => optional($order->user)->phone ?? '',
            'payment_status' => (string) ($order->payment_status ?? ''),
            'total_amount' => round($totalAmount, 2),
            'total_amount_display' => number_format($totalAmount, 2),
            'tds_percentage' => round($tdsPercentage, 2),
            'tds_percentage_display' => number_format($tdsPercentage, 2),
            'tds_amount' => round($tdsAmount, 2),
            'tds_amount_display' => number_format($tdsAmount, 2),
            'invoice_url' => route('sales.invoice', $order->id),
        ];
    }

    private function resolveBranchIdForTdsReport($user, $selectedSubAdminId = null): int
    {
        if ($user->role === 'staff') {
            return (int) ($user->branch_id ?? 0);
        }

        if ($user->role === 'admin' && !empty($selectedSubAdminId)) {
            return (int) $selectedSubAdminId;
        }

        return (int) $user->id;
    }

    private function getTdsDateFilterLabel(?string $filter): string
    {
        $labels = [
            'this_week' => 'This Week',
            'this_month' => 'This Month',
            'last_6_months' => 'Last 6 Months',
            'this_year' => 'This Year',
            'previous_year' => 'Previous Year',
        ];

        return $labels[$filter] ?? 'All Time';
    }

    private function getTdsMonthLabel($month): string
    {
        if (empty($month)) {
            return 'All Months';
        }

        $monthNumber = (int) $month;
        if ($monthNumber < 1 || $monthNumber > 12) {
            return 'All Months';
        }

        return Carbon::createFromDate(null, $monthNumber, 1)->format('F');
    }

    private function getTdsYearLabel($year): string
    {
        return !empty($year) ? (string) $year : 'All Years';
    }

    private function getTdsCurrencySettings(?Setting $setting): array
    {
        $symbol = trim(html_entity_decode($setting->currency_symbol ?? '&#8377;', ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $position = $setting->currency_position ?? 'left';

        return [$symbol, $position];
    }

    private function buildTdsOrderReportQuery(Request $request, $user)
    {
        $userId = $user->id;
        $role = $user->role;
        $filter = $request->query('filter');
        $month = $request->query('month');
        $year = $request->query('year');
        $customerId = $request->query('customer_id');
        $search = trim((string) $request->query('search', ''));
        $selectedSubAdminId = $request->input('selectedSubAdminId');
        $branchIdForNonStaff = $this->resolveBranchIdForTdsReport($user, $selectedSubAdminId);

        $query = Order::with(['user:id,name,phone'])
            ->where('isDeleted', 0)
            ->where(function ($q) {
                $q->where('tds_amount', '>', 0)
                    ->orWhere('tds_percentage', '>', 0);
            });

        if ($role === 'staff') {
            $query->where('created_by', $userId);
        } else {
            $query->where('branch_id', $branchIdForNonStaff);
        }

        if ($filter) {
            $today = Carbon::today();

            switch ($filter) {
                case 'this_week':
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', $today->month)->whereYear('created_at', $today->year);
                    break;
                case 'last_6_months':
                    $query->whereBetween('created_at', [Carbon::now()->subMonths(6), Carbon::now()]);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', $today->year);
                    break;
                case 'previous_year':
                    $query->whereYear('created_at', Carbon::now()->subYear()->year);
                    break;
            }
        }

        if ($month) {
            $query->whereMonth('created_at', $month);
        }

        if ($year) {
            $query->whereYear('created_at', $year);
        }

        if ($customerId) {
            $query->where('user_id', $customerId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                    ->orWhere('po_number', 'LIKE', "%{$search}%")
                    ->orWhere('payment_status', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
                    });

                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        return $query;
    }

    private function getTdsOrderReportSummary($query): array
    {
        $summary = (clone $query)
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('SUM(COALESCE(total_amount, 0)) as total_order_amount')
            ->selectRaw('SUM(COALESCE(tds_amount, 0)) as total_tds_amount')
            ->selectRaw('AVG(CASE WHEN COALESCE(tds_percentage, 0) > 0 THEN tds_percentage END) as average_tds_percentage')
            ->first();

        return [
            'total_orders' => (int) ($summary->total_orders ?? 0),
            'total_order_amount' => round((float) ($summary->total_order_amount ?? 0), 2),
            'total_tds_amount' => round((float) ($summary->total_tds_amount ?? 0), 2),
            'average_tds_percentage' => round((float) ($summary->average_tds_percentage ?? 0), 2),
        ];
    }

    public function todayDeliveries(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated access',
                'data' => [],
            ], 401);
        }

        $selectedSubAdminId = $request->query('selectedSubAdminId');
        $branchId = $this->resolveSalesSettingsBranchId($user, $selectedSubAdminId);
        $today = Carbon::today('Asia/Kolkata');

        $query = Order::with(['user:id,name,phone', 'creator:id,name'])
            ->where('isDeleted', 0)
            ->whereDate('created_at', $today);

        if ($user->role === 'staff') {
            $query->where('created_by', $user->id);
        } else {
            $query->where('branch_id', $branchId);
        }

        $orders = $query->orderBy('created_at', 'desc')->get()->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => optional($order->user)->name ?? 'N/A',
                'customer_phone' => optional($order->user)->phone ?? 'N/A',
                'total_amount' => number_format((float) ($order->total_amount ?? 0), 2, '.', ''),
                'payment_status' => $order->payment_status ?? 'N/A',
                'assigned_staff' => optional($order->creator)->name ?? 'Unassigned',
            ];
        })->values();

        return response()->json([
            'status' => true,
            'data' => $orders,
        ]);
    }

    public function pendingEmis(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated access',
                'data' => [],
            ], 401);
        }

        $selectedSubAdminId = $request->query('selectedSubAdminId');
        $branchId = $this->resolveSalesSettingsBranchId($user, $selectedSubAdminId);

        $query = Order::with(['user:id,name,phone'])
            ->where('isDeleted', 0)
            ->where('payment_method', 'emi')
            ->where(function ($q) {
                $q->whereNull('remaining_amount')
                  ->orWhere('remaining_amount', '>', 0);
            });

        if ($user->role === 'staff') {
            $query->where('created_by', $user->id);
        } else {
            $query->where('branch_id', $branchId);
        }

        $today = Carbon::today('Asia/Kolkata');
        $orders = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) use ($today) {
                $pendingDate = $order->created_at;

                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => optional($order->user)->name ?? 'N/A',
                    'customer_phone' => optional($order->user)->phone ?? 'N/A',
                    'total_amount' => number_format((float) ($order->total_amount ?? 0), 2, '.', ''),
                    'remaining_amount' => number_format((float) ($order->remaining_amount ?? 0), 2, '.', ''),
                    'emi_monthly_amount' => number_format((float) ($order->emi_monthly_amount ?? 0), 2, '.', ''),
                    'emi_month_label' => $pendingDate ? Carbon::parse($pendingDate)->timezone('Asia/Kolkata')->format('d M Y') : 'N/A',
                    'next_pending_date' => $pendingDate ? Carbon::parse($pendingDate)->timezone('Asia/Kolkata')->format('d M Y') : 'N/A',
                    'is_overdue' => $pendingDate ? Carbon::parse($pendingDate)->lt($today) : false,
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'data' => $orders,
        ]);
    }
}
