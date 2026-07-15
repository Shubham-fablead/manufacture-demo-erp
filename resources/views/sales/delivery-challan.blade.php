@extends('layout.app')

@section('content')
    <div class="content">
        
        <div class="card">
            <div class="card-body">
                <div style="max-width:900px;margin:0 auto;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                        <div>
                            <h3>{{ $setting->name ?? 'Company' }}</h3>
                            <div>{{ $setting->address ?? '' }}</div>
                            <div>{{ $setting->phone ?? '' }} | {{ $setting->email ?? '' }}</div>
                        </div>
                        <div style="text-align:right;">
                            <h4>DELIVERY CHALLAN</h4>
                            <div>Challan No: DC-{{ $order->id }}</div>
                            <div>Date: {{ now()->format('d M Y') }}</div>
                        </div>
                    </div>

                    <div style="display:flex;gap:20px;margin-bottom:10px;">
                        <div style="flex:1;border:1px solid #ddd;padding:8px;">
                            <strong>Customer Details</strong>
                            <div>Name: {{ $order->customer_name ?? ($order->user?->name ?? 'Walk-in Customer') }}</div>
                            <div>Phone: {{ $order->customer_phone ?? ($order->user?->phone ?? '') }}</div>
                            <div>Email: {{ $order->customer_email ?? ($order->user?->email ?? '') }}</div>
                            @php
                                $customerAddress = trim(implode(', ', array_filter([
                                    $order->customer_address ?? '',
                                    $order->customer_city ?? '',
                                    $order->customer_country ?? '',
                                ])));
                            @endphp
                            @if (!empty($customerAddress))
                                <div>Address: {{ $customerAddress }}</div>
                            @endif
                        </div>
                        <div style="flex:1;border:1px solid #ddd;padding:8px;">
                            <strong>Company Details</strong>
                            <div>{{ $setting->name ?? '' }}</div>
                            <div>{{ $setting->address ?? '' }}</div>
                            <div>{{ $setting->phone ?? '' }} | {{ $setting->email ?? '' }}</div>
                        </div>
                        <div style="flex:1;border:1px solid #ddd;padding:8px;">
                            <strong>Delivery Details</strong>
                            <div>Order #: {{ $order->order_number ?? $order->id }}</div>
                            <div>Order Date: {{ optional($order->created_at)->format('d M Y') }}</div>
                            @if ($deliveries->isNotEmpty() && optional($deliveries->first()->deliveredBy)->name)
                                <div>Delivered By: {{ $deliveries->first()->deliveredBy->name }}</div>
                            @endif
                        </div>
                    </div>

                    <h5>Delivered Items</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Sr No</th>
                                <th>Product Name</th>
                                <th>Unit</th>
                                <th>Ordered Qty</th>
                                <th>Delivered Qty</th>
                                <th>Pending Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i = 1; @endphp
                            @foreach($deliveries as $d)
                                @php
                                    $product = $d->orderItem->product ?? $d->product ?? null;
                                @endphp
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $product->name ?? 'N/A' }}</td>
                                    <td>{{ $product->unit->unit_name ?? 'N/A' }}</td>
                                    <td>{{ number_format($d->ordered_quantity, 2) }}</td>
                                    <td>{{ number_format($d->delivered_quantity, 2) }}</td>
                                    <td>{{ number_format(max(0, $d->ordered_quantity - $d->delivered_quantity), 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @php
                        $deliveryNotes = $deliveries->pluck('notes')->filter()->unique()->implode(', ');
                        $remarks = trim($deliveryNotes) !== '' ? $deliveryNotes : ($order->remarks ?? $order->notes ?? 'N/A');
                    @endphp
                    <div style="margin-top:20px; display:flex; justify-content:space-between; gap:20px;">
                        <div style="flex:1;">
                            <strong>Remarks:</strong>
                            <div>{{ $remarks }}</div>
                        </div>
                        <div style="flex:1; text-align:center;">
                            <div>Received By (Customer)</div>
                            <div style="margin-top:60px; border-top:1px solid #999;">Signature &amp; Date</div>
                        </div>
                        <div style="flex:1; text-align:center;">
                            <div>For, {{ $setting->name ?? '' }}</div>
                            <div style="margin-top:60px; border-top:1px solid #999;">(Authorized Signatory)</div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <a href="javascript:window.print()" class="btn btn-sm btn-primary">Print</a>
                        <a href="{{ route('sales.delivery', $order->id) }}" class="btn btn-sm btn-secondary">Back to Delivery</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
