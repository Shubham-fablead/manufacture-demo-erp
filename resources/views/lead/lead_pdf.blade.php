<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Leads PDF</title>
    <style>
        @page {
            size: A4;
            margin: 1mm 1mm;
        }

        body {
            font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
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

        .header-table {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            margin-bottom: 30px;
        }

        .header-table td {
            padding: 10px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
            padding: 4px 8px;
        }

        .table-bordered thead tr {
            background-color: #e9ecf0ff;
            color: #333;
        }

        .table-bordered tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .text-center {
            text-align: center;
        }

        h3,
        h4 {
            margin: 0 0 10px 0;
            color: #343a40;
        }


        .logo-container {
            position: relative;
            min-height: 50px;
            margin-bottom: 10px;
        }

        .logo-container .qr-code {
            height: 60px;
            position: absolute;
        }

        .logo-container .company-logo {
            height: 50px;
            position: absolute;
            top: 0;
            left: 0;
        }

        .logo-container .company-details {
            text-align: center;
        }

        .signature-section img {
            height: 50px;
            margin-top: 5px;
        }

        .signature-section {
            margin-top: 50px;
            text-align: right;
        }

        .footer-section {
            width: 95%;
            position: fixed;
            bottom: 45px;
            left: 20px;
            right: 0;
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
                            <img src="data:{{ $logoMime }};base64,{{ $logoData }}" alt="Company Logo"
                                style="height: 60px; width: auto;">
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

            <hr style="height: 2px; background-color: #d7cdcd; border: none; margin-top: 0; margin-bottom: 20px;">

            <div class="text-center">
                <h4 class="report-title" style="text-transform: uppercase;">Lead Details</h4>
            </div>

            <table class="table-bordered">
                <thead>
                    <tr>
                        <th style="width:5%; background-color:#ff9f43; color:#fff;">Sr No</th>
                        <th style="width:18%; background-color:#ff9f43; color:#fff;">Lead Name</th>
                        <th style="width:15%; background-color:#ff9f43; color:#fff;">Lead Source</th>
                        <th style="width:15%; background-color:#ff9f43; color:#fff;">Assigned To</th>
                        <th style="width:15%; background-color:#ff9f43; color:#fff;">Created By</th>
                        <th style="width:12%; background-color:#ff9f43; color:#fff;">Created At</th>
                        <th style="width:10%; background-color:#ff9f43; color:#fff;">Status</th>
                        <th style="width:10%; background-color:#ff9f43; color:#fff;">Company</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leads as $index => $lead)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ !empty($lead->name) ? ucfirst(strtolower($lead->name)) : 'N/A' }}</td>
                            <td class="text-center">{{ !empty($lead->lead_source) ? ucfirst(strtolower($lead->lead_source)) : 'N/A' }}</td>
                            <td class="text-center">{{ $lead->assignedUser->name ?? 'N/A' }}</td>
                            <td class="text-center">{{ $lead->creator->name ?? 'N/A' }}</td>
                            <td class="text-center">{{ optional($lead->created_at)->format('d-m-Y') }}</td>
                            <td class="text-center">{{ $lead->lead_status ?? 'N/A' }}</td>
                            <td class="text-center">{{ $lead->company_name ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No leads found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
