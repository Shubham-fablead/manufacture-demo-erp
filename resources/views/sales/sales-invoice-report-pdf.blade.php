
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice PDF</title>
    <style>
        @page {
            size: A4;
            margin: 8mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #222;
            background: #fff;
        }

        .sheet {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #222;
            padding: 10px 12px 14px;
        }

        .topbar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .topbar td {
            vertical-align: top;
        }

        .company-name {
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            line-height: 1.15;
            margin: 0;
        }

        .company-meta {
            font-size: 11px;
            line-height: 1.35;
            text-align: right;
        }

        .divider {
            height: 2px;
            background: #d6c9c3;
            margin: 6px 0 12px;
        }

        .info-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            background: #e9edf1;
            margin-bottom: 12px;
        }

        .info-grid td {
            vertical-align: top;
            padding: 8px 10px 10px;
        }

        .info-grid .left {
            width: 50%;
            border-right: 1px solid #ff9f43;
        }

        .info-title {
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px 0;
            font-size: 12px;
        }

        .info-table td:first-child {
            width: 46%;
        }

        .section-title {
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
            margin: 4px 0 10px;
            font-size: 13px;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 11px;
        }

        .product-table th,
        .product-table td {
            border: 1px solid #e2e2e2;
            padding: 7px 6px;
            vertical-align: middle;
            word-break: break-word;
        }

        .product-table thead th {
            background: #ff9f43;
            color: #fff;
            text-align: left;
            font-weight: 700;
        }

        .product-cell {
            display: block;
        }

        .product-name {
            font-weight: 700;
            margin-top: 4px;
        }

        .product-thumb {
            width: 42px;
            height: 42px;
            object-fit: cover;
            display: block;
            margin-top: 2px;
            border: 1px solid #ddd;
            background: #fff;
        }

        .total-row {
            width: 290px;
            margin-left: auto;
            margin-top: 0;
            border-collapse: collapse;
            font-size: 12px;
        }

        .total-row td {
            border: 1px solid #e2e2e2;
            padding: 7px 12px;
        }

        .total-row .label {
            background: #ff9f43;
            color: #fff;
            font-weight: 700;
            text-align: left;
        }

        .total-row .value {
            text-align: right;
            background: #fff;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    @php
        $fallbackSetting = new \App\Models\Setting([
            'name' => 'Fablead Developer & Technolab',
            'email' => 'info@gmail.com',
            'phone' => '1234567890',
            'address' => 'Adajan Surat',
            'logo' => 'admin/assets/img/logo-image.jpg',
            'currency_symbol' => '?',
            'currency_position' => 'left',
        ]);
        $setting = ($setting ?? null) ?: ($settings ?? null) ?: $fallbackSetting;
        $settings = $setting;
        $currencySymbol = $currencySymbol ?? ($setting->currency_symbol ?? '?');
        $currencyPosition = $currencyPosition ?? ($setting->currency_position ?? 'left');

        $logoPath = null;
        $logoData = null;
        $logoMime = null;
        if (isset($setting->logo) && file_exists(storage_path('app/public/' . $setting->logo))) {
            $logoPath = storage_path('app/public/' . $setting->logo);
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoMime = mime_content_type($logoPath);
        }
    @endphp

    <div class="sheet">
        <table class="topbar">
            <tr>
                <td style="width: 180px;">
                    @if ($logoData && $logoMime)
                        <img src="data:{{ $logoMime }};base64,{{ $logoData }}" alt="Company Logo"
                            style="max-width: 160px; max-height: 65px;">
                    @endif
                </td>
                <td>
                    <div class="company-meta">
                        <div class="company-name">{{ $setting->name ?? '' }}</div>
                        <div>{{ $setting->address ?? '' }}</div>
                        <div>PHONE: {{ $setting->phone ?? '' }} | EMAIL: {{ $setting->email ?? '' }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="divider"></div>

        <table class="info-grid">
            <tr>
                <td class="left">
                    <div class="info-title">Company Details:</div>
                    <table class="info-table">
                        @if (!empty($setting->name))
                            <tr>
                                <td>Name :</td>
                                <td class="text-right">{{ $setting->name }}</td>
                            </tr>
                        @endif
                        @if (!empty($setting->email))
                            <tr>
                                <td>Email :</td>
                                <td class="text-right">{{ $setting->email }}</td>
                            </tr>
                        @endif
                        @if (!empty($setting->phone))
                            <tr>
                                <td>Phone :</td>
                                <td class="text-right">{{ $setting->phone }}</td>
                            </tr>
                        @endif
                        @if (!empty($setting->address))
                            <tr>
                                <td>Address :</td>
                                <td class="text-right">{{ $setting->address }}</td>
                            </tr>
                        @endif
                        @if (!empty($setting->gst_num))
                            <tr>
                                <td>GST :</td>
                                <td class="text-right">{{ $setting->gst_num }}</td>
                            </tr>
                        @endif
                    </table>
                </td>
                <td>
                    <div class="info-title">Order Report Details:</div>
                    <table class="info-table">
                        <tr>
                            <td>Total Sales :</td>
                            <td class="text-right">{{ count($sales) }}</td>
                        </tr>
                        <tr>
                            <td>Report Date :</td>
                            <td class="text-right">{{ \Carbon\Carbon::now()->format('d M Y') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="section-title">Products</div>

        <table class="product-table">
            <thead>
                <tr>
                    <th style="width: 3.5%;">Sr. No.</th>
                    <th style="width: 8%;">Order Number</th>
                    <th style="width: 7%;">Sale Date</th>
                    <th style="width: 11%;">Product</th>
                    <th style="width: 9%;">Customer</th>
                    <th style="width: 8%;">GST NO</th>
                    <th style="width: 12%;">Address</th>
                    <th style="width: 8%;">Category</th>
                    <th style="width: 8%;">Price</th>
                    <th style="width: 7%;">Discount</th>
                    <th style="width: 8%;">Final Price</th>
                    <th style="width: 5%;">Qty</th>
                    <th style="width: 8%;">Taxes</th>
                    <th style="width: 6%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $subtotal = 0;
                @endphp
                @foreach ($sales as $index => $sale)
                    @php
                        $discountPercent = $sale->invoice->discount ?? 0;
                        $originalUnitPrice = $sale->quantity ? $sale->total_amount / $sale->quantity : 0;
                        $discountPerUnit = ($originalUnitPrice * $discountPercent) / 100;
                        $finalUnitPrice = $originalUnitPrice - $discountPerUnit;
                        $finalTotal = $sale->rowFinalTotal ?? $finalUnitPrice * $sale->quantity;
                        $orderNumber = $sale->invoice->order_number ?? 'N/A';
                        $saleDate = optional($sale->created_at)->format('d M Y') ?? 'N/A';
                        $customerName = $sale->user->name ?? 'N/A';
                        $gstNo = $sale->user->gst_number ?? 'N/A';
                        $customerAddress = optional($sale->user->userDetail)->address ?? 'N/A';
                        $subtotal += $finalTotal;

                        $images = json_decode($sale->product->images ?? '[]', true);
                        $imagePath =
                            isset($images[0]) && file_exists(public_path('storage/' . $images[0]))
                                ? env('ImagePath') . 'storage/' . $images[0]
                                : env('ImagePath') . 'admin/assets/img/product/noimage.png';
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $orderNumber }}</td>
                        <td>{{ $saleDate }}</td>
                        <td>
                            <span class="product-cell">
                                <img src="{{ $imagePath }}" alt="Product Image" class="product-thumb">
                                <span class="product-name">{{ $sale->product->name ?? '-' }}</span>
                            </span>
                        </td>
                        <td>{{ $customerName }}</td>
                        <td>{{ $gstNo }}</td>
                        <td>{{ $customerAddress }}</td>
                        <td>{{ $sale->product->category->name ?? 'N/A' }}</td>
                        <td>{{ $currencyPosition === 'left' ? $currencySymbol . number_format($originalUnitPrice, 2) : number_format($originalUnitPrice, 2) . $currencySymbol }}</td>
                        <td>{{ number_format($discountPercent, 2) }}%</td>
                        <td>{{ $currencyPosition === 'left' ? $currencySymbol . number_format($finalUnitPrice, 2) : number_format($finalUnitPrice, 2) . $currencySymbol }}</td>
                        <td>{{ number_format($sale->quantity, 2) }}</td>
                        <td>
                            @if ($sale->rowGSTOption === 'with_gst' && !empty($sale->rowTaxes))
                                @foreach ($sale->rowTaxes as $t)
                                    <div>{{ $t['name'] }} ({{ $t['rate'] }}%) : {{ $currencyPosition === 'left' ? $currencySymbol . number_format($t['amount'], 2) : number_format($t['amount'], 2) . $currencySymbol }}</div>
                                @endforeach
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $currencyPosition === 'left' ? $currencySymbol . number_format($finalTotal, 2) : number_format($finalTotal, 2) . $currencySymbol }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="total-row">
            <tr>
                <td class="label">Total Amount</td>
                <td class="value">
                    {{ $currencyPosition === 'left' ? $currencySymbol . number_format($totalAmount, 2) : number_format($totalAmount, 2) . $currencySymbol }}
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
