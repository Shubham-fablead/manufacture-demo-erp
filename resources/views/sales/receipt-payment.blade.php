@extends('layout.app')

@section('title', 'Receipt & Payment Transactions')

@section('content')
    <style>
        .summary-label {
            font-size: 13px;
            color: #6c757d;
        }

        .summary-value {
            font-size: 15px;
            font-weight: 600;
            color: #1b2850;
        }

        .table thead th {
            white-space: nowrap;
        }

        .bank-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 4px;
        }

        .bank-add-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            border: 1px solid #ff9f43;
            background: #fff7ed;
            color: #ff9f43;
            border-radius: 4px;
            padding: 3px 0px;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.2;
            text-decoration: none;
        }

        .bank-add-btn:hover {
            color: #fff;
            background: #ff9f43;
        }

        .responsive-party-name {
            display: none;
            margin-top: 4px;
            font-size: 14px;
            /* font-weight: 600; */
            color: #0a0a0a;
            line-height: 1.35;
            white-space: normal;
            word-break: break-word;
        }

        /* Mobile responsive table styles */
        @media (max-width: 768px) {
            .select2-container {
                width: 100% !important;
            }

            .responsive-table {
                width: 100% !important;
                table-layout: fixed;
            }

            .responsive-table thead th:not(:first-child):not(.details-column) {
                display: none !important;
            }

            .responsive-table tbody td:not(:first-child):not(.details-column) {
                display: none !important;
            }

            .responsive-table thead th.details-column,
            .responsive-table tbody td.details-column {
                display: table-cell !important;
                text-align: center;
                vertical-align: top !important;
                width: 56px !important;
                min-width: 56px !important;
                max-width: 56px !important;
                padding: 12px 6px !important;
            }

            .responsive-table tbody td:first-child {
                display: table-cell !important;
                width: calc(100% - 56px) !important;
                max-width: calc(100vw - 96px) !important;
                vertical-align: top !important;
            }

            .responsive-table tbody td:first-child > div {
                width: 100%;
            }

            .toggle-details {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 44px;
                height: 44px;
                margin-left: auto;
            }

            .toggle-details i {
                font-size: 20px;
            }

            /* .collapse-details {
                margin-top: 10px;
                padding: 10px;
                background-color: #f8f9fa;
                border-radius: 5px;
                border-left: 3px solid #ff9f43;
            } */

            .detail-item {
                display: flex;
                margin-bottom: 8px;
                font-size: 14px;
            }

            .detail-label {
                font-weight: 600;
                min-width: 120px;
                color: #495057;
            }

            .detail-value {
                color: #212529;
                flex: 1;
            }

            .responsive-party-name {
                display: block;
            }
        }

        @media (min-width: 769px) {
            .details-column {
                display: none !important;
            }
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Receipt & Payment Transactions</h4>
            </div>
        </div>

        @if (session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: '{{ session('success') }}',
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                });
            </script>
        @endif

        @if (session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: '{{ session('error') }}',
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                });
            </script>
        @endif

        <div class="card">
            <div class="card-body">
                <form id="receiptPaymentForm" novalidate>
                    @csrf
                    <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-6 mb-3">
                            <label for="transaction_type" class="form-label">Transaction Type</label>
                            <select class="form-select @error('transaction_type') is-invalid @enderror" id="transaction_type" name="transaction_type" required>
                                <option value="">Select Type</option>
                                <option value="receipt" {{ old('transaction_type', 'receipt') === 'receipt' ? 'selected' : '' }}>Receipt</option>
                                <option value="payment" {{ old('transaction_type') === 'payment' ? 'selected' : '' }}>Payment</option>
                            </select>
                            <div id="transaction_type_error" class="invalid-feedback {{ $errors->has('transaction_type') ? 'd-block' : 'd-none' }}">
                                {{ $errors->first('transaction_type') }}
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 col-6 mb-3" id="customer_select_container">
                            <label for="customer_id" class="form-label">Select Customer</label>
                            <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id">
                                <option value="">Select customer</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ (int) old('customer_id', $selectedCustomerId) === (int) $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}{{ $customer->phone ? ' - ' . $customer->phone : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="customer_id_error" class="invalid-feedback {{ $errors->has('customer_id') ? 'd-block' : 'd-none' }}">
                                {{ $errors->first('customer_id') }}
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 col-6 mb-3 d-none" id="vendor_select_container">
                            <label for="vendor_id" class="form-label">Select Vendor</label>
                            <select class="form-select @error('vendor_id') is-invalid @enderror" id="vendor_id" name="vendor_id">
                                <option value="">Select vendor</option>
                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ (int) old('vendor_id') === (int) $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->name }}{{ $vendor->phone ? ' - ' . $vendor->phone : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="vendor_id_error" class="invalid-feedback {{ $errors->has('vendor_id') ? 'd-block' : 'd-none' }}">
                                {{ $errors->first('vendor_id') }}
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-12 col-12 mb-3">
                            <label for="remaining_amount_display" class="form-label">Total Remaining Amount</label>
                            <input type="text" class="form-control" id="remaining_amount_display" readonly value="{{ $selectedCustomerId > 0 ? number_format($totalCustomerRemaining, 2) : '' }}">
                            <input type="hidden" id="total_remaining_value" value="{{ $selectedCustomerId > 0 ? $totalCustomerRemaining : 0 }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-6 mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
                                <option value="">Select</option>
                                <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="online" {{ old('payment_method') === 'online' ? 'selected' : '' }}>Online</option>
                                <option value="cash_online" {{ old('payment_method') === 'cash_online' ? 'selected' : '' }}>Cash + Online</option>
                            </select>
                            <div id="payment_method_error" class="invalid-feedback {{ $errors->has('payment_method') ? 'd-block' : 'd-none' }}">
                                {{ $errors->first('payment_method') }}
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 col-6 mb-3">
                            <label for="paid_type" class="form-label">Paid Type</label>
                            <select class="form-select @error('paid_type') is-invalid @enderror" id="paid_type" name="paid_type" required>
                                <option value="">Select</option>
                            </select>
                            <div id="paid_type_error" class="invalid-feedback {{ $errors->has('paid_type') ? 'd-block' : 'd-none' }}">
                                {{ $errors->first('paid_type') }}
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 col-6 mb-3 d-none" id="cash_online_type_div">
                            <label for="cash_online_type" class="form-label">Cash + Online Type</label>
                            <select class="form-select @error('cash_online_type') is-invalid @enderror" id="cash_online_type" name="cash_online_type">
                                <option value="">Select</option>
                                <option value="cash_online_fully">Cash + Online Fully</option>
                                <option value="cash_online_partially">Cash + Online Partially</option>
                            </select>
                            <div id="cash_online_type_error" class="invalid-feedback {{ $errors->has('cash_online_type') ? 'd-block' : 'd-none' }}">
                                {{ $errors->first('cash_online_type') }}
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12 mb-3 d-none" id="bank_container">
                            <div class="bank-label-row">
                                <label for="bank_id" class="form-label mb-0">Select Bank</label>
                                <button type="button" class="bank-add-btn" id="openAddBankModal">Add Bank</button>
                            </div>
                            <select class="form-select @error('bank_id') is-invalid @enderror" id="bank_id" name="bank_id" style="width: 100%;">
                                <option value="">Select Bank</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->id }}" {{ (int) old('bank_id') === (int) $bank->id ? 'selected' : '' }}>
                                        {{ $bank->bank_name }}{{ $bank->account_number ? ' (' . $bank->account_number . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="bank_id_error" class="invalid-feedback {{ $errors->has('bank_id') ? 'd-block' : 'd-none' }}">
                                {{ $errors->first('bank_id') }}
                            </div>
                        </div>
                    </div>

                    <div class="row d-none" id="cash_online_fields">
                        <div class="col-lg-4 col-md-6 col-6 mb-3">
                            <label for="cash_amount" class="form-label">Cash Amount</label>
                            <input type="number" min="0" step="0.01" class="form-control @error('cash_amount') is-invalid @enderror" id="cash_amount" name="cash_amount">
                            <div id="cash_amount_error" class="invalid-feedback {{ $errors->has('cash_amount') ? 'd-block' : 'd-none' }}">
                                {{ $errors->first('cash_amount') }}
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 col-6 mb-3">
                            <label for="online_amount" class="form-label">Online Amount</label>
                            <input type="number" min="0" step="0.01" class="form-control @error('online_amount') is-invalid @enderror" id="online_amount" name="online_amount" readonly>
                            <div id="online_amount_error" class="invalid-feedback {{ $errors->has('online_amount') ? 'd-block' : 'd-none' }}">
                                {{ $errors->first('online_amount') }}
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 col-12 mb-3">
                            <label for="cash_online_pending" class="form-label">Pending Amount</label>
                            <input type="text" class="form-control" id="cash_online_pending" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-12 mb-3 d-none" id="amount_field_container">
                            <label for="amount" class="form-label">Enter Amount</label>
                            <input type="number" min="0.01" step="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount"
                                name="amount" value="{{ old('amount') }}">
                            <div id="amount_error" class="invalid-feedback {{ $errors->has('amount') ? 'd-block' : 'd-none' }}">
                                {{ $errors->first('amount') }}
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 col-12 mb-3" id="pending_amount_display_container">
                            <label for="pending_amount_display" class="form-label">Pending Amount</label>
                            <input type="text" class="form-control" id="pending_amount_display" readonly>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12 mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="2" placeholder="Enter remarks">{{ old('remarks') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-submit text-white" style="background-color: #ff9f43;">Submit
                            Payment</button>
                        <a href="{{ route('sales.receipt.index') }}" class="btn btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="mb-3" id="pending_table_title">Pending Orders</h5>
                <div class="table-responsive">
                    <table class="table responsive-table">
                        <thead>
                            <tr>
                                <th id="order_number_th">Order Number</th>
                                <th>Date</th>
                                <th>Total Amount</th>
                                <th>Remaining Amount</th>
                                <th>Status</th>
                                <th class="details-column">Details</th>
                            </tr>
                        </thead>
                        <tbody id="pendingOrdersTableBody">
                            @if($selectedCustomerId > 0)
                                @forelse ($selectedCustomerOrders as $order)
                                    <tr>
                                        <td>
                                            <div>
                                                {{ $order['bill_no'] ?? $order['order_number'] ?? $order['invoice_number'] ?? '-' }}
                                                <div class="responsive-party-name">{{ $order['customer_name'] ?? $order['vendor_name'] ?? '' }}</div>
                                                <div class="collapse mt-2 d-lg-none" id="pending-details-{{ $loop->index }}">
                                                    <div class="collapse-details">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Date:</span>
                                                            <span class="detail-value">{{ $order['order_date'] }}</span>
                                                        </div>
                                                        <div class="detail-item">
                                                            <span class="detail-label">Total:</span>
                                                            <span class="detail-value">{{ number_format($order['total_amount'], 2) }}</span>
                                                        </div>
                                                        <div class="detail-item">
                                                            <span class="detail-label">Remaining:</span>
                                                            <span class="detail-value">{{ number_format($order['remaining_amount'], 2) }}</span>
                                                        </div>
                                                        <div class="detail-item">
                                                            <span class="detail-label">Status:</span>
                                                            <span class="detail-value text-capitalize">{{ $order['payment_status'] }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $order['order_date'] }}</td>
                                        <td>{{ number_format($order['total_amount'], 2) }}</td>
                                        <td>{{ number_format($order['remaining_amount'], 2) }}</td>
                                        <td class="text-capitalize">{{ $order['payment_status'] }}</td>
                                        <td class="details-column">
                                            <a href="#pending-details-{{ $loop->index }}" class="toggle-details" data-bs-toggle="collapse">
                                                <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No pending orders for this customer.</td>
                                    </tr>
                                @endforelse
                            @else
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Select a customer to load pending orders.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Payment Transaction History</h5>
                <div class="table-responsive">
                    <table class="table responsive-table">
                        <thead>
                            <tr>
                                <th id="history_order_number_th">Order Number</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Method</th>
                                <th>Type</th>
                                <th>Paid Amount</th>
                                <th>Remaining</th>
                                <th>Remarks</th>
                                <th>Action</th>
                                <th class="details-column">Details</th>
                            </tr>
                        </thead>
                        <tbody id="paymentTransactionsTableBody">
                            @if($selectedCustomerId > 0)
                                @forelse ($paymentTransactions as $payment)
                                    <tr>
                                          <td>
                                              <div>
                                                  {{ $payment->order->bill_no ?? $payment->order->order_number ?? $payment->purchaseInvoice->bill_no ?? $payment->purchaseInvoice->invoice_number ?? 'N/A' }}
                                                  <div class="responsive-party-name">{{ $payment->order->user->name ?? $payment->purchaseInvoice->vendor->name ?? 'N/A' }}</div>
                                                  <div class="collapse mt-2 d-lg-none" id="history-details-{{ $loop->index }}">
                                                      <div class="collapse-details">
                                                          <div class="detail-item">
                                                              <span class="detail-label">Date:</span>
                                                              <span class="detail-value">{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d-M-Y') : '-' }}</span>
                                                          </div>
                                                          {{-- <div class="detail-item">
                                                              <span class="detail-label">Customer/Vendor:</span>
                                                              <span class="detail-value">{{ $payment->order->user->name ?? $payment->purchaseInvoice->vendor->name ?? 'N/A' }}</span>
                                                          </div>
                                                          <div class="detail-item">
                                                              <span class="detail-label">Order/Invoice:</span>
                                                              <span class="detail-value">{{ $payment->order->bill_no ?? $payment->order->order_number ?? $payment->purchaseInvoice->bill_no ?? $payment->purchaseInvoice->invoice_number ?? 'N/A' }}</span>
                                                          </div> --}}
                                                          <div class="detail-item">
                                                              <span class="detail-label">Method:</span>
                                                              <span class="detail-value text-capitalize">{{ $payment->payment_method }}</span>
                                                          </div>
                                                          <div class="detail-item">
                                                              <span class="detail-label">Type:</span>
                                                              <span class="detail-value text-capitalize">{{ $payment->payment_type }}</span>
                                                          </div>
                                                          <div class="detail-item">
                                                              <span class="detail-label">Paid Amount:</span>
                                                              <span class="detail-value">{{ number_format((float) $payment->payment_amount, 2) }}</span>
                                                          </div>
                                                          <div class="detail-item">
                                                              <span class="detail-label">Remaining:</span>
                                                              <span class="detail-value">{{ number_format((float) $payment->remaining_amount, 2) }}</span>
                                                          </div>
                                                          <div class="detail-item">
                                                              <span class="detail-label">Remarks:</span>
                                                              <span class="detail-value">{{ $payment->remarks ?: '-' }}</span>
                                                          </div>
                                                          <div class="detail-item">
                                                              <span class="detail-label">Action:</span>
                                                              <span class="detail-value">
                                                                  <button type="button" class="btn btn-sm btn-outline-danger delete-payment-btn" data-payment-id="{{ $payment->id }}" title="Delete payment">
                                                                      <i class="fas fa-trash"></i>
                                                                  </button>
                                                              </span>
                                                          </div>
                                                      </div>
                                                  </div>
                                              </div>
                                          </td>

                                        <td>
                                            <div>
                                                {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d-M-Y') : '-' }}
                                            </div>
                                        </td>
                                        <td>{{ $payment->order->user->name ?? $payment->purchaseInvoice->vendor->name ?? 'N/A' }}</td>
                                         <td class="text-capitalize">{{ $payment->payment_method }}</td>
                                        <td class="text-capitalize">{{ $payment->payment_type }}</td>
                                        <td>{{ number_format((float) $payment->payment_amount, 2) }}</td>
                                        <td>{{ number_format((float) $payment->remaining_amount, 2) }}</td>
                                        <td>{{ $payment->remarks ?: '-' }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-payment-btn" data-payment-id="{{ $payment->id }}" title="Delete payment">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                        <td class="details-column">
                                            <a href="#history-details-{{ $loop->index }}" class="toggle-details" data-bs-toggle="collapse">
                                                <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">No payment transactions found for this customer.</td>
                                    </tr>
                                @endforelse
                            @else
                                <tr>
                                    <td colspan="10" class="text-center text-muted">Select a customer to load payment transactions.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addBankModal" tabindex="-1" aria-labelledby="addBankModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="addBankForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addBankModalLabel">Add Bank</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="add_bank_name" class="form-label">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="add_bank_name" name="bank_name">
                                    <div class="text-danger small" id="addBankNameError"></div>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="add_account_number" class="form-label">Account Number<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="add_account_number" name="account_number">
                                    <div class="text-danger small" id="addAccountNumberError"></div>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="add_ifsc_code" class="form-label">IFSC Code<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="add_ifsc_code" name="ifsc_code">
                                    <div class="text-danger small" id="addIfscCodeError"></div>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="add_branch_name" class="form-label">Branch Name<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="add_branch_name" name="branch_name">
                                    <div class="text-danger small" id="addBranchNameError"></div>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="add_opening_balance" class="form-label">Opening Balance</label>
                                    <input type="number" class="form-control" id="add_opening_balance" name="opening_balance" min="0" step="0.01" value="0">
                                    <div class="text-danger small" id="addOpeningBalanceError"></div>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="add_bank_status" class="form-label">Status</label>
                                    <select class="form-select" id="add_bank_status" name="status">
                                        <option value="1" selected>Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <div class="text-danger small" id="addBankStatusError"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-submit text-white" id="saveBankBtn" style="background-color: #ff9f43;">Save Bank</button>
                            <button type="button" class="btn btn-secondary btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        (function() {
            const transactionTypeSelect = document.getElementById('transaction_type');
            const customerSelect = document.getElementById('customer_id');
            const vendorSelect = document.getElementById('vendor_id');
            const customerSelectContainer = document.getElementById('customer_select_container');
            const vendorSelectContainer = document.getElementById('vendor_select_container');
            const paymentMethodSelect = document.getElementById('payment_method');
            const paidTypeSelect = document.getElementById('paid_type');
            const cashOnlineTypeSelect = document.getElementById('cash_online_type');
            const bankSelect = document.getElementById('bank_id');
            const amountInput = document.getElementById('amount');
            const cashAmountInput = document.getElementById('cash_amount');
            const onlineAmountInput = document.getElementById('online_amount');
            const remainingDisplay = document.getElementById('remaining_amount_display');
            const pendingDisplay = document.getElementById('pending_amount_display');
            const pendingAmountDisplayContainer = document.getElementById('pending_amount_display_container');
            const cashOnlinePending = document.getElementById('cash_online_pending');
            const pendingOrdersTableBody = document.getElementById('pendingOrdersTableBody');
            const paymentTransactionsTableBody = document.getElementById('paymentTransactionsTableBody');
            const totalRemainingInput = document.getElementById('total_remaining_value');
            const cashOnlineTypeDiv = document.getElementById('cash_online_type_div');
            const cashOnlineFields = document.getElementById('cash_online_fields');
            const bankContainer = document.getElementById('bank_container');
            const pendingTableTitle = document.getElementById('pending_table_title');
            const orderNumberTh = document.getElementById('order_number_th');
            const historyOrderNumberTh = document.getElementById('history_order_number_th');
            const amountFieldContainer = document.getElementById('amount_field_container');
            const remarksInput = document.getElementById('remarks');
            const openAddBankModalButton = document.getElementById('openAddBankModal');
            const addBankModalElement = document.getElementById('addBankModal');
            const addBankForm = document.getElementById('addBankForm');
            const saveBankBtn = document.getElementById('saveBankBtn');
            const addBankModal = addBankModalElement && typeof bootstrap !== 'undefined'
                ? new bootstrap.Modal(addBankModalElement)
                : null;

            const ordersUrlTemplate = @json(route('sales.receipt.orders', ['customer' => '__CUSTOMER__']));
            const vendorInvoicesUrlTemplate = @json(route('sales.receipt.vendor-invoices', ['vendor' => '__VENDOR__']));
            const deleteTransactionUrlTemplate = @json(route('sales.receipt.transaction.delete', ['payment' => '__PAYMENT__']));
            const oldPaidType = @json(old('paid_type'));
            const oldPaymentMethod = @json(old('payment_method'));
            const oldTransactionType = @json(old('transaction_type', 'receipt'));
            const currencySymbol = @json($currencySymbol);
            const currencyPosition = @json($currencyPosition);
            const fieldErrorElements = {
                transaction_type: {
                    input: transactionTypeSelect,
                    error: document.getElementById('transaction_type_error')
                },
                customer_id: {
                    input: customerSelect,
                    error: document.getElementById('customer_id_error')
                },
                vendor_id: {
                    input: vendorSelect,
                    error: document.getElementById('vendor_id_error')
                },
                payment_method: {
                    input: paymentMethodSelect,
                    error: document.getElementById('payment_method_error')
                },
                paid_type: {
                    input: paidTypeSelect,
                    error: document.getElementById('paid_type_error')
                },
                cash_online_type: {
                    input: cashOnlineTypeSelect,
                    error: document.getElementById('cash_online_type_error')
                },
                bank_id: {
                    input: bankSelect,
                    error: document.getElementById('bank_id_error')
                },
                amount: {
                    input: amountInput,
                    error: document.getElementById('amount_error')
                },
                cash_amount: {
                    input: cashAmountInput,
                    error: document.getElementById('cash_amount_error')
                },
                online_amount: {
                    input: onlineAmountInput,
                    error: document.getElementById('online_amount_error')
                },
            };

            function parseAmount(value) {
                if (typeof value === 'number') {
                    return Number.isFinite(value) ? value : 0;
                }

                const normalized = String(value ?? '')
                    .replace(/,/g, '')
                    .replace(/[^0-9.-]/g, '')
                    .trim();

                const parsed = parseFloat(normalized);
                return Number.isFinite(parsed) ? parsed : 0;
            }

            function initSelect2ForDropdowns() {
                if (typeof window.jQuery === 'undefined' || !window.jQuery.fn || !window.jQuery.fn.select2) {
                    return;
                }

                const $ = window.jQuery;
                $('#transaction_type, #customer_id, #vendor_id, #payment_method, #paid_type, #cash_online_type, #bank_id').select2({
                    width: '100%',
                });
            }

            function formatCurrency(value) {
                const formatted = parseAmount(value).toFixed(2);
                return currencyPosition === 'right' ? `${formatted} ${currencySymbol}` : `${currencySymbol}${formatted}`;
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function currentRemainingAmount() {
                return parseAmount(totalRemainingInput.value);
            }

            function clearFieldError(fieldName) {
                const field = fieldErrorElements[fieldName];
                if (!field || !field.input || !field.error) return;

                field.input.classList.remove('is-invalid');
                field.error.textContent = '';
                field.error.classList.add('d-none');
                field.error.classList.remove('d-block');
            }

            function setFieldError(fieldName, message) {
                const field = fieldErrorElements[fieldName];
                if (!field || !field.input || !field.error || !message) return;

                field.input.classList.add('is-invalid');
                field.error.textContent = message;
                field.error.classList.remove('d-none');
                field.error.classList.add('d-block');
            }

            function clearAllFieldErrors() {
                Object.keys(fieldErrorElements).forEach((fieldName) => {
                    clearFieldError(fieldName);
                });
            }

            function refreshSelect2Options(selectElement) {
                if (!selectElement) return;
                if (typeof window.jQuery === 'undefined' || !window.jQuery.fn || !window.jQuery.fn.select2) return;

                window.jQuery(selectElement).trigger('change');
            }

            function resetAddBankForm() {
                if (addBankForm) {
                    addBankForm.reset();
                }
                const openingBalanceInput = document.getElementById('add_opening_balance');
                const bankStatusSelect = document.getElementById('add_bank_status');
                if (openingBalanceInput) {
                    openingBalanceInput.value = '0';
                }
                if (bankStatusSelect) {
                    bankStatusSelect.value = '1';
                }

                [
                    'addBankNameError',
                    'addAccountNumberError',
                    'addIfscCodeError',
                    'addBranchNameError',
                    'addOpeningBalanceError',
                    'addBankStatusError'
                ].forEach(function(id) {
                    const node = document.getElementById(id);
                    if (node) {
                        node.textContent = '';
                    }
                });
            }

            function upsertBankOption(bank) {
                if (!bankSelect || !bank || !bank.id) {
                    return;
                }

                const bankId = String(bank.id);
                const bankName = bank.bank_name || 'Unnamed Bank';
                const accountNumber = bank.account_number ? ` (${bank.account_number})` : '';
                const optionLabel = `${bankName}${accountNumber}`;
                const existingOption = bankSelect.querySelector(`option[value="${bankId}"]`);

                if (existingOption) {
                    existingOption.textContent = optionLabel;
                } else {
                    const option = new Option(optionLabel, bankId, true, true);
                    bankSelect.add(option);
                }

                bankSelect.value = bankId;
                refreshSelect2Options(bankSelect);
                clearFieldError('bank_id');
            }

            function bindSelectChange(selectElement, handler) {
                if (!selectElement || typeof handler !== 'function') return;

                if (typeof window.jQuery !== 'undefined') {
                    window.jQuery(selectElement).on('change', function(event) {
                        handler.call(this, event);
                    });
                    return;
                }

                selectElement.addEventListener('change', handler);
            }

            function toggleAmountFieldVisibility() {
                const paymentMethod = paymentMethodSelect.value;
                const paidType = paidTypeSelect.value;
                const shouldShowAmount = (paymentMethod === 'cash' || paymentMethod === 'online') && !!paidType;

                if (shouldShowAmount) {
                    amountFieldContainer.classList.remove('d-none');
                } else {
                    amountFieldContainer.classList.add('d-none');
                    if (amountInput) {
                        amountInput.value = '';
                        amountInput.readOnly = false;
                    }
                }
            }

            function resetPaymentSelectionFields() {
                clearFieldError('payment_method');
                clearFieldError('paid_type');
                clearFieldError('cash_online_type');
                clearFieldError('bank_id');
                clearFieldError('amount');
                clearFieldError('cash_amount');
                clearFieldError('online_amount');

                paymentMethodSelect.value = '';
                paidTypeSelect.innerHTML = '<option value="">Select</option>';
                cashOnlineTypeSelect.value = '';
                if (bankSelect) {
                    bankSelect.value = '';
                }

                if (amountInput) {
                    amountInput.value = '';
                    amountInput.readOnly = false;
                }

                if (cashAmountInput) {
                    cashAmountInput.value = '';
                    cashAmountInput.readOnly = false;
                    cashAmountInput.oninput = null;
                }

                if (onlineAmountInput) {
                    onlineAmountInput.value = '';
                    onlineAmountInput.readOnly = true;
                    onlineAmountInput.oninput = null;
                }

                if (pendingDisplay) {
                    pendingDisplay.value = '';
                }

                if (cashOnlinePending) {
                    cashOnlinePending.value = '';
                }

                if (remarksInput) {
                    remarksInput.value = '';
                }

                cashOnlineTypeDiv.classList.add('d-none');
                cashOnlineFields.classList.add('d-none');
                bankContainer.classList.add('d-none');
                paidTypeSelect.parentElement.classList.remove('d-none');
                amountFieldContainer.classList.add('d-none');

                refreshSelect2Options(paymentMethodSelect);
                refreshSelect2Options(paidTypeSelect);
                refreshSelect2Options(cashOnlineTypeSelect);
                refreshSelect2Options(bankSelect);
            }

            function focusFirstInvalidField() {
                const firstInvalid = receiptPaymentForm.querySelector('.is-invalid');
                if (!firstInvalid) return;

                firstInvalid.focus();
                firstInvalid.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

            function buildPaidTypeOptions(method, selectedValue) {
                const optionSets = {
                    cash: [{
                            value: 'cash_partially',
                            label: 'Cash Partially'
                        },
                        {
                            value: 'cash_fully',
                            label: 'Cash Fully'
                        }
                    ],
                    online: [{
                            value: 'online_partially',
                            label: 'Online Partially'
                        },
                        {
                            value: 'online_fully',
                            label: 'Online Fully'
                        }
                    ],
                };

                paidTypeSelect.innerHTML = '<option value="">Select</option>';
                (optionSets[method] || []).forEach((item) => {
                    const option = document.createElement('option');
                    option.value = item.value;
                    option.textContent = item.label;
                    if (selectedValue && selectedValue === item.value) {
                        option.selected = true;
                    }
                    paidTypeSelect.appendChild(option);
                });

                refreshSelect2Options(paidTypeSelect);
            }

            function updateAmounts() {
                const remaining = currentRemainingAmount();
                const paymentMethod = paymentMethodSelect.value;

                remainingDisplay.value = remaining > 0 ? formatCurrency(remaining) : '';

                if (pendingAmountDisplayContainer) {
                    if (paymentMethod === 'cash_online') {
                        pendingAmountDisplayContainer.classList.add('d-none');
                    } else {
                        pendingAmountDisplayContainer.classList.remove('d-none');
                    }
                }

                if (!paymentMethod) {
                    pendingDisplay.value = '';
                    cashOnlinePending.value = '';
                    return;
                }

                // Handle Cash + Online
                if (paymentMethod === 'cash_online') {
                    pendingDisplay.value = '';
                    const cashOnlineType = cashOnlineTypeSelect.value;
                    const isFull = cashOnlineType === 'cash_online_fully';

                    if (isFull) {
                        const cash = parseAmount(cashAmountInput.value);
                        const online = Math.max(0, remaining - cash);
                        onlineAmountInput.value = online.toFixed(2);
                        cashOnlinePending.value = formatCurrency(0);
                    } else {
                        const cash = parseAmount(cashAmountInput.value);
                        const online = parseAmount(onlineAmountInput.value);
                        const pending = Math.max(0, remaining - cash - online);
                        cashOnlinePending.value = formatCurrency(pending);
                    }
                    return;
                }

                // Handle Cash and Online
                const paidType = paidTypeSelect.value;
                const isFull = paidType && paidType.endsWith('_fully');
                cashOnlinePending.value = '';

                if (isFull) {
                    if (paymentMethod === 'cash') {
                        // For cash fully, we'll use amount input
                        amountInput.value = remaining > 0 ? remaining.toFixed(2) : '';
                        amountInput.readOnly = true;
                    } else if (paymentMethod === 'online') {
                        // For online fully, we'll use amount input
                        amountInput.value = remaining > 0 ? remaining.toFixed(2) : '';
                        amountInput.readOnly = true;
                    }
                    pendingDisplay.value = formatCurrency(0);
                    return;
                }

                if (amountInput) {
                    amountInput.readOnly = false;
                    const enteredAmount = parseAmount(amountInput.value);
                    const pending = Math.max(0, remaining - enteredAmount);
                    pendingDisplay.value = formatCurrency(pending);
                }
            }

            function fillPendingOrdersTable(orders) {
                if (!orders.length) {
                    pendingOrdersTableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center text-muted">No pending orders for this customer.</td>
                        </tr>
                    `;
                    return;
                }

                pendingOrdersTableBody.innerHTML = orders.map((order, index) => `
                    <tr>
                        <td>
                            <div>
                                <div class="responsive-party-name">${escapeHtml(order.customer_name || order.vendor_name || '')}</div>
                                ${order.bill_no || order.order_number || order.invoice_number || '-'}
                                <div class="collapse mt-2 d-lg-none" id="pending-details-${index}">
                                    <div class="collapse-details">
                                        <div class="detail-item">
                                            <span class="detail-label">Date:</span>
                                            <span class="detail-value">${order.order_date || '-'}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Total:</span>
                                            <span class="detail-value">${parseAmount(order.total_amount).toFixed(2)}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Remaining:</span>
                                            <span class="detail-value">${parseAmount(order.remaining_amount).toFixed(2)}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Status:</span>
                                            <span class="detail-value text-capitalize">${order.payment_status || 'pending'}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>${order.order_date || '-'}</td>
                        <td>${parseAmount(order.total_amount).toFixed(2)}</td>
                        <td>${parseAmount(order.remaining_amount).toFixed(2)}</td>
                        <td class="text-capitalize">${order.payment_status || 'pending'}</td>
                        <td class="details-column">
                            <a href="#pending-details-${index}" class="toggle-details" data-bs-toggle="collapse">
                                <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                            </a>
                        </td>
                    </tr>
                `).join('');
            }

            function fillPaymentTransactionsTable(transactions) {
                if (!transactions.length) {
                    paymentTransactionsTableBody.innerHTML = `
                        <tr>
                            <td colspan="10" class="text-center text-muted">No payment transactions found for this customer.</td>
                        </tr>
                    `;
                    return;
                }

                paymentTransactionsTableBody.innerHTML = transactions.map((transaction, index) => {
                    // Format date if needed (assuming YYYY-MM-DD or similar from API)
                    let formattedDate = transaction.payment_date || '-';
                    const partyName = transaction.customer_name || transaction.vendor_name || 'N/A';
                    if (formattedDate !== '-') {
                        try {
                            const dateObj = new Date(formattedDate);
                            if (!isNaN(dateObj.getTime())) {
                                formattedDate = `${dateObj.getDate().toString().padStart(2, '0')}-${dateObj.toLocaleString('default', { month: 'short' })}-${dateObj.getFullYear()}`;
                            }
                        } catch (e) {}
                    }

                    return `
                        <tr>
                             <td>
                                 <div>
                                    <div class="responsive-party-name">${escapeHtml(partyName)}</div>
                                     ${transactionTypeSelect.value === 'payment' ? (transaction.bill_no || transaction.invoice_number || transaction.order_number || 'N/A') : (transaction.order_number || transaction.invoice_number || transaction.bill_no || 'N/A')}
                                     <div class="collapse mt-2 d-lg-none" id="history-details-${index}">
                                         <div class="collapse-details">
                                             <div class="detail-item">
                                                 <span class="detail-label">Date:</span>
                                                 <span class="detail-value">${formattedDate}</span>
                                             </div>
                                           
                                             <div class="detail-item">
                                                 <span class="detail-label">Method:</span>
                                                 <span class="detail-value text-capitalize">${transaction.payment_method}</span>
                                             </div>
                                             <div class="detail-item">
                                                 <span class="detail-label">Type:</span>
                                                 <span class="detail-value text-capitalize">${transaction.payment_type}</span>
                                             </div>
                                             <div class="detail-item">
                                                 <span class="detail-label">Paid Amount:</span>
                                                 <span class="detail-value">${parseAmount(transaction.payment_amount).toFixed(2)}</span>
                                             </div>
                                             <div class="detail-item">
                                                 <span class="detail-label">Remaining:</span>
                                                 <span class="detail-value">${parseAmount(transaction.remaining_amount).toFixed(2)}</span>
                                             </div>
                                             <div class="detail-item">
                                                 <span class="detail-label">Remarks:</span>
                                                 <span class="detail-value">${transaction.remarks || '-'}</span>
                                             </div>
                                             <div class="detail-item">
                                                 <span class="detail-label">Action:</span>
                                                 <span class="detail-value">
                                                     <button type="button" class="btn btn-sm btn-outline-danger delete-payment-btn" data-payment-id="${transaction.id}" title="Delete payment">
                                                         <i class="fas fa-trash"></i>
                                                     </button>
                                                 </span>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </td>
                             <td>
                                 <div>
                                     ${formattedDate}
                                 </div>
                             </td>
                             <td>${escapeHtml(partyName)}</td>
                             <td class="text-capitalize">${transaction.payment_method}</td>
                             <td class="text-capitalize">${transaction.payment_type}</td>
                             <td>${parseAmount(transaction.payment_amount).toFixed(2)}</td>
                             <td>${parseAmount(transaction.remaining_amount).toFixed(2)}</td>
                             <td>${transaction.remarks || '-'}</td>
                             <td>
                                 <button type="button" class="btn btn-sm btn-outline-danger delete-payment-btn" data-payment-id="${transaction.id}" title="Delete payment">
                                     <i class="fas fa-trash"></i>
                                 </button>
                             </td>
                             <td class="details-column">
                                 <a href="#history-details-${index}" class="toggle-details" data-bs-toggle="collapse">
                                     <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                 </a>
                             </td>
                        </tr>
                    `;
                }).join('');
            }

            function loadCustomerOrders(customerId) {
                if (!customerId) {
                    fillPendingOrdersTable([]);
                    fillPaymentTransactionsTable([]);
                    remainingDisplay.value = '';
                    pendingDisplay.value = '';
                    if (amountInput) amountInput.value = '';
                    if (cashAmountInput) cashAmountInput.value = '';
                    if (onlineAmountInput) onlineAmountInput.value = '';
                    totalRemainingInput.value = 0;
                    return;
                }

                const url = ordersUrlTemplate.replace('__CUSTOMER__', customerId);
                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    })
                    .then((response) => response.json())
                    .then((response) => {
                        const orders = response.orders || [];
                        const totalRemaining = response.total_remaining || 0;
                        const transactions = response.payment_transactions || [];

                        fillPendingOrdersTable(orders);
                        fillPaymentTransactionsTable(transactions);
                        totalRemainingInput.value = totalRemaining;
                        updateAmounts();
                    })
                    .catch(() => {
                        fillPendingOrdersTable([]);
                        fillPaymentTransactionsTable([]);
                        totalRemainingInput.value = 0;
                        updateAmounts();
                    });
            }

            function loadVendorInvoices(vendorId) {
                if (!vendorId) {
                    fillPendingOrdersTable([]);
                    fillPaymentTransactionsTable([]);
                    remainingDisplay.value = '';
                    pendingDisplay.value = '';
                    if (amountInput) amountInput.value = '';
                    if (cashAmountInput) cashAmountInput.value = '';
                    if (onlineAmountInput) onlineAmountInput.value = '';
                    totalRemainingInput.value = 0;
                    return;
                }

                const url = vendorInvoicesUrlTemplate.replace('__VENDOR__', vendorId);
                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    })
                    .then((response) => response.json())
                    .then((response) => {
                        const invoices = response.invoices || [];
                        const totalRemaining = response.total_remaining || 0;
                        const transactions = response.payment_transactions || [];

                        fillPendingOrdersTable(invoices);
                        fillPaymentTransactionsTable(transactions);
                        totalRemainingInput.value = totalRemaining;
                        updateAmounts();
                    })
                    .catch(() => {
                        fillPendingOrdersTable([]);
                        fillPaymentTransactionsTable([]);
                        totalRemainingInput.value = 0;
                        updateAmounts();
                    });
            }

            function reloadCurrentTransactionData() {
                const transactionType = transactionTypeSelect.value;

                if (transactionType === 'receipt' && customerSelect.value) {
                    loadCustomerOrders(customerSelect.value);
                    return;
                }

                if (transactionType === 'payment' && vendorSelect.value) {
                    loadVendorInvoices(vendorSelect.value);
                    return;
                }

                fillPendingOrdersTable([]);
                fillPaymentTransactionsTable([]);
                totalRemainingInput.value = 0;
                updateAmounts();
            }

            function deletePaymentTransaction(paymentId, triggerButton) {
                const url = deleteTransactionUrlTemplate.replace('__PAYMENT__', paymentId);
                const csrfToken = document.getElementById('csrf_token').value;
                const authToken = localStorage.getItem('authToken');

                const headers = {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                };

                if (authToken) {
                    headers['Authorization'] = 'Bearer ' + authToken;
                }

                if (triggerButton) {
                    triggerButton.disabled = true;
                }

                fetch(url, {
                        method: 'DELETE',
                        headers: headers,
                    })
                    .then((response) => response.json())
                    .then((result) => {
                        if (result.success) {
                            showAlert('success', result.message || 'Payment transaction deleted successfully.');
                            reloadCurrentTransactionData();
                            return;
                        }

                        showAlert('danger', result.message || 'Unable to delete payment transaction.');
                    })
                    .catch(() => {
                        showAlert('danger', 'An error occurred while deleting the payment transaction.');
                    })
                    .finally(() => {
                        if (triggerButton) {
                            triggerButton.disabled = false;
                        }
                    });
            }

            bindSelectChange(customerSelect, function() {
                clearFieldError('customer_id');
                if (transactionTypeSelect.value === 'receipt') {
                    resetPaymentSelectionFields();
                    loadCustomerOrders(this.value);
                }
            });

            bindSelectChange(vendorSelect, function() {
                clearFieldError('vendor_id');
                if (transactionTypeSelect.value === 'payment') {
                    resetPaymentSelectionFields();
                    loadVendorInvoices(this.value);
                }
            });

            bindSelectChange(transactionTypeSelect, function() {
                const type = this.value;
                clearFieldError('transaction_type');
                clearFieldError('customer_id');
                clearFieldError('vendor_id');

                // Reset selections
                customerSelect.value = '';
                vendorSelect.value = '';
                refreshSelect2Options(customerSelect);
                refreshSelect2Options(vendorSelect);
                remainingDisplay.value = '';
                pendingDisplay.value = '';
                totalRemainingInput.value = 0;
                fillPendingOrdersTable([]);
                fillPaymentTransactionsTable([]);
                resetPaymentSelectionFields();

                if (type === 'receipt') {
                    customerSelectContainer.classList.remove('d-none');
                    vendorSelectContainer.classList.add('d-none');
                    customerSelect.setAttribute('required', 'required');
                    vendorSelect.removeAttribute('required');
                    pendingTableTitle.textContent = 'Pending Orders';
                    orderNumberTh.textContent = 'Order Number';
                    if (historyOrderNumberTh) historyOrderNumberTh.textContent = 'Order Number';
                } else if (type === 'payment') {
                    customerSelectContainer.classList.add('d-none');
                    vendorSelectContainer.classList.remove('d-none');
                    vendorSelect.setAttribute('required', 'required');
                    customerSelect.removeAttribute('required');
                    pendingTableTitle.textContent = 'Pending Invoices';
                    orderNumberTh.textContent = 'Bill No.';
                    if (historyOrderNumberTh) historyOrderNumberTh.textContent = 'Bill No.';
                } else {
                    customerSelectContainer.classList.add('d-none');
                    vendorSelectContainer.classList.add('d-none');
                    customerSelect.removeAttribute('required');
                    vendorSelect.removeAttribute('required');
                }

                updateAmounts();
            });

            bindSelectChange(paymentMethodSelect, function() {
                const method = this.value;
                clearFieldError('payment_method');
                clearFieldError('paid_type');
                clearFieldError('cash_online_type');
                clearFieldError('bank_id');
                clearFieldError('amount');
                clearFieldError('cash_amount');
                clearFieldError('online_amount');

                // Hide all optional sections first
                cashOnlineTypeDiv.classList.add('d-none');
                cashOnlineFields.classList.add('d-none');
                bankContainer.classList.add('d-none');
                paidTypeSelect.parentElement.classList.remove('d-none');
                if (bankSelect && method !== 'online' && method !== 'cash_online') {
                    bankSelect.value = '';
                    refreshSelect2Options(bankSelect);
                }

                // Reset paid type
                paidTypeSelect.innerHTML = '<option value="">Select</option>';
                refreshSelect2Options(paidTypeSelect);

                if (method === 'cash' || method === 'online') {
                    buildPaidTypeOptions(method, '');
                    amountFieldContainer.classList.add('d-none');
                    if (method === 'online') {
                        bankContainer.classList.remove('d-none');
                    }
                } else if (method === 'cash_online') {
                    paidTypeSelect.parentElement.classList.add('d-none');
                    cashOnlineTypeDiv.classList.remove('d-none');
                    cashOnlineFields.classList.remove('d-none');
                    bankContainer.classList.remove('d-none');
                    amountFieldContainer.classList.add('d-none');
                } else {
                    amountFieldContainer.classList.add('d-none');
                }

                toggleAmountFieldVisibility();
                updateAmounts();
            });

            bindSelectChange(paidTypeSelect, function() {
                clearFieldError('paid_type');
                toggleAmountFieldVisibility();
                updateAmounts();
            });

            bindSelectChange(cashOnlineTypeSelect, function() {
                const type = this.value;
                const remaining = currentRemainingAmount();
                clearFieldError('cash_online_type');

                if (type === 'cash_online_fully') {
                    cashAmountInput.readOnly = false;
                    onlineAmountInput.readOnly = true;

                    // Live calculation
                    cashAmountInput.oninput = function() {
                        const cash = parseAmount(this.value);
                        const online = Math.max(0, remaining - cash);
                        onlineAmountInput.value = online.toFixed(2);
                        cashOnlinePending.value = formatCurrency(0);
                    };
                } else if (type === 'cash_online_partially') {
                    cashAmountInput.readOnly = false;
                    onlineAmountInput.readOnly = false;
                    cashAmountInput.value = '';
                    onlineAmountInput.value = '';

                    // Live calculation
                    cashAmountInput.oninput = onlineAmountInput.oninput = function() {
                        const cash = parseAmount(cashAmountInput.value);
                        const online = parseAmount(onlineAmountInput.value);
                        const pending = Math.max(0, remaining - cash - online);
                        cashOnlinePending.value = formatCurrency(pending);
                    };
                }

                updateAmounts();
            });

            bindSelectChange(bankSelect, function() {
                clearFieldError('bank_id');
            });

            if (openAddBankModalButton) {
                openAddBankModalButton.addEventListener('click', function() {
                    resetAddBankForm();
                    if (addBankModal) {
                        addBankModal.show();
                    }
                });
            }

            if (addBankForm) {
                addBankForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    [
                        'addBankNameError',
                        'addAccountNumberError',
                        'addIfscCodeError',
                        'addBranchNameError',
                        'addOpeningBalanceError',
                        'addBankStatusError'
                    ].forEach(function(id) {
                        const node = document.getElementById(id);
                        if (node) {
                            node.textContent = '';
                        }
                    });

                    const formData = new FormData(addBankForm);
                    const authToken = localStorage.getItem('authToken');
                    const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');

                    if (selectedSubAdminId && selectedSubAdminId !== 'null' && selectedSubAdminId !== 'undefined') {
                        formData.append('selectedSubAdminId', selectedSubAdminId);
                    }

                    if (saveBankBtn) {
                        saveBankBtn.disabled = true;
                        saveBankBtn.textContent = 'Saving...';
                    }

                    const headers = {
                        'X-CSRF-TOKEN': document.getElementById('csrf_token').value,
                        'Accept': 'application/json',
                    };

                    if (authToken) {
                        headers['Authorization'] = 'Bearer ' + authToken;
                    }

                    fetch('/api/banks', {
                        method: 'POST',
                        headers: headers,
                        body: formData,
                    })
                    .then(async (response) => {
                        const result = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw {
                                status: response.status,
                                result: result,
                            };
                        }
                        return result;
                    })
                    .then((result) => {
                        upsertBankOption(result.data || null);
                        if (addBankModal) {
                            addBankModal.hide();
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: result.message || 'Bank added successfully.',
                            confirmButtonText: 'OK'
                        });
                    })
                    .catch((error) => {
                        const errors = error.result?.errors || {};

                        const errorMap = {
                            bank_name: 'addBankNameError',
                            account_number: 'addAccountNumberError',
                            ifsc_code: 'addIfscCodeError',
                            branch_name: 'addBranchNameError',
                            opening_balance: 'addOpeningBalanceError',
                            status: 'addBankStatusError',
                        };

                        Object.keys(errorMap).forEach(function(key) {
                            const node = document.getElementById(errorMap[key]);
                            if (node) {
                                node.textContent = errors[key] ? errors[key][0] : '';
                            }
                        });

                        if (!Object.keys(errors).length) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: error.result?.message || 'Failed to add bank.',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .finally(() => {
                        if (saveBankBtn) {
                            saveBankBtn.disabled = false;
                            saveBankBtn.textContent = 'Save Bank';
                        }
                    });
                });
            }

            if (amountInput) {
                amountInput.addEventListener('input', function() {
                    clearFieldError('amount');
                    updateAmounts();
                });
            }

            if (cashAmountInput) {
                cashAmountInput.addEventListener('input', function() {
                    clearFieldError('cash_amount');
                    updateAmounts();
                });
            }

            if (onlineAmountInput) {
                onlineAmountInput.addEventListener('input', function() {
                    clearFieldError('online_amount');
                    updateAmounts();
                });
            }

            // Initialize on page load
            if (oldTransactionType) {
                transactionTypeSelect.value = oldTransactionType;
                transactionTypeSelect.dispatchEvent(new Event('change'));
            }

            initSelect2ForDropdowns();

            if (oldPaymentMethod) {
                paymentMethodSelect.value = oldPaymentMethod;
                paymentMethodSelect.dispatchEvent(new Event('change'));
            }

            if (oldPaidType) {
                paidTypeSelect.value = oldPaidType;
                paidTypeSelect.dispatchEvent(new Event('change'));
            }

            toggleAmountFieldVisibility();
            updateAmounts();

            // Form submission via AJAX
            const receiptPaymentForm = document.getElementById('receiptPaymentForm');

            paymentTransactionsTableBody.addEventListener('click', function(event) {
                const deleteButton = event.target.closest('.delete-payment-btn');
                if (!deleteButton) {
                    return;
                }

                const paymentId = deleteButton.getAttribute('data-payment-id');
                if (!paymentId) {
                    return;
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Delete payment?',
                    text: 'This will remove the transaction and revert remaining amount.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#dc3545',
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    deletePaymentTransaction(paymentId, deleteButton);
                });
            });

            receiptPaymentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                clearAllFieldErrors();

                // Get form data
                const transactionType = transactionTypeSelect.value;
                const customerId = customerSelect.value;
                const vendorId = vendorSelect.value;
                const paymentMethod = paymentMethodSelect.value;
                const paidType = paidTypeSelect.value;
                const cashOnlineType = cashOnlineTypeSelect.value;
                const bankId = bankSelect ? bankSelect.value : '';
                const amount = amountInput ? amountInput.value : '';
                const cashAmount = cashAmountInput ? cashAmountInput.value : '';
                const onlineAmount = onlineAmountInput ? onlineAmountInput.value : '';
                const remarks = document.getElementById('remarks').value;
                let hasValidationError = false;

                // Validation
                if (!transactionType) {
                    setFieldError('transaction_type', 'Please select a transaction type.');
                    hasValidationError = true;
                }

                if (transactionType === 'receipt' && !customerId) {
                    setFieldError('customer_id', 'Please select a customer.');
                    hasValidationError = true;
                }

                if (transactionType === 'payment' && !vendorId) {
                    setFieldError('vendor_id', 'Please select a vendor.');
                    hasValidationError = true;
                }

                if (!paymentMethod) {
                    setFieldError('payment_method', 'Please select a payment method.');
                    hasValidationError = true;
                }

                // Prepare data object
                const data = {
                    transaction_type: transactionType,
                    payment_method: paymentMethod,
                    remarks: remarks,
                };

                if (paymentMethod === 'online' || paymentMethod === 'cash_online') {
                    if (!bankId) {
                        setFieldError('bank_id', 'Please select a bank.');
                        hasValidationError = true;
                    } else {
                        data.bank_id = bankId;
                    }
                }

                // Add type-specific IDs
                if (transactionType === 'receipt') {
                    data.customer_id = customerId;
                } else {
                    data.vendor_id = vendorId;
                }

                // Add payment-specific fields
                if (paymentMethod === 'cash_online') {
                    if (!cashOnlineType) {
                        setFieldError('cash_online_type', 'Please select Cash + Online type.');
                        hasValidationError = true;
                    } else {
                        data.cash_online_type = cashOnlineType;

                        if (cashOnlineType === 'cash_online_fully') {
                            if (!cashAmount || parseFloat(cashAmount) < 0) {
                                setFieldError('cash_amount', 'Please enter cash amount.');
                                hasValidationError = true;
                            }
                            data.cash_amount = cashAmount;
                        } else {
                            if (!cashAmount || parseFloat(cashAmount) < 0) {
                                setFieldError('cash_amount', 'Please enter cash amount.');
                                hasValidationError = true;
                            }
                            if (!onlineAmount || parseFloat(onlineAmount) < 0) {
                                setFieldError('online_amount', 'Please enter online amount.');
                                hasValidationError = true;
                            }
                            data.cash_amount = cashAmount;
                            data.online_amount = onlineAmount;
                        }
                    }
                } else if (paymentMethod === 'cash' || paymentMethod === 'online') {
                    if (!paidType) {
                        setFieldError('paid_type', 'Please select paid type.');
                        hasValidationError = true;
                    }
                    data.paid_type = paidType;

                    if (!paidType.endsWith('_fully')) {
                        if (!amount || parseFloat(amount) <= 0) {
                            setFieldError('amount', 'Please enter a valid amount.');
                            hasValidationError = true;
                        }
                        data.amount = amount;
                    }
                }

                if (hasValidationError) {
                    focusFirstInvalidField();
                    return;
                }

                // Show loading state
                const submitBtn = receiptPaymentForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

                // Make AJAX call
                const csrfToken = document.getElementById('csrf_token').value;
                const authToken = localStorage.getItem('authToken');

                const headers = {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                };

                // Add auth token if available
                if (authToken) {
                    headers['Authorization'] = 'Bearer ' + authToken;
                }

                const apiUrl = transactionType === 'receipt'
                    ? '/api/receipt-payment/store'
                    : '/api/vendor-payment/store';

                fetch(apiUrl, {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify(data),
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        clearAllFieldErrors();
                        showAlert('success', result.message);

                        // Reset form
                        receiptPaymentForm.reset();
                        refreshSelect2Options(transactionTypeSelect);
                        refreshSelect2Options(customerSelect);
                        refreshSelect2Options(vendorSelect);
                        refreshSelect2Options(paymentMethodSelect);
                        refreshSelect2Options(paidTypeSelect);
                        refreshSelect2Options(cashOnlineTypeSelect);
                        refreshSelect2Options(bankSelect);
                        remainingDisplay.value = '';
                        pendingDisplay.value = '';
                        if (cashOnlinePending) cashOnlinePending.value = '';
                        totalRemainingInput.value = 0;
                        fillPendingOrdersTable([]);
                        fillPaymentTransactionsTable([]);
                        toggleAmountFieldVisibility();
                        updateAmounts();

                        // Reload data to refresh tables
                        if (transactionType === 'receipt' && customerId) {
                            loadCustomerOrders(customerId);
                        } else if (transactionType === 'payment' && vendorId) {
                            loadVendorInvoices(vendorId);
                        }

                        // Also reload page after 3 seconds to refresh all data
                        setTimeout(() => {
                            window.location.reload();
                        }, 3000);
                    } else {
                        if (result.errors && typeof result.errors === 'object') {
                            let apiHasFieldErrors = false;
                            Object.entries(result.errors).forEach(([fieldName, fieldMessages]) => {
                                if (fieldErrorElements[fieldName]) {
                                    const message = Array.isArray(fieldMessages) ? fieldMessages[0] : fieldMessages;
                                    setFieldError(fieldName, message);
                                    apiHasFieldErrors = true;
                                }
                            });

                            if (apiHasFieldErrors) {
                                focusFirstInvalidField();
                                return;
                            }
                        }

                        if (result.message) {
                            showAlert('danger', result.message);
                        } else {
                            showAlert('danger', 'An error occurred.');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', 'An error occurred while processing the payment.');
                })
                .finally(() => {
                    // Restore button state
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });

            // Helper function to show alerts using SweetAlert
            function showAlert(type, message) {
                const iconMap = {
                    'success': 'success',
                    'danger': 'error',
                    'warning': 'warning',
                    'info': 'info'
                };

                Swal.fire({
                    icon: iconMap[type] || 'info',
                    title: type.charAt(0).toUpperCase() + type.slice(1) + '!',
                    text: message,
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });

                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
            // Trigger initial transaction type logic
            if (transactionTypeSelect) {
                if (typeof window.jQuery !== 'undefined') {
                    window.jQuery(transactionTypeSelect).trigger('change');
                } else {
                    transactionTypeSelect.dispatchEvent(new Event('change'));
                }
            }
        })();
        document.addEventListener('DOMContentLoaded', function () {

    // जब collapse खुले
    document.addEventListener('show.bs.collapse', function (e) {
        const targetId = e.target.id;
        const toggle = document.querySelector(`[href="#${targetId}"]`);

        if (toggle) {
            const icon = toggle.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-plus-circle');
                icon.classList.add('fa-minus-circle');
                icon.style.color = '#dc3545';
            }
        }
    });

    // जब collapse बंद हो
    document.addEventListener('hide.bs.collapse', function (e) {
        const targetId = e.target.id;
        const toggle = document.querySelector(`[href="#${targetId}"]`);

        if (toggle) {
            const icon = toggle.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-minus-circle');
                icon.classList.add('fa-plus-circle');
                icon.style.color = '#ff9f43';
            }
        }
    });

});
    </script>
@endpush
