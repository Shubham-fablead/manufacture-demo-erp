<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\BankMaster;
use App\Models\Order;
use App\Models\PaymentStore;
use App\Models\PurchaseInvoice;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesReceiptPaymentController extends Controller
{
    public function index(Request $request)
    {
        $branchIdToUse = $this->resolveBranchIdToUse();
        $selectedCustomerId = (int) ($request->query('customer_id') ?? old('customer_id', 0));
        $selectedVendorId = (int) ($request->query('vendor_id') ?? old('vendor_id', 0));
        $selectedOrderId = (int) old('order_id', 0);

        $setting = Setting::where('branch_id', $branchIdToUse)->first();
        $currencySymbol = $setting->currency_symbol ?? 'Rs.';
        $currencyPosition = $setting->currency_position ?? 'left';

        $customers = $this->getPendingCustomers($branchIdToUse);
        $vendors = $this->getPendingVendors($branchIdToUse);
        $banks = BankMaster::query()
            ->where('branch_id', $branchIdToUse)
            ->where('isDeleted', 0)
            ->where('status', 1)
            ->orderBy('bank_name')
            ->get();
        $selectedCustomerOrders = collect();
        $selectedVendorInvoices = collect();
        $totalCustomerRemaining = 0;
        $totalVendorRemaining = 0;

        if ($selectedCustomerId > 0) {
            $selectedCustomerOrders = $this->getPendingOrdersByCustomer($selectedCustomerId, $branchIdToUse);
            $totalCustomerRemaining = $selectedCustomerOrders->sum('remaining_amount');
        }

        if ($selectedVendorId > 0) {
            $selectedVendorInvoices = $this->getPendingInvoicesByVendor($selectedVendorId, $branchIdToUse);
            $totalVendorRemaining = $selectedVendorInvoices->sum('remaining_amount');
        }

        $paymentTransactions = $this->getPaymentTransactions($branchIdToUse, $selectedCustomerId > 0 ? $selectedCustomerId : null, $selectedVendorId > 0 ? $selectedVendorId : null);

        return view('sales.receipt-payment', compact(
            'customers',
            'vendors',
            'banks',
            'selectedCustomerId',
            'selectedVendorId',
            'selectedCustomerOrders',
            'selectedVendorInvoices',
            'selectedOrderId',
            'paymentTransactions',
            'currencySymbol',
            'currencyPosition',
            'totalCustomerRemaining',
            'totalVendorRemaining'
        ));
    }

    public function customerOrders(int $customer): JsonResponse
    {
        $branchIdToUse = $this->resolveBranchIdToUse();
        $orders = $this->getPendingOrdersByCustomer($customer, $branchIdToUse);
        $totalRemaining = $orders->sum('remaining_amount');
        
        // Get payment transactions for this customer
        $paymentTransactions = $this->getPaymentTransactions($branchIdToUse, $customer);

        return response()->json([
            'status' => true,
            'orders' => $orders->values(),
            'total_remaining' => $totalRemaining,
            'payment_transactions' => $paymentTransactions->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'receipt_number' => 'RCPT-' . str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT),
                    'payment_date' => $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d-M-Y') : '-',
                    'customer_name' => optional(optional($payment->order)->user)->name ?? 'N/A',
                    'order_number' => optional($payment->order)->order_number ?? 'N/A',
                    'payment_method' => $payment->payment_method,
                    'payment_type' => $payment->payment_type,
                    'payment_amount' => number_format((float) $payment->payment_amount, 2),
                    'remaining_amount' => number_format((float) $payment->remaining_amount, 2),
                    'remarks' => $payment->remarks ?: '-',
                ];
            }),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:users,id',
            'payment_method' => 'required|in:cash,online,cash_online',
            'paid_type' => 'nullable|in:cash_partially,cash_fully,online_partially,online_fully',
            'cash_online_type' => 'nullable|in:cash_online_fully,cash_online_partially',
            'bank_id' => 'nullable|integer|exists:bank_master,id',
            'amount' => 'nullable|numeric|min:0.01',
            'cash_amount' => 'nullable|numeric|min:0',
            'online_amount' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
        ]);

        $branchIdToUse = $this->resolveBranchIdToUse();
        
        // Get all pending orders for the customer
        $pendingOrders = $this->getPendingOrdersByCustomer($validated['customer_id'], $branchIdToUse);
        
        if ($pendingOrders->isEmpty()) {
            return back()->withInput()->with('error', 'No pending orders found for this customer.');
        }

        $totalRemaining = $pendingOrders->sum('remaining_amount');
        
        if ($totalRemaining <= 0) {
            return back()->withInput()->with('error', 'This customer has no pending amount.');
        }

        $method = strtolower($validated['payment_method']);
        $paymentAmount = 0;
        $cashAmount = 0;
        $upiAmount = 0;
        $paymentType = '';

        // Determine payment amounts based on method
        if ($method === 'cash_online') {
            if (empty($validated['cash_online_type'])) {
                return back()->withInput()->with('error', 'Please select Cash + Online type.');
            }
            
            $isFullyPaid = $validated['cash_online_type'] === 'cash_online_fully';
            
            if ($isFullyPaid) {
                $cashAmount = (float) ($validated['cash_amount'] ?? 0);
                $upiAmount = max(0, $totalRemaining - $cashAmount);
                $paymentAmount = $totalRemaining;
                $paymentType = 'fully';
            } else {
                $cashAmount = (float) ($validated['cash_amount'] ?? 0);
                $upiAmount = (float) ($validated['online_amount'] ?? 0);
                $paymentAmount = $cashAmount + $upiAmount;
                $paymentType = 'partially';
                
                if ($paymentAmount <= 0) {
                    return back()->withInput()->with('error', 'Enter a valid payment amount.');
                }
                
                if ($paymentAmount > $totalRemaining) {
                    return back()->withInput()->with('error', 'Payment amount cannot be greater than total remaining amount.');
                }
            }
        } else {
            // Cash or Online payment
            if (empty($validated['paid_type'])) {
                return back()->withInput()->with('error', 'Please select paid type.');
            }
            
            $methodPrefix = explode('_', $validated['paid_type'])[0] ?? '';
            if ($methodPrefix !== $method) {
                return back()->withInput()->with('error', 'Paid type does not match selected payment method.');
            }
            
            $isFullyPaid = str_ends_with($validated['paid_type'], '_fully');
            $paymentAmount = $isFullyPaid ? $totalRemaining : (float) ($validated['amount'] ?? 0);
            $paymentType = $isFullyPaid ? 'fully' : 'partially';
            
            if (! $isFullyPaid && $paymentAmount <= 0) {
                return back()->withInput()->with('error', 'Enter a valid payment amount.');
            }
            
            if ($paymentAmount > $totalRemaining) {
                return back()->withInput()->with('error', 'Payment amount cannot be greater than total remaining amount.');
            }
            
            $cashAmount = $method === 'cash' ? $paymentAmount : 0;
            $upiAmount = $method === 'online' ? $paymentAmount : 0;
        }

        if (in_array($method, ['online', 'cash_online'], true) && empty($validated['bank_id'])) {
            return back()->withInput()->with('error', 'Please select a bank for online payment.');
        }

        $bankId = in_array($method, ['online', 'cash_online'], true) ? (int) ($validated['bank_id'] ?? 0) : null;

        DB::transaction(function () use ($validated, $pendingOrders, $paymentAmount, $paymentType, $method, $cashAmount, $upiAmount, $bankId) {
            $remainingToDistribute = $paymentAmount;
            
            // Distribute payment across pending orders (oldest first)
            foreach ($pendingOrders as $orderData) {
                if ($remainingToDistribute <= 0) {
                    break;
                }
                
                $order = Order::find($orderData['id']);
                if (!$order) continue;
                
                $orderRemaining = $orderData['remaining_amount'];
                $paymentForThisOrder = min($remainingToDistribute, $orderRemaining);
                $newOrderRemaining = round(max(0, $orderRemaining - $paymentForThisOrder), 2);
                
                // Calculate proportional cash and online amounts for this order
                $proportionalCash = $paymentAmount > 0 ? ($paymentForThisOrder / $paymentAmount) * $cashAmount : 0;
                $proportionalUpi = $paymentAmount > 0 ? ($paymentForThisOrder / $paymentAmount) * $upiAmount : 0;
                
                // Create payment record for this order
                PaymentStore::create([
                    'user_id' => auth()->id(),
                    'order_id' => $order->id,
                    'custom_invoice_id' => null,
                    'purchase_id' => null,
                    'payment_amount' => $paymentForThisOrder,
                    'payment_date' => now('Asia/Kolkata')->toDateString(),
                    'payment_method' => $method === 'cash_online' ? 'cash_online' : $method,
                    'reference_number' => null,
                    'remarks' => $validated['remarks'] ?? null,
                    'payment_type' => $paymentType,
                    'cash_amount' => round($proportionalCash, 2),
                    'upi_amount' => round($proportionalUpi, 2),
                    'emi_month' => 1,
                    'remaining_amount' => $newOrderRemaining,
                    'status' => 'credit',
                    'bank_id' => $bankId,
                    'isDeleted' => 0,
                ]);

                // Update order
                $order->update([
                    'remaining_amount' => $newOrderRemaining,
                    'payment_status' => $newOrderRemaining <= 0 ? 'completed' : 'partially',
                    'payment_method' => $this->normalizeOrderPaymentMethod($method),
                ]);
                
                $remainingToDistribute -= $paymentForThisOrder;
            }
        });

        return redirect()
            ->route('sales.receipt.index', ['customer_id' => $validated['customer_id']])
            ->with('success', 'Payment receipt saved successfully.');
    }

    public function deleteTransaction(int $paymentId): JsonResponse
    {
        try {
            $branchIdToUse = $this->resolveBranchIdToUse();

            DB::transaction(function () use ($paymentId, $branchIdToUse) {
                $payment = PaymentStore::query()
                    ->where('id', $paymentId)
                    ->where('isDeleted', 0)
                    ->lockForUpdate()
                    ->first();

                if (! $payment) {
                    throw new \RuntimeException('Payment transaction not found or already deleted.');
                }

                $status = strtolower((string) $payment->status);
                $hasOrderLink = (int) $payment->order_id > 0;
                $hasPurchaseLink = (int) $payment->purchase_id > 0;

                if ($status === 'credit' || ($status !== 'debit' && $hasOrderLink)) {
                    $order = Order::query()
                        ->where('id', (int) $payment->order_id)
                        ->where('isDeleted', 0)
                        ->where(function ($query) use ($branchIdToUse) {
                            $query->where('branch_id', $branchIdToUse)->orWhereNull('branch_id');
                        })
                        ->lockForUpdate()
                        ->first();

                    if (! $order) {
                        throw new \RuntimeException('Related order not found for this payment.');
                    }

                    $payment->update(['isDeleted' => 1]);

                    $orderTotal = (float) ($order->total_amount ?? 0);
                    $totalPaid = (float) PaymentStore::query()
                        ->where('order_id', $order->id)
                        ->where('isDeleted', 0)
                        ->sum('payment_amount');

                    $newRemaining = round(max(0, $orderTotal - $totalPaid), 2);

                    if ($newRemaining <= 0) {
                        $paymentStatus = 'completed';
                    } elseif ($newRemaining < $orderTotal) {
                        $paymentStatus = 'partially';
                    } else {
                        $paymentStatus = 'pending';
                    }

                    $order->update([
                        'remaining_amount' => $newRemaining,
                        'payment_status' => $paymentStatus,
                    ]);

                    return;
                }

                if ($status === 'debit' || ($status !== 'credit' && $hasPurchaseLink)) {
                    $invoice = PurchaseInvoice::query()
                        ->where('id', (int) $payment->purchase_id)
                        ->where('isDeleted', 0)
                        ->where(function ($query) use ($branchIdToUse) {
                            $query->where('branch_id', $branchIdToUse)->orWhereNull('branch_id');
                        })
                        ->lockForUpdate()
                        ->first();

                    if (! $invoice) {
                        throw new \RuntimeException('Related invoice not found for this payment.');
                    }

                    $payment->update(['isDeleted' => 1]);

                    $invoiceTotal = (float) ($invoice->grand_total ?? $invoice->total_amount ?? 0);
                    $totalPaid = (float) PaymentStore::query()
                        ->where('purchase_id', $invoice->id)
                        ->where('isDeleted', 0)
                        ->sum('payment_amount');

                    $newRemaining = round(max(0, $invoiceTotal - $totalPaid), 2);

                    $invoice->update([
                        'remaining_amount' => $newRemaining,
                    ]);

                    return;
                }

                throw new \RuntimeException('Unsupported payment transaction type.');
            });

            return response()->json([
                'success' => true,
                'message' => 'Payment transaction deleted and amounts reverted successfully.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment transaction. Please try again.',
            ], 500);
        }
    }

    private function resolveBranchIdToUse(): int
    {
        $user = Auth::guard('api')->user() ?? auth()->user();
        
        if (!$user) {
            throw new \Exception('Unauthenticated');
        }
        
        $subAdminId = session('selectedSubAdminId');

        if ($user->role === 'staff' && ! empty($user->branch_id)) {
            return (int) $user->branch_id;
        }

        if ($user->role === 'admin' && ! empty($subAdminId)) {
            return (int) $subAdminId;
        }

        return (int) $user->id;
    }

    private function pendingOrdersBaseQuery(int $branchIdToUse)
    {
        return Order::query()
            ->where('isDeleted', 0)
            ->whereNotNull('user_id')
            ->where(function ($query) use ($branchIdToUse) {
                $query->where('branch_id', $branchIdToUse)->orWhereNull('branch_id');
            })
            ->where(function ($query) {
                $query->whereNull('quotation_status')
                    ->orWhere('quotation_status', 'sales');
            });
    }

    private function getPendingCustomers(int $branchIdToUse): Collection
    {
        $customerIds = $this->pendingOrdersBaseQuery($branchIdToUse)
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values();

        return User::query()
            ->select('id', 'name', 'phone')
            ->whereIn('id', $customerIds)
            ->orderBy('name')
            ->get();
    }

    private function getPendingOrdersByCustomer(int $customerId, int $branchIdToUse): Collection
    {
        $orders = $this->pendingOrdersBaseQuery($branchIdToUse)
            ->where('user_id', $customerId)
            ->orderByDesc('created_at')
            ->get();

        return $this->transformPendingOrders($orders)->values();
    }

    private function getPaymentTransactions(int $branchIdToUse, ?int $customerId = null, ?int $vendorId = null): Collection
    {
        // If vendorId is provided, get vendor payments
        if ($vendorId && ! $customerId) {
            return $this->getVendorPaymentTransactions($branchIdToUse, $vendorId);
        }

        // Otherwise get customer receipts (original logic)
        $query = PaymentStore::query()
            ->with([
                'order:id,order_number,user_id,branch_id',
                'order.user:id,name',
            ])
            ->where('isDeleted', 0)
            ->where('status', 'credit')
            ->whereHas('order', function ($orderQuery) use ($branchIdToUse, $customerId) {
                $orderQuery->where('isDeleted', 0)
                    ->where(function ($query) use ($branchIdToUse) {
                        $query->where('branch_id', $branchIdToUse)->orWhereNull('branch_id');
                    });

                if (! empty($customerId)) {
                    $orderQuery->where('user_id', $customerId);
                }
            })
            ->latest('id');

        return $query->limit(150)->get();
    }

    private function transformPendingOrders(Collection $orders): Collection
    {
        return $orders->map(function (Order $order) {
            $remaining = $this->calculateOrderRemaining($order);
            if ($remaining <= 0) {
                return null;
            }

            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => optional($order->user)->name ?? 'N/A',
                'order_date' => optional($order->created_at)->format('d-M-Y'),
                'total_amount' => (float) ($order->total_amount ?? 0),
                'remaining_amount' => $remaining,
                'payment_status' => $order->payment_status ?? 'pending',
            ];
        })->filter();
    }

    private function calculateOrderRemaining(Order $order): float
    {
        if ($order->remaining_amount !== null && $order->remaining_amount !== '') {
            return max(0, round((float) $order->remaining_amount, 2));
        }

        $totalPaid = (float) PaymentStore::query()
            ->where('order_id', $order->id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');

        $remaining = (float) ($order->total_amount ?? 0) - $totalPaid;

        return max(0, round($remaining, 2));
    }

    private function normalizeOrderPaymentMethod(string $method): string
    {
        if ($method === 'online') {
            return 'Online';
        }

        if ($method === 'cash_online') {
            return 'Cash + Online';
        }

        return 'Cash';
    }

    /**
     * API endpoint for storing receipt payment via AJAX
     */
    public function apiStore(Request $request): JsonResponse
    {
        try {
            // Get authenticated user
            $user = Auth::guard('api')->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated access',
                ], 401);
            }
            
            $validated = $request->validate([
                'customer_id' => 'required|integer|exists:users,id',
                'payment_method' => 'required|in:cash,online,cash_online',
                'paid_type' => 'nullable|in:cash_partially,cash_fully,online_partially,online_fully',
                'cash_online_type' => 'nullable|in:cash_online_fully,cash_online_partially',
                'bank_id' => 'nullable|integer|exists:bank_master,id',
                'amount' => 'nullable|numeric|min:0.01',
                'cash_amount' => 'nullable|numeric|min:0',
                'online_amount' => 'nullable|numeric|min:0',
                'remarks' => 'nullable|string|max:500',
            ]);

            $branchIdToUse = $this->resolveBranchIdToUse();
            
            // Get all pending orders for the customer
            $pendingOrders = $this->getPendingOrdersByCustomer($validated['customer_id'], $branchIdToUse);
            
            if ($pendingOrders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending orders found for this customer.',
                ], 422);
            }

            $totalRemaining = $pendingOrders->sum('remaining_amount');
            
            if ($totalRemaining <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'This customer has no pending amount.',
                ], 422);
            }

            $method = strtolower($validated['payment_method']);
            $paymentAmount = 0;
            $cashAmount = 0;
            $upiAmount = 0;
            $paymentType = '';

            // Determine payment amounts based on method
            if ($method === 'cash_online') {
                if (empty($validated['cash_online_type'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please select Cash + Online type.',
                    ], 422);
                }
                
                $isFullyPaid = $validated['cash_online_type'] === 'cash_online_fully';
                
                if ($isFullyPaid) {
                    $cashAmount = (float) ($validated['cash_amount'] ?? 0);
                    $upiAmount = max(0, $totalRemaining - $cashAmount);
                    $paymentAmount = $totalRemaining;
                    $paymentType = 'fully';
                } else {
                    $cashAmount = (float) ($validated['cash_amount'] ?? 0);
                    $upiAmount = (float) ($validated['online_amount'] ?? 0);
                    $paymentAmount = $cashAmount + $upiAmount;
                    $paymentType = 'partially';
                    
                    if ($paymentAmount <= 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Enter a valid payment amount.',
                        ], 422);
                    }
                    
                    if ($paymentAmount > $totalRemaining) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Payment amount cannot be greater than total remaining amount.',
                        ], 422);
                    }
                }
            } else {
                // Cash or Online payment
                if (empty($validated['paid_type'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please select paid type.',
                    ], 422);
                }
                
                $methodPrefix = explode('_', $validated['paid_type'])[0] ?? '';
                if ($methodPrefix !== $method) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Paid type does not match selected payment method.',
                    ], 422);
                }
                
                $isFullyPaid = str_ends_with($validated['paid_type'], '_fully');
                $paymentAmount = $isFullyPaid ? $totalRemaining : (float) ($validated['amount'] ?? 0);
                $paymentType = $isFullyPaid ? 'fully' : 'partially';
                
                if (! $isFullyPaid && $paymentAmount <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Enter a valid payment amount.',
                    ], 422);
                }
                
                if ($paymentAmount > $totalRemaining) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment amount cannot be greater than total remaining amount.',
                    ], 422);
                }
                
                $cashAmount = $method === 'cash' ? $paymentAmount : 0;
                $upiAmount = $method === 'online' ? $paymentAmount : 0;
            }

            if (in_array($method, ['online', 'cash_online'], true) && empty($validated['bank_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a bank for online payment.',
                    'errors' => [
                        'bank_id' => ['Please select a bank for online payment.'],
                    ],
                ], 422);
            }

            $bankId = in_array($method, ['online', 'cash_online'], true) ? (int) ($validated['bank_id'] ?? 0) : null;

            DB::transaction(function () use ($validated, $pendingOrders, $paymentAmount, $paymentType, $method, $cashAmount, $upiAmount, $user, $bankId) {
                $remainingToDistribute = $paymentAmount;
                
                // Distribute payment across pending orders (oldest first)
                foreach ($pendingOrders as $orderData) {
                    if ($remainingToDistribute <= 0) {
                        break;
                    }
                    
                    $order = Order::find($orderData['id']);
                    if (!$order) continue;
                    
                    $orderRemaining = $orderData['remaining_amount'];
                    $paymentForThisOrder = min($remainingToDistribute, $orderRemaining);
                    $newOrderRemaining = round(max(0, $orderRemaining - $paymentForThisOrder), 2);
                    
                    // Calculate proportional cash and online amounts for this order
                    $proportionalCash = $paymentAmount > 0 ? ($paymentForThisOrder / $paymentAmount) * $cashAmount : 0;
                    $proportionalUpi = $paymentAmount > 0 ? ($paymentForThisOrder / $paymentAmount) * $upiAmount : 0;
                    
                    // Create payment record for this order
                    PaymentStore::create([
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'custom_invoice_id' => null,
                        'purchase_id' => null,
                        'payment_amount' => $paymentForThisOrder,
                        'payment_date' => now('Asia/Kolkata')->toDateString(),
                        'payment_method' => $method === 'cash_online' ? 'cash_online' : $method,
                        'reference_number' => null,
                        'remarks' => $validated['remarks'] ?? null,
                        'payment_type' => $paymentType,
                        'cash_amount' => round($proportionalCash, 2),
                        'upi_amount' => round($proportionalUpi, 2),
                        'emi_month' => 1,
                        'remaining_amount' => $newOrderRemaining,
                        'status' => 'credit',
                        'bank_id' => $bankId,
                        'isDeleted' => 0,
                    ]);

                    // Update order
                    $order->update([
                        'remaining_amount' => $newOrderRemaining,
                        'payment_status' => $newOrderRemaining <= 0 ? 'completed' : 'partially',
                        'payment_method' => $this->normalizeOrderPaymentMethod($method),
                    ]);
                    
                    $remainingToDistribute -= $paymentForThisOrder;
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Payment receipt saved successfully.',
                'data' => [
                    'customer_id' => $validated['customer_id'],
                    'payment_amount' => $paymentAmount,
                    'payment_method' => $method,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get vendors with pending invoices
     */
    private function getPendingVendors(int $branchIdToUse): Collection
    {
        $vendorIds = PurchaseInvoice::query()
            ->where('isDeleted', 0)
            ->where('remaining_amount', '>', 0)
            ->where(function ($query) use ($branchIdToUse) {
                $query->where('branch_id', $branchIdToUse)->orWhereNull('branch_id');
            })
            ->pluck('vendor_id')
            ->filter()
            ->unique()
            ->values();

        return User::query()
            ->select('id', 'name', 'phone')
            ->whereIn('id', $vendorIds)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get pending invoices by vendor
     */
    private function getPendingInvoicesByVendor(int $vendorId, int $branchIdToUse): Collection
    {
        $invoices = PurchaseInvoice::query()
            ->where('isDeleted', 0)
            ->where('vendor_id', $vendorId)
            ->where('remaining_amount', '>', 0)
            ->where(function ($query) use ($branchIdToUse) {
                $query->where('branch_id', $branchIdToUse)->orWhereNull('branch_id');
            })
            ->orderByDesc('created_at')
            ->get();

        return $invoices->map(function (PurchaseInvoice $invoice) {
            return [
                'id' => $invoice->id,
                'order_number' => $invoice->invoice_number,
                'bill_no' => $invoice->bill_no,
                'customer_name' => optional($invoice->vendor)->name ?? 'N/A',
                'order_date' => optional($invoice->created_at)->format('d-M-Y'),
                'total_amount' => (float) ($invoice->grand_total ?? 0),
                'remaining_amount' => (float) $invoice->remaining_amount,
                'payment_status' => $invoice->remaining_amount > 0 ? 'pending' : 'completed',
            ];
        });
    }

    /**
     * Get vendor invoices for API
     */
    public function vendorInvoices(int $vendor): JsonResponse
    {
        $branchIdToUse = $this->resolveBranchIdToUse();
        $invoices = $this->getPendingInvoicesByVendor($vendor, $branchIdToUse);
        $totalRemaining = $invoices->sum('remaining_amount');
        
        // Get payment transactions for this vendor
        $paymentTransactions = $this->getVendorPaymentTransactions($branchIdToUse, $vendor);

        return response()->json([
            'status' => true,
            'invoices' => $invoices->values(),
            'total_remaining' => $totalRemaining,
            'payment_transactions' => $paymentTransactions->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'receipt_number' => 'PAY-' . str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT),
                    'payment_date' => $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d-M-Y') : '-',
                    'customer_name' => optional($payment->purchaseInvoice->vendor)->name ?? 'N/A',
                    'order_number' => optional($payment->purchaseInvoice)->invoice_number ?? 'N/A',
                    'bill_no' => optional($payment->purchaseInvoice)->bill_no ?? 'N/A',
                    'payment_method' => $payment->payment_method,
                    'payment_type' => $payment->payment_type,
                    'payment_amount' => number_format((float) $payment->payment_amount, 2),
                    'remaining_amount' => number_format((float) $payment->remaining_amount, 2),
                    'remarks' => $payment->remarks ?: '-',
                ];
            }),
        ]);
    }

    /**
     * Get vendor payment transactions
     */
    private function getVendorPaymentTransactions(int $branchIdToUse, ?int $vendorId = null): Collection
    {
        $query = PaymentStore::query()
            ->with([
                'purchaseInvoice:id,invoice_number,bill_no,vendor_id,branch_id',
                'purchaseInvoice.vendor:id,name',
            ])
            ->where('isDeleted', 0)
            ->where('status', 'debit')
            ->whereHas('purchaseInvoice', function ($invoiceQuery) use ($branchIdToUse, $vendorId) {
                $invoiceQuery->where('isDeleted', 0)
                    ->where(function ($query) use ($branchIdToUse) {
                        $query->where('branch_id', $branchIdToUse)->orWhereNull('branch_id');
                    });

                if (! empty($vendorId)) {
                    $invoiceQuery->where('vendor_id', $vendorId);
                }
            })
            ->latest('id');

        return $query->limit(150)->get();
    }

    /**
     * API endpoint for storing vendor payment via AJAX
     */
    public function apiVendorStore(Request $request): JsonResponse
    {
        try {
            // Get authenticated user
            $user = Auth::guard('api')->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated access',
                ], 401);
            }
            
            $validated = $request->validate([
                'vendor_id' => 'required|integer|exists:users,id',
                'payment_method' => 'required|in:cash,online,cash_online',
                'paid_type' => 'nullable|in:cash_partially,cash_fully,online_partially,online_fully',
                'cash_online_type' => 'nullable|in:cash_online_fully,cash_online_partially',
                'bank_id' => 'nullable|integer|exists:bank_master,id',
                'amount' => 'nullable|numeric|min:0.01',
                'cash_amount' => 'nullable|numeric|min:0',
                'online_amount' => 'nullable|numeric|min:0',
                'remarks' => 'nullable|string|max:500',
            ]);

            $branchIdToUse = $this->resolveBranchIdToUse();
            
            // Get all pending invoices for the vendor
            $pendingInvoices = $this->getPendingInvoicesByVendor($validated['vendor_id'], $branchIdToUse);
            
            if ($pendingInvoices->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending invoices found for this vendor.',
                ], 422);
            }

            $totalRemaining = $pendingInvoices->sum('remaining_amount');
            
            if ($totalRemaining <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'This vendor has no pending amount.',
                ], 422);
            }

            $method = strtolower($validated['payment_method']);
            $paymentAmount = 0;
            $cashAmount = 0;
            $upiAmount = 0;
            $paymentType = '';

            // Determine payment amounts based on method
            if ($method === 'cash_online') {
                if (empty($validated['cash_online_type'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please select Cash + Online type.',
                    ], 422);
                }
                
                $isFullyPaid = $validated['cash_online_type'] === 'cash_online_fully';
                
                if ($isFullyPaid) {
                    $cashAmount = (float) ($validated['cash_amount'] ?? 0);
                    $upiAmount = max(0, $totalRemaining - $cashAmount);
                    $paymentAmount = $totalRemaining;
                    $paymentType = 'fully';
                } else {
                    $cashAmount = (float) ($validated['cash_amount'] ?? 0);
                    $upiAmount = (float) ($validated['online_amount'] ?? 0);
                    $paymentAmount = $cashAmount + $upiAmount;
                    $paymentType = 'partially';
                    
                    if ($paymentAmount <= 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Enter a valid payment amount.',
                        ], 422);
                    }
                    
                    if ($paymentAmount > $totalRemaining) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Payment amount cannot be greater than total remaining amount.',
                        ], 422);
                    }
                }
            } else {
                // Cash or Online payment
                if (empty($validated['paid_type'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please select paid type.',
                    ], 422);
                }
                
                $methodPrefix = explode('_', $validated['paid_type'])[0] ?? '';
                if ($methodPrefix !== $method) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Paid type does not match selected payment method.',
                    ], 422);
                }
                
                $isFullyPaid = str_ends_with($validated['paid_type'], '_fully');
                $paymentAmount = $isFullyPaid ? $totalRemaining : (float) ($validated['amount'] ?? 0);
                $paymentType = $isFullyPaid ? 'fully' : 'partially';
                
                if (! $isFullyPaid && $paymentAmount <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Enter a valid payment amount.',
                    ], 422);
                }
                
                if ($paymentAmount > $totalRemaining) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment amount cannot be greater than total remaining amount.',
                    ], 422);
                }
                
                $cashAmount = $method === 'cash' ? $paymentAmount : 0;
                $upiAmount = $method === 'online' ? $paymentAmount : 0;
            }

            if (in_array($method, ['online', 'cash_online'], true) && empty($validated['bank_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a bank for online payment.',
                    'errors' => [
                        'bank_id' => ['Please select a bank for online payment.'],
                    ],
                ], 422);
            }

            $bankId = in_array($method, ['online', 'cash_online'], true) ? (int) ($validated['bank_id'] ?? 0) : null;

            DB::transaction(function () use ($validated, $pendingInvoices, $paymentAmount, $paymentType, $method, $cashAmount, $upiAmount, $user, $bankId) {
                $remainingToDistribute = $paymentAmount;
                
                // Distribute payment across pending invoices (oldest first)
                foreach ($pendingInvoices as $invoiceData) {
                    if ($remainingToDistribute <= 0) {
                        break;
                    }
                    
                    $invoice = PurchaseInvoice::find($invoiceData['id']);
                    if (!$invoice) continue;
                    
                    $invoiceRemaining = $invoiceData['remaining_amount'];
                    $paymentForThisInvoice = min($remainingToDistribute, $invoiceRemaining);
                    $newInvoiceRemaining = round(max(0, $invoiceRemaining - $paymentForThisInvoice), 2);
                    
                    // Calculate proportional cash and online amounts for this invoice
                    $proportionalCash = $paymentAmount > 0 ? ($paymentForThisInvoice / $paymentAmount) * $cashAmount : 0;
                    $proportionalUpi = $paymentAmount > 0 ? ($paymentForThisInvoice / $paymentAmount) * $upiAmount : 0;
                    
                    // Create payment record for this invoice
                    PaymentStore::create([
                        'user_id' => $validated['vendor_id'],
                        'order_id' => null,
                        'custom_invoice_id' => null,
                        'purchase_id' => $invoice->id,
                        'payment_amount' => $paymentForThisInvoice,
                        'payment_date' => now('Asia/Kolkata')->toDateString(),
                        'payment_method' => $method === 'cash_online' ? 'cash_online' : $method,
                        'reference_number' => null,
                        'remarks' => $validated['remarks'] ?? null,
                        'payment_type' => $paymentType,
                        'cash_amount' => round($proportionalCash, 2),
                        'upi_amount' => round($proportionalUpi, 2),
                        'emi_month' => 1,
                        'remaining_amount' => $newInvoiceRemaining,
                        'status' => 'debit',
                        'bank_id' => $bankId,
                        'isDeleted' => 0,
                    ]);

                    // Update invoice
                    $invoice->update([
                        'remaining_amount' => $newInvoiceRemaining,
                    ]);
                    
                    $remainingToDistribute -= $paymentForThisInvoice;
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Vendor payment saved successfully.',
                'data' => [
                    'vendor_id' => $validated['vendor_id'],
                    'payment_amount' => $paymentAmount,
                    'payment_method' => $method,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
}
