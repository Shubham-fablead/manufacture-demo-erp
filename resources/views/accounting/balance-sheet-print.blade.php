<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance Sheet</title>
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
    </style>
</head>
<body>
    @php
        function money_fmt_pdf($v, $sym, $pos)
        {
            return number_format((float) $v, 2);
        }
    @endphp

    <div class="pdf-wrapper">
        <div class="card-body">
            <div class="bs-container">
                <div class="bs-header">
                    <table style="width:100%; margin-bottom: 10px; border-collapse: collapse;">
                        <tr>
                            <!-- Logo -->
                            <td style="width: 150px; vertical-align: middle; text-align: left;">
                                @if (isset($settings->logo) && file_exists(storage_path('app/public/' . $settings->logo)))
                                    @php
                                        $logoPath = storage_path('app/public/' . $settings->logo);
                                        $logoData = base64_encode(file_get_contents($logoPath));
                                        $logoMime = mime_content_type($logoPath);
                                    @endphp
                                    <img src="data:{{ $logoMime }};base64,{{ $logoData }}" alt="Company Logo" style="height: 60px; width: auto;">
                                @endif
                            </td>

                            <!-- Company Info -->
                            <td style="vertical-align: middle; text-align: right;">
                                <h3 style="margin: 0; font-weight: bold; font-size: 18px; text-transform: uppercase;">
                                    {{ $companyName ?? 'Company Name' }}
                                </h3>
                                <p style="margin: 2px 0; font-size: 12px; color: #444;">
                                    {{ $companyAddress ?? 'Address' }}<br>
                                    Contact : {{ $companyPhone ?? '' }}
                                </p>
                            </td>
                        </tr>
                    </table>

            <hr style="height: 2px; background-color: #d7cdcd; border: none; margin-top: 0; margin-bottom: 20px;">

                    
                    <div class="bs-title" style="text-align: center;">Balance Sheet</div>
                    <div class="bs-period" style="text-align: center;">
                        From {{ \Carbon\Carbon::parse($startDate)->format('d-M-y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-M-y') }}
                    </div>
                </div>

        <table class="bs-table">
            <thead>
                <tr>
                    <th class="table-half">
                        Liabilities
                        <span class="float-end fw-normal" style="float: right; font-size: 11px;">as at {{ date('d-M-y') }}</span>
                    </th>
                    <th class="table-half col-divider">
                        Assets
                        <span class="float-end fw-normal" style="float: right; font-size: 11px;">as at {{ date('d-M-y') }}</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <!-- Liabilities Column -->
                    <td style="padding: 0;">
                        <table style="width: 100%; border-collapse: collapse;">
                            @foreach($liabilityGroups as $groupName => $accounts)
                                @php 
                                    $groupTotal = array_sum($accounts);
                                @endphp
                                <tr>
                                    <td class="group-title">{{ $groupName }}</td>
                                    <td class="group-total">{{ money_fmt_pdf($groupTotal, $sym, $pos) }}</td>
                                </tr>
                                @foreach($accounts as $accName => $accAmt)
                                    <tr class="account-row">
                                        <td class="account-name">{{ $accName }}</td>
                                        <td class="account-amount">{{ money_fmt_pdf($accAmt, $sym, $pos) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </table>
                    </td>

                    <!-- Assets Column -->
                    <td class="col-divider" style="padding: 0;">
                        <table style="width: 100%; border-collapse: collapse;">
                            @foreach($assetGroups as $groupName => $accounts)
                                @php 
                                    $groupTotal = array_sum($accounts);
                                @endphp
                                <tr>
                                    <td class="group-title">{{ $groupName }}</td>
                                    <td class="group-total">{{ money_fmt_pdf($groupTotal, $sym, $pos) }}</td>
                                </tr>
                                @foreach($accounts as $accName => $accAmt)
                                    <tr class="account-row">
                                        <td class="account-name">{{ $accName }}</td>
                                        <td class="account-amount">{{ money_fmt_pdf($accAmt, $sym, $pos) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </table>
                    </td>
                </tr>
                
                <!-- Grand Total Row -->
                <tr class="total-row">
                    <td style="padding: 0;">
                        <table style="width: 100%;">
                            <tr>
                                <td>Total</td>
                                <td class="text-right">{{ money_fmt_pdf($totals['liabilities_equity'] ?? 0, $sym, $pos) }}</td>
                            </tr>
                        </table>
                    </td>
                    <td class="col-divider" style="padding: 0;">
                        <table style="width: 100%;">
                            <tr>
                                <td>Total</td>
                                <td class="text-right">{{ money_fmt_pdf($totals['assets'] ?? 0, $sym, $pos) }}</td>
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
