<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\BankMaster;
use App\Models\Category;
use App\Models\LabourItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentStore;
use App\Models\Sales_Labour_Items;
use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\Setting;
use App\Models\TaxRate;
use App\Models\User;
use App\Models\Delivery;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesController extends Controller
{
    private function fallbackSetting(?int $branchId = null): Setting
    {
        return Setting::where('branch_id', $branchId)->first()
            ?? Setting::first()
            ?? new Setting([
                'name' => 'Fablead Developer & Technolab',
                'email' => 'info@gmail.com',
                'phone' => 1234567890,
                'address' => 'Adajan Surat',
                'logo' => 'admin/assets/img/logo-image.jpg',
                'currency_symbol' => '₹',
                'currency_position' => 'left',
            ]);
    }

    public function sales_list(Request $request)
    {
        $user = auth()->user();
        $subAdminId = session('selectedSubAdminId');

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branchIdToUse = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $user->id;
        }

        $years = Order::where('isDeleted', 0)
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $banks = BankMaster::where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->get();

        $setting = $this->fallbackSetting($branchIdToUse);
        $financialYearEnabled = (bool) ($setting->financial_year ?? true);

        $staffs = \App\Models\User::where('role', 'staff')
            ->where('branch_id', $branchIdToUse)
            ->where('isDeleted', 0)
            ->get();

        return view('sales/saleslist', [
            'years' => $years,
            'banks' => $banks,
            'financialYearEnabled' => $financialYearEnabled,
            'financial_year_enabled' => $financialYearEnabled,
            'staffs' => $staffs,
        ]);
    }
      private function normalizeDeliveryStatus(?string $status): string
    {
        $normalized = strtolower(trim((string) $status));

        return match ($normalized) {
            'partial', 'partially', 'partially_delivered' => 'partially_delivered',
            'delivered' => 'delivered',
            'cancelled', 'canceled' => 'cancelled',
            default => 'pending',
        };
    }


    public function add_sales(Request $request)
    {

        return view('sales/add-sales');
    }
public function emiDetails($id)
{
    $order = Order::with('user')->findOrFail($id);

    $totalAmount = round((float) ($order->total_amount ?? 0), 2);
    $downPayment = round((float) ($order->emi_down_payment ?? 0), 2);
    $interestRate = round((float) ($order->emi_interest_rate ?? 0), 2);
    $tenure = max(0, (int) ($order->emi_tenure ?? 0));
    $loanAmount = round((float) ($order->emi_loan_amount ?? 0), 2);
    $monthlyEmi = round((float) ($order->emi_monthly_amount ?? 0), 2);

    if ($loanAmount <= 0) {
        $loanAmount = max($totalAmount - $downPayment, 0);
    }

    if ($monthlyEmi <= 0 && $tenure > 0) {
        $totalRepayment = $loanAmount + (($loanAmount * $interestRate) / 100);
        $monthlyEmi = round($totalRepayment / $tenure, 2);
    }

    if ($tenure <= 0 && $monthlyEmi > 0 && $loanAmount > 0) {
        $tenure = (int) ceil($loanAmount / $monthlyEmi);
    }

    $installmentTotal = round($monthlyEmi * $tenure, 2);

    $payments = PaymentStore::where('order_id', $order->id)
        ->where(function ($query) {
            $query->where('isDeleted', 0)->orWhereNull('isDeleted');
        })
        ->whereNotNull('emi_month')
        ->where(function ($query) {
            $query->where('payment_method', 'emi')
                ->orWhere('payment_type', 'emi');
        })
        ->orderBy('emi_month')
        ->get()
        ->keyBy(fn ($payment) => (int) $payment->emi_month);

    $months = [];
    $paidMonths = 0;
    $paidInstallmentTotal = 0;
    $nextDueMonth = null;

    for ($month = 1; $month <= $tenure; $month++) {
        $payment = $payments->get($month);
        $isPaid = (bool) $payment;

        if ($isPaid) {
            $paidMonths++;
            $paidInstallmentTotal += (float) $payment->payment_amount;
        } elseif ($nextDueMonth === null) {
            $nextDueMonth = $month;
        }

        $months[] = [
            'month' => $month,
            'label' => $this->ordinalMonth($month) . ' Month',
            'status' => $isPaid ? 'Paid' : 'Pending',
            'amount' => $isPaid ? (float) $payment->payment_amount : $monthlyEmi,
            'paid_on' => $payment && $payment->payment_date
                ? \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y')
                : '-',
            'remark' => $payment && $payment->remarks ? $payment->remarks : '-',
        ];
    }

    return response()->json([
        'order_number' => $order->order_number,
        'customer_name' => $order->user->name ?? 'N/A',
        'loan_amount' => $loanAmount,
        'emi_amount' => $monthlyEmi,
        'months' => $tenure,
        'total' => $installmentTotal,
        'total_amount' => $totalAmount,
        'down_payment' => $downPayment,
        'interest_rate' => $interestRate,
        'guarantor_name' => $order->emi_guarantor_name ?: 'N/A',
        'do_id' => $order->emi_do_id ?: 'N/A',
        'aadhar_number' => $order->emi_aadhar_number ?: 'N/A',
        'pan_number' => $order->emi_pan_number ?: 'N/A',
        'paid_months' => $paidMonths,
        'pending_months' => max($tenure - $paidMonths, 0),
        'paid_installment_total' => round($paidInstallmentTotal, 2),
        'pending_installment_total' => max(round($installmentTotal - $paidInstallmentTotal, 2), 0),
        'remaining_amount' => round((float) ($order->remaining_amount ?? 0), 2),
        'next_due_month' => $nextDueMonth,
        'next_due_label' => $nextDueMonth ? $this->ordinalMonth($nextDueMonth) . ' Month' : 'Completed',
        'month_details' => $months,
    ]);
}

   public function productsDelivery(Request $request)
    {
        $branchId = auth()->user()?->branch_id;
        $setting = $this->fallbackSetting($branchId);
        $status = trim((string) $request->query('status', ''));
        $orderNo = trim((string) $request->query('order_no', ''));
        $fromDate = trim((string) $request->query('from', ''));
        $toDate = trim((string) $request->query('to', ''));

        $query = Order::with(['creator:id,name', 'deliveries.deliveredBy'])
            ->where('isDeleted', '!=', 1)
            ->where('quotation_status', 'sales')
            ->where('order_type', 'delivery');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($orderNo !== '') {
            $query->where('order_number', 'like', '%' . $orderNo . '%');
        }

        if ($status !== '') {
            if ($status === 'partially_delivered') {
                $query->where(function ($q) {
                    $q->where('delivery_status', 'partial')
                      ->orWhere('delivery_status', 'partially_delivered');
                });
            } else {
                $query->where('delivery_status', $status);
            }
        }

        if ($fromDate !== '') {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate !== '') {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $orders = $query->orderByDesc('created_at')->get()->map(function ($order) {
            $latestDelivery = $order->deliveries->sortByDesc('created_at')->first();
            $status = $this->normalizeDeliveryStatus($latestDelivery?->status ?: $order->delivery_status);

            return (object) [
                'id' => $latestDelivery?->id,
                'order_id' => $order->id,
                'order' => $order,
                'status' => $status,
                'deliveredBy' => $latestDelivery?->deliveredBy ?: $order->creator,
                'delivered_by' => $latestDelivery?->delivered_by ?: $order->created_by,
                'has_delivery_record' => (bool) $latestDelivery,
            ];
        });

        return view('sales.products-delivery', compact('orders', 'setting', 'status', 'orderNo', 'fromDate', 'toDate'));
    }


      private function attachOrderCustomerDetails(Order $order): void
    {
        $orderUser = $order->relationLoaded('user')
            ? $order->user
            : ($order->user_id ? User::with('userDetail')->find($order->user_id) : null);

        if ($orderUser) {
            $order->customer_name       = $orderUser->name ?? 'Walk-in Customer';
            $order->customer_email      = $orderUser->email ?? '';
            $order->customer_phone      = $orderUser->phone ?? '';
            $order->customer_address    = optional($orderUser->userDetail)->address ?? '';
            $order->customer_city       = optional($orderUser->userDetail)->city ?? '';
            $order->customer_country    = optional($orderUser->userDetail)->country ?? '';
        } else {
            $order->customer_name       = $order->customer_name ?? 'Walk-in Customer';
            $order->customer_email      = $order->customer_email ?? '';
            $order->customer_phone      = $order->customer_phone ?? '';
            $order->customer_address    = $order->customer_address ?? '';
            $order->customer_city       = $order->customer_city ?? '';
            $order->customer_country    = $order->customer_country ?? '';
        }
    }


      public function productsDeliveryData(Request $request)
    {
        $branchId = auth()->user()?->branch_id;
        $setting = $this->fallbackSetting($branchId);
        $status = trim((string) $request->query('status', ''));
        $orderNo = trim((string) $request->query('order_no', ''));
        $fromDate = trim((string) $request->query('from', ''));
        $toDate = trim((string) $request->query('to', ''));

        $query = Order::with(['creator:id,name', 'deliveries.deliveredBy', 'user.userDetail'])
            ->where('isDeleted', '!=', 1)
            ->where('quotation_status', 'sales')
            ->where('order_type', 'delivery');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($orderNo !== '') {
            $query->where('order_number', 'like', '%' . $orderNo . '%');
        }

        if ($status !== '') {
            if ($status === 'partially_delivered') {
                $query->where(function ($q) {
                    $q->where('delivery_status', 'partial')
                        ->orWhere('delivery_status', 'partially_delivered');
                });
            } else {
                $query->where('delivery_status', $status);
            }
        }

        if ($fromDate !== '') {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate !== '') {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $orders = $query->orderByDesc('created_at')->get()->map(function ($order) {
            $latestDelivery = $order->deliveries->sortByDesc('created_at')->first();
            $status = $this->normalizeDeliveryStatus($latestDelivery?->status ?: $order->delivery_status);

            return [
                'id' => $latestDelivery?->id,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'created_at' => optional($order->created_at)->format('d-M-Y'),
                'customer_name' => $order->customer_name ?: ($order->user?->name ?? '--'),
                'total' => (float) ($order->grand_total ?? $order->total_amount ?? 0),
                'currency_symbol' => $setting->currency_symbol ?? '₹',
                'delivered_by' => $latestDelivery?->deliveredBy?->name ?? $latestDelivery?->delivered_by ?? $order->creator?->name ?? '--',
                'status' => $status,
                'has_delivery_record' => (bool) $latestDelivery,
                'pdf_url' => route('sales.delivery.challan.pdf', $order->id),
                'delivery_url' => route('sales.delivery', $order->id),
                'status_update_url' => $latestDelivery
                    ? route('sales.delivery.status.update', $latestDelivery->id)
                    : route('sales.order.delivery.status.update', $order->id),
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $orders,
        ]);
    }

private function ordinalMonth(int $month): string
{
    if (in_array($month % 100, [11, 12, 13], true)) {
        return $month . 'th';
    }

    return match ($month % 10) {
        1 => $month . 'st',
        2 => $month . 'nd',
        3 => $month . 'rd',
        default => $month . 'th',
    };
}
    public function edit_sales($id)
    {
        $user       = auth()->user();
        $subAdminId = session('selectedSubAdminId');

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branchIdToUse = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $user->id;
        }

        // 🔹 Load sales record - IMPORTANT: Load product_gst_details from order_items
        $sales = Order::with(['order_items' => function ($query) {
            $query->select(
                'id',
                'order_id',
                'product_id',
                'product_gst_details',
                'product_gst_total',
                'quantity',
                'price',
                'discount_percentage',
                'discount_amount',
                'total_amount'
            );
        }, 'order_items.product', 'payments.bank'])->find($id);

        if (! $sales) {
            return redirect()->route('sales.list')->with('error', 'Sales record not found.');
        }

        $update_id = $id;

        // 🔹 Load related data
        $usernames = User::where('role', 'customer')
            ->where('branch_id', $branchIdToUse)
            ->where('isDeleted', 0)
            ->get();

        $category = Category::where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->get();

        $TaxRate = TaxRate::where('status', 'active')->where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->get();

        $products = Product::where('status', 'active')
            ->where('availablility', 'in_stock')
            ->where('branch_id', $branchIdToUse)
            ->get();

        $setting = $this->fallbackSetting($branchIdToUse);
        $labourItems = LabourItem::where('created_by', $branchIdToUse)
            ->where('isDeleted', false)
            ->get();
        $banks = BankMaster::where('branch_id', $branchIdToUse)
            ->where('status', 1)
            ->where('isDeleted', 0)
            ->get();
            
        $staffs = User::where('role', 'staff')
            ->where('branch_id', $branchIdToUse)
            ->where('isDeleted', 0)
            ->get();

        return view('sales/edit-sales', compact('sales', 'TaxRate', 'category', 'usernames', 'products', 'update_id', 'setting', 'labourItems', 'banks', 'staffs'));
    }

    public function sales_details($id)
    {
        $user       = auth()->user();
        $subAdminId = session('selectedSubAdminId');

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branchIdToUse = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $user->id;
        }

        // 🔹 Get branch-specific setting
        $setting          = $this->fallbackSetting($branchIdToUse);
        $currencySymbol   = $setting->currency_symbol ?? '₹';
        $currencyPosition = $setting->currency_position ?? 'left';

        // 🔹 Load sales with order items + products
        $sales = Order::with(['order_items.product'])->find($id);

        if (! $sales) {
            return redirect()->route('sales.list')->with('error', 'Order not found.');
        }
        $totalPaid = PaymentStore::where('order_id', $id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');

        // ✅ Final payable amount (IMPORTANT)
        $finalAmount =
            ($sales->total_amount ?? 0)
            + ($sales->total_gst ?? 0)
            - ($sales->discount_amount ?? 0);
        // dd($finalAmount);
        // ✅ Pending & Extra calculation
        $pendingAmount = max(0, $finalAmount - $totalPaid);
        $extraPaid     = max(0, $totalPaid - $finalAmount);
        // dd($pendingAmount);
        // dd($extraPaid);
        // ✅ Attach values for Blade
        $sales->final_amount   = $finalAmount;
        $sales->total_paid     = $totalPaid;
        $sales->pending_amount = $pendingAmount;
        $sales->extra_paid     = $extraPaid;

        // dd($totalPaid);
        // dd($extraPaid);

        // 🔹 Company info (branch-specific setting)
        $compenyinfo = $setting;

        // 🔹 Get taxes (safely handle null/empty tax_id)
        // $taxIds = ! empty($sales->tax_id) ? json_decode($sales->tax_id, true) : [];

        // $taxes  = ! empty($taxIds)
        //     ? TaxRate::where('branch_id', $branchIdToUse)
        //     ->whereIn('id', $taxIds)
        //     ->where('isDeleted', 0)
        //     ->get()
        //     : collect();

        // 🔹 Order items & totals
        $orderItems  = OrderItem::where('order_id', $id)->get();
        $totalAmount = $orderItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });


        $view_id = $id; // define view_id for blade
        $user    = $sales->user_id ? User::with('userDetail')->find($sales->user_id) : null;
        $userAddress = $user && $user->userDetail ? $user->userDetail->address : null;
        $userDeliveryAddress = $user && $user->userDetail ? $user->userDetail->delivery_address : null;

        if ($user) {
            $sales->customer_role       = ucfirst($user->role ?? 'Customer');
            $sales->customer_name       = $user->name ?? 'Walk-in Customer';
            $sales->customer_email      = $user->email ?? '';
            $sales->customer_phone      = $user->phone ?? '';
            $sales->customer_address    = optional($user->userDetail)->address ?? '';
            $sales->customer_city       = optional($user->userDetail)->city ?? '';
            $sales->customer_country    = optional($user->userDetail)->country ?? '';
            $sales->customer_gst_number = $user->gst_number ?? '';
            $sales->customer_pan_number = $user->pan_number ?? '';
        } else {
            // default values if no user is linked
            $sales->customer_role       = 'Customer';
            $sales->customer_name       = 'Walk-in Customer';
            $sales->customer_email      = '';
            $sales->customer_phone      = '';
            $sales->customer_address    = '';
            $sales->customer_city       = '';
            $sales->customer_country    = '';
            $sales->customer_gst_number = '';
            $sales->customer_pan_number = '';
        }

        // ✅ Check if payment already started for this order
        $hasPaymentStarted = PaymentStore::where('order_id', $view_id)
            ->where('isDeleted', 0)
            ->exists();

        // ✅ Check if return already started for this order
        $hasReturnStarted = SalesReturn::where('order_id', $view_id)->exists();

        return view('sales.sales-details', compact(
            'view_id',
            'sales',
            'totalAmount',
            'compenyinfo',
            'setting',
            'userAddress',
            'userDeliveryAddress',
            'orderItems',
            'currencySymbol',
            'currencyPosition',
            'hasPaymentStarted',
            'hasReturnStarted'
        ));
    }

    public function salse_invoice($id)
    {
        $user       = auth()->user();
        $subAdminId = session('selectedSubAdminId');

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branchIdToUse = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $user->id;
        }
        $view_id = $id;
        $sales   = Order::find($view_id);
        $setting = $this->fallbackSetting($branchIdToUse); // Get currency info
        // dd($setting);
        if (! $sales) {
            return redirect()->route('sales.list')->with('error', 'Order not found.');
        }
        $totalPaid = PaymentStore::where('order_id', $view_id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');

        $extraPaid = max(0, $totalPaid - ($sales->total_amount ?? 0));

        $sales->total_paid = $totalPaid;
        $sales->extra_paid = $extraPaid;
        // $taxIds      = json_decode($sales->tax_id, true);
        // $taxes       = TaxRate::where('branch_id', $branchIdToUse)->whereIn('id', $taxIds)->where('isDeleted', 0)->get();
        $orderItems  = OrderItem::where('order_id', $view_id)->get();
        $totalAmount = $orderItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $user = $sales->user_id ? User::with('userDetail')->find($sales->user_id) : null;

        $userAddress = $user && $user->userDetail ? $user->userDetail->address : null;
        $userDeliveryAddress = $user && $user->userDetail ? $user->userDetail->delivery_address : null;

        // ✅ Check if payment already started for this order
        $hasPaymentStarted = PaymentStore::where('order_id', $view_id)
            ->where('isDeleted', 0)
            ->exists();

        // ✅ Check if return already started for this order
        $hasReturnStarted = SalesReturn::where('order_id', $view_id)->exists();

        // dd($view_id,  $sales,  $totalAmount, $setting, $userAddress);
        // dd($orderItems);
        return view('sales/salse-invoice', compact('view_id', 'sales', 'totalAmount', 'setting', 'userAddress', 'userDeliveryAddress', 'orderItems', 'hasPaymentStarted', 'hasReturnStarted'));
    }

      public function updateDeliveryStatus(Request $request, Delivery $delivery)
    {
        $user = auth()->user();
        $selectedSubAdminId = session('selectedSubAdminId');
        $branchIdToUse = $user->role === 'staff' && $user->branch_id
            ? $user->branch_id
            : (! empty($selectedSubAdminId) ? $selectedSubAdminId : $user->id);

        $delivery->load('order');
        if (! $delivery->order || (int) $delivery->order->branch_id !== (int) $branchIdToUse) {
            abort(404);
        }

        $allowedStatuses = [
            'pending',
            'delivered',
            'partially_delivered',
            'cancelled',
        ];

        $data = $request->validate([
            'status' => 'required|string|in:' . implode(',', $allowedStatuses),
        ]);

        $delivery->status = $data['status'];
        $delivery->save();

        $delivery->order->delivery_status = $data['status'] === 'partially_delivered'
            ? 'partial'
            : $data['status'];
        $delivery->order->save();

        return response()->json([
            'status' => true,
            'message' => 'Delivery status updated successfully.',
            'data' => [
                'id' => $delivery->id,
                'status' => $delivery->status,
            ],
        ]);
    }


     public function updateOrderDeliveryStatus(Request $request, Order $order)
    {
        $user = auth()->user();
        $selectedSubAdminId = session('selectedSubAdminId');
        $branchIdToUse = $user->role === 'staff' && $user->branch_id
            ? $user->branch_id
            : (! empty($selectedSubAdminId) ? $selectedSubAdminId : $user->id);

        if ((int) $order->branch_id !== (int) $branchIdToUse || (int) $order->isDeleted === 1) {
            abort(404);
        }

        $allowedStatuses = [
            'pending',
            'delivered',
            'partially_delivered',
            'cancelled',
        ];

        $data = $request->validate([
            'status' => 'required|string|in:' . implode(',', $allowedStatuses),
        ]);

        $order->delivery_status = $data['status'] === 'partially_delivered'
            ? 'partial'
            : $data['status'];
        $order->save();

        
        $latestDelivery = Delivery::where('order_id', $order->id)
            ->latest('created_at')
            ->first();

        if ($latestDelivery) {
            $latestDelivery->status = $data['status'];
            $latestDelivery->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Order delivery status updated successfully.',
            'data' => [
                'id' => $order->id,
                'status' => $data['status'],
            ],
        ]);
    }

    public function storeDelivery(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|integer|exists:order_items,id',
            'items.*.delivered_quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $selectedSubAdminId = session('selectedSubAdminId');
        $branchIdToUse = $user->role === 'staff' && $user->branch_id
            ? $user->branch_id
            : (! empty($selectedSubAdminId) ? $selectedSubAdminId : $user->id);

        $order = Order::with(['order_items.product'])->findOrFail($data['order_id']);
        if ((int) $order->branch_id !== (int) $branchIdToUse || (int) $order->isDeleted === 1) {
            abort(404);
        }

        $hasDeliveredAny = false;

        \DB::transaction(function () use ($data, $order, $user, &$hasDeliveredAny) {
            $orderItems = $order->order_items->keyBy('id');

            foreach ($data['items'] as $row) {
                $orderItemId = (int) $row['order_item_id'];
                $deliveredQty = round((float) $row['delivered_quantity'], 2);

                if ($deliveredQty <= 0) {
                    continue;
                }

                $orderItem = $orderItems->get($orderItemId);
                if (! $orderItem) {
                    continue;
                }

                $alreadyDelivered = (float) Delivery::where('order_id', $order->id)
                    ->where('order_item_id', $orderItemId)
                    ->sum('delivered_quantity');

                $orderedQty = (float) ($orderItem->quantity ?? 0);
                $remainingQty = max(0, $orderedQty - $alreadyDelivered);
                $finalDeliveredQty = min($deliveredQty, $remainingQty);

                if ($finalDeliveredQty <= 0) {
                    continue;
                }

                Delivery::create([
                    'order_id' => $order->id,
                    'order_item_id' => $orderItemId,
                    'product_id' => $orderItem->product_id ?? null,
                    'delivered_quantity' => $finalDeliveredQty,
                    'ordered_quantity' => $orderedQty,
                    'status' => 'delivered',
                    'delivered_by' => $user?->id,
                    'delivered_at' => now(),
                    'notes' => $data['notes'] ?? null,
                ]);

                $hasDeliveredAny = true;
            }

            $totalOrdered = (float) $order->order_items->sum('quantity');
            $totalDelivered = (float) Delivery::where('order_id', $order->id)->sum('delivered_quantity');
            $remainingQuantity = max(0, $totalOrdered - $totalDelivered);

            $order->remaining_amount = $remainingQuantity;
            $order->delivery_status = $remainingQuantity <= 0 && $totalDelivered > 0
                ? 'delivered'
                : ($totalDelivered > 0 ? 'partial' : 'pending');
            $order->save();
        });

        if (! $hasDeliveredAny) {
            return back()->with('error', 'Please enter a valid delivery quantity.');
        }

        return redirect()
            ->route('sales.delivery', $order->id)
            ->with('success', 'Delivery saved successfully.');
    }
    public function delivery($id, Request $request)
    {
        $order = Order::with(['user.userDetail', 'order_items.product'])->find($id);
        if (! $order) {
            return redirect()->route('sales.list')->with('error', 'Order not found.');
        }

        $this->attachOrderCustomerDetails($order);
        $setting = $this->fallbackSetting($order->branch_id ?? auth()->user()->branch_id ?? null);

        $deliveriesQuery = Delivery::where('order_id', $order->id)
            ->with(['orderItem.product.unit', 'product.unit', 'deliveredBy'])
            ->orderByDesc('id');

        $deliveryIds = $request->query('delivery_ids');
        if ($deliveryIds) {
            $ids = array_filter(array_map('trim', explode(',', $deliveryIds)));
            $deliveriesQuery->whereIn('id', $ids);
        }

        $deliveries = $deliveriesQuery->limit(20)->get();
        $deliveredByItem = $deliveries->groupBy('order_item_id')->map(function ($items) {
            return (float) $items->sum('delivered_quantity');
        });

        $orderItems = $order->order_items->map(function ($item) use ($deliveredByItem) {
            $deliveredQuantity = (float) ($deliveredByItem[$item->id] ?? 0);
            $remainingToDeliver = max(0, (float) ($item->quantity ?? 0) - $deliveredQuantity);

            $item->remaining_to_deliver = $remainingToDeliver;
            return $item;
        });

        $previousDeliveries = $deliveries;

        return view('sales.delivery', compact('order', 'deliveries', 'setting', 'orderItems', 'previousDeliveries'));
    }

     public function deliveryChallan($id, Request $request)
    {
        $order = Order::with(['user.userDetail', 'order_items.product'])->find($id);
        if (! $order) {
            return redirect()->route('sales.list')->with('error', 'Order not found.');
        }

        $this->attachOrderCustomerDetails($order);
        $setting = $this->fallbackSetting($order->branch_id ?? auth()->user()->branch_id ?? null);

        $deliveryIds = $request->query('delivery_ids');
        if ($deliveryIds) {
            $ids = array_filter(array_map('trim', explode(',', $deliveryIds)));
            $deliveries = Delivery::whereIn('id', $ids)
                ->with(['orderItem.product.unit', 'product.unit', 'deliveredBy'])
                ->get();
        } else {
            $deliveries = Delivery::where('order_id', $order->id)
                ->with(['orderItem.product.unit', 'product.unit', 'deliveredBy'])
                ->orderByDesc('id')
                ->limit(20)
                ->get();
        }

        return view('sales.delivery-challan', compact('order', 'deliveries', 'setting'));
    }

    // Generate delivery challan PDF
    public function deliveryChallanPdf($id, Request $request)
    {
        $order = Order::with(['user.userDetail', 'order_items.product'])->find($id);
        if (! $order) {
            return redirect()->route('sales.list')->with('error', 'Order not found.');
        }

        $this->attachOrderCustomerDetails($order);
        $setting = $this->fallbackSetting($order->branch_id ?? auth()->user()->branch_id ?? null);

        $deliveryIds = $request->query('delivery_ids');
        if ($deliveryIds) {
            $ids = array_filter(array_map('trim', explode(',', $deliveryIds)));
            $deliveries = Delivery::whereIn('id', $ids)
                ->with(['orderItem.product.unit', 'product.unit', 'deliveredBy'])
                ->get();
        } else {
            $deliveries = Delivery::where('order_id', $order->id)
                ->with(['orderItem.product.unit', 'product.unit', 'deliveredBy'])
                ->orderByDesc('id')
                ->limit(20)
                ->get();
        }

        $data = [
            'order' => $order,
            'deliveries' => $deliveries,
            'setting' => $setting,
            'challan_number' => 'DC-' . $order->id,
        ];

        $pdf = Pdf::loadView('sales.delivery-challan-pdf', $data)->setPaper('A4', 'portrait');
        return $pdf->stream('delivery_challan_' . $order->id . '.pdf');
    }



    // public function salse_invoice_pdf($id)
    // {
    //     $view_id    = $id;
    //     $sales      = Order::find($view_id);
    //     $user       = Auth::user();
    //     $subAdminId = session('selectedSubAdminId') ?? $user->id;

    //     if ($user->role === 'staff' && $user->branch_id) {
    //         $setting = Setting::where('branch_id', $user->branch_id)->first();
    //     } else {
    //         $setting = Setting::where('branch_id', $subAdminId)->first();
    //     }

    //     if (! $sales) {
    //         return redirect()->route('sales.list')->with('error', 'Order not found.');
    //     }
    //     $labourItems = Sales_Labour_Items::where('order_id', $id)
    //         ->with('labourItem')
    //         ->get();
    //         // dd($labourItems);
    //     $labourCost = 0;
    //     if ($labourItems && $labourItems->isNotEmpty()) {
    //         foreach ($labourItems as $labourItem) {
    //             $labourCost += ($labourItem->qty ?? 0) * ($labourItem->price ?? 0);
    //         }
    //     }

    //     // Fetch user data (assuming 'user_id' in orders table)
    //     $user = $sales->user_id ? User::with('userDetail')->find($sales->user_id) : null;

    //     // Helper function for currency formatting
    //     $formatCurrency = function ($amount) use ($setting) {
    //         $amount = number_format($amount, 2);
    //         return $setting->currency_position === 'right'
    //             ? $amount . $setting->currency_symbol
    //             : $setting->currency_symbol . $amount;
    //     };

    //     // ✅ Subtotal (Amount before GST)
    //     $orderItems = OrderItem::where('order_id', $view_id)->get();
    //     $subtotal   = $orderItems->sum(function ($item) {
    //         return $item->price * $item->quantity;
    //     });

    //     // dd($orderItems);

    //     // ✅ Discount
    //     $discountPercent = $sales->discount ?? 0;
    //     $discountAmount  = ($discountPercent / 100) * $subtotal;
    //     $afterDiscount   = $subtotal - $discountAmount;

    //     // ✅ Tax calculation only if gst_option = 'with'
    //     // $taxDetails = [];
    //     // if ($sales->gst_option === 'with_gst') {
    //     //     $taxIds = json_decode($sales->tax_id, true) ?? [];
    //     //     if (! empty($taxIds)) {
    //     //         $taxes = TaxRate::whereIn('id', $taxIds)->get();
    //     //         foreach ($taxes as $tax) {
    //     //             $taxAmount    = ($tax->tax_rate / 100) * $afterDiscount;
    //     //             $taxDetails[] = [
    //     //                 'name'             => $tax->tax_name,
    //     //                 'rate'             => $tax->tax_rate,
    //     //                 'amount'           => $taxAmount,
    //     //                 'formatted_amount' => $formatCurrency($taxAmount),
    //     //             ];
    //     //         }
    //     //     }
    //     // }
    //     $totalGstAmount = 0;
    //     $taxSummary = [];

    //     foreach ($orderItems as $item) {
    //         $totalGstAmount += (float) ($item->product_gst_total ?? 0);
    //         $gstDetails = $item->product_gst_details;

    //         // Handle legacy/double-encoded JSON payloads.
    //         if (is_string($gstDetails)) {
    //             $gstDetails = json_decode($gstDetails, true);
    //             if (is_string($gstDetails)) {
    //                 $gstDetails = json_decode($gstDetails, true);
    //             }
    //         }

    //         if (is_array($gstDetails) && isset($gstDetails['tax_name'])) {
    //             $gstDetails = [$gstDetails];
    //         }

    //         if (!empty($gstDetails) && is_array($gstDetails)) {
    //             foreach ($gstDetails as $tax) {
    //                 if (!is_array($tax)) {
    //                     continue;
    //                 }

    //                 $taxName = $tax['tax_name'] ?? 'GST';
    //                 $taxRate = $tax['tax_rate'] ?? 0;
    //                 $taxAmount = (float) ($tax['tax_amount'] ?? 0);
    //                 $key = $taxName . '_' . $taxRate;

    //                 if (!isset($taxSummary[$key])) {
    //                     $taxSummary[$key] = [
    //                         'name'   => $taxName,
    //                         'rate'   => $taxRate,
    //                         'amount' => 0,
    //                     ];
    //                 }

    //                 $taxSummary[$key]['amount'] += $taxAmount;
    //             }
    //         }
    //     }

    //     // format GST summary
    //     $taxDetails = [];
    //     foreach ($taxSummary as $tax) {
    //         $taxDetails[] = [
    //             'name'             => $tax['name'],
    //             'rate'             => $tax['rate'],
    //             'amount'           => $tax['amount'],
    //             'formatted_amount' => $formatCurrency($tax['amount']),
    //         ];
    //     }

    //     // final total
    //     $finalTotal = $afterDiscount + $totalGstAmount;


    //     // ✅ Final total
    //     $finalTotal = $afterDiscount + collect($taxDetails)->sum('amount');

    //     // ✅ Prepare formatted values
    //     $formattedSubtotal       = $formatCurrency($subtotal);
    //     $formattedDiscountAmount = $formatCurrency($discountAmount);
    //     $formattedAfterDiscount  = $formatCurrency($afterDiscount);
    //     $formattedFinalTotal     = $formatCurrency($finalTotal);

    //     // ✅ Retrieve customer data
    //     $customer = $user ? [
    //         'name'       => $user->name ?? 'walk-in-customer',
    //         'email'      => $user->email ?? '',
    //         'phone'      => $user->phone ?? '',
    //         'address'    => optional($user->userDetail)->address ?? '',
    //         'gst_number' => $user->gst_number ?? '',
    //         'pan_number' => $user->pan_number ?? '',
    //     ] : [
    //         'name'       => 'walk-in-customer',
    //         'email'      => '',
    //         'phone'      => '',
    //         'address'    => '',
    //         'gst_number' => '',
    //         'pan_number' => '',
    //     ];

    //     $paidAmount = PaymentStore::where('order_id', $sales->id)
    //         ->where('isDeleted', 0)
    //         ->sum('payment_amount');

    //     // ✅ Fetch returns
    //     $returns = \App\Models\SalesReturn::with('items.product')
    //         ->where('order_id', $view_id)
    //         ->get();

    //     // ✅ Pending amount (single source of truth)
    //     $pendingAmount = $sales->remaining_amount ?? 0;

    //     // ✅ Extra Paid calculation
    //         $totalOrderAmount = $sales->total_amount ?? $finalTotal; // fallback safety
    //         $extraPaid = max(0, $paidAmount - $totalOrderAmount);

    //     // ✅ Prepare data for view
    //     $pdfData = [
    //         'view_id'                => $view_id,
    //         'sales'                  => $sales,
    //         'setting'                => $setting,
    //         'orderItems'             => $orderItems,
    //         'salesItems'             => $orderItems,
    //         'labourItems'            => $labourItems,
    //         'returns'                => $returns,
    //         'taxDetails1'            => $taxDetails,
    //         'totalGst'      => $formatCurrency($totalGstAmount),
    //         'finalTotal'    => $formatCurrency($finalTotal),
    //         'customer'               => [
    //             'name'    => $user->name ?? 'walk-in-customer',
    //             'email'   => $user->email ?? '',
    //             'phone'   => $user->phone ?? '',
    //             'pan_number'   => $user->pan_number ?? '',
    //             'gst_number'   => $user->gst_number ?? '',
    //             'address' => optional($user->userDetail)->address ?? 'arga',
    //         ],
    //         'user'                   => $user ? $user->toArray() : null,
    //         'subtotal'               => $formattedSubtotal,
    //         'discountPercent'        => $discountPercent,
    //         'discountAmount'         => $formattedDiscountAmount,
    //         'afterDiscount'          => (float) $afterDiscount,  // numeric
    //         'formattedAfterDiscount' => $formattedAfterDiscount, // formatted
    //         'finalTotal'             => $formattedFinalTotal,
    //         'taxDetails1'            => $taxDetails,
    //         'paidAmount'             => $paidAmount,
    //         'pendingAmount'          => $pendingAmount,
    //         'extraPaid'              => $extraPaid,
    //     ];
    //     // dd($pdfData);

    //     // ✅ Load and render PDF
    //     // $pdf = PDF::loadView('sales.salse-invoice-pdf', $pdfData);

    //     // return $pdf->stream('invoice_' . $view_id . '.pdf');
    //     // ===== Invoice size condition =====
    //         if ($setting && $setting->invoice_size === 'small') {

    //             $pdf = PDF::loadView('sales.salse-invoice-small-pdf', $pdfData)
    //                     ->setPaper('A5', 'portrait');

    //         } else {

    //             $pdf = PDF::loadView('sales.salse-invoice-pdf', $pdfData)
    //                     ->setPaper('A4', 'portrait');
    //         }
    //         // ==================================

    //     return $pdf->stream('invoice_' . $view_id . '.pdf');
    // }
    public function salse_invoice_pdf($id)
    {
        $view_id    = $id;
        $sales      = Order::find($view_id);
        $user       = Auth::user();
        $subAdminId = session('selectedSubAdminId') ?? $user->id;

        if ($user->role === 'staff' && $user->branch_id) {
            $setting = $this->fallbackSetting($user->branch_id);
        } else {
            $setting = $this->fallbackSetting($subAdminId);
        }

        if (! $sales) {
            return redirect()->route('sales.list')->with('error', 'Order not found.');
        }

        $labourItems = Sales_Labour_Items::where('order_id', $id)
            ->with('labourItem')
            ->get();

        $labourCost = 0;
        if ($labourItems && $labourItems->isNotEmpty()) {
            foreach ($labourItems as $labourItem) {
                $labourCost += ($labourItem->qty ?? 0) * ($labourItem->price ?? 0);
            }
        }

        // Fetch user data
        $user = $sales->user_id ? User::with('userDetail')->find($sales->user_id) : null;

        // Helper function for currency formatting
        $formatCurrency = function ($amount) use ($setting) {
            $amount = number_format($amount, 2);
            return $setting->currency_position === 'right'
                ? $amount . $setting->currency_symbol
                : $setting->currency_symbol . $amount;
        };

        // Subtotal
        $orderItems = OrderItem::where('order_id', $view_id)->get();
        $subtotal   = $orderItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // Discount
        $discountPercent = $sales->discount ?? 0;
        $discountAmount  = ($discountPercent / 100) * $subtotal;
        $afterDiscount   = $subtotal - $discountAmount;

        // Calculate GST
        $totalGstAmount = 0;
        $taxSummary = [];

        foreach ($orderItems as $item) {
            $totalGstAmount += (float) ($item->product_gst_total ?? 0);
            $gstDetails = $item->product_gst_details;

            if (is_string($gstDetails)) {
                $gstDetails = json_decode($gstDetails, true);
                if (is_string($gstDetails)) {
                    $gstDetails = json_decode($gstDetails, true);
                }
            }

            if (is_array($gstDetails) && isset($gstDetails['tax_name'])) {
                $gstDetails = [$gstDetails];
            }

            if (!empty($gstDetails) && is_array($gstDetails)) {
                foreach ($gstDetails as $tax) {
                    if (!is_array($tax)) {
                        continue;
                    }

                    $taxName = $tax['tax_name'] ?? 'GST';
                    $taxRate = $tax['tax_rate'] ?? 0;
                    $taxAmount = (float) ($tax['tax_amount'] ?? 0);
                    $key = $taxName . '_' . $taxRate;

                    if (!isset($taxSummary[$key])) {
                        $taxSummary[$key] = [
                            'name'   => $taxName,
                            'rate'   => $taxRate,
                            'amount' => 0,
                        ];
                    }

                    $taxSummary[$key]['amount'] += $taxAmount;
                }
            }
        }

        // Format GST summary
        $taxDetails = [];
        foreach ($taxSummary as $tax) {
            $taxDetails[] = [
                'name'             => $tax['name'],
                'rate'             => $tax['rate'],
                'amount'           => $tax['amount'],
                'formatted_amount' => $formatCurrency($tax['amount']),
            ];
        }

        // Calculate Return Amount
        $totalReturnAmount = 0;
        $returns = \App\Models\SalesReturn::with('items.product')
            ->where('order_id', $view_id)
            ->get();

        $allItemsFullyReturned = false;

        if ($returns->isNotEmpty()) {
            foreach ($returns as $ret) {
                $totalReturnAmount += (float) ($ret->total_amount ?? 0);
            }

            // Check if all items are fully returned
            $orderItemsQuantities = [];
            foreach ($orderItems as $item) {
                $orderItemsQuantities[$item->id] = $item->quantity;
            }

            $returnedQuantities = [];
            foreach ($returns as $ret) {
                foreach ($ret->items as $retItem) {
                    if (!isset($returnedQuantities[$retItem->order_item_id])) {
                        $returnedQuantities[$retItem->order_item_id] = 0;
                    }
                    $returnedQuantities[$retItem->order_item_id] += $retItem->quantity;
                }
            }

            $allItemsFullyReturned = true;
            foreach ($orderItemsQuantities as $orderItemId => $originalQty) {
                $returnedQty = $returnedQuantities[$orderItemId] ?? 0;
                if ($returnedQty < $originalQty) {
                    $allItemsFullyReturned = false;
                    break;
                }
            }
        }

        // Get shipping charge
        $shippingCharge = (float) ($sales->shipping ?? 0);

        // Calculate return amount with shipping if fully returned
        $totalReturnWithShipping = $totalReturnAmount;
        if ($allItemsFullyReturned && $totalReturnAmount > 0) {
            $totalReturnWithShipping = $totalReturnAmount + $shippingCharge;
        }

        // Final total
        $finalTotal = $afterDiscount + $totalGstAmount + $shippingCharge + $labourCost;

        // Calculate pending amount after returns
        $paidAmount = PaymentStore::where('order_id', $sales->id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');

        // Pending amount = Final Total - Total Returns - Paid Amount
        $pendingAmount = max(0, $finalTotal - $totalReturnWithShipping - $paidAmount);

        // Extra Paid calculation
        $extraPaid = max(0, $paidAmount - ($finalTotal - $totalReturnWithShipping));

        // Prepare formatted values
        $formattedSubtotal       = $formatCurrency($subtotal);
        $formattedDiscountAmount = $formatCurrency($discountAmount);
        $formattedAfterDiscount  = $formatCurrency($afterDiscount);
        $formattedTotalGstAmount = $formatCurrency($totalGstAmount);
        $formattedShippingCharge = $formatCurrency($shippingCharge);
        $formattedLabourCost     = $formatCurrency($labourCost);
        $formattedFinalTotal     = $formatCurrency($finalTotal);
        $formattedReturnAmount   = $formatCurrency($totalReturnWithShipping);
        $formattedPaidAmount     = $formatCurrency($paidAmount);
        $formattedPendingAmount  = $formatCurrency($pendingAmount);
        $formattedExtraPaid      = $formatCurrency($extraPaid);

        // Determine return status
        $returnStatus = 'No return';
        $returnStatusColor = '#28c76f';
        if ($totalReturnAmount > 0) {
            if ($totalReturnWithShipping >= $finalTotal) {
                $returnStatus = 'Fully Returned';
                $returnStatusColor = '#ea5455';
            } else {
                $returnStatus = 'Partially Returned';
                $returnStatusColor = '#ff9f43';
            }
        }

        // Prepare customer data
        $customer = $user ? [
            'name'       => $user->name ?? 'walk-in-customer',
            'company_name' =>$user->company_name ?? '',
            'email'      => $user->email ?? '',
            'phone'      => $user->phone ?? '',
            'address'    => optional($user->userDetail)->address ?? '',
            'delivery_address' => optional($user->userDetail)->delivery_address ?? '',
            'gst_number' => $user->gst_number ?? '',
            'pan_number' => $user->pan_number ?? '',
        ] : [
            'name'       => 'walk-in-customer',
                'company_name' => '',
            'email'      => '',
            'phone'      => '',
            'address'    => '',
            'delivery_address' => '',
            'gst_number' => '',
            'pan_number' => '',
        ];

        // dd($formattedPaidAmount);
        // Prepare data for view
        $pdfData = [
            'view_id'                => $view_id,
            'sales'                  => $sales,
            'setting'                => $setting,
            'orderItems'             => $orderItems,
            'salesItems'             => $orderItems,
            'labourItems'            => $labourItems,
            'returns'                => $returns,
            'taxDetails1'            => $taxDetails,
            'totalGst'               => $formattedTotalGstAmount,
            'finalTotal'             => $formattedFinalTotal,
            'subtotal'               => $formattedSubtotal,
            'discountPercent'        => $discountPercent,
            'discountAmount'         => $formattedDiscountAmount,
            'afterDiscount'          => (float) $afterDiscount,
            'formattedAfterDiscount' => $formattedAfterDiscount,
            'shippingCharge'         => $formattedShippingCharge,
            'labourCost'             => $formattedLabourCost,
            'returnAmount'           => $formattedReturnAmount,
            'returnStatus'           => $returnStatus,
            'returnStatusColor'      => $returnStatusColor,
            'totalReturnAmount'      => $totalReturnWithShipping,
            'allItemsFullyReturned'  => $allItemsFullyReturned,
            'customer'               => $customer,
            'user'                   => $user ? $user->toArray() : null,
            'paidAmount' => $paidAmount,
            'pendingAmount'          => $formattedPendingAmount,
            'extraPaid'              => $formattedExtraPaid,
            'pendingAmountNumeric'   => $pendingAmount,
        ];

        // Load and render PDF
        if ($setting && $setting->invoice_size === 'small') {
            $pdf = PDF::loadView('sales.salse-invoice-small-pdf', $pdfData)
                ->setPaper('A5', 'portrait');
        } else {
            $pdf = PDF::loadView('sales.salse-invoice-pdf', $pdfData)
                ->setPaper('A4', 'portrait');
        }

        return $pdf->stream('invoice_' . $view_id . '.pdf');
    }


    public function sales_report(Request $request)
    {
        $user         = Auth()->user();
        $branchId     = $user->id ?? null;
        $UserBranchId = $user->branch_id ?? null;
        $userRole     = $user->role ?? '';
        $subAdminId   = session('selectedSubAdminId');

        // Decide branch based on role
        if ($userRole === 'sub-admin') {
            $branchIdToUse = $branchId;
        } elseif ($userRole === 'admin' && $subAdminId) {
            $branchIdToUse = $subAdminId;
        } elseif ($userRole === 'staff') {
            $branchIdToUse = $UserBranchId;
        } else {
            $branchIdToUse = $branchId;
        }

        // Fetch customer
        if ($userRole === 'staff') {
            // Only customer created by this staff
            $customers = User::where('role', 'customer')
                ->where('branch_id', $branchIdToUse)
                ->where('isDeleted', 0)
                ->orderBy('name')
                ->get();
        } else {
            // Admin / sub-admin sees all customer in branch
            $customers = User::where('role', 'customer')
                ->where('branch_id', $branchIdToUse)
                ->where('isDeleted', 0)
                ->orderBy('name')
                ->get();
        }

        // ✅ Fetch categories based on branch
        if ($userRole === 'staff') {
            $categories = Category::where('isDeleted', 0)
                ->where('branch_id', $UserBranchId)
                ->orderBy('name')
                ->get();
        } else {
            $categories = Category::where('isDeleted', 0)
                ->where('branch_id', $branchIdToUse)
                ->orderBy('name')
                ->get();
        }

        return view('sales/salesreport', compact('customers', 'categories'));
    }

    public function pos()
    {
        $user       = auth()->user();
        $userRole   = $user->role ?? '';
        $userId     = $user->id ?? null;
        $branchId   = $user->branch_id ?? null;
        $subAdminId = session('selectedSubAdminId');

        // 🔹 Decide which branch_id to use
        if ($userRole === 'sub-admin' && $userId) {
            $branchIdToUse = $userId;
        } elseif ($userRole === 'admin' && ! empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } elseif ($userRole === 'staff' && $branchId) {
            $branchIdToUse = $branchId;
        } else {
            $branchIdToUse = $userId;
        }
        // dd($branchIdToUse);
        // 🔹 Get settings
        $setting           = Setting::where('branch_id', $branchIdToUse)->first();
        $currency_symbol   = $setting->currency_symbol ?? '₹';
        $currency_position = $setting->currency_position ?? 'left';

        // 🔹 Common queries
        $categories = Category::where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->orderBy('id', 'desc')
            ->get();
        $taxRates = TaxRate::where('status', 'active')->where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->get();

        // 🔹 Banks
        $banks = BankMaster::where('branch_id', $branchIdToUse)
            ->where('status', 1)
            ->where('isDeleted', 0)
            ->get();

        // $customers = User::where('role', 'customer')
        //     ->where('branch_id', $branchIdToUse)
        //     ->where('isDeleted', 0)
        //     ->get();
        // 🔹 Customers: filter by created_by if staff
        $customersQuery = User::where('role', 'customer')
            ->where('branch_id', $branchIdToUse)
            ->where('isDeleted', 0);

        if (strtolower($userRole) === 'staff') {
            $customersQuery->where('branch_id', $branchIdToUse);
        }

        $customers = $customersQuery->get();
        // dd($customers);
        // 🔹 Vendors only for Staff or default case
        $vendors = collect(); // empty collection if not needed
        if ($userRole === 'staff' || $userRole === 'admin' || $userRole === 'sub-admin') {
            $vendors = User::where('role', 'vendor')
                ->where('branch_id', $branchIdToUse)
                ->where('isDeleted', 0)
                ->get();
        }

        return view('sales.pos', compact(
            'categories',
            'taxRates',
            'customers',
            'vendors',
            'currency_symbol',
            'currency_position',
            'setting',
            'banks'
        ));
    }

    public function sale_report($ids)
    {
        $authUser   = auth()->user();
        $subAdminId = session('selectedSubAdminId');

        // Decide branch_id based on role
        if ($authUser->role === 'staff' && $authUser->branch_id) {
            $branchIdToUse = $authUser->branch_id;
        } elseif ($authUser->role === 'admin' && ! empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $authUser->id;
        }

        $idsArray = explode(',', $ids);

        // Eager load related models
        $sales = OrderItem::with('product.category', 'invoice', 'user.userDetail')
            ->whereIn('id', $idsArray)
            ->get();

        if ($sales->isEmpty()) {
            return redirect()->route('sales.index')->with('error', 'No sales data found.');
        }

        // Get settings
        $settings         = Setting::where('branch_id', $branchIdToUse)->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        // Process each sale
        $totalAmount    = 0;
        $discountAmount = 0;
        $taxDetails     = [];
        // 🔹 GST / Tax Calculation
        // $taxDetails     = [];
        $totalTaxAmount = 0;

        foreach ($sales as $sale) {
            $rowTaxes     = [];
            $rowTaxAmount = 0;

            $rowGSTOption = $sale->invoice->gst_option ?? 'without_gst';
            $rowTaxIds    = $sale->invoice->tax_id ?? '[]';

            // Decode JSON tax array
            $rowTaxIdsArray = json_decode($rowTaxIds, true) ?: [];

            $rowTaxRates = collect();
            if ($rowGSTOption === 'with_gst' && ! empty($rowTaxIdsArray)) {
                $rowTaxRates = TaxRate::where('status', 'active')
                    ->where('branch_id', $branchIdToUse)
                    ->where('isDeleted', 0)
                    ->whereIn('id', $rowTaxIdsArray)
                    ->get();
            }

            $unitPrice = $sale->price;
            // Apply discount per unit
            if ($sale->invoice && $sale->invoice->discount) {
                $discountPercent = $sale->invoice->discount;
                $unitPrice -= ($unitPrice * $discountPercent) / 100;
            }

            // Calculate row taxes
            foreach ($rowTaxRates as $tax) {
                $taxBase = $unitPrice * $sale->quantity;
                $amount  = $taxBase * ($tax->tax_rate / 100);

                $rowTaxes[] = [
                    'name'   => $tax->tax_name,
                    'rate'   => $tax->tax_rate,
                    'amount' => $amount,
                ];

                $rowTaxAmount += $amount;

                // accumulate overall tax totals
                if (! isset($taxDetails[$tax->id])) {
                    $taxDetails[$tax->id] = [
                        'name'   => $tax->tax_name,
                        'rate'   => $tax->tax_rate,
                        'amount' => 0,
                    ];
                }
                $taxDetails[$tax->id]['amount'] += $amount;
            }

            // Attach to sale row
            $sale->rowGSTOption = $rowGSTOption;
            $sale->rowTaxes     = $rowTaxes;
            $sale->rowTaxAmount = $rowTaxAmount;

            // Final total per row = (unit price after discount × qty) + row taxes
            $sale->rowFinalTotal = ($unitPrice * $sale->quantity) + $rowTaxAmount;

            // $totalAmount += $rowTaxAmount;
            $totalAmount = $sales->sum('rowFinalTotal');
        }

        // Customer info (from first sale)
        $customer    = $sales->first()->user ?? null;
        $userDetails = $customer ? $customer->userDetail : null;

        return view('sales.sale_report', compact(
            'sales',
            'settings',
            'discountAmount',
            'totalAmount',
            'taxDetails',
            'currencySymbol',
            'currencyPosition',
            'customer',
            'userDetails',
            'ids'
        ));
    }

    public function export_sales_report_pdf($ids)
    {
        $authUser   = auth()->user();
        $subAdminId = session('selectedSubAdminId');

        // 🔹 Decide branch_id based on role
        if ($authUser->role === 'staff' && $authUser->branch_id) {
            $branchIdToUse = $authUser->branch_id;
        } elseif ($authUser->role === 'admin' && ! empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $authUser->id;
        }

        $idsArray = explode(',', $ids);

        // 🔹 Eager load related models
        $sales = OrderItem::with('product.category', 'invoice', 'user.userDetail')
            ->whereIn('id', $idsArray)
            ->get();

        if ($sales->isEmpty()) {
            return redirect()->route('sales.index')->with('error', 'No sales data found.');
        }

        // 🔹 Get settings
        $setting          = Setting::where('branch_id', $branchIdToUse)->first();
        $currencySymbol   = $setting->currency_symbol ?? '₹';
        $currencyPosition = $setting->currency_position ?? 'left';

        $subtotal = $sales->sum('total_amount');

        // 🔹 Discount calculation
        $discountAmount = 0;
        foreach ($sales as $sale) {
            if ($sale->invoice && $sale->invoice->discount) {
                $discountPercent = $sale->invoice->discount;
                $discountAmount += ($sale->total_amount * $discountPercent) / 100;
            }
        }

        $subtotalAfterDiscount = $subtotal - $discountAmount;

        // 🔹 GST / Tax Calculation
        $taxDetails     = [];
        $totalTaxAmount = 0;

        foreach ($sales as $sale) {
            $rowTaxes     = [];
            $rowTaxAmount = 0;

            $rowGSTOption = $sale->invoice->gst_option ?? 'without_gst';
            $rowTaxIds    = $sale->invoice->tax_id ?? '[]';

            // Decode JSON tax array
            $rowTaxIdsArray = json_decode($rowTaxIds, true) ?: [];

            $rowTaxRates = collect();
            if ($rowGSTOption === 'with_gst' && ! empty($rowTaxIdsArray)) {
                $rowTaxRates = TaxRate::where('status', 'active')
                    ->where('branch_id', $branchIdToUse)
                    ->where('isDeleted', 0)
                    ->whereIn('id', $rowTaxIdsArray)
                    ->get();
            }

            $unitPrice = $sale->price;
            // Apply discount per unit
            if ($sale->invoice && $sale->invoice->discount) {
                $discountPercent = $sale->invoice->discount;
                $unitPrice -= ($unitPrice * $discountPercent) / 100;
            }

            // Calculate row taxes
            foreach ($rowTaxRates as $tax) {
                $taxBase = $unitPrice * $sale->quantity;
                $amount  = $taxBase * ($tax->tax_rate / 100);

                $rowTaxes[] = [
                    'name'   => $tax->tax_name,
                    'rate'   => $tax->tax_rate,
                    'amount' => $amount,
                ];

                $rowTaxAmount += $amount;

                // accumulate overall tax totals
                if (! isset($taxDetails[$tax->id])) {
                    $taxDetails[$tax->id] = [
                        'name'   => $tax->tax_name,
                        'rate'   => $tax->tax_rate,
                        'amount' => 0,
                    ];
                }
                $taxDetails[$tax->id]['amount'] += $amount;
            }

            // Attach to sale row
            $sale->rowGSTOption = $rowGSTOption;
            $sale->rowTaxes     = $rowTaxes;
            $sale->rowTaxAmount = $rowTaxAmount;

            // Final total per row = (unit price after discount × qty) + row taxes
            $sale->rowFinalTotal = ($unitPrice * $sale->quantity) + $rowTaxAmount;

            $totalTaxAmount += $rowTaxAmount;
        }

        // 🔹 Total after discount + taxes
        // Total after discount + taxes
        $totalAmount = $sales->sum('rowFinalTotal');

        $pdfData = [
            'sales'            => $sales,
            'setting'          => $setting,
            'currencySymbol'   => $currencySymbol,
            'currencyPosition' => $currencyPosition,
            'subtotal'         => $subtotal,
            'discountAmount'   => $discountAmount,
            // 'afterDiscount'    => $afterDiscount,
            'taxDetails'       => $taxDetails,
            'totalTaxAmount'   => $totalTaxAmount,
            'totalAmount'      => $totalAmount,
            // 'ids' => $ids
        ];

        // Load PDF
        $pdf = PDF::loadView('sales.sales-invoice-report-pdf', $pdfData)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
            ]);

        return $pdf->download('sales_report.pdf');
    }

    public function show_sales_report_page(Request $request)
    {
        try {
            // 🔹 Get inputs directly from the request
            $ids      = $request->input('ids');
            $branchId = $request->input('branch');

            // 🔹 Validate required parameters
            if (empty($ids)) {
                abort(404, 'No sales selected.');
            }
            if (empty($branchId)) {
                abort(404, 'Branch ID is missing.');
            }

            // 🔹 Convert to array if comma-separated
            $idsArray = explode(',', $ids);

            // 🔹 Fetch sales data with relationships
            $sales = OrderItem::with('product.category', 'invoice', 'user.userDetail')
                ->whereIn('id', $idsArray)
                ->get();

            if ($sales->isEmpty()) {
                abort(404, 'No sales found.');
            }

            // 🔹 Fetch settings for branch
            $settings         = Setting::where('branch_id', $branchId)->first();
            $currencySymbol   = $settings->currency_symbol ?? '₹';
            $currencyPosition = $settings->currency_position ?? 'left';

            // 🔹 Initialize totals
            $taxDetails     = [];
            $totalAmount    = 0;
            $discountAmount = 0;

            foreach ($sales as $sale) {
                $rowTaxes     = [];
                $rowTaxAmount = 0;

                $rowGSTOption   = $sale->invoice->gst_option ?? 'without_gst';
                $rowTaxIds      = $sale->invoice->tax_id ?? '[]';
                $rowTaxIdsArray = json_decode($rowTaxIds, true) ?: [];

                // 🔹 Fetch applicable tax rates
                $rowTaxRates = collect();
                if ($rowGSTOption === 'with_gst' && ! empty($rowTaxIdsArray)) {
                    $rowTaxRates = TaxRate::where('status', 'active')
                        ->where('branch_id', $branchId)
                        ->where('isDeleted', 0)
                        ->whereIn('id', $rowTaxIdsArray)
                        ->get();
                }

                // 🔹 Apply discount (if any)
                $unitPrice = $sale->price;
                if ($sale->invoice && $sale->invoice->discount) {
                    $discountPercent = $sale->invoice->discount;
                    $discountPerUnit = ($unitPrice * $discountPercent) / 100;
                    $unitPrice -= $discountPerUnit;
                    $discountAmount += $discountPerUnit * $sale->quantity;
                }

                // 🔹 Calculate taxes per item
                foreach ($rowTaxRates as $tax) {
                    $taxBase = $unitPrice * $sale->quantity;
                    $amount  = $taxBase * ($tax->tax_rate / 100);

                    $rowTaxes[] = [
                        'name'   => $tax->tax_name,
                        'rate'   => $tax->tax_rate,
                        'amount' => $amount,
                    ];

                    $rowTaxAmount += $amount;

                    // Accumulate total tax details
                    if (! isset($taxDetails[$tax->id])) {
                        $taxDetails[$tax->id] = [
                            'name'   => $tax->tax_name,
                            'rate'   => $tax->tax_rate,
                            'amount' => 0,
                        ];
                    }
                    $taxDetails[$tax->id]['amount'] += $amount;
                }

                // 🔹 Attach row-level summary
                $sale->rowGSTOption = $rowGSTOption;
                $sale->rowTaxes     = $rowTaxes;
                $sale->rowTaxAmount = $rowTaxAmount;

                // 🔹 Final total per item
                $sale->rowFinalTotal = ($unitPrice * $sale->quantity) + $rowTaxAmount;
            }

            // 🔹 Calculate final grand total
            $totalAmount = $sales->sum('rowFinalTotal');

            // 🔹 Customer info (first sale user)
            $customer    = $sales->first()->user ?? null;
            $userDetails = $customer ? $customer->userDetail : null;

            // 🔹 Prepare data for Blade view
            $data = [
                'sales'            => $sales,
                'settings'         => $settings,
                'discountAmount'   => $discountAmount,
                'totalAmount'      => $totalAmount,
                'taxDetails'       => $taxDetails,
                'currencySymbol'   => $currencySymbol,
                'currencyPosition' => $currencyPosition,
                'customer'         => $customer,
                'userDetails'      => $userDetails,
                'ids'              => $ids,
            ];

            // ✅ Return view without requiring authentication
            return view('sales.web_sale_report', $data);
        } catch (\Throwable $e) {
            abort(500, 'Error loading sales report: ' . $e->getMessage());
        }
    }

    public function tds_report(Request $request)
    {
        $user         = Auth()->user();
        $branchId     = $user->id ?? null;
        $userBranchId = $user->branch_id ?? null;
        $userRole     = $user->role ?? '';
        $subAdminId   = session('selectedSubAdminId');

        if ($userRole === 'sub-admin') {
            $branchIdToUse = $branchId;
        } elseif ($userRole === 'admin' && $subAdminId) {
            $branchIdToUse = $subAdminId;
        } elseif ($userRole === 'staff') {
            $branchIdToUse = $userBranchId;
        } else {
            $branchIdToUse = $branchId;
        }

        $customers = User::where('role', 'customer')
            ->where('branch_id', $branchIdToUse)
            ->where('isDeleted', 0)
            ->orderBy('name')
            ->get();

        return view('sales.tdsreport', compact('customers'));
    }
}
