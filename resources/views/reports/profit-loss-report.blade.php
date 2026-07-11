<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Profit Loss Report PDF</title>
    <style>
        @page {
            size: A4;
            margin: 1mm 1mm;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: white;
        }

        .pdf-wrapper {
            margin-top: 3mm;
        }

        .card-body {
            width: 95%;
            min-height: 95%;
            padding: 3mm;
            margin: auto;
            box-sizing: border-box;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border: 1px solid black;
            font-size: 12px;
        }

        table,
        table td,
        table th {
            font-size: inherit;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
            padding: 6px 8px;
        }

        .table-bordered thead tr {
            background-color: #ff9f43;
            color: #fff;
        }

        .table-bordered tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .table-bordered tfoot tr {
            font-weight: bold;
            background-color: #e9ecef;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .report-title {
            text-transform: uppercase;
            margin: 0 0 12px 0;
            letter-spacing: 1px;
            color: #343a40;
        }

        .meta-table {
            margin-bottom: 14px;
        }

        .meta-table td {
            border: 1px solid #dee2e6;
            padding: 6px 8px;
            background: #f8f9fa;
        }

        .profit {
            color: #198754;
            font-weight: 600;
        }

        .loss {
            color: #dc3545;
            font-weight: 600;
        }

        .summary-box {
            border: 1px solid #dee2e6;
            background: #f8f9fa;
            margin-top: 14px;
            padding: 10px;
        }

        .summary-box h4 {
            margin: 0 0 8px 0;
            text-transform: uppercase;
            color: #343a40;
        }

        .inr {
            font-family: 'DejaVu Sans', 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            font-weight: 400;
        }
    </style>
</head>

<body>
    <div class="pdf-wrapper">
        <div class="card-body">
            <table style="width:100%; margin-bottom: 10px; border-collapse: collapse;">
                <tr>
                    <td style="width: 150px; vertical-align: middle;">
                        @if (isset($settings->logo) && file_exists(storage_path('app/public/' . $settings->logo)))
                            @php
                                $logoPath = storage_path('app/public/' . $settings->logo);
                                $logoData = base64_encode(file_get_contents($logoPath));
                                $logoMime = mime_content_type($logoPath);
                            @endphp
                            <img src="data:{{ $logoMime }};base64,{{ $logoData }}" alt="Company Logo" style="height: 60px; width: auto;">
                        @endif
                    </td>
                    <td style="vertical-align: middle; text-align: right; padding-left: 15px;">
                        <h3 style="margin: 0; text-transform: uppercase; font-size: 16px; color: #000;">
                            {{ $settings->name ?? '' }}
                        </h3>
                        <small style="text-transform: uppercase; font-size: 14px;">
                            {{ $settings->address ?? '' }}<br>
                            Phone: {{ $settings->phone ?? '' }} |
                            Email: <span style="text-transform: none;">{{ $settings->email ?? '' }}</span>
                        </small>
                    </td>
                </tr>
            </table>

            <hr style="height: 2px; background-color: #d7cdcd; border: none; margin-top: 0; margin-bottom: 16px;">

            <div class="text-center">
                <h4 class="report-title">Profit Loss Report</h4>
            </div>

            @php
                $items = $items ?? collect();
                $totalSales = 0;
                $totalPurchase = 0;

                $formatIndianCurrency = function ($amount, $decimals = 2) {
                    $amount = (float) $amount;
                    $isNegative = $amount < 0;
                    $amount = abs($amount);

                    $formatted = number_format($amount, $decimals, '.', '');
                    $parts = explode('.', $formatted);
                    $integerPart = $parts[0] ?? '0';
                    $decimalPart = $parts[1] ?? str_repeat('0', $decimals);

                    if (strlen($integerPart) > 3) {
                        $lastThree = substr($integerPart, -3);
                        $remaining = substr($integerPart, 0, -3);
                        $remaining = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $remaining);
                        $integerPart = $remaining . ',' . $lastThree;
                    }

                    $result = $decimals > 0 ? ($integerPart . '.' . $decimalPart) : $integerPart;
                    return $isNegative ? '-' . $result : $result;
                };

                $reportPeriod = 'All Time';
                if (!empty($month) || !empty($year)) {
                    $monthName = !empty($month) ? date('F', mktime(0, 0, 0, (int) $month, 1)) : '';
                    $reportPeriod = trim($monthName . ' ' . ($year ?? ''));
                } elseif (!empty($startDate) || !empty($endDate)) {
                    $reportPeriod = ($startDate ?: 'N/A') . ' to ' . ($endDate ?: 'N/A');
                }
            @endphp

            {{-- <table class="meta-table">
                <tr>
                    <td><strong>Product:</strong> {{ $selectedProduct ? $selectedProduct->name : 'All Products' }}</td>
                    <td><strong>Vendor:</strong> {{ $selectedVendor ? $selectedVendor->name : 'All Vendors' }}</td>
                </tr>
                <tr>
                    <td><strong>Customer:</strong> {{ $selectedCustomer ? $selectedCustomer->name : 'All Customers' }}</td>
                    <td><strong>Period:</strong> {{ $reportPeriod }}</td>
                </tr>
            </table> --}}

            <table class="table-bordered">
                <thead>
                    <tr>
                        <th style="width: 6%;">Sr No</th>
                        <th style="width: 14%;">Date</th>
                        <th style="width: 30%;">Product Name</th>
                        <th style="width: 16%;" class="text-end">Purchase Amount</th>
                        <th style="width: 16%;" class="text-end">Sales Amount</th>
                        <th style="width: 18%;" class="text-end">Profit / Loss</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $index => $item)
                        @php
                            $purchase = (float) ($item->purchase_rate ?? 0);
                            $sales = (float) ($item->sales_amount ?? 0);
                            $profit = (float) ($item->profit_amount ?? ($sales - $purchase));

                            $totalSales += $sales;
                            $totalPurchase += $purchase;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}</td>
                            <td>{{ $item->product_name ?? 'N/A' }}</td>
                            <td class="text-end"><span class="inr">&#8377;</span>{{ $formatIndianCurrency($purchase, 2) }}</td>
                            <td class="text-end"><span class="inr">&#8377;</span>{{ $formatIndianCurrency($sales, 2) }}</td>
                            <td class="text-end {{ $profit >= 0 ? 'profit' : 'loss' }}">
                                <span class="inr">&#8377;</span>{{ $formatIndianCurrency($profit, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No records found</td>
                        </tr>
                    @endforelse
                </tbody>
                @if (count($items) > 0)
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end">TOTAL</td>
                            <td class="text-end"><span class="inr">&#8377;</span>{{ $formatIndianCurrency($totalSales, 2) }}</td>
                            <td class="text-end {{ ($totalSales - $totalPurchase) >= 0 ? 'profit' : 'loss' }}">
                                <span class="inr">&#8377;</span>{{ $formatIndianCurrency($totalSales - $totalPurchase, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>

            @if (count($items) > 0)
                <div class="summary-box">
                    <h4>Final Summary</h4>
                    <table style="margin: 0;">
                        <tr>
                            <td style="border: none; width: 50%;"><strong>Total Sales</strong></td>
                            <td style="border: none;" class="text-end"><span class="inr">&#8377;</span>{{ $formatIndianCurrency($totalSales, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="border: none;"><strong>Total Purchase Cost</strong></td>
                            <td style="border: none;" class="text-end"><span class="inr">&#8377;</span>{{ $formatIndianCurrency($totalPurchase, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; border-top: 1px solid #333;"><strong>Net Profit / Loss</strong></td>
                            <td style="border: none; border-top: 1px solid #333;" class="text-end {{ ($totalSales - $totalPurchase) >= 0 ? 'profit' : 'loss' }}">
                                <span class="inr">&#8377;</span>{{ $formatIndianCurrency($totalSales - $totalPurchase, 2) }}
                            </td>
                        </tr>
                    </table>
                </div>
            @endif
        </div>
    </div>
</body>

</html>
