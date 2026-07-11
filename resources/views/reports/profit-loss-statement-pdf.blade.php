<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit & Loss Statement</title>
    <style>
        @page {
            size: A4;
            margin: 1mm 1mm;
        }
        body { font-family: Arial, sans-serif; color: #333; font-size: 12px; margin: 0; padding: 0; }
        .pdf-wrapper { margin-top: 3mm; }
        .card-body {
            width: 95%;
            min-height: 95%;
            padding: 3mm;
            margin: auto;
            box-sizing: border-box;
            background: white;
            border: 1px solid black;
        }

        .bs-container { width: 100%; margin: auto; }
        .bs-header { margin-bottom: 20px; }
        .bs-header h3 { margin: 0 0 5px; font-weight: bold; font-size: 18px; }
        .bs-header p { margin: 2px 0; font-size: 12px; color: #444; }
        .bs-title { margin-top: 10px; font-weight: bold; font-size: 16px; text-decoration: underline; }
        .bs-period { font-size: 11px; color: #555; margin-top: 5px; }
        
        .bs-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .bs-table th, .bs-table td { padding: 6px 8px; vertical-align: top; }
        .bs-table th { border-top: 1px solid #000; border-bottom: 1px solid #000; text-align: left; font-weight: bold; }
        .col-divider { border-left: 1px solid #000; }
        
        .group-title { font-weight: bold; padding-top: 10px; padding-bottom: 3px; }
        .group-total { font-weight: bold; text-align: right; padding-top: 10px; padding-bottom: 3px; }
        
        .account-row td { padding: 2px 8px; font-style: italic; font-size: 11px; }
        .account-name { padding-left: 15px !important; }
        .account-amount { text-align: right; }
        
        .total-row td { border-top: 1px solid #000; border-bottom: 2px solid #000; font-weight: bold; padding-top: 8px; padding-bottom: 8px; }
        .text-right { text-align: right; }
        .table-half { width: 50%; }
        .float-end { float: right; }
        .fw-normal { font-weight: normal; }

        .inner-table { width: 100%; border-collapse: collapse; }
        .inner-table td { padding: 2px 8px; }
        .inner-table .detail { font-style: italic; color: #333; padding-left: 15px; font-size: 11px; }
        .inner-table .strong { font-weight: bold; padding-top: 10px; padding-bottom: 3px; }
        .amount { text-align: right; white-space: nowrap; }
    </style>
</head>
<body>
    @php
        $company = $data['company'] ?? [];
        $summary = $data['summary'] ?? [];
        
        $from = $data['period']['from'] ? date('d-M-Y', strtotime($data['period']['from'])) : 'All time';
        $to = $data['period']['to'] ? date('d-M-Y', strtotime($data['period']['to'])) : '';
        $periodText = $data['period']['from'] || $data['period']['to'] ? "{$from}" . ($to ? " to {$to}" : "") : 'All time';
        
        $leftTotal = $summary['opening_stock'] + $summary['purchase_total'] + $summary['indirect_expenses'] + ($summary['profit_loss'] > 0 ? $summary['profit_loss'] : 0);
        $rightTotal = $summary['sales_total'] + $summary['closing_stock'] + ($summary['profit_loss'] < 0 ? abs($summary['profit_loss']) : 0);
    @endphp

    <div class="pdf-wrapper">
        <div class="card-body">
            <div class="bs-container">
                <div class="bs-header">
                    <table style="width:100%; margin-bottom: 10px; border-collapse: collapse;">
                        <tr>
                            <!-- Logo -->
                            <td style="width: 150px; vertical-align: middle; text-align: left;">
                                @if (!empty($company['logo']) && file_exists(storage_path('app/public/' . $company['logo'])))
                                    @php
                                        $logoPath = storage_path('app/public/' . $company['logo']);
                                        $logoData = base64_encode(file_get_contents($logoPath));
                                        $logoMime = mime_content_type($logoPath);
                                    @endphp
                                    <img src="data:{{ $logoMime }};base64,{{ $logoData }}" alt="Company Logo" style="height: 60px; width: auto;">
                                @endif
                            </td>

                            <!-- Company Info -->
                            <td style="vertical-align: middle; text-align: right;">
                                <h3 style="margin: 0; font-weight: bold; font-size: 18px; text-transform: uppercase;">
                                    {{ $company['name'] ?? 'Company Name' }}
                                </h3>
                                <p style="margin: 2px 0; font-size: 12px; color: #444;">
                                    {{ $company['address'] ?? 'Address' }}<br>
                                    @if(!empty($company['phone']) || !empty($company['email']))
                                        Contact : {{ implode(' | ', array_filter([$company['phone'] ?? '', $company['email'] ?? ''])) }}
                                    @endif
                                </p>
                            </td>
                        </tr>
                    </table>

                    <hr style="height: 2px; background-color: #d7cdcd; border: none; margin-top: 0; margin-bottom: 20px;">
                    
                    <div class="bs-title" style="text-align: center;">Profit & Loss A/c</div>
                    <div class="bs-period" style="text-align: center;">
                        {{ $periodText }}
                    </div>
                </div>

                <table class="bs-table">
                    <thead>
                        <tr>
                            <th class="table-half">
                                Particulars
                                <span class="float-end fw-normal" style="float: right; font-size: 11px;">{{ $periodText }}</span>
                            </th>
                            <th class="table-half col-divider">
                                Particulars
                                <span class="float-end fw-normal" style="float: right; font-size: 11px;">{{ $periodText }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- Left Column -->
                            <td style="padding: 0;">
                                <table class="inner-table">
                                    @foreach($data['left_rows'] as $row)
                                        @if($row['type'] === 'empty')
                                            <tr><td colspan="3" style="height: 10px;"></td></tr>
                                            @continue
                                        @endif
                                        <tr class="{{ $row['type'] }}">
                                            <td class="{{ $row['type'] === 'detail' ? 'detail' : '' }}" style="width: 50%;">{{ $row['label'] }}</td>
                                            <td class="amount" style="width: 25%; {{ !empty($row['is_last']) ? 'border-bottom: 1px solid #333;' : '' }}">
                                                @if($row['inner_amount'] !== '')
                                                    {{ $row['inner_amount'] < 0 ? '(-)' . number_format(abs($row['inner_amount']), 2) : number_format($row['inner_amount'], 2) }}
                                                @endif
                                            </td>
                                            <td class="amount" style="width: 25%;">{{ $row['outer_amount'] !== '' ? number_format($row['outer_amount'], 2) : '' }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="strong">
                                        <td style="width: 50%;">Nett Profit</td>
                                        <td class="amount" style="width: 25%;"></td>
                                        <td class="amount" style="width: 25%;">{{ $summary['profit_loss'] >= 0 ? number_format($summary['profit_loss'], 2) : '0.00' }}</td>
                                    </tr>
                                </table>
                            </td>

                            <!-- Right Column -->
                            <td class="col-divider" style="padding: 0;">
                                <table class="inner-table">
                                    @foreach($data['right_rows'] as $row)
                                        @if($row['type'] === 'empty')
                                            <tr><td colspan="3" style="height: 10px;"></td></tr>
                                            @continue
                                        @endif
                                        <tr class="{{ $row['type'] }}">
                                            <td class="{{ $row['type'] === 'detail' ? 'detail' : '' }}" style="width: 50%;">{{ $row['label'] }}</td>
                                            <td class="amount" style="width: 25%; {{ !empty($row['is_last']) ? 'border-bottom: 1px solid #333;' : '' }}">
                                                @if($row['inner_amount'] !== '')
                                                    {{ $row['inner_amount'] < 0 ? '(-)' . number_format(abs($row['inner_amount']), 2) : number_format($row['inner_amount'], 2) }}
                                                @endif
                                            </td>
                                            <td class="amount" style="width: 25%;">{{ $row['outer_amount'] !== '' ? number_format($row['outer_amount'], 2) : '' }}</td>
                                        </tr>
                                    @endforeach
                                    @if($summary['profit_loss'] < 0)
                                        <tr class="strong">
                                            <td style="width: 50%;">Nett Loss</td>
                                            <td class="amount" style="width: 25%;"></td>
                                            <td class="amount" style="width: 25%;">{{ number_format(abs($summary['profit_loss']), 2) }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </td>
                        </tr>
                        
                        <!-- Grand Total Row -->
                        <tr class="total-row">
                            <td style="padding: 0;">
                                <table style="width: 100%;">
                                    <tr>
                                        <td>Total</td>
                                        <td class="text-right" style="width: 25%;">{{ number_format($leftTotal, 2) }}</td>
                                    </tr>
                                </table>
                            </td>
                            <td class="col-divider" style="padding: 0;">
                                <table style="width: 100%;">
                                    <tr>
                                        <td>Total</td>
                                        <td class="text-right" style="width: 25%;">{{ number_format($rightTotal, 2) }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
