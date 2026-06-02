<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>TDS Report PDF</title>
    <style>
        @page {
            size: A4;
            margin: 1mm 1mm;
        }

        body {
            font-family: DejaVu Serif, DejaVu Sans, serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: #fff;
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
            background: #fff;
            border: 1px solid #000;
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
            padding: 5px 6px;
        }

        .table-bordered thead tr {
            background-color: #ff9f43;
            color: #fff;
        }

        .table-bordered tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .meta-inline {
            margin-bottom: 12px;
            font-size: 10.5px;
            border-collapse: collapse;
        }

        .meta-inline td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    @php
        $formatAmount = function ($amount) use ($currencySymbol, $currencyPosition) {
            $formatted = number_format((float) $amount, 2);
            return $currencyPosition === 'right' ? $formatted . $currencySymbol : $currencySymbol . $formatted;
        };
    @endphp

    <div class="pdf-wrapper">
        <div class="card-body">
            <table style="width:100%; margin-bottom: 10px; border-collapse: collapse;">
                <tr>
                    <td style="width: 150px; vertical-align: middle;">
                        @if (!empty($settings?->logo) && file_exists(storage_path('app/public/' . $settings->logo)))
                            @php
                                $logoPath = storage_path('app/public/' . $settings->logo);
                                $logoData = base64_encode(file_get_contents($logoPath));
                                $logoMime = mime_content_type($logoPath);
                            @endphp
                            <img src="data:{{ $logoMime }};base64,{{ $logoData }}" alt="Company Logo"
                                style="height: 60px; width: auto;">
                        @endif
                    </td>
                    <td style="vertical-align: middle; text-align: right; padding-left: 15px;">
                        <h3 style="margin: 0; text-transform: uppercase; font-size: 16px; color: #000;">
                            {{ $settings?->name ?? '' }}
                        </h3>
                        <small style="text-transform: uppercase; font-size: 13px;">
                            {{ $settings?->address ?? '' }}<br>
                            Phone: {{ $settings?->phone ?? '' }} |
                            Email: <span style="text-transform: none;">{{ $settings?->email ?? '' }}</span>
                        </small>
                    </td>
                </tr>
            </table>

            <hr style="height: 2px; background-color: #d7cdcd; border: none; margin-top: 0; margin-bottom: 14px;">

            <div class="text-center" style="margin-bottom: 10px;">
                <h4 style="text-transform: uppercase; margin: 0;">TDS Report Details</h4>
            </div>

            <table class="meta-inline">
                <tbody>
                    <tr>
                        <td colspan="3">
                            <strong>Month:</strong> {{ $reportFilters['month'] ?? 'All Months' }} |
                            <strong>Year:</strong> {{ $reportFilters['year'] ?? 'All Years' }} |
                            <strong>Customer:</strong> {{ $reportFilters['customer'] ?? 'All Customers' }}
                        </td>
                        <td class="text-end">
                            <strong>Total Orders:</strong> {{ (int) ($summary['total_orders'] ?? 0) }} |
                            <strong>Total TDS:</strong> {{ $formatAmount($summary['total_tds_amount'] ?? 0) }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="table-bordered">
                <thead>
                    <tr>
                        <th style="width: 6%;">Sr No</th>
                        <th style="width: 17%;">Order No.</th>
                        <th style="width: 12%;">Order Date</th>
                        <th style="width: 23%;">Customer</th>
                        <th style="width: 13%;" class="text-end">Total Amount</th>
                        <th style="width: 8%;" class="text-center">TDS %</th>
                        <th style="width: 12%;" class="text-end">TDS Amount</th>
                        <th style="width: 9%;" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $index => $order)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $order['order_number'] ?? '-' }}</td>
                            <td>{{ $order['order_date'] ?? '-' }}</td>
                            <td>{{ $order['customer_name'] ?? '-' }}</td>
                            <td class="text-end">{{ $formatAmount($order['total_amount'] ?? 0) }}</td>
                            <td class="text-center">{{ $order['tds_percentage_display'] ?? '0.00' }}%</td>
                            <td class="text-end">{{ $formatAmount($order['tds_amount'] ?? 0) }}</td>
                            <td class="text-center">{{ ucfirst($order['payment_status'] ?? '-') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No TDS records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
