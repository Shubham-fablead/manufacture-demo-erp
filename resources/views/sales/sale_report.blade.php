@extends('layout.app')

@section('title', 'Sale Report')

@section('content')
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

$topSoldProducts = collect($sales ?? [])
    ->groupBy(fn($sale) => $sale->product->name ?? 'Unknown')
    ->map(function ($items, $name) {
        return [
            'name' => $name,
            'qty' => (float) $items->sum('quantity'),
        ];
    })
    ->sortByDesc('qty')
    ->take(14)
    ->values();

$topSoldMaxQty = max(1, (float) ($topSoldProducts->max('qty') ?? 1));
@endphp

    <style>
        .invoice-box tr td {
            vertical-align: middle
        }

        .purchase_report_table1 {
            table-layout: fixed;
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #dee2e6;
        }

        .purchase_report_table1 th,
        .purchase_report_table1 td {
            padding: 8px 8px !important;
            font-size: 12px;
            line-height: 1.45;
            word-break: break-word;
            white-space: normal;
            vertical-align: top;
            border: 1px solid #dee2e6;
        }

        .purchase_report_table1 thead th {
            background: #f3f2f7;
            font-weight: 600;
            color: #222;
            border: 1px solid #dee2e6;
            vertical-align: middle;
            white-space: nowrap;
            text-align: left;
        }

        .purchase_report_table1 tbody tr:hover {
            background: #fafafa;
        }

        /* Remove borders from the inner Company Info / Report Info table */
        .purchase_report_table1 tr:first-child td {
            border: none !important;
        }

        .purchase_report_table1 tr:first-child td table,
        .purchase_report_table1 tr:first-child td table td {
            border: none !important;
            background: transparent !important;
        }

        .purchase_report_table1 .col-sr {
            width: 46px;
        }

        .purchase_report_table1 .col-order {
            width: 95px;
        }

        .purchase_report_table1 .col-date {
            width: 78px;
        }

        .purchase_report_table1 .col-product {
            width: 160px;
        }

        .purchase_report_table1 .col-customer {
            width: 130px;
        }

        .purchase_report_table1 .col-gst {
            width: 85px;
        }

        .purchase_report_table1 .col-address {
            width: 220px;
        }

        .purchase_report_table1 .col-category {
            width: 95px;
        }

        .purchase_report_table1 .col-price,
        .purchase_report_table1 .col-discount,
        .purchase_report_table1 .col-final,
        .purchase_report_table1 .col-qty,
        .purchase_report_table1 .col-total {
            width: 76px;
        }

        .purchase_report_table1 .col-taxes {
            width: 96px;
        }

        .purchase_report_table1 .heading th {
            font-size: 12px;
            padding-top: 10px !important;
            padding-bottom: 10px !important;
        }

        .purchase_report_table1 .details td {
            font-size: 12px;
        }

        .report-metrics {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin: 8px 0 18px;
        }

        .report-total-badge {
            border: 1px solid #2a3270;
            color: #1b214f;
            background: #fff;
            border-radius: 4px;
            padding: 8px 16px;
            font-size: 17px;
            font-weight: 700;
            white-space: nowrap;
        }

        .report-total-badge span {
            color: #ff8f2a;
        }

        .chart-card {
            border: 1px solid #e3e6ef;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            margin-bottom: 22px;
        }

        .chart-card__header {
            background: linear-gradient(90deg, #11172f 0%, #2c315d 100%);
            color: #fff;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chart-card__icon {
            width: 34px;
            height: 28px;
            border-radius: 8px;
            background: rgba(78, 153, 255, 0.22);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #6fb4ff;
            font-size: 16px;
        }

        .chart-card__title {
            font-size: 18px;
            font-weight: 700;
            line-height: 1.1;
        }

        .chart-card__subtitle {
            margin-top: 3px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.75);
        }

        .chart-card__body {
            padding: 18px 16px 12px;
            background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
        }

        .top-sold-chart {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            min-height: 300px;
            overflow-x: auto;
            padding: 8px 6px 6px;
        }

        .chart-column {
            width: 48px;
            flex: 0 0 48px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            height: 100%;
        }

        .chart-bar-wrap {
            height: 240px;
            width: 100%;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-bottom: 4px;
        }

        .chart-bar {
            width: 34px;
            border-radius: 6px 6px 2px 2px;
            box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.12);
        }

        .chart-value {
            font-size: 11px;
            color: #667085;
            margin-bottom: 4px;
        }

        .chart-label {
            margin-top: 8px;
            font-size: 11px;
            color: #6e7787;
            transform: rotate(-28deg);
            transform-origin: top left;
            width: 92px;
            height: 38px;
            text-align: left;
            white-space: nowrap;
        }

        @media screen and (max-width: 768px) {
            .page-header {
                flex-direction: row;
            }

            .page-header .page-btn {
                margin-top: 0 !important;
            }

            .card-sales-split ul {
                margin-left: 14rem;
            }

            .purchase_report_head img {
                max-width: 100px !important;
            }

            .purchase_report_head h2 {
                font-size: 17px !important;
            }

            .purchase_report_table1 tr td:first-child {
                font-size: 11px !important;
            }

            .purchase_report_table1 tr {
                display: flex;
                justify-content: space-between;
            }

            .purchase_report_table1 tr td:last-child {
                padding: 10px 5px !important;
                font-size: 11px !important;
            }

            .invoice-box {
                overflow-x: auto;
            }

            .heading td:first-child {
                padding: 10px 40px !important;

            }
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Sales Report</h4>

            </div>
            <div class="page-btn d-flex gap-2">
                <a href="{{ route('sales.report') }}" class="btn btn-added">
                    Back
                </a>
                @if (!empty($ids))
                    <a href="{{ url('/sales/report/' . $ids . '/export-excel') }}" class="btn btn-success">
                        Export Excel
                    </a>
                    <a href="{{ url('/sales/report/' . $ids . '/export-pdf') }}" class="btn btn-danger">
                        Export PDF
                    </a>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="report-metrics">
                    <div></div>
                    <div class="report-total-badge">
                        Total: <span>{{ $currencyPosition === 'left' ? $currencySymbol . number_format($totalAmount, 2) : number_format($totalAmount, 2) . $currencySymbol }}</span>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-card__header">
                        <div class="chart-card__icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div>
                            <div class="chart-card__title">Top Sold Products</div>
                            <div class="chart-card__subtitle">Ranked by quantity sold</div>
                        </div>
                    </div>
                    <div class="chart-card__body">
                        <div class="top-sold-chart">
                            @forelse ($topSoldProducts as $topProduct)
                                @php
                                    $barHeight = max(18, round(($topProduct['qty'] / $topSoldMaxQty) * 200));
                                    $barColors = ['#5B6EE1', '#5AA0FF', '#8B3FD9', '#FF4F93', '#55C8F2', '#28D7A0', '#3FA3C6', '#FFD166', '#F15F79', '#4B6B79', '#F4C96B', '#F8A96B', '#F07D62', '#5B7487'];
                                    $barColor = $barColors[$loop->index % count($barColors)];
                                @endphp
                                <div class="chart-column">
                                    <div class="chart-value">{{ rtrim(rtrim(number_format($topProduct['qty'], 2), '0'), '.') }}</div>
                                    <div class="chart-bar-wrap">
                                        <div class="chart-bar" style="height: {{ $barHeight }}px; background: {{ $barColor }};"></div>
                                    </div>
                                    <div class="chart-label" title="{{ $topProduct['name'] }}">{{ \Illuminate\Support\Str::limit($topProduct['name'], 18) }}</div>
                                </div>
                            @empty
                                <div class="text-center w-100 py-4">No product data available.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <input type="hidden" name="selse_id" id="selse_id" value="">
                <tr class="top">
                    <td colspan="6">
                        <div class="row purchase_report_head">
                            <div class="col-6">
                                <img src="{{ $settings->logo ? env('ImagePath') . 'storage/' . $settings->logo : env('ImagePath') . 'admin/assets/img/logo-image.jpg' }}"
                                    style="max-width: 150px;">

                            </div>
                            <div class="col-6 mt-4" style=" text-align: end;">
                                <h2>Sales Report</h2>
                            </div>
                        </div>

                    </td>
                </tr>
                <div class="download_pdf">
                    <div class="invoice-box table-height"
                        style="max-width: 1600px; width:100%; margin:15px auto; padding: 0; font-size: 14px; line-height: 24px; color: #555;">
                        <table class="purchase_report_table1" style="line-height: inherit; text-align: left;">
                            <tr>
                                <td colspan="12">
                                    <table style="width: 100%;">
                                        <tr>
                                            <!-- <td
                                                style="padding:5px; vertical-align:top; text-align:left; padding-bottom:20px">
                                                <font style="vertical-align: inherit; margin-bottom:25px;">
                                                    <font
                                                        style="vertical-align: inherit; font-size:14px; color:#7367F0; font-weight:600; line-height: 35px;">
                                                        User Info
                                                    </font>
                                                </font><br>

                                                @if (!empty($user->name))
                                                    <font>
                                                        <font class="vendor-name">{{ $user->name }}</font>
                                                    </font><br>
                                                @endif

                                                @if (!empty($user->email))
                                                    <font>
                                                        <font>{{ $user->email }}</font>
                                                    </font><br>
                                                @endif

                                                @if (!empty($user->phone))
                                                    <font>
                                                        <font class="vendor-phone">{{ $user->phone }}</font>
                                                    </font><br>
                                                @endif

                                                <font>
                                                    <strong>GST No : </strong>
                                                    <font class="gst-no">{{ $user->gst_number ?? '--' }}</font>
                                                </font><br>
                                                <font>
                                                    <strong>PAN No : </strong>
                                                    <font class="pan-no">{{ $user->pan_number ?? '--' }}</font>
                                                </font><br>
                                            </td> -->

                                            <td style="padding: 10px; float: left;">
                                                <strong style="font-size:14px; color:#7367F0; font-weight:600;">Company
                                                    Info</strong><br>
                                                {{ $settings->name ?? 'Company Name' }}<br>
                                                {{ $settings->email ?? 'N/A' }}<br>
                                                {{ $settings->phone ?? 'N/A' }}<br>
                                                {{ $settings->address ?? 'N/A' }}<br>
                                                GST: {{ $settings->gst_num ?? 'N/A' }}<br>
                                            </td>

                                            <td style="padding: 10px; float: right;">
                                                <strong style="font-size:14px; color:#7367F0; font-weight:600;">Report
                                                    Info</strong><br>
                                                Total Sales: {{ count($sales) }}<br>
                                                Report Date: {{ \Carbon\Carbon::now()->format('d M Y') }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <tr class="heading">
                                <th class="col-sr">Sr. No.</th>
                                <th class="col-order">Order Number</th>
                                <th class="col-date">Sale Date</th>
                                <th class="col-product">Product</th>
                                <th class="col-customer">Customer Name</th>
                                <th class="col-gst">GST NO</th>
                                <th class="col-address">Customer Address</th>
                                <th class="col-category">Category</th>
                                <th class="col-price">Price</th>
                                <th class="col-discount">Discount</th>
                                <th class="col-final">Final Price</th>
                                <th class="col-qty">Qty</th>
                                <th class="col-taxes">Taxes</th>
                                <th class="col-total">Total</th>
                            </tr>

                            @php $subtotal = 0; @endphp
                            @foreach ($sales as $index => $sale)
                                @php
                                    $discountPercent = $sale->invoice->discount ?? 0;
                                    $originalUnitPrice = $sale->quantity ? $sale->total_amount / $sale->quantity : 0;
                                    $discountPerUnit = ($originalUnitPrice * $discountPercent) / 100;
                                    $finalUnitPrice = $originalUnitPrice - $discountPerUnit;
                                    $finalTotal = $finalUnitPrice * $sale->quantity;
                                    $orderNumber = $sale->invoice->order_number ?? 'N/A';
                                    $saleDate = optional($sale->created_at)->format('d M Y') ?? 'N/A';
                                    $customerName = $sale->user->name ?? 'N/A';
                                    $gstNo = $sale->user->gst_number ?? 'N/A';
                                    $customerAddress = optional($sale->user->userDetail)->address ?? 'N/A';

                                    $subtotal += $finalTotal;

                                    $images = json_decode($sale->product->images, true);
                                    $imagePath =
                                        isset($images[0]) && file_exists(public_path('storage/' . $images[0]))
                                            ? env('ImagePath') . 'storage/' . $images[0]
                                            : env('ImagePath') . 'admin/assets/img/product/noimage.png';
                                @endphp

                                <tr class="details">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $orderNumber }}</td>
                                    <td>{{ $saleDate }}</td>
                                    <td>
                                        <a href="{{ url('product-view/' . ($sale->product->id ?? '')) }}"
                                            style="display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit;">
                                            <img src="{{ $imagePath }}" alt="Product Image"
                                                style="width: 30px; height: 30px; object-fit: cover;">
                                            <span>{{ $sale->product->name ?? '-' }}</span>
                                        </a>
                                    </td>
                                    <td>{{ $customerName }}</td>
                                    <td>{{ $gstNo }}</td>
                                    <td>{{ $customerAddress }}</td>
                                    <td>{{ $sale->product->category->name ?? 'N/A' }}</td>
                                    <td>{{ $currencyPosition === 'left' ? $currencySymbol . number_format($originalUnitPrice, 2) : number_format($originalUnitPrice, 2) . $currencySymbol }}</td>
                                    <td>{{ $discountPercent }}%</td>
                                    <td>{{ $currencyPosition === 'left' ? $currencySymbol . number_format($finalUnitPrice, 2) : number_format($finalUnitPrice, 2) . $currencySymbol }}</td>
                                    <td>{{ $sale->quantity }}</td>
                                    <td>
                                        @if ($sale->rowGSTOption === 'with_gst' && !empty($sale->rowTaxes))
                                            @foreach ($sale->rowTaxes as $t)
                                                <div>
                                                    {{ $t['name'] }} ({{ $t['rate'] }}%) :
                                                    {{ $currencyPosition === 'left' ? $currencySymbol . number_format($t['amount'], 2) : number_format($t['amount'], 2) . $currencySymbol }}
                                                </div>
                                            @endforeach
                                        @else
                                            <span>N/A</span>
                                            @endif
                                    </td>
                                    <td>{{ $currencyPosition === 'left' ? $currencySymbol . number_format($sale->rowFinalTotal, 2) : number_format($sale->rowFinalTotal, 2) . $currencySymbol }}</td>
                                </tr>
                            @endforeach

                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadPDF() {
            const element = document.querySelector('.download_pdf');
            const logoUrl =
                "{{ $settings->logo ? env('ImagePath') . '/storage/' . $settings->logo : env('ImagePath') . '/admin/assets/img/logo-image.jpg' }}";

            loadImageAsBase64(logoUrl, function(logoBase64, logoWidth, logoHeight) {
                const opt = {
                    margin: [40, 10, 30, 10],
                    filename: 'Sales Report.pdf',
                    image: {
                        type: 'jpeg',
                        quality: 0.98
                    },
                    html2canvas: {
                        scale: 2
                    },
                    jsPDF: {
                        unit: 'mm',
                        format: 'a4',
                        orientation: 'portrait'
                    },
                    pagebreak: {
                        avoid: 'tr'
                    }
                };

                html2pdf().set(opt).from(element).toPdf().get('pdf').then(function(pdf) {
                    const pageCount = pdf.internal.getNumberOfPages();
                    const pageWidth = pdf.internal.pageSize.getWidth();
                    const pageHeight = pdf.internal.pageSize.getHeight();

                    for (let i = 1; i <= pageCount; i++) {
                        pdf.setPage(i);

                        const logoTop = 10;
                        const logoBottomMargin = 5; // Add margin below logo
                        const headerEndY = logoTop + logoHeight + logoBottomMargin;

                        // === HEADER START ===
                        // Logo
                        pdf.addImage(logoBase64, 'JPEG', 10, logoTop, logoWidth, logoHeight);

                        // Report Title (align with bottom of logo + margin)
                        pdf.setFontSize(14);
                        pdf.setFont('helvetica', 'bold');
                        pdf.setTextColor(100);
                        pdf.text("Sales Report", pageWidth - 10, logoTop + (logoHeight / 2), {
                            align: 'right'
                        });

                        // Header Line below logo
                        pdf.setDrawColor(200);
                        pdf.line(10, headerEndY, pageWidth - 10, headerEndY);
                        // === HEADER END ===


                        // === FOOTER START ===
                        pdf.setFontSize(9);
                        pdf.setFont('helvetica', 'normal');
                        pdf.setTextColor(150);
                        pdf.line(10, pageHeight - 15, pageWidth - 10, pageHeight - 15);
                        pdf.text(
                            `© {{ now()->year }} {{ $settings->name ?? 'Company Name' }} - All rights reserved.`,
                            10, pageHeight - 10);
                        pdf.text(`Page ${i} of ${pageCount}`, pageWidth - 10, pageHeight - 10, {
                            align: 'right'
                        });
                        // === FOOTER END ===
                    }
                }).save();
            });
        }

        // ✅ Image to base64 with aspect ratio preserved
        function loadImageAsBase64(url, callback) {
            const img = new Image();
            img.crossOrigin = 'anonymous'; // Fix for CORS issues on live

            img.onload = function() {
                const canvas = document.createElement('canvas');
                canvas.width = img.width;
                canvas.height = img.height;

                const ctx = canvas.getContext('2d');

                // ✅ Fill white background before drawing image (fixes black bg)
                ctx.fillStyle = "#FFFFFF";
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                ctx.drawImage(img, 0, 0);

                const base64 = canvas.toDataURL('image/jpeg'); // JPEG used in pdf.addImage
                const desiredWidth = 30; // Width in mm
                const ratio = img.height / img.width;
                const desiredHeight = desiredWidth * ratio;

                callback(base64, desiredWidth, desiredHeight);
            };

            img.onerror = function() {
                alert("Logo could not be loaded.");
                callback('', 0, 0);
            };

            img.src = url;
        }
    </script>
@endpush
