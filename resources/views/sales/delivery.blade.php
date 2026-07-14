@extends('layout.app')

@section('title', 'Delivery')

@section('content')
    <style>
        @media (max-width: 767px) {
            /* ── Page header: stack title + buttons ── */
            .page-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }

            .page-btn {
                display: flex !important;
                flex-wrap: wrap !important;
                gap: 6px !important;
                width: 100%;
            }

            .page-btn .btn {
                flex: 1 1 calc(50% - 6px);
                min-width: 0;
                font-size: 11px !important;
                padding: 5px 8px !important;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                text-align: center;
            }

            .page-btn .btn i {
                display: none; /* hide icons to save space on mobile */
            }
            /* ── Info header: stack 3 columns vertically ── */
            .delivery-info-table td {
                display: block !important;
                width: 100% !important;
                text-align: left !important;
                padding: 8px 4px !important;
                border-bottom: 1px solid #f0f0f0;
            }

            /* ── Items table → card layout ── */
            #itemsTable thead { display: none; }

            #itemsTable tbody tr {
                display: block;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                background: #fff;
                box-shadow: 0 1px 3px rgba(15,23,42,.05);
                margin-bottom: 14px;
                padding: 12px 14px;
                position: relative;
            }

            #itemsTable tbody td {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 7px 0;
                border: none;
                font-size: 14px;
            }

            /* First cell: checkbox — keep it top-left */
            #itemsTable tbody td:first-child {
                justify-content: flex-start;
                gap: 8px;
                padding-bottom: 4px;
            }

            /* Label prefix for each data cell */
            #itemsTable tbody td[data-label]::before {
                content: attr(data-label);
                flex: 0 0 120px;
                color: #6b7280;
                font-size: 12px;
                font-weight: 600;
            }

            /* Deliver QTY input full-width on right */
            #itemsTable tbody td[data-label="Deliver QTY"] {
                flex-direction: row;
                align-items: flex-start;
            }

            #itemsTable tbody td[data-label="Deliver QTY"]::before {
                flex: none;
                margin-bottom: 4px;
            }

            #itemsTable tbody td[data-label="Deliver QTY"] .deliver-qty {
                width: 100% !important;
            }

            /* Subtotal row — align value to end */
            #itemsTable tbody td[data-label="Subtotal"] {
                font-weight: 700;
                border-top: 1px solid #eee;
                padding-top: 10px;
                margin-top: 4px;
            }
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Delivery</h4>
            </div>

            <div class="page-btn d-flex gap-2">
                <a href="{{ route('sales.details', $order->id) }}" class="btn btn-primary"><i class="fa fa-arrow-left me-1"></i> Details</a>
                <a href="{{ route('sales.invoice', $order->id) }}" target="_blank" class="btn btn-info"><i class="fa fa-file-invoice me-1"></i> Invoice</a>
                <a href="{{ route('sales.invoice.pdf', $order->id) }}" target="_blank" class="btn btn-danger"><i class="fa fa-print me-1"></i> Print Invoice</a>
                <a href="{{ route('sales.delivery.challan.pdf', $order->id) }}" target="_blank" class="btn btn-success"><i class="fa fa-file-alt me-1"></i> Delivery Challan</a>
                <a href="/sales" class="btn btn-secondary"><i class="fa fa-arrow-left me-1"></i> Back</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('sales.delivery.store') }}">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $order->id }}">

                    <div class="invoice-box table-height" style="max-width: 1600px; width:100%; margin:15px auto;">
                        <table style="width:100%;" class="delivery-info-table">
                            <tbody>
                                <tr>
                                    <td style="width:33%; vertical-align: top; padding:10px">
                                        <h5>Customer Info</h5>
                                        <p>
                                            {{ $order->customer_name ?? 'N/A' }}<br>
                                            {{ $order->customer_phone ?? ($order->user?->phone ?? 'N/A') }}<br>
                                            GST No: {{ $order->customer_gst_number ?? 'N/A' }}<br>
                                            PAN No: {{ $order->customer_pan_number ?? 'N/A' }}
                                        </p>
                                    </td>
                                    <td style="width:34%; vertical-align: top; padding:10px; text-align:center;">
                                        <h5>Company Info</h5>
                                        <p>
                                            {{ $setting->name ?? 'Company' }}<br>
                                            {{ $setting->email ?? '' }}<br>
                                            {{ $setting->phone ?? '' }}<br>
                                            {{ $setting->address ?? '' }}
                                        </p>
                                    </td>
                                    <td style="width:33%; vertical-align: top; padding:10px; text-align:right;">
                                        <h5>Order Info</h5>
                                        <p>
                                            Order Number: #{{ $order->order_number ?? $order->id }}<br>
                                            Order Date: {{ optional($order->created_at)->format('Y-m-d') }}<br>
                                            Payment Status: {{ $order->payment_status ?? 'Pending' }}<br>
                                            Payment Method: {{ $order->payment_method ?? 'N/A' }}
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <hr>

                        <div class="table-responsive">
                        <table id="itemsTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width:40px;"><input type="checkbox" id="selectAll"></th>
                                    <th>Product Name</th>
                                    <th>Ordered QTY</th>
                                    <th>Deliver QTY</th>
                                    <th>Price</th>
                                    <th>Item Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orderItems as $index => $item)
                                    @php
                                        $orderedQty = (float) ($item->quantity ?? 0);
                                        $itemGstTotal = (float) ($item->product_gst_total ?? 0);
                                        $unitGst = ($order->gst_option ?? null) === 'with_gst' && $orderedQty > 0
                                            ? $itemGstTotal / $orderedQty
                                            : 0;
                                        $displayUnitPrice = max(0, (float) ($item->price ?? 0) - $unitGst);
                                    @endphp
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="select-item" data-index="{{ $index }}" checked>
                                            <input type="hidden" name="items[{{ $index }}][order_item_id]" value="{{ $item->id }}">
                                        </td>
                                        <td data-label="Product Name">{{ $item->product->name ?? 'N/A' }}</td>
                                        <td data-label="Ordered QTY">{{ $item->quantity }}</td>
                                        <td data-label="Deliver QTY">
                                            <input type="number" step="0.01" min="0" max="{{ $item->remaining_to_deliver }}"
                                                name="items[{{ $index }}][delivered_quantity]"
                                                class="form-control deliver-qty"
                                                value="{{ $item->remaining_to_deliver }}"
                                                data-price="{{ $item->price }}"
                                                data-gst-total="{{ $item->product_gst_total ?? 0 }}"
                                                data-ordered-qty="{{ $item->quantity }}"
                                                data-max="{{ $item->remaining_to_deliver }}">
                                            <small class="text-muted">Remaining: {{ $item->remaining_to_deliver }}</small>
                                        </td>
                                        <td data-label="Price">{{ number_format($displayUnitPrice, 2) }}</td>
                                        <td data-label="Subtotal" class="item-subtotal">0.00</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>

                        <h5>Previous Deliveries</h5>
                        <div class="mb-3">
                            @if(isset($previousDeliveries) && $previousDeliveries->isNotEmpty())
                                <style>
                                    /* ── Previous Deliveries mobile card layout ── */
                                    @media (max-width: 767px) {
                                        .prev-delivery-table thead { display: none; }

                                        .prev-delivery-table tbody tr.pd-main-row {
                                            display: block;
                                            border: 1px solid #e5e7eb;
                                            border-radius: 10px;
                                            /* background: #fff; */
                                            /* box-shadow: 0 1px 3px rgba(15,23,42,.05); */
                                            margin-bottom: 10px;
                                            padding: 10px 48px 10px 12px;
                                            position: relative;
                                        }

                                        .prev-delivery-table tbody tr.pd-main-row td {
                                            display: block;
                                            border: none;
                                            padding: 3px 0;
                                            font-size: 13px;
                                        }

                                        /* Show only # and Product on collapsed state */
                                        .prev-delivery-table tbody tr.pd-main-row td.pd-hide {
                                            display: none;
                                        }
                                        .prev-delivery-table tbody tr.pd-main-row.expanded td.pd-hide {
                                            display: block;
                                        }

                                        /* Label prefix */
                                        .prev-delivery-table tbody tr.pd-main-row td[data-label]::before {
                                            content: attr(data-label) ': ';
                                            font-weight: 600;
                                            color: #6b7280;
                                            font-size: 11px;
                                            display: inline;
                                        }

                                        /* Toggle button — absolute top-right of the card row */
                                        .pd-toggle-btn {
                                            position: absolute;
                                            top: 50%;
                                            right: 10px;
                                            transform: translateY(-50%);
                                            width: 30px;
                                            height: 30px;
                                            border-radius: 50%;
                                            background: #ff9f43;
                                            border: none;
                                            color: #fff;
                                            font-size: 20px;
                                            font-weight: 700;
                                            line-height: 1;
                                            display: inline-flex;
                                            align-items: center;
                                            justify-content: center;
                                            cursor: pointer;
                                            transition: background 0.2s;
                                            padding: 0;
                                            z-index: 2;
                                        }
                                        .pd-toggle-btn.open { background: #ff9f43; }
                                    }

                                    @media (min-width: 768px) {
                                        .pd-toggle-btn { display: none !important; }
                                        .prev-delivery-table tbody tr.pd-main-row td.pd-hide {
                                            display: table-cell !important;
                                        }
                                    }
                                </style>

                                <div class="table-responsive">
                                <table class="table table-sm table-striped prev-delivery-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Product</th>
                                            <th>Delivered Qty</th>
                                            <th>By</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($previousDeliveries as $d)
                                            <tr class="pd-main-row">
                                                <td data-label="#">
                                                    {{-- Toggle button sits inside first cell, absolutely positioned top-right of the row --}}
                                                    <button type="button" class="pd-toggle-btn" aria-label="Toggle details">
                                                        <span class="pd-icon">+</span>
                                                    </button>
                                                    {{ $d->id }}
                                                </td>
                                                <td data-label="Product">{{ $d->product?->name ?? ($d->orderItem?->product?->name ?? 'N/A') }}</td>
                                                <td data-label="Delivered Qty" class="pd-hide">{{ $d->delivered_quantity }}</td>
                                                <td data-label="By" class="pd-hide">{{ $d->deliveredBy?->name ?? 'N/A' }}</td>
                                                <td data-label="Date" class="pd-hide">{{ optional($d->delivered_at)->format('Y-m-d H:i') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                </div>

                                <script>
                                    document.querySelectorAll('.pd-toggle-btn').forEach(function(btn) {
                                        btn.addEventListener('click', function() {
                                            const row = btn.closest('tr.pd-main-row');
                                            const icon = btn.querySelector('.pd-icon');
                                            const isOpen = row.classList.contains('expanded');
                                            row.classList.toggle('expanded', !isOpen);
                                            btn.classList.toggle('open', !isOpen);
                                            icon.textContent = isOpen ? '+' : '−';
                                        });
                                    });
                                </script>
                            @else
                                <div class="alert alert-light">No previous deliveries found for this order.</div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-8"></div>
                            <div class="col-md-4">
                                <table class="table table-sm">
                                    <tr><td>Subtotal (items)</td><td class="text-end" id="subtotalText">₹0.00</td></tr>
                                    <tr><td>Discount</td><td class="text-end" id="discountText">0.00%</td></tr>
                                    <tr><td>After Discount</td><td class="text-end" id="afterDiscountText">₹0.00</td></tr>
                                    <tr id="gstAmountRow" style="display:none;"><td>Total GST Amount</td><td class="text-end" id="gstAmountText">₹0.00</td></tr>
                                    <tr><td><strong>Total</strong></td><td class="text-end" id="totalText">₹0.00</td></tr>
                                </table>
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary">Submit Delivery</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const deliveryOrderGstOption = @json($order->gst_option ?? 'without_gst');

        // Select all
        document.getElementById('selectAll')?.addEventListener('change', function(e){
            const checked = e.target.checked;
            document.querySelectorAll('.select-item').forEach(ch => ch.checked = checked);
            recalcTotals();
        });

        document.querySelectorAll('.select-item').forEach(function(ch){
            ch.addEventListener('change', recalcTotals);
        });

        document.querySelectorAll('.deliver-qty').forEach(function(el){
            el.addEventListener('input', function(){
                const max = parseFloat(el.getAttribute('data-max')) || 0;
                let val = parseFloat(el.value) || 0;
                if (val > max) el.value = max;
                if (val < 0) el.value = 0;
                recalcTotals();
            });
        });

        function recalcTotals(){
            let subtotal = 0;
            let displaySubtotal = 0;
            let totalGst = 0;
            const rows = document.querySelectorAll('#itemsTable tbody tr');
            rows.forEach(function(row){
                const checkbox = row.querySelector('.select-item');
                const qtyInput = row.querySelector('.deliver-qty');
                if (!qtyInput) return;
                const price = parseFloat(qtyInput.getAttribute('data-price') || 0);
                const itemGstTotal = parseFloat(qtyInput.getAttribute('data-gst-total') || 0);
                const orderedQty = parseFloat(qtyInput.getAttribute('data-ordered-qty') || 0);
                const qty = parseFloat(qtyInput.value) || 0;
                const rowSubtotal = price * qty;
                const rowGst = deliveryOrderGstOption === 'with_gst' && orderedQty > 0
                    ? (itemGstTotal / orderedQty) * qty
                    : 0;
                const rowDisplaySubtotal = Math.max(0, rowSubtotal - rowGst);
                if (checkbox && checkbox.checked && qty > 0) {
                    subtotal += rowSubtotal;
                    displaySubtotal += rowDisplaySubtotal;
                    totalGst += rowGst;
                }
                const subCell = row.querySelector('.item-subtotal');
                if (subCell) subCell.textContent = rowDisplaySubtotal.toFixed(2);
            });
            document.getElementById('gstAmountText').textContent = '₹' + totalGst.toFixed(2);
            document.getElementById('gstAmountRow').style.display =
                deliveryOrderGstOption === 'with_gst' && totalGst > 0 ? '' : 'none';
            document.getElementById('subtotalText').textContent = '₹' + displaySubtotal.toFixed(2);
            document.getElementById('afterDiscountText').textContent = '₹' + displaySubtotal.toFixed(2);
            document.getElementById('totalText').textContent = '₹' + subtotal.toFixed(2);
        }

        // initial recalc
        recalcTotals();
    </script>
@endsection
