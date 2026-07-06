@extends('layout.app')

@section('title', 'Edit Sales')

@section('content')
    <style>
        .d-none {
            display: none !important;
        }

        /* Fix for labour items select */
        .select2-labour+.select2-container,
        .product-select+.select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            padding-top: 4px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .img-flag {
            vertical-align: middle;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 29px !important;
        }

        .gst-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 8px;
        }

        .gst-badge.with {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            width: fit-content;
        }

        .gst-badge.without {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #e9ecef;
            width: fit-content;
        }

        .product-gst-details {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 8px;
            margin-top: 5px;
            font-size: 12px;
        }

        .product-gst-details small {
            display: block;
            line-height: 1.4;
        }

        .bank-label-row {
            display: flex;
            align-items: center;
            /* justify-content: space-between; */
            gap: 8px;
            margin-bottom: 8px;
        }

        .bank-add-btn {
            border: 1px solid #ff9f43;
            background: #fff7ed;
            color: #ff9f43;
            border-radius: 4px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.2;
        }

        .bank-add-btn:hover {
            background: #ff9f43;
            color: #fff;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1px;
        }

        .page-header .page-title {
            display: flex;
            align-items: center;
        }

        .page-header .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 0;
        }

        .gst-header {
            display: flex;
            align-items: center;
            margin-bottom: 0 !important;
        }

        .gst-header .d-flex {
            display: flex;
            align-items: center;

        }

        .custom-radio-label {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 0;
        }

        .form-check-input {
            margin-top: 0;
        }

        .pos-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: 1px solid #ff9f43;
            background: #ff9f43;
            color: #fff;
            border-radius: 4px;
            padding: 3px 8px;
            font-size: 12px;
            font-weight: 600;
            line-height: 1;
            height: 28px;
            margin-left: 6px;
        }

        .pos-back-btn:hover {
            background: #ff9f43;
            color: #fff;
        }

        .pos-back-btn i {
            font-size: 12px;
            line-height: 1;
        }

        @media (max-width: 767.98px) {
            .page-header {
                flex-wrap: wrap;
                align-items: flex-start;
                gap: 10px;
            }

            .page-header .form-check {
                margin-left: 0 !important;
            }

            .gst-header {
                width: 100%;
                flex-wrap: wrap;
                gap: 10px;
            }

            .gst-header .d-flex {
                flex-wrap: wrap;
                row-gap: 8px;
            }

            .custom-radio-label {
                white-space: nowrap;
            }

            .pos-back-btn {
                margin-left: auto;
                min-width: 70px;
                justify-content: center;
            }

            #paid_type_col,
            #bank_container,
            #cash_amount_col,
            #online_amount_col,
            #pending_amount_col {
                flex: 0 0 50%;
                max-width: 50%;
            }

            #payment_method_col {
                flex: 0 0 100%;
                max-width: 100%;
            }

            #payment_details_row > .col-lg-12 > .row {
                row-gap: 0;
            }

            #bank_container .bank-label-row {
                flex-wrap: nowrap;
                justify-content: space-between;
                align-items: center;
                gap: 6px;
            }

            #bank_container .bank-add-btn {
                padding: 3px 8px;
                font-size: 11px;
                white-space: nowrap;
            }
        }

        @media (max-width: 991.98px) {
            .table-responsive {
                overflow-x: visible;
            }

            .table-responsive .table thead {
                display: none;
            }

            .table-responsive .table,
            .table-responsive .table tbody,
            .table-responsive .table tr,
            .table-responsive .table td {
                display: block;
                width: 100%;
            }

            #product-table-body {
                display: flex;
                flex-direction: column;
                gap: 14px;
            }

            #product-table-body tr[data-product-id] {
                position: relative;
                padding: 14px 14px 12px;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                background: #fff;
                box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            }

            #product-table-body tr[data-product-id]>td {
                border: 0;
                padding: 0;
            }

            #product-table-body tr[data-product-id]>td:first-child {
                display: none;
            }

            #product-table-body tr[data-product-id]>td:nth-child(2) {
                display: grid;
                grid-template-columns: 56px minmax(0, 1fr);
                gap: 12px;
                align-items: start;
                padding-right: 36px;
                margin-bottom: 12px;
            }

            #product-table-body tr[data-product-id]>td:nth-child(2) .product-img {
                display: block;
                width: 56px;
                height: 56px;
                border-radius: 10px;
                overflow: hidden;
                background: #f8fafc;
            }

            #product-table-body tr[data-product-id]>td:nth-child(2) .product-img img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }

            #product-table-body tr[data-product-id]>td:nth-child(2)>a:last-of-type {
                display: block;
                color: #111827;
                font-weight: 600;
                line-height: 1.35;
                margin-bottom: 4px;
                word-break: break-word;
            }

            #product-table-body tr[data-product-id]>td:nth-child(2) .gst-badge {
                margin-left: 0;
                margin-top: 2px;
            }

            #product-table-body tr[data-product-id]>td:not(:first-child):not(:nth-child(2)):not(:last-child) {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                padding: 8px 0;
            }

            #product-table-body tr[data-product-id]>td:not(:first-child):not(:nth-child(2)):not(:last-child)::before {
                content: attr(data-label);
                flex: 0 0 96px;
                color: #6b7280;
                font-size: 12px;
                font-weight: 600;
                line-height: 1.4;
            }

            #product-table-body tr[data-product-id]>td[data-label="GST Details"] {
                align-items: flex-start;
            }

            #product-table-body tr[data-product-id]>td[data-label="GST Details"] .product-gst-details,
            #product-table-body tr[data-product-id]>td[data-label="GST Details"] .text-muted {
                flex: 1 1 auto;
                text-align: right;
            }

            #product-table-body tr[data-product-id]>td[data-label="Total"] .total-amount {
                flex: 1 1 auto;
                text-align: right;
            }

            #product-table-body tr[data-product-id] input.form-control {
                width: 140px !important;
                max-width: 100%;
                margin-left: auto;
                text-align: right;
            }

            #product-table-body tr[data-product-id]>td:last-child {
                position: absolute;
                top: 12px;
                right: 12px;
                width: auto;
                display: block;
            }

            #product-table-body tr[data-product-id]>td:last-child .delete-set {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 26px;
                height: 26px;
            }
        }

        /* Fix for Labour Items on iPad Pro/Tablets */
        @media (min-width: 992px) and (max-width: 1199px) {
            .labour-item-row .col-lg-5 {
                flex: 0 0 33.333333% !important;
                max-width: 33.333333% !important;
            }

            .labour-item-row .col-lg-3 {
                flex: 0 0 25% !important;
                max-width: 25% !important;
            }

            .labour-item-row .col-lg-1 {
                flex: 0 0 16.666667% !important;
                max-width: 16.666667% !important;
            }

            .labour-item-row .btn {
                padding: 6px 10px !important;
                height: 38px !important;
                width: 100% !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            .labour-item-row .select2-container {
                width: 100% !important;
            }
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Edit Sales</h4>
            </div>
            @php
                $user = auth()->user();
            @endphp
            @php
                $subAdminId = session('selectedSubAdminId');
                $role = Auth::user()->role;
            @endphp
            @if ($role == 'admin' || $role == 'staff' || $role == 'sub-admin')
                <div class="form-check ms-3">
                    <input class="form-check-input me-2" type="checkbox" id="quotationToggle" value="quotation"
                        {{ ($sales->quotation_status ?? 'sales') === 'quotation' ? 'checked' : '' }}>
                    <label class="form-check-label" for="quotationToggle">Quotation</label>
                </div>
            @endif
            <div class="gst-header mb-4" style="display: flex; align-items: center; justify-content: space-between;">
                <div class="d-flex">
                    <label class="custom-radio-label" style="margin-right: 1rem;">
                        <input type="radio" name="gst_option" id="without_gst" value="without_gst"
                            {{ $sales->gst_option === 'without_gst' ? 'checked' : '' }} />
                        Without GST
                    </label>

                    <label class="custom-radio-label">
                        <input type="radio" name="gst_option" id="with_gst" value="with_gst"
                            {{ $sales->gst_option === 'with_gst' ? 'checked' : '' }} />
                        With GST
                    </label>
                </div>
                <a href="{{ route('sales.list') }}" class="pos-back-btn">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back
                </a>
            </div>
        </div>




        <div class="card">
            <div class="card-body">
                <div class="row">
                    @php
                        $activePayments = $sales->payments->filter(function ($payment) {
                            return (int) ($payment->isDeleted ?? 0) === 0;
                        });

                        $prefillOrderTotal = max(0, (float) ($sales->total_amount ?? 0));
                        $prefillRemainingAmount = max(0, (float) ($sales->remaining_amount ?? 0));
                        $prefillTotalPaidFromPayments = (float) $activePayments->sum(function ($payment) {
                            return (float) ($payment->payment_amount ?? 0);
                        });
                        $prefillTotalPaid = $prefillTotalPaidFromPayments > 0
                            ? min($prefillOrderTotal, $prefillTotalPaidFromPayments)
                            : max(0, $prefillOrderTotal - $prefillRemainingAmount);
                        $prefillCashAmount = (float) $activePayments->sum(function ($payment) {
                            return (float) ($payment->cash_amount ?? 0);
                        });
                        $prefillOnlineAmount = (float) $activePayments->sum(function ($payment) {
                            $method = strtolower((string) ($payment->payment_method ?? ''));
                            if ($method === 'cash') {
                                return 0;
                            }

                            $upiAmount = (float) ($payment->upi_amount ?? 0);
                            return $upiAmount > 0 ? $upiAmount : (float) ($payment->payment_amount ?? 0);
                        });
                        $prefillBankId = optional($activePayments->first(function ($payment) {
                            return !empty($payment->bank_id);
                        }))->bank_id;

                        $prefillPaymentMethod = strtolower((string) ($sales->payment_method ?? 'pending'));
                        $prefillPaymentMethod = match ($prefillPaymentMethod) {
                            'cash_online', 'cash+bank', 'cash_bank', 'cash + bank', 'cash + online' => 'cash+online',
                            'debit card', 'upi', 'debit', 'scan' => 'online',
                            default => $prefillPaymentMethod,
                        };

                        if ($prefillCashAmount > 0 && $prefillOnlineAmount > 0) {
                            $prefillPaymentMethod = 'cash+online';
                        } elseif ($prefillOnlineAmount > 0 || !empty($prefillBankId)) {
                            $prefillPaymentMethod = 'online';
                        } elseif ($prefillCashAmount > 0) {
                            $prefillPaymentMethod = 'cash';
                        } elseif ($prefillTotalPaid <= 0) {
                            $prefillPaymentMethod = 'pending';
                        }

                        if ($prefillPaymentMethod === 'cash' && $prefillCashAmount <= 0) {
                            $prefillCashAmount = $prefillTotalPaid;
                        }

                        if (in_array($prefillPaymentMethod, ['online', 'debit', 'scan'], true) && $prefillOnlineAmount <= 0) {
                            $prefillOnlineAmount = $prefillTotalPaid;
                        }

                        if ($prefillPaymentMethod === 'cash+online' && $prefillCashAmount <= 0 && $prefillOnlineAmount <= 0) {
                            $prefillOnlineAmount = $prefillTotalPaid;
                        }

                        $prefillPendingAmount = $prefillRemainingAmount > 0
                            ? min($prefillOrderTotal, $prefillRemainingAmount)
                            : max(0, $prefillOrderTotal - $prefillTotalPaid);
                        $prefillPaidType = $prefillPendingAmount > 0 ? '' : 'full';
                        $prefillPaymentStatus = $prefillPendingAmount <= 0 && $prefillTotalPaid > 0
                            ? 'completed'
                            : ($prefillTotalPaid > 0 ? 'partially' : 'pending');

                        if ($prefillPendingAmount > 0) {
                            $prefillPaymentMethod = 'pending';
                        }

                        $displayCashAmount = $prefillPendingAmount > 0 ? 0 : $prefillCashAmount;
                        $displayOnlineAmount = $prefillPendingAmount > 0 ? 0 : $prefillOnlineAmount;
                    @endphp
                    <div class="col-lg-3 col-sm-6 col-6">
                        <input type="hidden" name="update_selse_id" id="update_selse_id" value="{{ $update_id }}">
                        <div class="form-group">
                            <label>Customer</label>
                            <select name="customer_id" id="customer_id" class="form-control select2">
                                <option value="">Select Customer</option>
                                @foreach ($usernames as $user)
                                    <option value="{{ $user->id }}" data-phone="{{ $user->phone }}"
                                        {{ $sales->user_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Order Number</label>
                            <div class="input-groupicon">
                                <input type="text" id="order_number" class="form-control" placeholder="Order number"
                                    name="order_number" value="{{ $sales->order_number ?? '' }}">
                                <span class="text-danger" id="order_number_error" style="display:none;"></span>
                            </div>
                        </div>
                    </div> <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Customer Number</label>
                            <div class="input-groupicon">
                                <input type="tel" id="customer_phone" class="form-control" placeholder="Customer number"
                                    name="customer_phone" value="{{ $sales->user->phone ?? '' }}" readonly>
                                <span class="error_customerphone"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Order Date</label>
                            <div class="input-groupicon">
                                <input type="hidden" id="order_date" name="order_date" value="{{ \Carbon\Carbon::parse($sales->created_at)->format('Y-m-d') }}">
                                <input type="text" class="datetimepicker form-control" id="order_date_display"
                                    value="{{ \Carbon\Carbon::parse($sales->created_at)->format('d/m/Y') }}" required>
                                <a class="addonset">
                                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/calendars.svg' }}" alt="img">
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-6" id="assign_staff_col">
                        <div class="form-group">
                            <label>Assign Staff</label>
                            <select class="select form-control" name="assign_staff" id="assign_staff">
                                <option value="">— Unassigned —</option>
                                @foreach($staffs as $staff)
                                    <option value="{{ $staff->id }}" {{ ($sales->staff_id ?? '') == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-6" id="order_type_col">
                        <div class="form-group">
                            <label>Order Type</label>
                            <select id="order_type" name="order_type" class="form-control select2">
                                <option value="Self Pickup" {{ ($sales->order_type ?? '') == 'Self Pickup' ? 'selected' : '' }}>Self Pickup</option>
                                <option value="Delivery" {{ ($sales->order_type ?? '') == 'Delivery' ? 'selected' : '' }}>Delivery</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-lg-12 col-sm-12 col-12">
                        <div class="form-group">
                            <label>Product Name</label>
                            <div class="input-groupicon">
                                <select name="product_id[]" class="form-control product-select" multiple="multiple" style="width: 100%;">
                                    @php
                                        $user = auth()->user();
                                        $branchIdToUse = $user->role === 'staff' ? $user->branch_id : $user->id;
                                        $settings = \DB::table('settings')->where('branch_id', $branchIdToUse)->first();
                                        $currencySymbol = $settings->currency_symbol ?? '₹';
                                        $currencyPosition = $settings->currency_position ?? 'left';
                                        $selectedProductIds = old(
                                            'product_id',
                                            $sales->order_items->pluck('product_id')->toArray(),
                                        );
                                        $selectedProductIds = array_map('strval', $selectedProductIds);
                                    @endphp

                                    @foreach ($products as $product)
                                        @php
                                            $images = json_decode($product->images ?? '', true);
                                            $imageUrl =
                                                !empty($images) && isset($images[0])
                                                    ? env('ImagePath') . 'storage/' . $images[0]
                                                    : env('ImagePath') . '/admin/assets/img/product/noimage.png';
                                            $priceFormatted = number_format($product->price, 2);
                                            $displayPrice =
                                                $currencyPosition === 'right'
                                                    ? $priceFormatted . ' ' . $currencySymbol
                                                    : $currencySymbol . $priceFormatted;

                                            // Get product GST info
                                            $gstOption = $product->gst_option ?? 'without_gst';
                                            $gstDetails = null;
                                            if ($gstOption === 'with_gst' && $product->product_gst) {
                                                try {
                                                    $gstDetails = json_decode($product->product_gst, true);
                                                } catch (\Exception $e) {
                                                    $gstDetails = null;
                                                }
                                            }
                                        @endphp

                                     <option value="{{ $product->id }}" data-image="{{ $imageUrl }}"
    data-price="{{ $product->price }}" data-name="{{ $product->name }}"
    data-unit="{{ $product->unit->unit_name ?? 'N/A' }}"
    data-gst-option="{{ $gstOption }}"
    data-product-gst="{{ $product->product_gst ?? '[]' }}"
    data-discount="{{ $product->discount ?? 0 }}"
    data-stock="{{ $product->quantity ?? 999999 }}"
    {{ in_array((string) $product->id, $selectedProductIds) ? 'selected' : '' }}>
                                            {{ $product->name }} - {{ $displayPrice }}
                                            @if ($gstOption === 'with_gst')
                                                (With GST)
                                            @else
                                                (Without GST)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="addonset">
                                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/scanner.svg' }}"
                                        alt="img">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="table-responsive mb-3">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th>Unit</th>
                                    <th>QTY</th>
                                    <th>Price</th>
                                    <th>Discount %</th>
                                    <th>GST Details</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="product-table-body">
                                @php
                                    $currencySymbol = $settings->currency_symbol ?? '₹';
                                    $currencyPosition = $settings->currency_position ?? 'left';
                                @endphp

                                @forelse ($sales->order_items as $index => $item)
                                    @php
                                        $product = $item->product;
                                        $gstOption = $product->gst_option ?? 'without_gst';

                                        // Get GST details from order_item, not from product
                                        $gstDetails = null;
                                        $productGstTotal = $item->product_gst_total ?? 0;

                                        if (!empty($item->product_gst_details)) {
                                            if (is_array($item->product_gst_details)) {
                                                $gstDetails = $item->product_gst_details;
                                            } else {
                                                try {
                                                    $gstDetails = json_decode($item->product_gst_details, true);
                                                    // Handle double-encoded JSON payloads.
                                                    if (is_string($gstDetails)) {
                                                        $gstDetails = json_decode($gstDetails, true);
                                                    }
                                                } catch (\Exception $e) {
                                                    $gstDetails = null;
                                                }
                                            }
                                        }

                                        // Handle single GST object shape.
                                        if (is_array($gstDetails) && isset($gstDetails['tax_name'])) {
                                            $gstDetails = [$gstDetails];
                                        }

                                        // Fallback to product GST if order_item doesn't have details
if (
    empty($gstDetails) &&
    $gstOption === 'with_gst' &&
    !empty($product->product_gst)
) {
    if (is_array($product->product_gst)) {
        $gstDetails = $product->product_gst;
    } else {
        try {
            $gstDetails = json_decode($product->product_gst, true);
        } catch (\Exception $e) {
            $gstDetails = null;
        }
    }
}

// Calculate base total
$baseTotal = $item->price * $item->quantity;
$finalTotal = $item->total_amount;

// Prepare GST data for data attribute
$gstDataForAttribute = '[]';
                                        if (!empty($gstDetails) && is_array($gstDetails)) {
                                            $gstDataForAttribute = json_encode($gstDetails);
                                        }
                                    @endphp

                                    <tr data-product-id="{{ $item->product_id }}" data-gst-option="{{ $gstOption }}"
    data-product-gst="{{ $gstDataForAttribute }}"
    data-stock="{{ $item->product->quantity ?? 999999 }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td class="">
                                            @php
                                                $images = json_decode($item->product->images ?? '', true);
                                            @endphp
                                            <a class="product-img">
                                                @if (!empty($images) && isset($images[0]))
                                                    <img src="{{ env('ImagePath') . 'storage/' . $images[0] }}"
                                                        alt="product" width="40">
                                                @else
                                                    <img src="{{ env('ImagePath') . '/admin/assets/img/product/noimage.png' }}"
                                                        alt="No image" width="40">
                                                @endif
                                            </a>
                                            <a href="javascript:void(0);">{{ $item->product->name ?? 'N/A' }}</a>
                                            <span class="gst-badge {{ $gstOption === 'with_gst' ? 'with' : 'without' }}">
                                                {{ $gstOption === 'with_gst' ? 'With GST' : 'Without GST' }}
                                            </span>
                                        </td>
                                        <td data-label="Unit">
                                            {{ $item->product->unit->unit_name ?? 'N/A' }}
                                        </td>
                                        <td data-label="QTY">
                                            <input type="text" name="quantities[{{ $item->product_id }}]"
                                                class="form-control quantity-input"
                                                value="{{ number_format($item->quantity, 2, '.', '') }}" step="1"
                                                min="0" style="width: 80px;">
                                        </td>
                                        <td data-label="Price">
                                            <input type="text" name="prices[{{ $item->product_id }}]"
                                                class="form-control price-input"
                                                value="{{ number_format($item->price, 2, '.', '') }}" min="0"
                                                step="0.01" style="width: 90px;">
                                        </td>
                                        <td data-label="Discount %">
                                            <input type="text" name="discounts[{{ $item->product_id }}]"
                                                class="form-control discount-input"
                                                value="{{ number_format($item->discount_percentage ?? 0, 2, '.', '') }}"
                                                min="0" max="100" step="0.01" style="width: 80px;">
                                        </td>
                                        <td class="gst-details-cell" data-label="GST Details">
                                            @if ($gstDetails && is_array($gstDetails))
                                                <div class="product-gst-details">
                                                    @foreach ($gstDetails as $tax)
                                                        <small>
                                                            {{ $tax['tax_name'] ?? 'GST' }}: {{ $tax['tax_rate'] ?? 0 }}%
                                                            @if (isset($tax['tax_amount']))
                                                                ({{ number_format($tax['tax_amount'], 2) }})
                                                            @endif
                                                        </small>
                                                    @endforeach
                                                    @if ($productGstTotal > 0)
                                                        <small style="font-weight: bold; color: #333;">
                                                            GST Total:
                                                            {{ $currencyPosition === 'right' ? number_format($productGstTotal, 2) . $currencySymbol : $currencySymbol . number_format($productGstTotal, 2) }}
                                                        </small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">No GST</span>
                                            @endif
                                        </td>
                                        <td data-label="Total">
                                            <span class="total-amount">
                                                <div style="color:#ff9f43;">
                                                    <strong>Sub Total:</strong>
                                                    @if ($currencyPosition === 'right')
                                                        {{ number_format($baseTotal, 2) }}{{ $currencySymbol }}
                                                    @else
                                                        {{ $currencySymbol }}{{ number_format($baseTotal, 2) }}
                                                    @endif
                                                </div>

                                                @if ($gstOption === 'with_gst')
                                                    <div style="color:#007bff;">
                                                        <strong>GST Included:</strong>
                                                        @if ($currencyPosition === 'right')
                                                            {{ number_format($baseTotal + $productGstTotal, 2) }}{{ $currencySymbol }}
                                                        @else
                                                            {{ $currencySymbol }}{{ number_format($baseTotal + $productGstTotal, 2) }}
                                                        @endif
                                                    </div>
                                                @endif

                                                @php
                                                    $discountAmt =
                                                        $item->price *
                                                        $item->quantity *
                                                        (($item->discount_percentage ?? 0) / 100);
                                                @endphp

                                                @if ($discountAmt > 0)
                                                    <div style="color:red;">
                                                        <strong>Discount:</strong> -
                                                        @if ($currencyPosition === 'right')
                                                            {{ number_format($discountAmt, 2) }}{{ $currencySymbol }}
                                                        @else
                                                            {{ $currencySymbol }}{{ number_format($discountAmt, 2) }}
                                                        @endif
                                                    </div>
                                                @endif

                                                <div
                                                    style="font-weight:bold; margin-top:4px; border-top:1px solid #ddd; padding-top:3px;color:green;">
                                                    Final Total:
                                                    @if ($currencyPosition === 'right')
                                                        {{ number_format($item->total_amount, 2) }}{{ $currencySymbol }}
                                                    @else
                                                        {{ $currencySymbol }}{{ number_format($item->total_amount, 2) }}
                                                    @endif
                                                </div>
                                            </span>
                                        </td>
                                        <td data-label="Action">
                                            <a href="javascript:void(0);" class="delete-set">
                                                <img src="{{ env('ImagePath') . 'admin/assets/img/icons/delete.svg' }}"
                                                    alt="svg">
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="no-products-row">
                                        <td colspan="7" class="text-center">No products selected</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>



                <div class="row">

                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Shipping</label>
                            <input type="number" class="form-control" name="shipping" id="shipping-input"
                                value="{{ $sales->shipping ?? 0 }}" min="0" step="0.01">
                            <div id="shipping-error" class="text-danger mt-1" style="display:none;"></div>
                        </div>
                    </div>

                    @if ((bool) ($setting->tds_apply ?? false))
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>TDS Percentage (%)</label>
                                <input type="number" class="form-control" name="tds_percentage"
                                    id="tds-percentage-input"
                                    value="{{ number_format((float) ($sales->tds_percentage ?? 0), 2, '.', '') }}"
                                    min="0" max="100" step="0.01">
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>TDS Amount</label>
                                <input type="number" class="form-control" name="tds_amount" id="tds-amount-input"
                                    value="{{ number_format((float) ($sales->tds_amount ?? 0), 2, '.', '') }}"
                                    min="0" step="0.01">
                            </div>
                        </div>
                    @endif

                    <div class="col-lg-3 col-sm-6 col-6" id="payment_method_col">
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select class="select form-control" name="payment_method" id="payment_method">
                                <option value="pending" {{ $prefillPaymentMethod === 'pending' ? 'selected' : '' }}>
                                    Pending
                                </option>
                                <option value="cash" {{ $prefillPaymentMethod === 'cash' ? 'selected' : '' }}>Cash
                                </option>
                                <option value="online" {{ in_array($prefillPaymentMethod, ['online', 'debit', 'scan'], true) ? 'selected' : '' }}>Online
                                </option>
                                <option value="cash+online" {{ $prefillPaymentMethod === 'cash+online' ? 'selected' : '' }}>
                                    Cash+Online
                                </option>
                                <option value="emi" {{ $prefillPaymentMethod === 'emi' ? 'selected' : '' }}>EMI
                                </option>
                            </select>
                        </div>
                    </div>

                    

                    <div class="col-lg-3 col-sm-6 col-6 d-none" id="payment_status_col">
                        <div class="form-group">
                            <label>Payment Status</label>
                            <select class="select form-control" name="status" id="payment_status">
                                <option value="pending" {{ $prefillPaymentStatus === 'pending' ? 'selected' : '' }}>
                                    Pending
                                </option>
                                <option value="partially" {{ $prefillPaymentStatus === 'partially' ? 'selected' : '' }}>
                                    Partially Paid
                                </option>
                                <option value="completed" {{ $prefillPaymentStatus === 'completed' ? 'selected' : '' }}>
                                    Completed
                                </option>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="row" id="payment_details_row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-3 col-sm-6 col-6 " id="paid_type_col">
                                <div class="form-group">
                                    <label>Paid Type</label>
                                    <select class="select form-control" name="paid_type" id="paid_type">
                                        <option value="" {{ $prefillPaidType === '' ? 'selected' : '' }}>Select Paid Type</option>
                                        <option value="full" {{ $prefillPaidType === 'full' ? 'selected' : '' }}>Fully</option>
                                        <option value="partial" {{ $prefillPaidType === 'partial' ? 'selected' : '' }}>Partially</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6 col-12 " id="bank_container">
                                <div class="form-group">
                                    <div class="bank-label-row">
                                        <label for="bank_id" class="mb-0">SelectBank</label>
                                        <button type="button" class="bank-add-btn" id="openAddBankModal">Add Bank</button>
                                    </div>
                                    <select class="form-control" id="bank_id" name="bank_id" style="width: 100%;">
                                        <option value="">Select Bank</option>
                                        @foreach ($banks as $bank)
                                            <option value="{{ $bank->id }}" {{ (int) $prefillBankId === (int) $bank->id ? 'selected' : '' }}>
                                                {{ $bank->bank_name }}{{ $bank->account_number ? ' (' . $bank->account_number . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6 col-6 " id="cash_amount_col">
                                <div class="form-group">
                                    <label id="cash_amount_label" for="cash_amount">Cash Amount</label>
                                    <input type="text" class="form-control" id="cash_amount" name="cash_amount"
                                        data-prefill="{{ number_format($prefillCashAmount, 2, '.', '') }}"
                                        value="{{ number_format($displayCashAmount, 2, '.', '') }}">
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6 col-6 " id="online_amount_col">
                                <div class="form-group">
                                    <label id="online_amount_label" for="online_amount">Bank Amount</label>
                                    <input type="text" class="form-control" id="online_amount" name="online_amount"
                                        data-prefill="{{ number_format($prefillOnlineAmount, 2, '.', '') }}"
                                        value="{{ number_format($displayOnlineAmount, 2, '.', '') }}">
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6 col-6 " id="pending_amount_col">
                                <div class="form-group">
                                    <label for="pending_amount">Pending Amount</label>
                                    <input type="text" class="form-control" id="pending_amount" name="pending_amount"
                                        data-prefill="{{ number_format($prefillPendingAmount, 2, '.', '') }}"
                                        data-paid-total="{{ number_format($prefillTotalPaid, 2, '.', '') }}"
                                        value="{{ number_format($prefillPendingAmount, 2, '.', '') }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="emiBox" style="display:none; margin-bottom:15px; border:1px solid #eee; padding:15px; border-radius:5px;">
                    <h5 class="mb-3">EMI Details</h5>
                    <div class="row">
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Down Payment (Optional)</label>
                                <input type="number" class="form-control" id="emiDownPayment" placeholder="₹ Amount" min="0" step="0.01" value="{{ $sales->emi_down_payment ?? '' }}">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Loan Amount</label>
                                <input type="number" class="form-control" id="emiLoanAmount" placeholder="Auto Calculate" min="0" step="0.01" value="{{ $sales->emi_loan_amount ?? '' }}" readonly>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-6">
                            @php
                                $savedTenure = $sales->emi_tenure ?? '';
                                $isCustomTenure = !in_array($savedTenure, ['3', '6', '9', '12', '', null]);
                            @endphp
                            <div class="form-group">
                                <label>EMI Tenure</label>
                                <select class="form-control" id="emiTenure">
                                    <option value="">Select Tenure</option>
                                    <option value="3" {{ $savedTenure == '3' ? 'selected' : '' }}>3 Months</option>
                                    <option value="6" {{ $savedTenure == '6' ? 'selected' : '' }}>6 Months</option>
                                    <option value="9" {{ $savedTenure == '9' ? 'selected' : '' }}>9 Months</option>
                                    <option value="12" {{ $savedTenure == '12' ? 'selected' : '' }}>12 Months</option>
                                    <option value="custom" {{ $isCustomTenure ? 'selected' : '' }}>Custom</option>
                                </select>
                                <small class="text-danger d-none" id="emiTenureError"></small>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-6 {{ $isCustomTenure ? '' : 'd-none' }}" id="emiCustomTenureCol">
                            <div class="form-group">
                                <label>Custom Tenure (Months)</label>
                                <input type="number" class="form-control" id="emiCustomTenure" min="1" max="120" step="1" placeholder="Enter months" value="{{ $isCustomTenure ? $savedTenure : '' }}">
                                <small class="text-danger d-none" id="emiCustomTenureError"></small>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Interest Rate (%) <small class="text-muted">Optional</small></label>
                                <input type="number" class="form-control" id="emiInterestRate" value="{{ $sales->emi_interest_rate ?? '0' }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Monthly EMI</label>
                                <input type="number" class="form-control" id="emiMonthlyAmount" placeholder="Auto Calculate" min="0" step="0.01" value="{{ $sales->emi_monthly_amount ?? '' }}" readonly>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Aadhar Number</label>
                                <input type="text" class="form-control" id="emiAadharNumber" placeholder="Customer Aadhar Number" value="{{ $sales->emi_aadhar_number ?? '' }}">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>DO ID</label>
                                <input type="text" class="form-control" id="emiDoId" placeholder="DO ID" value="{{ $sales->emi_do_id ?? '' }}">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>PAN Number <small class="text-muted">Optional</small></label>
                                <input type="text" class="form-control" id="emiPanNumber" placeholder="PAN Number" value="{{ $sales->emi_pan_number ?? '' }}">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Guarantor Name <small class="text-muted">Optional</small></label>
                                <input type="text" class="form-control" id="emiGuarantorName" placeholder="Guarantor Name" value="{{ $sales->emi_guarantor_name ?? '' }}">
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12 col-12">
                            <div class="form-group">
                                <div class="bank-label-row">
                                    <label class="mb-0">Select Bank</label>
                                    <button type="button" class="bank-add-btn" id="openAddBankModalEmi">Add Bank</button>
                                </div>
                                <select class="form-control" id="emiBankId">
                                    <option value="">Select Bank</option>
                                    @foreach ($banks as $bank)
                                        <option value="{{ $bank->id }}" {{ (($sales->emi_bank_id ?? $sales->bank_id ?? '') == $bank->id) ? 'selected' : '' }}>
                                            {{ $bank->bank_name }}{{ $bank->account_number ? ' (' . $bank->account_number . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-danger d-none" id="emiBankError"></small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row ">
                    <div class="col-lg-6 mb-3">
                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea class="form-control" name="remarks" id="remarks" rows="6" placeholder="Enter any remarks">{{ old('remarks', $sales->remarks ?? '') }}</textarea>
                        </div>
                    </div>
                    <div class="col-lg-6 justify-content-end">
                            @php
                                // 1. Calculate Product Subtotal (Gross)
                                $productsSubtotal = $sales->order_items->sum(function ($item) {
                                    return $item->price * $item->quantity;
                                });

                                // Total product-level discounts
                                $totalProductDiscounts = $sales->order_items->sum(function ($item) {
                                    return $item->price * $item->quantity * (($item->discount_percentage ?? 0) / 100);
                                });

                                // Products Net Subtotal (after product discounts but before order discount)
                                $productsNetSubtotal = $productsSubtotal - $totalProductDiscounts;

                                // 2. Order Discount is now removed
                                $discountPercent = 0;
                                $discountAmount = 0;

                                // 3. After Discount (All Product discounts)
                                $productsAfterDiscount = $productsNetSubtotal;

                                // 4. Calculate Labour Subtotal
                                $labourSubtotal = 0;
                                if (isset($sales) && $sales->labour_items) {
                                    $labourSubtotal = $sales->labour_items->sum(function ($item) {
                                        return $item->qty * $item->price;
                                    });
                                }

                                // 5. Calculate Shipping
                                $shippingCost = $sales->shipping ?? 0;

                                // 5.1 Calculate TDS
                                $isTdsEnabled = (bool) ($setting->tds_apply ?? false);
                                $tdsPercentage = $isTdsEnabled ? (float) ($sales->tds_percentage ?? 0) : 0;
                                $storedTdsAmount = (float) ($sales->tds_amount ?? 0);

                                // 6. Calculate Taxes on (Products After Discount) only
                                $taxRates = $TaxRate;
                                $totalTaxAmount = 0;
                                $taxDetails = [];

                                foreach ($taxRates as $tax) {
                                    $taxAmount = ($productsAfterDiscount * $tax->tax_rate) / 100;
                                    $taxDetails[] = [
                                        'name' => $tax->tax_name,
                                        'rate' => $tax->tax_rate,
                                        'amount' => $taxAmount,
                                    ];
                                    $totalTaxAmount += $taxAmount;
                                }

                                // 7. Grand Total
                            // Subtotal = product amount + GST - discount.
                            $subtotalAmount = $productsAfterDiscount;
                            // TDS base stays on raw product base amount.
                            $preTdsTotal =
                                $subtotalAmount + $labourSubtotal + $shippingCost;
                            $tdsAmount = $isTdsEnabled
                                ? ($storedTdsAmount > 0
                                        ? $storedTdsAmount
                                        : ($productsSubtotal * $tdsPercentage) / 100)
                                    : 0;
                            $grandTotal = $preTdsTotal - $tdsAmount;
                                $roundedGrandTotal = round($grandTotal);
                                $roundOffAmount = $roundedGrandTotal - $grandTotal;
                            @endphp

                            <!-- Labour Items Section -->
                            @if ($role == 'admin' || $role == 'staff' || $role == 'sub-admin')
                                <div class="col-lg-12 mb-3">
                                    <div class="select-split">
                                        <div class="select-group w-100">
                                            <hr>
                                            <h5 style=" font-weight: 600; font-size: 19px; ">Labour Items</h5>
                                            <div id="labour-items-container">
                                                @if (isset($sales->labour_items) && $sales->labour_items->count() > 0)
                                                    @foreach ($sales->labour_items as $index => $item)
                                                        <div class="row mb-2 labour-item-row">
                                                            <div class="col-lg-5 col-sm-4 col-4">
                                                                <select name="labour_item_id[]"
                                                                    class="form-control select2 select2-labour">
                                                                    <option value="">Select Labour Item</option>
                                                                    @foreach ($labourItems as $lItem)
                                                                        <option value="{{ $lItem->id }}"
                                                                            data-price="{{ $lItem->price }}"
                                                                            {{ $item->labour_item_id == $lItem->id ? 'selected' : '' }}>
                                                                            {{ $lItem->item_name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-lg-3 col-sm-3 col-3">
                                                                <input type="text" name="labour_qty[]"
                                                                    class="form-control labour-qty" placeholder="Qty"
                                                                    value="{{ $item->qty }}" min="0">
                                                            </div>
                                                            <div class="col-lg-3 col-sm-3 col-3">
                                                                <input type="text" name="labour_price[]"
                                                                    class="form-control labour-price" placeholder="Price"
                                                                    value="{{ $item->price }}" min="0">
                                                            </div>
                                                            <div class="col-lg-1 col-sm-2 col-2">
                                                                @if ($loop->last)
                                                                    <button type="button"
                                                                        class="btn btn-success add-labour-item">
                                                                        <i class="fas fa-plus"></i>
                                                                    </button>
                                                                @else
                                                                    <button type="button"
                                                                        class="btn btn-danger remove-labour-item">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="row mb-2 labour-item-row">
                                                        <div class="col-lg-5 col-sm-4 col-4">
                                                            <select name="labour_item_id[]"
                                                                class="form-control select2 select2-labour">
                                                                <option value="">Select Labour Item</option>
                                                                @foreach ($labourItems as $lItem)
                                                                    <option value="{{ $lItem->id }}"
                                                                        data-price="{{ $lItem->price }}">
                                                                        {{ $lItem->item_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-lg-3 col-sm-3 col-3">
                                                            <input type="number" name="labour_qty[]"
                                                                class="form-control labour-qty" placeholder="Qty"
                                                                value="1" min="0">
                                                        </div>
                                                        <div class="col-lg-3 col-sm-3 col-3">
                                                            <input type="number" name="labour_price[]"
                                                                class="form-control labour-price" placeholder="Price"
                                                                value="0" min="0">
                                                        </div>
                                                        <div class="col-lg-1 col-sm-2 col-2">
                                                            <button type="button"
                                                                class="btn btn-success add-labour-item">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <hr>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <!-- End Labour Items Section -->

                            <div class="total-order w-100 max-widthauto m-auto mb-4">
                                <ul>
                                    <li class="subtotal">
                                        <h4>Total (Products)</h4>
                                        <h5>
                                            @if ($setting->currency_position === 'right')
                                                <span
                                                    id="subtotal-display">{{ number_format($productsSubtotal, 2) }}</span>{{ $setting->currency_symbol ?? '₹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '₹' }}<span
                                                    id="subtotal-display">{{ number_format($productsSubtotal, 2) }}</span>
                                            @endif
                                        </h5>
                                    </li>

                                    <li class="total-gst" style="display: none;">
                                        <h4>Total GST</h4>
                                        <h5>
                                            @if ($setting->currency_position === 'right')
                                                <span
                                                    id="total-gst-amount">0.00</span>{{ $setting->currency_symbol ?? '₹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '₹' }}<span
                                                    id="total-gst-amount">0.00</span>
                                            @endif
                                        </h5>
                                    </li>
                                    <li class="product-discount"
                                        @if ($totalProductDiscounts <= 0) style="display:none;" @endif>
                                        <h4>Discounts</h4>
                                        <h5>
                                            @if ($setting->currency_position === 'right')
                                                <span
                                                    id="product-discount-total-display">{{ number_format($totalProductDiscounts, 2) }}</span>{{ $setting->currency_symbol ?? '₹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '₹' }}<span
                                                    id="product-discount-total-display">{{ number_format($totalProductDiscounts, 2) }}</span>
                                            @endif
                                        </h5>
                                    </li>

                                    <!-- <li class="discount">
                                                    <h4>Discount</h4>
                                                    <h5>
                                                        <span
                                                            id="discount-percent">{{ number_format($discountPercent, 2) }}</span>%
                                                        (
                                                        @if ($setting->currency_position === 'right')
                                                        <span
                                                                                                                    id="discount-amount">{{ number_format($discountAmount, 2) }}</span>{{ $setting->currency_symbol ?? '₹' }}
                                                    @else
                                                        {{ $setting->currency_symbol ?? '₹' }}<span
                                                                                                                    id="discount-amount">{{ number_format($discountAmount, 2) }}</span>
                                                        @endif
                                                                                                            )
                                                    </h5>
                                                </li> -->

                                    <li class="after-discount"
                                        @if ($totalProductDiscounts <= 0) style="display:none;" @endif>
                                        <h4>Sub Total</h4>
                                        <h5 id="after-discount-display">
                                            {{-- @if ($setting->currency_position === 'right')
                                                0.00{{ $setting->currency_symbol ?? '₹' }} +
                                                0.00{{ $setting->currency_symbol ?? '₹' }} -
                                                0.00{{ $setting->currency_symbol ?? '₹' }} =
                                                0.00{{ $setting->currency_symbol ?? '₹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '₹' }}0.00 +
                                                {{ $setting->currency_symbol ?? '₹' }}0.00 -
                                                {{ $setting->currency_symbol ?? '₹' }}0.00 =
                                                {{ $setting->currency_symbol ?? '₹' }}0.00
                                            @endif --}}
                                        </h5>
                                    </li>



                                    {{-- <div class="tax-section">
                                        @foreach ($TaxRate as $tax)
                                            <li>
                                                <h4>{{ $tax->tax_name }}</h4>
                                                <h5>{{ number_format($tax->tax_rate, 2) }}% (
                                                    @if ($setting->currency_position === 'right')
                                                        <span class="tax-amount"
                                                            data-rate="{{ $tax->tax_rate }}">0.00</span>{{ $setting->currency_symbol ?? '₹' }}
                                                    @else
                                                        {{ $setting->currency_symbol ?? '₹' }}<span class="tax-amount"
                                                            data-rate="{{ $tax->tax_rate }}">0.00</span>
                                                    @endif
                                                    )
                                                </h5>
                                            </li>
                                        @endforeach
                                    </div> --}}
                                    <li class="labour-cost"
                                        @if ($labourSubtotal <= 0) style="display:none;" @endif>
                                        <h4>Labour Cost</h4>
                                        <h5>
                                            @if ($setting->currency_position === 'right')
                                                <span
                                                    id="labour-cost-display">{{ number_format($labourSubtotal, 2) }}</span>{{ $setting->currency_symbol ?? '₹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '₹' }}<span
                                                    id="labour-cost-display">{{ number_format($labourSubtotal, 2) }}</span>
                                            @endif
                                        </h5>
                                    </li>

                                    <li class="shipping-cost"
                                        @if ($shippingCost <= 0) style="display:none;" @endif>
                                        <h4>Shipping Cost</h4>
                                        <h5>
                                            @if ($setting->currency_position === 'right')
                                                <span
                                                    id="shipping-cost-display">{{ number_format($shippingCost, 2) }}</span>{{ $setting->currency_symbol ?? '₹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '₹' }}<span
                                                    id="shipping-cost-display">{{ number_format($shippingCost, 2) }}</span>
                                            @endif
                                        </h5>
                                    </li>

                                    <li class="tds-summary"
                                        @if (!$isTdsEnabled) style="display:none;" @endif>
                                        <h4>TDS (<span
                                                id="tds-percentage-display">{{ number_format($tdsPercentage, 2) }}</span>%)
                                        </h4>
                                        <h5>
                                            @if ($setting->currency_position === 'right')
                                                <span
                                                    id="tds-amount-display">-{{ number_format(abs($tdsAmount), 2) }}</span>{{ $setting->currency_symbol ?? 'â‚¹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? 'â‚¹' }}<span
                                                    id="tds-amount-display">-{{ number_format(abs($tdsAmount), 2) }}</span>
                                            @endif
                                        </h5>
                                    </li>



                                    <li class="round-off d-none">
                                        <h4>Round Off</h4>
                                        <h5>
                                            @if ($setting->currency_position === 'right')
                                                <span
                                                    id="round-off-display">{{ number_format($roundOffAmount, 2) }}</span>{{ $setting->currency_symbol ?? 'â‚¹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? 'â‚¹' }}<span
                                                    id="round-off-display">{{ number_format($roundOffAmount, 2) }}</span>
                                            @endif
                                        </h5>
                                    </li>
                                    <li class="total">
                                        <h4>Grand Total</h4>
                                        <h5>
                                            @if ($setting->currency_position === 'right')
                                                <span id="grand-total">0.00</span>{{ $setting->currency_symbol ?? '₹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '₹' }}<span id="grand-total">0.00</span>
                                            @endif
                                        </h5>
                                    </li>
                                </ul>
                            </div>

                            {{-- <div class="row justify-content-end">
                    <div class="col-lg-6">
                        <div class="total-order w-100 max-widthauto m-auto mb-4">
                            <ul>
                                <li class="subtotal">
                                    <h4>Subtotal</h4>
                                    <h5>
                                        @if ($setting->currency_position === 'right')
                                            <span id="subtotal-display">0.00</span>{{ $setting->currency_symbol ?? '₹' }}
                                        @else
                                            {{ $setting->currency_symbol ?? '₹' }}<span id="subtotal-display">0.00</span>
                                        @endif
                                    </h5>
                                </li>

                                <li class="discount">
                                    <h4>Discount</h4>
                                    <h5>
                                        <span id="discount-percent">0.00</span>%
                                        (
                                        @if ($setting->currency_position === 'right')
                                            <span id="discount-amount">0.00</span>{{ $setting->currency_symbol ?? '₹' }}
                                        @else
                                            {{ $setting->currency_symbol ?? '₹' }}<span id="discount-amount">0.00</span>
                                        @endif
                                        )
                                    </h5>
                                </li>

                                <li class="after-discount">
                                    <h4>After Discount</h4>
                                    <h5>
                                        @if ($setting->currency_position === 'right')
                                            <span
                                                id="after-discount-display">0.00</span>{{ $setting->currency_symbol ?? '₹' }}
                                        @else
                                            {{ $setting->currency_symbol ?? '₹' }}<span
                                                id="after-discount-display">0.00</span>
                                        @endif
                                    </h5>
                                </li>

                                <li class="total-gst" style="display: none;">
                                    <h4>Total GST</h4>
                                    <h5>
                                        @if ($setting->currency_position === 'right')
                                            <span id="total-gst-amount">0.00</span>{{ $setting->currency_symbol ?? '₹' }}
                                        @else
                                            {{ $setting->currency_symbol ?? '₹' }}<span id="total-gst-amount">0.00</span>
                                        @endif
                                    </h5>
                                </li>

                                <li class="total">
                                    <h4>Grand Total</h4>
                                    <h5>
                                        @if ($setting->currency_position === 'right')
                                            <span id="grand-total">0.00</span>{{ $setting->currency_symbol ?? '₹' }}
                                        @else
                                            {{ $setting->currency_symbol ?? '₹' }}<span id="grand-total">0.00</span>
                                        @endif
                                    </h5>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div> --}}

                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-submit me-2" id="update-order-btn">
                                    <span class="spinner-border spinner-border-sm d-none" role="status"
                                        aria-hidden="true" id="btn-loader"></span>
                                    <span id="btn-text">Update Order</span>
                                </button>
                                <a href="{{ route('sales.list') }}" class="btn btn-cancel">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="addBankModal" tabindex="-1" aria-labelledby="addBankModalLabel"
                    aria-hidden="true">
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
                                            <label for="add_bank_name" class="form-label">Bank Name</label>
                                            <input type="text" class="form-control" id="add_bank_name" name="bank_name">
                                            <div class="text-danger small" id="addBankNameError"></div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="add_account_number" class="form-label">Account Number</label>
                                            <input type="text" class="form-control" id="add_account_number" name="account_number">
                                            <div class="text-danger small" id="addAccountNumberError"></div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="add_ifsc_code" class="form-label">IFSC Code</label>
                                            <input type="text" class="form-control" id="add_ifsc_code" name="ifsc_code">
                                            <div class="text-danger small" id="addIfscCodeError"></div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="add_branch_name" class="form-label">Branch Name</label>
                                            <input type="text" class="form-control" id="add_branch_name" name="branch_name">
                                            <div class="text-danger small" id="addBranchNameError"></div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="add_opening_balance" class="form-label">Opening Balance</label>
                                            <input type="number" class="form-control" id="add_opening_balance"
                                                name="opening_balance" min="0" step="0.01" value="0">
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
                                    <button type="submit" class="btn text-white" id="saveBankBtn"
                                        style="background-color: #ff9f43;">Save Bank</button>
                                    <button type="button" class="btn btn-secondary btn-cancel"
                                        data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endsection

            @push('js')
                <script>
                    $(document).ready(function() {
                        const currencySymbol = '{{ $setting->currency_symbol ?? '₹' }}';
                        const currencyPosition = '{{ $setting->currency_position ?? 'left' }}';
                        const isTdsEnabled = @json((bool) ($setting->tds_apply ?? false));
                        const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                        const addBankModalElement = document.getElementById('addBankModal');
                        const addBankModal = addBankModalElement && typeof bootstrap !== 'undefined' ?
                            new bootstrap.Modal(addBankModalElement) :
                            null;

                        function formatNumber(amount, decimals = 2) {
                            return parseFloat(amount).toLocaleString('en-US', {
                                minimumFractionDigits: decimals,
                                maximumFractionDigits: decimals
                            });
                        }

                        function formatCurrency(amount) {
                            const formatted = formatNumber(amount);
                            return currencyPosition === 'right' ?
                                formatted + currencySymbol :
                                currencySymbol + formatted;
                        }

                        function parseMoney(value) {
                            const normalized = String(value ?? '')
                                .replace(/,/g, '')
                                .replace(/[^0-9.-]/g, '')
                                .trim();
                            const parsed = parseFloat(normalized);
                            return Number.isFinite(parsed) ? parsed : 0;
                        }

                        function getGrandTotalValue() {
                            return parseMoney($('#grand-total').first().text());
                        }

                        function normalizePaymentMethod(value) {
                            const normalized = String(value || '').toLowerCase().trim();

                            if (['cash+online', 'cash_online', 'cash + online', 'cash+bank', 'cash_bank', 'cash + bank']
                                .includes(normalized)) {
                                return 'cash+online';
                            }

                            if (['online', 'debit', 'debit card', 'scan', 'upi'].includes(normalized)) {
                                return 'online';
                            }

                            if (normalized === 'cash') {
                                return 'cash';
                            }
                            if (normalized === 'emi') {
                                return 'emi';
                            }

                            return 'pending';
                        }

                        function setSelect2Value(selector, value) {
                            const $element = $(selector);
                            if (!$element.length) {
                                return;
                            }

                            $element.find('option').prop('selected', false);
                            $element.find(`option[value="${value}"]`).prop('selected', true);
                            $element.val(value);
                            if ($element.hasClass('select2-hidden-accessible')) {
                                $element.trigger('change.select2');
                                const selectedText = $element.find('option:selected').text();
                                $element.next('.select2-container').find('.select2-selection__rendered').text(selectedText);
                            }
                        }

                        function syncPaidTypeWithStatus() {
                            const status = $('#payment_status').val();
                            if (status === 'pending') {
                                setSelect2Value('#paid_type', '');
                            } else if (status === 'partially') {
                                setSelect2Value('#paid_type', 'partial');
                            } else if (status === 'completed') {
                                setSelect2Value('#paid_type', 'full');
                            }
                        }

                        function syncStatusWithPayment(pendingAmount, paidAmount, grandTotal) {
                            let status = 'pending';

                            if (pendingAmount <= 0 && paidAmount > 0) {
                                status = 'completed';
                            } else if (paidAmount > 0 && paidAmount < grandTotal) {
                                status = 'partially';
                            }

                            setSelect2Value('#payment_status', status);
                        }

                        // function formatPaymentAmountInput(value) {
                        //     const amount = parseMoney(value);
                        //     return amount > 0 ? amount.toFixed(2) : '';
                        // }
                        function formatPaymentAmountInput(value) {
    const amount = parseMoney(value);
    return amount > 0 ? amount.toFixed(2) : '';
}

// Format cash/online fields to 2 decimal places only on blur
$(document).on('blur', '#cash_amount, #online_amount', function() {
    const val = parseMoney($(this).val());
    if (val > 0) {
        $(this).val(val.toFixed(2));
    }
});
$(document).on('blur', '#tds-percentage-input', function() {
    const val = Math.max(0, Math.min(100, parseFloat($(this).val()) || 0));
    $(this).val(val.toFixed(2));
});

                        function normalizeDiscountInputValue(value) {
                            const normalized = String(value ?? '')
                                .replace(/[^0-9.]/g, '')
                                .trim();

                            if (normalized === '') {
                                return '';
                            }

                            const parts = normalized.split('.');
                            const sanitized = parts.length > 2
                                ? `${parts[0]}.${parts.slice(1).join('')}`
                                : normalized;
                            const parsed = parseFloat(sanitized);

                            if (!Number.isFinite(parsed)) {
                                return '';
                            }

                            return Math.max(0, Math.min(100, parsed)).toFixed(2);
                        }

                        function togglePaymentInputLayout() {
                            const isQuotation = $('#quotationToggle').is(':checked');
                            const method = normalizePaymentMethod($('#payment_method').val());
                            const rawMethod = $('#payment_method').val();
                            const $paymentColumns = $('#payment_details_row > .col-lg-12 > .row').children();
                            const shouldShowPaymentRow = !isQuotation && method !== 'pending' && method !== 'emi';

                            $('#payment_method_col').toggle(!isQuotation);
                            $('#payment_status_col').show();
                            $('#payment_details_row').toggle(shouldShowPaymentRow);
                            $('#paid_type_col').toggle(shouldShowPaymentRow);
                            $('#bank_container').toggle(shouldShowPaymentRow && (method === 'online' || method === 'cash+online'));
                            $('#cash_amount_col').toggle(shouldShowPaymentRow && (method === 'cash' || method === 'cash+online'));
                            $('#online_amount_col').toggle(shouldShowPaymentRow && (method === 'online' || method === 'cash+online'));
                            $('#pending_amount_col').toggle(shouldShowPaymentRow);

                            $('#cash_amount_label').text(method === 'cash' ? 'Payment Amount' : 'Cash Amount');
                            $('#online_amount_label').text(method === 'online' ? 'Payment Amount' : 'Bank Amount');

                            $('#bank_id').prop('disabled', isQuotation || !(method === 'online' || method === 'cash+online'));
                            
                            if (!isQuotation && method === 'emi') {
                                $('#emiBox').show();
                                calculateEmi();
                            } else {
                                $('#emiBox').hide();
                            }
                            $('#cash_amount').prop('disabled', isQuotation || !(method === 'cash' || method === 'cash+online'));
                            $('#online_amount').prop('disabled', isQuotation || !(method === 'online' || method === 'cash+online'));
                            $('#pending_amount').prop('disabled', true);
                            $('#paid_type').prop('disabled', !shouldShowPaymentRow);

                            $paymentColumns.removeClass('col-lg-3 col-lg-4 col-lg-6 col-lg-12 d-none');

                            const visibleColumns = $paymentColumns.filter(':visible');
                            let paymentColumnClass = 'col-lg-3';

                            if (visibleColumns.length === 1) {
                                paymentColumnClass = 'col-lg-12';
                            } else if (visibleColumns.length === 2) {
                                paymentColumnClass = 'col-lg-6';
                            } else if (visibleColumns.length === 3) {
                                paymentColumnClass = 'col-lg-4';
                            }

                            visibleColumns.addClass(paymentColumnClass);
                        }

                        function calculatePaymentBreakdown() {
                            const isQuotation = $('#quotationToggle').is(':checked');
                            const method = normalizePaymentMethod($('#payment_method').val());
                            const paidType = $('#paid_type').val();
                            const grandTotal = getGrandTotalValue();
                            const historicalPaidAmount = Math.min(
                                grandTotal,
                                parseMoney($('#pending_amount').data('paid-total'))
                            );
                            const outstandingAmount = Math.max(0, grandTotal - historicalPaidAmount);
                            let cashAmount = parseMoney($('#cash_amount').val());
                            let onlineAmount = parseMoney($('#online_amount').val());
                            let additionalPaidAmount = 0;
                            let paidAmount = historicalPaidAmount;
                            let pendingAmount = outstandingAmount;

                            if (isQuotation) {
                                cashAmount = 0;
                                onlineAmount = 0;
                                pendingAmount = grandTotal;
                                paidAmount = 0;
                            } else if (method === 'pending' || !paidType) {
                                cashAmount = 0;
                                onlineAmount = 0;
                                pendingAmount = outstandingAmount;
                            } else if (method === 'cash') {
                                onlineAmount = 0;
                                additionalPaidAmount = paidType === 'full'
                                    ? outstandingAmount
                                    : Math.min(cashAmount, outstandingAmount);
                                cashAmount = additionalPaidAmount;
                                pendingAmount = Math.max(outstandingAmount - additionalPaidAmount, 0);
                                paidAmount = Math.min(historicalPaidAmount + additionalPaidAmount, grandTotal);
                            } else if (method === 'online') {
                                cashAmount = 0;
                                additionalPaidAmount = paidType === 'full'
                                    ? outstandingAmount
                                    : Math.min(onlineAmount, outstandingAmount);
                                onlineAmount = additionalPaidAmount;
                                pendingAmount = Math.max(outstandingAmount - additionalPaidAmount, 0);
                                paidAmount = Math.min(historicalPaidAmount + additionalPaidAmount, grandTotal);
                            } else if (method === 'cash+online') {
                                cashAmount = Math.min(cashAmount, outstandingAmount);

                                if (paidType === 'full') {
                                    additionalPaidAmount = outstandingAmount;
                                    onlineAmount = Math.max(outstandingAmount - cashAmount, 0);
                                } else {
                                    onlineAmount = Math.min(onlineAmount, Math.max(outstandingAmount - cashAmount, 0));
                                    additionalPaidAmount = Math.min(cashAmount + onlineAmount, outstandingAmount);
                                }

                                pendingAmount = Math.max(outstandingAmount - additionalPaidAmount, 0);
                                paidAmount = Math.min(historicalPaidAmount + additionalPaidAmount, grandTotal);
                            }

                            if (!isQuotation && method !== 'pending' && outstandingAmount <= 0) {
                                const prefillCash = parseMoney($('#cash_amount').data('prefill'));
                                const prefillOnline = parseMoney($('#online_amount').data('prefill'));

                                if (prefillCash > 0 && prefillOnline > 0) {
                                    const totalPrefill = prefillCash + prefillOnline;
                                    if (totalPrefill > grandTotal) {
                                        cashAmount = (prefillCash / totalPrefill) * grandTotal;
                                        onlineAmount = (prefillOnline / totalPrefill) * grandTotal;
                                    } else {
                                        cashAmount = prefillCash;
                                        onlineAmount = prefillOnline;
                                    }
                                } else if (prefillCash > 0) {
                                    cashAmount = Math.min(prefillCash, grandTotal);
                                    onlineAmount = 0;
                                } else if (prefillOnline > 0) {
                                    cashAmount = 0;
                                    onlineAmount = Math.min(prefillOnline, grandTotal);
                                } else {
                                    if (method === 'cash') {
                                        cashAmount = grandTotal;
                                        onlineAmount = 0;
                                    } else if (method === 'online') {
                                        cashAmount = 0;
                                        onlineAmount = grandTotal;
                                    } else {
                                        cashAmount = 0;
                                        onlineAmount = 0;
                                    }
                                }
                            }

                            $('#cash_amount').val(formatPaymentAmountInput(cashAmount));
                            $('#online_amount').val(formatPaymentAmountInput(onlineAmount));
                            $('#pending_amount').val(pendingAmount.toFixed(2));

                            syncStatusWithPayment(pendingAmount, paidAmount, grandTotal);
                            togglePaymentInputLayout();
                        }

                        function resetAddBankForm() {
                            const form = document.getElementById('addBankForm');
                            if (form) {
                                form.reset();
                            }

                            $('#add_opening_balance').val('0');
                            $('#add_bank_status').val('1');
                            $('#addBankForm .text-danger').text('');
                        }

                        function validateAddBankForm() {
                            const formValues = {
                                bank_name: $('#add_bank_name').val().trim(),
                                account_number: $('#add_account_number').val().trim(),
                                ifsc_code: $('#add_ifsc_code').val().trim(),
                                branch_name: $('#add_branch_name').val().trim(),
                                opening_balance: $('#add_opening_balance').val().trim(),
                                status: $('#add_bank_status').val()
                            };
                            let hasError = false;

                            $('#addBankForm .text-danger').text('');

                            if (!formValues.bank_name) {
                                $('#addBankNameError').text('Bank name is required.');
                                hasError = true;
                            }

                            if (!formValues.account_number) {
                                $('#addAccountNumberError').text('Account number is required.');
                                hasError = true;
                            }

                            if (!formValues.ifsc_code) {
                                $('#addIfscCodeError').text('IFSC code is required.');
                                hasError = true;
                            }

                            if (!formValues.branch_name) {
                                $('#addBranchNameError').text('Branch name is required.');
                                hasError = true;
                            }

                            if (formValues.opening_balance === '') {
                                $('#addOpeningBalanceError').text('Opening balance is required.');
                                hasError = true;
                            } else if (parseMoney(formValues.opening_balance) < 0) {
                                $('#addOpeningBalanceError').text('Opening balance must be 0 or more.');
                                hasError = true;
                            }

                            if (!['0', '1'].includes(formValues.status)) {
                                $('#addBankStatusError').text('Status is required.');
                                hasError = true;
                            }

                            return !hasError;
                        }

                        function upsertBankOption(bank) {
                            if (!bank || !bank.id) {
                                return;
                            }

                            const bankId = String(bank.id);
                            const accountNumber = bank.account_number ? ` (${bank.account_number})` : '';
                            const label = `${bank.bank_name || 'Unnamed Bank'}${accountNumber}`;
                            const $bankSelect = $('#bank_id');
                            let $option = $bankSelect.find(`option[value="${bankId}"]`);

                            if ($option.length) {
                                $option.text(label);
                            } else {
                                $option = $('<option></option>').val(bankId).text(label);
                                $bankSelect.append($option);
                            }

                            setSelect2Value('#bank_id', bankId);
                        }

                        function getOptionData($option, key, fallback = null) {
                            if (!$option || !$option.length) {
                                return fallback;
                            }

                            const dataValue = $option.data(key);
                            if (dataValue !== undefined && dataValue !== null && dataValue !== '') {
                                return dataValue;
                            }

                            const attrValue = $option.attr(`data-${key}`);
                            return attrValue !== undefined && attrValue !== null && attrValue !== '' ? attrValue :
                                fallback;
                        }

                        function normalizeProductPrice(price) {
                            const normalizedPrice = parseFloat(price);
                            return Number.isFinite(normalizedPrice) ? normalizedPrice : null;
                        }

                        function fetchProductDetails(productId) {
                            return $.ajax({
                                url: `/api/getProductById/${productId}`,
                                type: 'GET',
                                headers: {
                                    "Authorization": "Bearer " + localStorage.getItem("authToken"),
                                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                                }
                            });
                        }

                        // Initialize Select2 with images and GST info
                        function formatProduct(product) {
                            if (!product.id) return product.text;

                            const $option = $(product.element);
                            const image = $option.data('image');
                            const name = $option.data('name');
                            const gstOption = $option.data('gst-option');
                            const productGst = $option.data('product-gst');

                            let gstBadge = '';
                            let gstDetails = '';

                            if (gstOption === 'with_gst' && productGst) {
                                try {
                                    const gstData = JSON.parse(productGst);
                                    if (Array.isArray(gstData) && gstData.length > 0) {
                                        const totalRate = gstData.reduce((sum, tax) => sum + parseFloat(tax.tax_rate || 0), 0);
                                        gstBadge = `<span class="badge bg-success ms-2">GST: ${totalRate}%</span>`;
                                        gstDetails = `<div class="small text-muted">`;
                                        gstData.forEach(tax => {
                                            gstDetails += `${tax.tax_name || 'GST'}: ${tax.tax_rate}%<br>`;
                                        });
                                        gstDetails += `</div>`;
                                    }
                                } catch (e) {
                                    gstBadge = `<span class="badge bg-success ms-2">With GST</span>`;
                                }
                            } else {
                                gstBadge = `<span class="badge bg-secondary ms-2">No GST</span>`;
                            }

                            return $(
                                `<span>
                <img src="${image}" class="img-flag" style="width: 20px; margin-right: 10px;" />
                ${name}
                ${gstBadge}
                ${gstDetails}
            </span>`
                            );
                        }

                        $('.product-select').select2({
                            templateResult: formatProduct,
                            templateSelection: formatProduct,
                            closeOnSelect: false,
                            width: '100%'
                        });

                        $('.select2-labour').select2({
                            width: '100%'
                        });

                        // Initialize Customer Select2
                        $('#customer_id').select2({
                            placeholder: "Select Customer",
                            allowClear: true
                        });

                        if ($.fn.select2) {
                            $('#payment_method, #payment_status, #paid_type, #bank_id, #assign_staff, #order_type').select2({
                                width: '100%'
                            });
                        }

                        // Handle customer selection change
                        $('#customer_id').on('change', function() {
                            var phone = $(this).find(':selected').data('phone');
                            $('#customer_phone').val(phone || '');
                        });




                        // Add product to table
                        function addProductToTable(productId, productName, productImage, productPrice, gstOption, productGst,
    existingGstDetails = null, unit = 'N/A', discount = 0, stock = 999999) {
                            const normalizedInitialPrice = normalizeProductPrice(productPrice);
                            const resolvedPrice = normalizedInitialPrice !== null ? normalizedInitialPrice : 0;
                            const rowCount = $('tbody tr[data-product-id]').length + 1;
                            let gstBadge = '';
                            let gstDetailsHtml = '';
                            let gstDetails = [];

                            // Use existing GST details if available (from order_items), otherwise use product GST
                            if (existingGstDetails) {
                                gstDetails = existingGstDetails;
                            } else if (gstOption === 'with_gst' && productGst) {
                                if (typeof productGst === 'string') {
                                    try {
                                        gstDetails = JSON.parse(productGst);
                                    } catch (e) {
                                        gstDetails = [];
                                    }
                                } else if (Array.isArray(productGst)) {
                                    gstDetails = productGst;
                                } else {
                                    gstDetails = [];
                                }
                            }

                            if (gstOption === 'with_gst' && gstDetails.length > 0) {
                                const totalRate = gstDetails.reduce((sum, tax) => sum + parseFloat(tax.tax_rate || 0), 0);
                                gstBadge = `<span class="gst-badge with">With GST (${totalRate}%)</span>`;

                                gstDetailsHtml = '<div class="product-gst-details">';
                                gstDetails.forEach(tax => {
                                    gstDetailsHtml += `<small>${tax.tax_name || 'GST'}: ${tax.tax_rate}%</small>`;
                                });
                                gstDetailsHtml += '</div>';
                            } else {
                                gstBadge = `<span class="gst-badge without">Without GST</span>`;
                                gstDetails = [];
                            }

                            const newRow = `
<tr data-product-id="${productId}" data-gst-option="${gstOption}" data-product-gst='${JSON.stringify(gstDetails).replace(/'/g, "&#39;")}' data-stock="${stock || 999999}">
                <td>${rowCount}</td>
                <td class="">
                    <a class="product-img">
                        <img src="${productImage}" alt="product" width="40">
                    </a>
                    <a href="javascript:void(0);">${productName}</a>
                    ${gstBadge}
                </td>
                <td data-label="Unit">${unit}</td>
                <td data-label="QTY">
                    <input type="text"
                           name="quantities[${productId}]"
                           class="form-control quantity-input"
                           value="1"
                           step="1"
                           min="0"
                           style="width: 80px;">
                </td>
                <td data-label="Price">
                    <input type="text"
                           name="prices[${productId}]"
                           class="form-control price-input"
                           value="${resolvedPrice.toFixed(2)}"
                           step="0.01"
                           min="0"
                           style="width: 90px;">
                </td>
                <td data-label="Discount %">
                    <input type="text"
                           name="discounts[${productId}]"
                           class="form-control discount-input"
                           value="${discount}"
                           step="0.01"
                           min="0"
                           max="100"
                           style="width: 80px;">
                </td>
                <td class="gst-details-cell" data-label="GST Details">
                    ${gstDetailsHtml || '<span class="text-muted">No GST</span>'}
                </td>
                <td data-label="Total">
                    <span class="total-amount">${formatNumber(resolvedPrice)}</span>
                </td>
                <td data-label="Action">
                    <a href="javascript:void(0);" class="delete-set">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/delete.svg' }}" alt="svg">
                    </a>
                </td>
            </tr>
        `;

                            $('#no-products-row').remove();
                            $('tbody').append(newRow);

                            // Add event listeners
                            $('tbody tr[data-product-id="' + productId + '"] .quantity-input').on('input', calculateAllTotals);
                            $('tbody tr[data-product-id="' + productId + '"] .price-input').on('input', calculateAllTotals);
                            $('tbody tr[data-product-id="' + productId + '"] .discount-input').on('input', calculateAllTotals);
                            $('tbody tr[data-product-id="' + productId + '"] .delete-set').on('click', function() {
                                $(this).closest('tr').remove();
                                updateProductSelection();
                                toggleNoProductsMessage();
                                calculateAllTotals();
                            });
                        }

                        // Update product selection in dropdown
                        function updateProductSelection() {
                            const selectedProducts = $('tbody tr[data-product-id]').map(function() {
                                return $(this).data('product-id').toString();
                            }).get();
                            $('.product-select').val(selectedProducts).trigger('change');
                        }

                        // Toggle "no products" message
                        function toggleNoProductsMessage() {
                            if ($('tbody tr[data-product-id]').length === 0) {
                                $('tbody').append(
                                    '<tr id="no-products-row"><td colspan="7" class="text-center">No products selected</td></tr>'
                                );
                            }
                        }

                        // Calculate all totals including product-wise GST
                        function calculateAllTotals() {
                            let grossSubtotal = 0;
                            let totalPerItemDiscount = 0;
                            let netSubtotal = 0;
                            let totalGst = 0;
                            const gstOption = $('input[name="gst_option"]:checked').val();
                            const hasGlobalGst = gstOption === 'with_gst';

                            // Calculate for each product
                            $('tbody tr[data-product-id]').each(function() {
                                const $row = $(this);
                                const quantity = parseFloat($row.find('.quantity-input').val()) || 0;
                                const price = parseFloat($row.find('.price-input').val()) || 0;
                                const discountPercent = Math.max(0, Math.min(100, parseFloat($row.find('.discount-input').val()) || 0));
                                const productGstOption = $row.data('gst-option');
                                let productGstData = $row.data('product-gst') || [];

                                // If productGstData is a string, parse it
                                if (typeof productGstData === 'string' && productGstData.trim() !== '') {
                                    try {
                                        productGstData = JSON.parse(productGstData);
                                    } catch (e) {
                                        // console.error('Error parsing GST data', e);
                                        productGstData = [];
                                    }
                                }

                                if (!Array.isArray(productGstData)) {
                                    productGstData = [];
                                }

                                // Base product total (Gross)
                                const baseProductTotal = quantity * price;
                                grossSubtotal += baseProductTotal;

                                // 1. Calculate product GST if applicable (on FULL base price)
                                let productGstAmount = 0;
                                if (hasGlobalGst && productGstOption === 'with_gst' && Array.isArray(productGstData) &&
                                    productGstData.length > 0) {
                                    productGstData.forEach(tax => {
                                        const taxRate = parseFloat(tax.tax_rate || 0) / 100;
                                        productGstAmount += baseProductTotal * taxRate;
                                    });
                                    totalGst += productGstAmount;

                                    // Update GST details display
                                    const $gstCell = $row.find('.gst-details-cell');
                                    let gstHtml = '<div class="product-gst-details">';
                                    productGstData.forEach(tax => {
                                        const taxRate = parseFloat(tax.tax_rate || 0);
                                        const taxAmount = baseProductTotal * (taxRate / 100);
                                        gstHtml +=
                                            `<small>${tax.tax_name || 'GST'}: ${taxRate}% (${formatNumber(taxAmount)})</small>`;
                                    });
                                    gstHtml +=
                                        `<small style="font-weight: bold;">Total GST: ${formatNumber(productGstAmount)}</small>`;
                                    gstHtml += '</div>';
                                    $gstCell.html(gstHtml);
                                } else {
                                    // Update GST details display for non-GST products
                                    const $gstCell = $row.find('.gst-details-cell');
                                    if (hasGlobalGst && productGstOption === 'with_gst') {
                                        $gstCell.html('<span class="text-muted">With GST (0%)</span>');
                                    } else {
                                        $gstCell.html('<span class="text-muted">No GST</span>');
                                    }
                                }

                                const totalWithGst = baseProductTotal + productGstAmount;

                                // 2. Apply per-item discount on (Base + GST)
                                const itemDiscountAmount = totalWithGst * (discountPercent / 100);
                                totalPerItemDiscount += itemDiscountAmount;

                                const rowFinalTotal = totalWithGst - itemDiscountAmount;
                                netSubtotal += rowFinalTotal;

                                // Update row total display (all parts)
                                let rowTotalHtml = `
                                    <div style="color:#ff9f43;">
                                        <strong>Sub Total:</strong> ${formatCurrency(baseProductTotal)}
                                    </div>`;

                                if (productGstOption === 'with_gst') {
                                    rowTotalHtml += `
                                        <div style="color:#007bff;">
                                            <strong>GST Included:</strong> ${formatCurrency(totalWithGst)}
                                        </div>`;
                                }

                                if (itemDiscountAmount > 0) {
                                    rowTotalHtml += `
                                        <div style="color:red;">
                                            <strong>Discount:</strong> -${formatCurrency(itemDiscountAmount)}
                                        </div>`;
                                }

                                rowTotalHtml += `
                                    <div style="font-weight:bold; margin-top:4px; border-top:1px solid #ddd; padding-top:3px;color:green;">
                                        Final Total: ${formatCurrency(rowFinalTotal)}
                                    </div>`;

                                $row.find('.total-amount').html(rowTotalHtml);
                            });

                            // Update summary display
                            $('#subtotal-display').text(formatNumber(grossSubtotal));

                            // Total Discounts
                            $('#product-discount-total-display').text(formatNumber(totalPerItemDiscount));
                            const hasDiscounts = totalPerItemDiscount > 0;
                            $('.product-discount, .after-discount').toggle(hasDiscounts);

                            // After discount (Total with GST - Discount)
                            const afterDiscount = (grossSubtotal + totalGst) - totalPerItemDiscount;
                            const subtotalFormulaText = `${formatCurrency(afterDiscount)}`;
                            $('#after-discount-display').text(subtotalFormulaText);

                            // Calculate labour cost
                            let labourSubtotal = 0;
                            $('.labour-item-row').each(function() {
                                const qty = parseFloat($(this).find('.labour-qty').val()) || 0;
                                const price = parseFloat($(this).find('.labour-price').val()) || 0;
                                labourSubtotal += qty * price;
                            });
                            $('#labour-cost-display').text(formatNumber(labourSubtotal));
                            $('.labour-cost').toggle(labourSubtotal > 0);

                            // Shipping cost
                            const shippingCost = parseFloat($('#shipping-input').val()) || 0;
                            $('#shipping-cost-display').text(formatNumber(shippingCost));
                            $('.shipping-cost').toggle(shippingCost > 0);

                            // TDS (applies only when enabled in settings)
                            const tdsPercentageInput = isTdsEnabled ? (parseFloat($('#tds-percentage-input').val()) || 0) : 0;
                            const tdsPercentage = Math.max(0, Math.min(100, tdsPercentageInput));
                            // if (isTdsEnabled) {
                            //     $('#tds-percentage-input').val(tdsPercentage.toFixed(2));
                            // }

                            // TDS should apply only to the product base amount.
                            const tdsBaseAmount = Math.max(0, grossSubtotal);
                            const preTdsGrandTotal = afterDiscount + labourSubtotal + shippingCost;
                            const tdsAmount = isTdsEnabled ? (tdsBaseAmount * tdsPercentage) / 100 : 0;

                            if (isTdsEnabled) {
                                $('#tds-percentage-display').text(formatNumber(tdsPercentage));
                                $('#tds-amount-display').text(`-${formatNumber(tdsAmount)}`);
                                $('#tds-amount-input').val(tdsAmount.toFixed(2));
                                $('.tds-summary').show();
                            } else {
                                $('.tds-summary').hide();
                            }

                            // Show/hide GST total
                            const $gstTotalLi = $('.total-gst');
                            if (hasGlobalGst && totalGst > 0) {
                                $gstTotalLi.show();
                                $('#total-gst-amount').text(formatNumber(totalGst));
                            } else {
                                $gstTotalLi.hide();
                            }

                            // Grand Total (After Discount + Labour + Shipping - TDS)
                            const grandTotal = preTdsGrandTotal - tdsAmount;
                            const roundedGrandTotal = Math.round(grandTotal);
                            const roundOffAmount = roundedGrandTotal - grandTotal;
                            $('#round-off-display').text(formatNumber(roundOffAmount));
                            $('#grand-total').text(formatNumber(roundedGrandTotal, 0));
                            calculatePaymentBreakdown();
                        }

                        // Bind events for recalculation
                        // $(document).on('input',
                        //     '.quantity-input, .price-input, .discount-input, #shipping-input, #tds-percentage-input, .labour-qty, .labour-price',
                        //     calculateAllTotals);

// $(document).on('input',
//     '.price-input, .discount-input, #shipping-input, #tds-percentage-input, .labour-qty, .labour-price',
//     calculateAllTotals);
$(document).on('input',
    '.price-input, .discount-input, #shipping-input, .labour-qty, .labour-price',
    calculateAllTotals);

// TDS: only recalculate on blur to avoid overwriting while typing
$(document).on('blur', '#tds-percentage-input', calculateAllTotals);

// While typing TDS: only update the display live, don't reformat the field
$(document).on('input', '#tds-percentage-input', function() {
    const rawVal = parseFloat($(this).val()) || 0;
    const tdsPercentage = Math.max(0, Math.min(100, rawVal));
    const productSubtotalBeforeTds = parseFloat($('#after-discount-display').text().replace(/[^0-9.]/g, '')) || 0;
    const tdsAmount = (productSubtotalBeforeTds * tdsPercentage) / 100;
    $('#tds-percentage-display').text(tdsPercentage.toFixed(2));
    $('#tds-amount-display').text(`-${tdsAmount.toFixed(2)}`);
    $('#tds-amount-input').val(tdsAmount.toFixed(2));
});

$(document).on('input', '#tds-amount-input', function() {
    const rawAmount = parseFloat($(this).val()) || 0;
    const productSubtotalBeforeTds = parseFloat($('#after-discount-display').text().replace(/[^0-9.]/g, '')) || 0;
    const tdsPercentage = productSubtotalBeforeTds > 0 ? (rawAmount / productSubtotalBeforeTds) * 100 : 0;
    const normalizedPercentage = Math.max(0, tdsPercentage);

    $('#tds-percentage-input').val(normalizedPercentage.toFixed(2));
    $('#tds-percentage-display').text(normalizedPercentage.toFixed(2));
    $('#tds-amount-display').text(`-${rawAmount.toFixed(2)}`);
});

$(document).on('blur', '#tds-amount-input', function() {
    const normalizedAmount = Math.max(0, parseFloat($(this).val()) || 0);
    $(this).val(normalizedAmount.toFixed(2));
    calculateAllTotals();
});

$(document).on('input', '.discount-input', function() {
    const normalizedDiscount = normalizeDiscountInputValue($(this).val());
    $(this).val(normalizedDiscount);
});

// Quantity input: validate stock then recalculate
$(document).on('input', '.quantity-input', function() {
    const $input = $(this);
    const $row = $input.closest('tr');
    const stock = parseFloat($row.data('stock'));
    const enteredQty = parseFloat($input.val()) || 0;
    const productName = $row.find('td:nth-child(2) a:last-of-type').text().trim() ||
                        $row.find('td:nth-child(2) a').last().text().trim() || 'this product';

    // Only validate if stock is a real finite number (not 999999 placeholder)
    if (isFinite(stock) && stock < 999999 && enteredQty > stock) {
        Swal.fire({
            title: 'Stock Quantity Exceeded',
            text: `Only ${stock.toFixed(2)} quantity are available for '${productName}'.`,
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#ff9f43'
        }).then(() => {
            $input.val(stock.toFixed(2));
            calculateAllTotals();
        });
        return; // Don't recalculate until user dismisses
    }

    if (enteredQty < 0) {
        $input.val(0);
    }

    calculateAllTotals();
});
                        $(document).on('change', 'input[name="gst_option"], .select2-labour', calculateAllTotals);

                        $(document).on('change', '.select2-labour', function() {
                            const price = $(this).find(':selected').data('price') || 0;
                            $(this).closest('.labour-item-row').find('.labour-price').val(price);
                            calculateAllTotals();
                        });

                        // Labour items dynamic row handling
                        $(document).on('click', '.add-labour-item', function() {
                            const newRow = `
                                <div class="row mb-2 labour-item-row">
                                    <div class="col-lg-5 col-sm-4 col-4">
                                        <select name="labour_item_id[]" class="form-control select2-labour-new">
                                            <option value="">Select Labour Item</option>
                                            @foreach ($labourItems as $lItem)
                                                <option value="{{ $lItem->id }}" data-price="{{ $lItem->price }}">{{ $lItem->item_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3 col-sm-3 col-3">
                                        <input type="number" name="labour_qty[]" class="form-control labour-qty" placeholder="Qty" value="1" min="0">
                                    </div>
                                    <div class="col-lg-3 col-sm-3 col-3">
                                        <input type="number" name="labour_price[]" class="form-control labour-price" placeholder="Price" value="0" min="0">
                                    </div>
                                    <div class="col-lg-1 col-sm-2 col-2">
                                        <button type="button" class="btn btn-success add-labour-item">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            `;

                            // Change current plus button to minus
                            $(this).removeClass('btn-success add-labour-item').addClass('btn-danger remove-labour-item')
                                .html('<i class="fas fa-times"></i>');

                            $('#labour-items-container').append(newRow);

                            // Initialize select2 for the new row
                            $('.select2-labour-new').select2({
                                width: '100%'
                            }).removeClass('select2-labour-new').addClass('select2-labour');
                        });

                        $(document).on('click', '.remove-labour-item', function() {
                            $(this).closest('.labour-item-row').remove();
                            calculateAllTotals();
                        });

                        function togglePaymentFields() {
                            togglePaymentInputLayout();
                            calculatePaymentBreakdown();
                            if (normalizePaymentMethod($('#payment_method').val()) === 'emi') {
                                calculateEmi();
                            }
                        }

                        $('#quotationToggle').on('change', togglePaymentFields);
                        togglePaymentFields(); // Initial call
                        
                        function calculateEmi() {
                            const totalAmount = getGrandTotalValue();
                            let downPayment = parseFloat($('#emiDownPayment').val()) || 0;
                            
                            if (downPayment > totalAmount) {
                                downPayment = totalAmount;
                                $('#emiDownPayment').val(downPayment);
                            }

                            const loanAmount = Math.max(0, totalAmount - downPayment);
                            $('#emiLoanAmount').val(loanAmount.toFixed(2));

                            let tenureStr = $('#emiTenure').val();
                            let tenureMonths = 0;
                            
                            if (tenureStr === 'custom') {
                                $('#emiCustomTenureCol').removeClass('d-none');
                                tenureMonths = parseInt($('#emiCustomTenure').val()) || 0;
                            } else {
                                $('#emiCustomTenureCol').addClass('d-none');
                                tenureMonths = parseInt(tenureStr) || 0;
                            }

                            let interestRate = parseFloat($('#emiInterestRate').val()) || 0;
                            
                            if (tenureMonths > 0 && loanAmount > 0) {
                                let totalInterest = (loanAmount * interestRate) / 100;
                                let totalRepayment = loanAmount + totalInterest;
                                let monthlyAmount = totalRepayment / tenureMonths;
                                $('#emiMonthlyAmount').val(monthlyAmount.toFixed(2));
                            } else {
                                $('#emiMonthlyAmount').val('');
                            }
                        }

                        $('#emiDownPayment, #emiInterestRate, #emiCustomTenure').on('input', function() {
                            calculateEmi();
                        });

                        $('#emiTenure').on('change', function() {
                            calculateEmi();
                        });
                        
                        $('#openAddBankModalEmi').on('click', function() {
                            resetAddBankForm();
                            if (addBankModal) {
                                addBankModal.show();
                            }
                        });

                        $('#payment_method').on('change', function() {
                            setSelect2Value('#paid_type', '');
                            $('#cash_amount').val('');
                            $('#online_amount').val('');
                            $('#pending_amount').val(parseMoney($('#pending_amount').data('prefill')).toFixed(2));
                            calculatePaymentBreakdown();
                        });

                        $('#payment_status').on('change', function() {
                            const status = $(this).val();

                            if (status === 'pending') {
                                setSelect2Value('#payment_method', 'pending');
                            } else {
                                syncPaidTypeWithStatus();

                                if (normalizePaymentMethod($('#payment_method').val()) === 'pending') {
                                    setSelect2Value('#payment_method', 'cash');
                                }
                            }

                            calculatePaymentBreakdown();
                        });

                        $('#paid_type').on('change', calculatePaymentBreakdown);
                        // $('#cash_amount, #online_amount').on('input', calculatePaymentBreakdown);

                        // Recalculate pending/status on blur only (so typing isn't interrupted)
$('#cash_amount, #online_amount').on('blur', calculatePaymentBreakdown);

// While typing: only update pending amount display live, don't reformat the field
$('#cash_amount, #online_amount').on('input', function() {
    const method = normalizePaymentMethod($('#payment_method').val());
    const grandTotal = getGrandTotalValue();
    const historicalPaidAmount = Math.min(grandTotal, parseMoney($('#pending_amount').data('paid-total')));
    const outstandingAmount = Math.max(0, grandTotal - historicalPaidAmount);

    let cashAmount = parseMoney($('#cash_amount').val());
    let onlineAmount = parseMoney($('#online_amount').val());
    let additionalPaid = 0;

    if (method === 'cash') {
        additionalPaid = Math.min(cashAmount, outstandingAmount);
    } else if (method === 'online') {
        additionalPaid = Math.min(onlineAmount, outstandingAmount);
    } else if (method === 'cash+online') {
        cashAmount = Math.min(cashAmount, outstandingAmount);
        onlineAmount = Math.min(onlineAmount, Math.max(outstandingAmount - cashAmount, 0));
        additionalPaid = Math.min(cashAmount + onlineAmount, outstandingAmount);
    }

    const pendingAmount = Math.max(outstandingAmount - additionalPaid, 0);
    $('#pending_amount').val(pendingAmount.toFixed(2));
});

                        $('#openAddBankModal').on('click', function() {
                            resetAddBankForm();
                            if (addBankModal) {
                                addBankModal.show();
                            }
                        });

                        $('#addBankForm').on('submit', function(e) {
                            e.preventDefault();

                            if (!validateAddBankForm()) {
                                return;
                            }

                            const authToken = localStorage.getItem("authToken");
                            const formData = new FormData(this);
                            if (selectedSubAdminId) {
                                formData.append('selectedSubAdminId', selectedSubAdminId);
                            }
                            formData.set('bank_name', $('#add_bank_name').val().trim());
                            formData.set('account_number', $('#add_account_number').val().trim());
                            formData.set('ifsc_code', $('#add_ifsc_code').val().trim().toUpperCase());
                            formData.set('branch_name', $('#add_branch_name').val().trim());
                            formData.set('opening_balance', parseMoney($('#add_opening_balance').val()).toFixed(2));

                            const $saveButton = $('#saveBankBtn');
                            $saveButton.prop('disabled', true).text('Saving...');

                            $.ajax({
                                url: '/api/banks',
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                    "Authorization": "Bearer " + authToken
                                },
                                success: function(response) {
                                    upsertBankOption(response.data || null);
                                    if (addBankModal) {
                                        addBankModal.hide();
                                    }

                                    Swal.fire({
                                        title: "Success",
                                        text: response.message || "Bank added successfully.",
                                        icon: "success",
                                        confirmButtonText: "OK",
                                        confirmButtonColor: "#ff9f43"
                                    });
                                },
                                error: function(xhr) {
                                    const errors = xhr.responseJSON?.errors || {};
                                    $('#addBankNameError').text(errors.bank_name ? errors.bank_name[0] : '');
                                    $('#addAccountNumberError').text(errors.account_number ? errors.account_number[0] : '');
                                    $('#addIfscCodeError').text(errors.ifsc_code ? errors.ifsc_code[0] : '');
                                    $('#addBranchNameError').text(errors.branch_name ? errors.branch_name[0] : '');
                                    $('#addOpeningBalanceError').text(errors.opening_balance ? errors.opening_balance[0] : '');
                                    $('#addBankStatusError').text(errors.status ? errors.status[0] : '');

                                    if (!Object.keys(errors).length) {
                                        Swal.fire({
                                            title: "Error",
                                            text: xhr.responseJSON?.message || "Failed to add bank.",
                                            icon: "error",
                                            confirmButtonText: "OK",
                                            confirmButtonColor: "#ff9f43"
                                        });
                                    }
                                },
                                complete: function() {
                                    $saveButton.prop('disabled', false).text('Save Bank');
                                }
                            });
                        });

                        // Handle product selection changes
                        $('.product-select').on('change', function() {
                            const selectedProducts = $(this).val() || [];
                            const existingProducts = $('tbody tr[data-product-id]').map(function() {
                                return $(this).data('product-id').toString();
                            }).get();
                            const addProductPromises = [];

                            // Add new products
                            selectedProducts.forEach(productId => {
                                if (!existingProducts.includes(productId)) {
                                    const option = $(this).find('option[value="' + productId + '"]');
                                    const optionName = getOptionData(option, 'name', option.text().trim());
                                    const optionImage = getOptionData(option, 'image', '');
                                    const optionGstOption = getOptionData(option, 'gst-option', 'without_gst');
                                    const optionProductGst = getOptionData(option, 'product-gst', '[]');
                                    const optionUnit = getOptionData(option, 'unit', 'N/A');
                                    const optionDiscount = getOptionData(option, 'discount', 0);
                                    const optionPrice = normalizeProductPrice(getOptionData(option, 'price'));

                                    if (optionPrice !== null) {
                                     const optionStock = parseFloat(getOptionData(option, 'stock', 999999)) || 999999;
addProductToTable(
    productId,
    optionName,
    optionImage,
    optionPrice,
    optionGstOption,
    optionProductGst,
    null,
    optionUnit,
    optionDiscount,
    optionStock
);
                                        return;
                                    }

                                    const fallbackRequest = fetchProductDetails(productId)
                                        .done(function(response) {
                                            const product = response.product || {};
                                            const fallbackPrice = normalizeProductPrice(product.price);
                                            const fallbackUnit = product.unit && product.unit.unit_name ?
                                                product.unit
                                                .unit_name : 'N/A';

                                         addProductToTable(
    productId,
    optionName || product.name || option.text().trim(),
    optionImage || product.image || '',
    fallbackPrice !== null ? fallbackPrice : 0,
    optionGstOption || product.gst_option || 'without_gst',
    optionProductGst || product.product_gst || '[]',
    null,
    optionUnit || fallbackUnit,
    optionDiscount,
    parseFloat(product.quantity ?? product.stock ?? 999999) || 999999
);
                                        })
                                        .fail(function() {
                                           addProductToTable(
    productId,
    optionName,
    optionImage,
    0,
    optionGstOption,
    optionProductGst,
    null,
    optionUnit,
    optionDiscount,
    parseFloat(getOptionData(option, 'stock', 999999)) || 999999
);
                                        });

                                    addProductPromises.push(fallbackRequest);
                                }
                            });

                            // Remove unselected products
                            $('tbody tr[data-product-id]').each(function() {
                                const rowProductId = $(this).data('product-id').toString();
                                if (!selectedProducts.includes(rowProductId)) {
                                    $(this).remove();
                                }
                            });

                            toggleNoProductsMessage();
                            if (addProductPromises.length) {
                                $.when.apply($, addProductPromises).always(calculateAllTotals);
                            } else {
                                calculateAllTotals();
                            }
                        });

                        // Initialize existing rows with their GST details from order_items
                        $('tbody tr[data-product-id]').each(function() {
                            const $row = $(this);
                            const productId = $row.data('product-id');

                            // Get GST details from the existing data attributes
                            let productGstData = $row.data('product-gst');

                            if (typeof productGstData === 'string' && productGstData.trim() !== '') {
                                try {
                                    const parsedGst = JSON.parse(productGstData);
                                    $row.data('product-gst', parsedGst);
                                } catch (e) {
                                    // console.error('Error parsing GST data:', e);
                                    $row.data('product-gst', []);
                                }
                            } else if (!productGstData) {
                                $row.data('product-gst', []);
                            }

                            // Add event listeners for existing rows
                            $row.find('.quantity-input').on('input', calculateAllTotals);
                            $row.find('.price-input').on('input', calculateAllTotals);
                            $row.find('.discount-input').on('input', calculateAllTotals);
                            $row.find('.delete-set').on('click', function() {
                                $(this).closest('tr').remove();
                                updateProductSelection();
                                toggleNoProductsMessage();
                                calculateAllTotals();
                            });
                        });



                        const initialQuotationStatus = @json($sales->quotation_status ?? 'sales');

                        function resetUpdateButtonState() {
                            const $btn = $('#update-order-btn');
                            const $loader = $('#btn-loader');
                            const $btnText = $('#btn-text');

                            $btn.prop('disabled', false);
                            $loader.addClass('d-none');
                            $btnText.text('Update Order');
                        }

                        function clearOrderNumberError() {
                            $('#order_number_error').hide().text('');
                        }

                        function showOrderNumberError(message) {
                            $('#order_number_error').text(message).show();
                            $('#order_number').focus();
                        }

                        function handleOrderNumberValidationError(xhr) {
                            const orderNumberError = xhr?.responseJSON?.errors?.order_number?.[0];

                            if (orderNumberError) {
                                showOrderNumberError(orderNumberError);
                                return true;
                            }

                            return false;
                        }

                        function submitOrderUpdate(formData, authToken, successMessage = "Order updated successfully!") {
                            $.ajax({
                                url: `/api/update_sale`,
                                type: "POST",
                                headers: {
                                    "Authorization": "Bearer " + authToken,
                                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                                },
                                data: formData,
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: "Success!",
                                            text: successMessage,
                                            icon: "success",
                                            confirmButtonText: "OK",
                                            confirmButtonColor: "#ff9f43"
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                window.location.href = "{{ route('sales.list') }}";
                                            }
                                        });
                                    } else {
                                        resetUpdateButtonState();

                                        Swal.fire({
                                            title: "Error",
                                            text: response.message,
                                            icon: "error",
                                            confirmButtonText: "OK",
                                            confirmButtonColor: "#ff9f43"
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    resetUpdateButtonState();

                                    if (handleOrderNumberValidationError(xhr)) {
                                        return;
                                    }

                                    let message = 'An error occurred while updating the order';
                                    try {
                                        const res = xhr.responseJSON;
                                        if (res.message) {
                                            message = res.message;
                                        } else if (res.errors) {
                                            message = Object.values(res.errors).join('<br>');
                                        }
                                    } catch (e) {
                                        // console.error('Failed to parse error message:', e);
                                    }

                                    Swal.fire({
                                        title: "Error",
                                        html: message,
                                        icon: "error",
                                        confirmButtonText: "OK",
                                        confirmButtonColor: "#ff9f43"
                                    });
                                }
                            });
                        }

                        function convertQuotationToSale(orderId, authToken) {
                            $.ajax({
                                url: `/api/convert-quotation-to-sale/${orderId}`,
                                type: "POST",
                                headers: {
                                    "Authorization": "Bearer " + authToken,
                                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    if (response.status === true) {
                                        Swal.fire({
                                            title: "Success!",
                                            text: "Quotation converted to sale and order updated successfully!",
                                            icon: "success",
                                            confirmButtonText: "OK",
                                            confirmButtonColor: "#ff9f43"
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                window.location.href = "{{ route('sales.list') }}";
                                            }
                                        });
                                    } else {
                                        resetUpdateButtonState();

                                        Swal.fire({
                                            title: "Error",
                                            text: response.message || "Failed to convert quotation.",
                                            icon: "error",
                                            confirmButtonText: "OK",
                                            confirmButtonColor: "#ff9f43"
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    resetUpdateButtonState();

                                    let message = 'Failed to convert quotation.';
                                    try {
                                        const res = xhr.responseJSON;
                                        if (res.message) {
                                            message = res.message;
                                        } else if (res.error) {
                                            message = res.error;
                                        }
                                    } catch (e) {
                                        // console.error('Failed to parse error message:', e);
                                    }

                                    Swal.fire({
                                        title: "Error",
                                        text: message,
                                        icon: "error",
                                        confirmButtonText: "OK",
                                        confirmButtonColor: "#ff9f43"
                                    });
                                }
                            });
                        }

                        function updateQuotationBeforeConvert(formData, authToken) {
                            const updateData = {
                                ...formData,
                                quotation_status: initialQuotationStatus
                            };

                            $.ajax({
                                url: `/api/update_sale`,
                                type: "POST",
                                headers: {
                                    "Authorization": "Bearer " + authToken,
                                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                                },
                                data: updateData,
                                success: function(response) {
                                    if (response.success) {
                                        convertQuotationToSale(formData.update_id, authToken);
                                    } else {
                                        resetUpdateButtonState();

                                        Swal.fire({
                                            title: "Error",
                                            text: response.message,
                                            icon: "error",
                                            confirmButtonText: "OK",
                                            confirmButtonColor: "#ff9f43"
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    resetUpdateButtonState();

                                    if (handleOrderNumberValidationError(xhr)) {
                                        return;
                                    }

                                    let message = 'An error occurred while updating the quotation';
                                    try {
                                        const res = xhr.responseJSON;
                                        if (res.message) {
                                            message = res.message;
                                        } else if (res.errors) {
                                            message = Object.values(res.errors).join('<br>');
                                        }
                                    } catch (e) {
                                        // console.error('Failed to parse error message:', e);
                                    }

                                    Swal.fire({
                                        title: "Error",
                                        html: message,
                                        icon: "error",
                                        confirmButtonText: "OK",
                                        confirmButtonColor: "#ff9f43"
                                    });
                                }
                            });
                        }

                        // Form submission handler
                        $(document).on("click", '#update-order-btn', function(e) {
                            var authToken = localStorage.getItem("authToken");
                            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                            e.preventDefault();
                            clearOrderNumberError();
                            $('#emiTenureError, #emiCustomTenureError, #emiBankError').text('').addClass('d-none');

                            // Collect all form data
                            const formData = {
                                update_id: $('#update_selse_id').val(),
                                customer_id: $('#customer_id').val(),
                                order_number: $('#order_number').val(),
                                order_date: $('input[name="order_date"]').val(),
                                product_ids: [],
                                quantities: {},
                                prices: {},
                                discounts: {},
                                discount: 0,
                                grand_total: $('#grand-total').text().replace(/[^0-9.]/g, ''),
                                gst_option: $('input[name="gst_option"]:checked').val(),
                                selectedSubAdminId: selectedSubAdminId || null,
                                remarks: $('#remarks').val(),
                                assign_staff: $('#assign_staff').val(),
                                order_type: $('#order_type').val(),
                                payment_method: $('#payment_method').val(),
                                status: $('#payment_status').val(),
                                paid_type: $('#paid_type').val(),
                                bank_id: $('#payment_method').val() === 'emi' ? $('#emiBankId').val() : $('#bank_id').val(),
                                cash_amount: $('#cash_amount').val() || 0,
                                online_amount: $('#online_amount').val() || 0,
                                pending_amount: $('#pending_amount').val() || 0,
                                payment_amount: Math.max(
                                    0,
                                    Math.max(
                                        0,
                                        getGrandTotalValue() - parseMoney($('#pending_amount').data('paid-total'))
                                    ) - parseMoney($('#pending_amount').val() || 0)
                                ).toFixed(2),
                                shipping: $('#shipping-input').val(),
                                tds_percentage: $('#tds-percentage-input').val() || 0,
                                tds_amount: $('#tds-amount-input').val() || 0,
                                emi_down_payment: $('#emiDownPayment').val() || 0,
                                emi_loan_amount: $('#emiLoanAmount').val() || 0,
                                emi_interest_rate: $('#emiInterestRate').val() || 0,
                                emi_tenure: $('#emiTenure').val() || "",
                                emi_custom_tenure: $('#emiCustomTenure').val() || "",
                                emi_monthly_amount: $('#emiMonthlyAmount').val() || 0,
                                emi_aadhar_number: $('#emiAadharNumber').val() || "",
                                emi_do_id: $('#emiDoId').val() || "",
                                emi_pan_number: $('#emiPanNumber').val() || "",
                                emi_guarantor_name: $('#emiGuarantorName').val() || "",
                                quotation_status: $('#quotationToggle').is(':checked') ? 'quotation' : 'sales',
                                labour_item_ids: [],
                                labour_qtys: [],
                                labour_prices: []
                            };

                            // Collect labour items
                            $('.labour-item-row').each(function() {
                                const itemId = $(this).find('select[name="labour_item_id[]"]').val();
                                if (itemId) {
                                    formData.labour_item_ids.push(itemId);
                                    formData.labour_qtys.push($(this).find('.labour-qty').val());
                                    formData.labour_prices.push($(this).find('.labour-price').val());
                                }
                            });

                            // Collect product rows. Rows with qty 0 are removed from the update payload.
                            $('tbody tr[data-product-id]').each(function() {
                                const $row = $(this);
                                const productId = $row.data('product-id');
                                const quantity = parseFloat($row.find('.quantity-input').val()) || 0;

                                if (quantity <= 0) {
                                    return;
                                }

                                formData.product_ids.push(productId.toString());
                                formData.quantities[productId] = $row.find('.quantity-input').val();
                                formData.prices[productId] = $row.find('.price-input').val();
                                formData.discounts[productId] = $row.find('.discount-input').val();
                            });

                            // Validate
                            if (!formData.customer_id) {
                                Swal.fire({
                                    title: "Error",
                                    text: "Please select a customer",
                                    icon: "error",
                                    confirmButtonText: "OK",
                                    confirmButtonColor: "#ff9f43"
                                });
                                return;
                            }

                            if (!formData.product_ids || formData.product_ids.length === 0) {
                                Swal.fire({
                                    title: "Error",
                                    text: "Please select at least one product",
                                    icon: "error",
                                    confirmButtonText: "OK",
                                    confirmButtonColor: "#ff9f43"
                                });
                                return;
                            }

                            const normalizedPaymentMethod = normalizePaymentMethod(formData.payment_method);
                            const paymentAmount = parseMoney(formData.payment_amount);

                            if (formData.quotation_status !== 'quotation' && ['online', 'cash+online'].includes(normalizedPaymentMethod) && !formData.bank_id) {
                                Swal.fire({
                                    title: "Error",
                                    text: "Please select a bank for bank payment.",
                                    icon: "error",
                                    confirmButtonText: "OK",
                                    confirmButtonColor: "#ff9f43"
                                });
                                return;
                            }

                            if (formData.quotation_status !== 'quotation' && normalizedPaymentMethod === 'emi') {
                                if (!formData.emi_tenure) {
                                    $('#emiTenureError').text('Please select EMI tenure.').removeClass('d-none');
                                    $('#emiTenure')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    return;
                                }
                                if (formData.emi_tenure === 'custom' && !formData.emi_custom_tenure) {
                                    $('#emiCustomTenureError').text('Please enter custom EMI tenure.').removeClass('d-none');
                                    $('#emiCustomTenure')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    return;
                                }
                                if (!formData.bank_id) {
                                    $('#emiBankError').text('Please select a bank for EMI.').removeClass('d-none');
                                    $('#emiBankId')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    return;
                                }
                            }

                            const grandTotalForValidation = getGrandTotalValue();
                            const historicalPaidForValidation = Math.min(
                                grandTotalForValidation,
                                parseMoney($('#pending_amount').data('paid-total'))
                            );
                            const outstandingForValidation = Math.max(0, grandTotalForValidation - historicalPaidForValidation);

                            if (formData.quotation_status !== 'quotation' && normalizedPaymentMethod !== 'pending' && normalizedPaymentMethod !== 'emi' && outstandingForValidation > 0 && paymentAmount <= 0) {
                                Swal.fire({
                                    title: "Error",
                                    text: "Please enter a valid payment amount.",
                                    icon: "error",
                                    confirmButtonText: "OK",
                                    confirmButtonColor: "#ff9f43"
                                });
                                return;
                            }

                            // Show loader and disable button
                            const $btn = $('#update-order-btn');
                            const $loader = $('#btn-loader');
                            const $btnText = $('#btn-text');

                            $btn.prop('disabled', true);
                            $loader.removeClass('d-none');
                            $btnText.text('Updating...');

                            const isConvertingToSale = initialQuotationStatus === 'quotation' &&
                                formData.quotation_status === 'sales';

                            if (isConvertingToSale) {
                                Swal.fire({
                                    title: "Convert To Sale?",
                                    text: "This quotation will be converted to a sale. Do you want to continue?",
                                    icon: "warning",
                                    showCancelButton: true,
                                    confirmButtonText: "Yes, Convert",
                                    cancelButtonText: "Cancel",
                                    confirmButtonColor: "#ff9f43",
                                    cancelButtonColor: "#6c757d"
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        updateQuotationBeforeConvert(formData, authToken);
                                    } else {
                                        resetUpdateButtonState();
                                    }
                                });
                                return;
                            }

                            submitOrderUpdate(formData, authToken);
                        });

                        // Initial calculation
                        calculateAllTotals();

                        // Initialize customer phone on page load
                        const selectedCustomer = $('#customer_id').find(':selected');
                        if (selectedCustomer.length) {
                            $('#customer_phone').val(selectedCustomer.data('phone') || '');
                        }

                        $('#order_number').on('input', function() {
                            clearOrderNumberError();
                        });

                        calculatePaymentBreakdown();

                        // Initialize Order Date Picker
                        const $orderDateDisplay = $('#order_date_display');
                        if ($orderDateDisplay.length && typeof $orderDateDisplay.datetimepicker === 'function') {
                            $orderDateDisplay.datetimepicker({
                                format: 'DD/MM/YYYY',
                                useCurrent: true,
                                showTodayButton: true,
                                icons: {
                                    date: 'fa fa-calendar',
                                    previous: 'fa fa-chevron-left',
                                    next: 'fa fa-chevron-right',
                                    today: 'fa fa-crosshairs',
                                    clear: 'fa fa-trash',
                                    close: 'fa fa-times'
                                }
                            });

                            $orderDateDisplay.on('dp.change', function(e) {
                                if (e.date) {
                                    $('#order_date').val(e.date.format('YYYY-MM-DD'));
                                }
                            });
                        }
                    });
                </script>
            @endpush
