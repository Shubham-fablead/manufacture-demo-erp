@extends('layout.app')

@section('title', 'Balance Sheet')

@section('content')
    @php
        $sym = $currency_symbol ?? '₹';
        $pos = $currency_position ?? 'left';
        function money_fmt($v, $sym, $pos)
        {
            $v = number_format((float) $v, 2);
            return $pos === 'left' ? $sym . $v : $v . $sym;
        }
    @endphp

    <style>
        .bs-container {
            background: #fff;
            padding: 40px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            max-width: 900px;
            margin: auto;
            /* font-family: Arial, sans-serif; */
            color: #333;
        }

        .bs-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .bs-header h3 {
            margin: 0 0 5px;
            font-weight: bold;
            font-size: 20px;
        }

        .bs-header p {
            margin: 2px 0;
            font-size: 14px;
            color: #444;
        }

        .bs-title {
            margin-top: 15px;
            font-weight: bold;
            font-size: 18px;
            text-decoration: underline;
        }

        .bs-period {
            font-size: 13px;
            color: #555;
            margin-top: 5px;
        }

        .bs-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .bs-table th, .bs-table td {
            padding: 8px 10px;
            vertical-align: top;
        }

        .bs-table th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            text-align: left;
            font-weight: bold;
        }

        .col-divider {
            border-left: 1px solid #000;
        }
        
        .header-date {
            text-align: right;
            font-weight: normal;
            font-size: 13px;
        }

        .group-title {
            font-weight: bold;
            padding-top: 15px;
            padding-bottom: 5px;
        }

        .group-total {
            font-weight: bold;
            text-align: right;
            padding-top: 15px;
            padding-bottom: 5px;
        }

        .account-row td {
            padding: 2px 10px;
            font-style: italic;
            font-size: 13px;
        }
        
        .account-name {
            padding-left: 20px !important;
        }

        .account-amount {
            text-align: right;
        }
        
        .bottom-border {
            border-bottom: 1px solid #ccc;
        }
        
        .total-row td {
            border-top: 1px solid #000;
            border-bottom: 2px solid #000; /* double underline effect */
            font-weight: bold;
            padding-top: 10px;
            padding-bottom: 10px;
        }
        
        .text-right {
            text-align: right;
        }

        .table-half {
            width: 50%;
        }

        .bs-report-wrap {
            width: 100%;
            overflow-x: hidden;
        }

        @media (max-width: 576px) {
            .balance-sheet-page .page-header {
                display: block;
            }

            .balance-sheet-page .page-title h4 {
                font-size: 14px;
                margin-bottom: 8px;
            }

            .balance-sheet-page .page-btn,
            .balance-sheet-page .page-btn form {
                width: 100%;
            }

            .balance-sheet-page .page-btn form > div {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
                align-items: end !important;
                justify-content: stretch !important;
                gap: 6px !important;
            }

            .balance-sheet-page .page-btn form > div > .d-flex {
                display: block !important;
                min-width: 0;
            }

            .balance-sheet-page .page-btn label {
                display: block;
                margin-bottom: 3px !important;
                font-size: 10px !important;
            }

            .balance-sheet-page .page-btn .form-control-sm,
            .balance-sheet-page .page-btn .btn-sm {
                width: 100%;
                min-height: 30px;
                font-size: 10px;
                padding: 4px 6px;
            }

            .balance-sheet-page .card-body {
                padding: 10px;
            }

            .bs-header {
                margin-bottom: 18px;
            }

            .bs-header h3 {
                font-size: 14px;
                line-height: 1.25;
            }

            .bs-header p,
            .bs-period {
                font-size: 10px;
            }

            .bs-title {
                font-size: 13px;
            }

            .bs-table {
                min-width: 0;
                table-layout: fixed;
                font-size: 7px;
            }

            .bs-table th,
            .bs-table td {
                padding: 4px 3px;
            }

            .bs-table th.table-half {
                width: 50% !important;
            }

            .bs-table th .float-end {
                font-size: 6px !important;
            }

            .col-divider {
                border-left: 1px solid #000;
            }

            .account-row td {
                font-size: 6px;
                padding: 1px 3px;
            }

            .group-title,
            .group-total {
                font-size: 7px;
                padding-top: 6px;
                padding-bottom: 3px;
            }

            .account-name {
                padding-left: 8px !important;
            }

            .total-row td {
                padding-top: 5px;
                padding-bottom: 5px;
            }
        }

    </style>

    <div class="content balance-sheet-page">
        <div class="page-header">
            <div class="page-title">
                <h4>Balance Sheet</h4>
            </div>
            <div class="page-btn">
                <form method="GET" action="{{ url()->current() }}" class="d-inline-block">
                    <div class="d-flex align-items-center justify-content-end" style="gap: 10px;">
                        <div class="d-flex align-items-center" style="gap: 5px;">
                            <label for="start_date" class="form-label mb-0" style="font-size: 13px; font-weight: bold; white-space: nowrap;">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $startDate }}" onchange="this.form.submit()">
                        </div>
                        <div class="d-flex align-items-center" style="gap: 5px;">
                            <label for="end_date" class="form-label mb-0" style="font-size: 13px; font-weight: bold; white-space: nowrap;">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $endDate }}" onchange="this.form.submit()">
                        </div>
                            <button type="button" onclick="downloadReport('pdf')" class="btn btn-danger btn-sm" title="Download PDF">PDF</button>
                            <button type="button" onclick="downloadReport('excel')" class="btn btn-success btn-sm" title="Download Excel">Excel</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body">

        <!-- <div class="bs-container"> -->
        <div class="bs-report-wrap">
            <div class="bs-header">
                <h3>{{ $companyName ?? 'Company Name' }}</h3>
                <p>{{ $companyAddress ?? 'Address' }}</p>
                <p>Contact : {{ $companyPhone ?? '' }}</p>
                
                <div class="bs-title">Balance Sheet</div>
                <div class="bs-period">
                    From {{ \Carbon\Carbon::parse($startDate)->format('d-M-y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-M-y') }}
                </div>
            </div>

            <table class="bs-table">
                <thead>
                    <tr>
                        <th class="table-half">
                            L i a b i l i t i e s
                            <span class="float-end fw-normal" style="float: right; font-size: 13px;">as at {{ date('d-M-y') }}</span>
                        </th>
                        <th class="table-half col-divider">
                            A s s e t s
                            <span class="float-end fw-normal" style="float: right; font-size: 13px;">as at {{ date('d-M-y') }}</span>
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
                                        <td class="group-total">{{ money_fmt($groupTotal, $sym, $pos) }}</td>
                                    </tr>
                                    @foreach($accounts as $accName => $accAmt)
                                        <tr class="account-row">
                                            <td class="account-name">{{ $accName }}</td>
                                            <td class="account-amount">{{ money_fmt($accAmt, $sym, $pos) }}</td>
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
                                        <td class="group-total">{{ money_fmt($groupTotal, $sym, $pos) }}</td>
                                    </tr>
                                    @foreach($accounts as $accName => $accAmt)
                                        <tr class="account-row">
                                            <td class="account-name">{{ $accName }}</td>
                                            <td class="account-amount">{{ money_fmt($accAmt, $sym, $pos) }}</td>
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
                                    <td>T o t a l</td>
                                    <td class="text-right">{{ money_fmt($totals['liabilities_equity'] ?? 0, $sym, $pos) }}</td>
                                </tr>
                            </table>
                        </td>
                        <td class="col-divider" style="padding: 0;">
                            <table style="width: 100%;">
                                <tr>
                                    <td>T o t a l</td>
                                    <td class="text-right">{{ money_fmt($totals['assets'] ?? 0, $sym, $pos) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
     </div>
        </div>
    </div>
    
    @push('js')
    <script>
        function downloadReport(type) {
            var startDate = document.getElementById('start_date').value;
            var endDate = document.getElementById('end_date').value;
            
            var url = "{{ url()->current() }}?start_date=" + startDate + "&end_date=" + endDate + "&export=" + type;
            
            var title = type === 'pdf' ? 'Generating PDF...' : 'Generating Excel...';
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({ 
                    title: title, 
                    html: 'Please wait',
                    allowOutsideClick: false, 
                    didOpen: () => Swal.showLoading() 
                });
            }

            fetch(url)
                .then(function(response) {
                    if (!response.ok) throw new Error('Export failed');
                    
                    var filename = type === 'pdf' ? 'Balance_Sheet.pdf' : 'Balance_Sheet.xlsx';
                    var disposition = response.headers.get('content-disposition');
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        var matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) { 
                            filename = matches[1].replace(/['"]/g, '');
                        }
                    }
                    
                    return response.blob().then(blob => ({ blob, filename }));
                })
                .then(function({ blob, filename }) {
                    if (typeof Swal !== 'undefined') Swal.close();
                    
                    var blobUrl = window.URL.createObjectURL(blob);
                    var link = document.createElement('a');
                    link.href = blobUrl;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(blobUrl);
                })
                .catch(function(error) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'Failed to generate file', 'error');
                    } else {
                        alert('Failed to generate file');
                    }
                });
        }
    </script>
    @endpush
@endsection

    
