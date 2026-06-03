<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Purchase PDF</title>
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
            /* min-height: auto; */
            min-height: 283mm;
            padding: 3mm;
            margin: auto;
            box-sizing: border-box;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            /* border: 1px solid black; */
            font-size: 12px;
            border: 1px solid black; /* thicker border */
            position: relative;
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
            margin-bottom: 15px;
        }

        .header-table td {
            padding: 1px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
            padding: 1px 2px;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            overflow-wrap: break-word;
        }

        .table-bordered thead tr {
            background-color: #e9ecf0ff;
            color: #333;
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

        .mb-0 {
            margin-bottom: 0;
        }

        h3,
        h4 {
            margin: 0 0 5px 0;
            color: #343a40;
        }

        .invoice-title {
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 2px solid #e5e7ebff;
            display: inline-block;
            /* padding-bottom: 5px; */
            /* margin-bottom: 20px; */
        }

        .logo-container {
            position: relative;
            min-height: 50px;
            margin-bottom: 5px;
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
            margin-top: 2px;
        }

        .signature-section {
            margin-top: 20px;
            text-align: right;
        }

        @php $totalthing =count($purchaseItems);
        $footerBottom =($totalthing > 5) ? 60 : 45;

        @endphp .footer-section {
            position: absolute;
            bottom: 20px;
            left: 0;
            width: 100%;
        }

        .footer-summary-table,
        .footer-notes-table,
        .footer-signature-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            color: #000;
            page-break-inside: avoid;
        }

        .footer-summary-table {
            margin-top: 8px;
            table-layout: fixed;
        }

        .bank-details-box,
        .qr-details-box,
        .totals-box {
            vertical-align: top;
            background-color: #eaedf0;
            padding: 2px 5px;
        }

        .bank-details-box {
            width: 40%;
            border: 1px solid #ff9f43;
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .qr-details-box {
            width: 20%;
            border: 1px solid #ff9f43;
            text-align: center;
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .totals-box {
            width: 40%;
            border: 1px solid #22b428;
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .footer-box-title {
            display: block;
            margin-bottom: 4px;
            text-transform: uppercase;
            font-size: 12px;
        }

        .bank-details-table,
        .totals-details-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            color: inherit;
            table-layout: fixed;
        }

        .bank-details-table td,
        .totals-details-table td {
            padding: 0 0 1px 0;
            vertical-align: top;
        }

        .bank-details-table td:first-child {
            width: 42%;
            font-weight: 600;
        }

        .bank-details-table td:last-child {
            text-align: left;
            word-break: break-word;
        }

        .totals-details-table td:last-child {
            text-align: right;
        }

        .footer-notes-table td,
        .footer-signature-table td {
            border: 2px solid #dee2e6;
            padding: 4px 10px;
            vertical-align: top;
        }

        .footer-terms {
            font-size: 10px;
            line-height: 1.2;
        }

        .footer-terms ol {
            margin: 3px 0 0 12px;
            padding: 0;
        }

        .footer-terms li {
            margin-bottom: 1px;
        }

        .signature-box {
            text-align: right;
        }
        .invoice-label-nowrap {
            white-space: nowrap;
            word-break: keep-all;
            width: 58px;
        }

    </style>
</head>

<body>
    @php
        $setting = $setting ?? new \App\Models\Setting([
            'name' => 'Fablead Developer & Technolab',
            'email' => 'info@gmail.com',
            'phone' => '1234567890',
            'address' => 'Adajan Surat',
            'logo' => 'admin/assets/img/logo-image.jpg',
            'currency_symbol' => '₹',
            'currency_position' => 'left',
        ]);
    @endphp
    @php
        $logoPath = null;
        $logoCandidates = [];

        if (!empty($setting->logo)) {
            $normalizedLogo = ltrim($setting->logo, '/\\');
            $logoCandidates = [
                public_path($normalizedLogo),
                storage_path('app/public/' . $normalizedLogo),
            ];

            foreach ($logoCandidates as $candidatePath) {
                if (file_exists($candidatePath)) {
                    $logoPath = $candidatePath;
                    break;
                }
            }
        }

        $purchaseDate = $invoice->purchase_date ?? $invoice->created_at ?? null;
    @endphp

    <div class="card-body">
        <table style="width:100%; margin-bottom: 5px; border-collapse: collapse;">
            <tr>
                <td style="width: 150px; vertical-align: top;">
                    @if ($logoPath)
                    @php
                        $logoData = base64_encode(file_get_contents($logoPath));
                        $logoMime = mime_content_type($logoPath) ?: 'image/png';
                    @endphp
                    <img src="data:{{ $logoMime }};base64,{{ $logoData }}" alt="Company Logo"
                        style="height: 60px; width: auto;">
                    @endif
                </td>
                <td style="vertical-align: middle; padding-left: 10px; text-align: right;">
                    <h3 style="margin: 0; text-transform: uppercase;">{{ $setting->name ?? '' }}</h3>
                    <small style="text-transform: uppercase;">
                        {{ $setting->address ?? '' }}<br>
                        Phone: {{ $setting->phone ?? '' }} |
                        Email: <span style="text-transform: none;">{{ $setting->email ?? '' }}</span>
                    </small>
                </td>
            </tr>
        </table>

        <hr style="height: 2px; background-color: #d7cdcd; border: none; margin-top: 0; margin-bottom: 10px;">


        <table style="width:100%; border-collapse: collapse; font-size: 10px; margin-bottom: 5px;">
            <tr>
                <td
                    style="width:33%; position: relative; padding: 5px 8px; vertical-align: top; background-color: #eaedf0; border-right: 1px solid #ff9f43;">
                    <strong style="text-transform: uppercase; display: block; margin-bottom: 0.5rem;">Vendor
                        Details:</strong>
                    <table style="width:100%; border-collapse: collapse; font-size: 10px; color: inherit;">
                        <tr>
                            <td style="padding: 0 0 5px 0;">Name :</td>
                            <td style="text-align: right; padding:  0 0 5px 0;">
                                {{ $vendor['name'] ?? 'walk-in-vendor' }}
                            </td>
                        </tr>
                        @if (!empty($vendor['company_name']))
                        <tr>
                            <td style="padding: 0 0 5px 0;">Company Name :</td>
                            <td style="text-align: right; padding: 0 0 5px 0;">{{ $vendor['company_name'] }}</td>
                        </tr>
                        @endif
                        @if (!empty($vendor['phone']))
                        <tr>
                            <td style="padding: 0 0 5px 0;">Phone :</td>
                            <td style="text-align: right; padding: 0 0 5px 0;">{{ $vendor['phone'] }}</td>
                        </tr>
                        @endif
                        @if (!empty($vendor['email']))
                        <tr>
                            <td style="padding: 0 0 5px 0;">Email :</td>
                            <td style="text-align: right; padding: 0 0 5px 0;">{{ $vendor['email'] }}</td>
                        </tr>
                        @endif
                        @if (!empty($vendor['address']))
                        <tr>
                         <td class="invoice-label-nowrap" style="padding: 0 0 4px 0; vertical-align: top;">Address :</td>
                            <td style="text-align: right; padding: 0 0 5px 0;">{{ $vendor['address'] }}</td>
                        </tr>
                        @endif
                         @if (!empty($vendor['gst_number']))
                        <tr>
                            <td style="padding: 0 0 5px 0;">Gst No :</td>
                            <td style="text-align: right; padding: 0 0 5px 0;">{{ $vendor['gst_number'] }}</td>
                        </tr>
                        @endif
                        @if (!empty($vendor['pan_number']))
                        <tr>
                            <td style="padding: 0 0 5px 0;">Pan No :</td>
                            <td style="text-align: right; padding: 0 0 5px 0;">{{ $vendor['pan_number'] }}</td>
                        </tr>
                        @endif
                    </table>

                        <div style="position: absolute; right: 0; top: 2%; height: 8%; border-right: 1px solid #ff9f43;">
                    </div>
                </td>

                <td
                    style="width:33%; position: relative; padding: 5px 8px; vertical-align: top; background-color: #eaedf0; border-right: 1px solid #ff9f43;">
                    <strong style="text-transform: uppercase; display: block; margin-bottom: 0.5rem;">Company Details:</strong>
                    <table style="width:100%; border-collapse: collapse; font-size: 10px; color: inherit;">
                        @if (!empty($setting->name))
                        <tr>
                            <td style="padding: 0 0 5px 0;">Name :</td>
                            <td style="text-align: right; padding: 0 0 5px 0;">{{ $setting->name }}</td>
                        </tr>
                        @endif

                        @if (!empty($setting->email))
                        <tr>
                            <td style="padding: 0 0 5px 0;">Email :</td>
                            <td style="text-align: right; padding: 0 0 5px 0;">{{ $setting->email }}</td>
                        </tr>
                        @endif

                        @if (!empty($setting->phone))
                        <tr>
                            <td style="padding: 0 0 5px 0;">Phone :</td>
                            <td style="text-align: right; padding: 0 0 5px 0;">{{ $setting->phone }}</td>
                        </tr>
                        @endif

                        @if (!empty($setting->address))
                        <tr>
                            <td class="invoice-label-nowrap" style="padding: 0 0 5px 0; vertical-align: top; white-space: nowrap;">
                                Address :
                            </td>
                            <td style="text-align: right; padding: 0 0 5px 0;">
                                <span style="display: inline-block; max-width: 180px; text-align: right;">
                                    {{ $setting->address }}
                                </span>
                            </td>
                        </tr>
                        @endif

                        {{-- @if (!empty($setting->gst_num))
                            <tr>
                                <td style="padding: 0 0 8px 0;">GST :</td>
                                <td style="text-align: right; padding: 0 0 8px 0;">{{ $setting->gst_num }}
                </td>
            </tr>
            @endif --}}
        </table>

 <div style="position: absolute; right: 0; top: 2%; height: 8%; border-right: 1px solid #ff9f43;">
        </div>
        </td>

        <td
            style="width:34%; border: 0px solid #dee2e6; padding: 5px 8px; vertical-align: top; background-color: #eaedf0;">
            <strong style="text-transform: uppercase; display: block; margin-bottom: 0.5rem;">Purchase
                Details:</strong>
            <table style="width:100%; border-collapse: collapse; font-size: 10px; color: inherit;">
                <tr>
                    <td style="padding: 0 0 5px 0;">Purchase Bill No :</td>
                    <td style="text-align: right; padding: 0 0 5px 0;">{{ $invoice->bill_no ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 0 0 5px 0;">Purchase Date :</td>
                    <td style="text-align: right; padding: 0 0 5px 0;">
                        {{ $purchaseDate ? \Carbon\Carbon::parse($purchaseDate)->format('d/m/y') : '-' }}
                    </td>
                </tr>

            </table>
        </td>
        </tr>
        </table>

        <div class="text-center">
            <h4 style="text-transform: uppercase;">Purchase</h4>
        </div>

        <!-- Updated table structure to match sales invoice -->
        <table class="table-bordered"
            style="width: 100%; border-collapse: collapse; font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 5px 0 5px 0;">
            @php
            $hasGst = false;

            foreach ($purchaseItems as $item) {
            if (($item->product_gst_total ?? 0) > 0) {
            $hasGst = true;
            break;
            }
            }
            @endphp
            @php
            // Initialize totals
            $totalExclGstSum = 0;
            $totalTaxSum = 0;
            $totalSubtotal = 0;
            $totalItemDiscount = 0;
            foreach ($purchaseItems as $item) {
            $totalItemDiscount += $item->discount_amount ?? 0;
            }
            $showDiscountColumn = $totalItemDiscount > 0;
            @endphp
            <thead>
                <tr style="background-color:#ff9f43; color:#fff;font-size:10px;">
                    <th style="width:10%; padding: 3px; text-align:center;">Sr No</th>
                    <th style="padding: 3px;width:30%; text-align:left;">Product Name</th>
                    <th style="width:8%; padding: 3px; text-align:center;">QTY</th>
                    @if($showDiscountColumn)
                    <th style="width:15%; padding: 3px; text-align:center;">Discount Amount</th>
                    @endif
                    <th style="padding: 3px; text-align:center;">Price</th>
                    @if($hasGst)
                    <th style="width:25%; padding: 3px; text-align:center;">Product Taxes</th>
                    <th style="width:15%; text-align:right;">Tax Amount</th>
                    @endif
                    <th style="width:25%; text-align:right;">Total (Excl.GST)</th>
                </tr>
            </thead>
            <tbody>

                @forelse($purchaseItems as $item)
                @php
                // Calculate values for each item
                $productGstTotal = $item->product_gst_total ?? 0;
                $totalExclGst = $item->price * $item->quantity;
                $itemTotal = $totalExclGst + $productGstTotal;


                // Accumulate totals
                $totalExclGstSum += $totalExclGst;
                $totalTaxSum += $productGstTotal;
                $totalSubtotal += $itemTotal;
                @endphp
                <tr style="font-size:10px;">
                    <td style="text-align:center; padding:3px;">{{ $loop->iteration }}</td>

                    <td style="padding:3px; text-align:left;">
                        @php
                        $images = json_decode($item->product->images ?? '[]');
                        $firstImage = !empty($images) ? $images[0] : null;
                        $base64 = null;

                        if ($firstImage) {
                        $imagePath = storage_path('app/public/' . $firstImage);
                        } else {
                        $imagePath = public_path('/admin/assets/img/product/noimage.png');
                        }

                        if (file_exists($imagePath)) {
                        $type = pathinfo($imagePath, PATHINFO_EXTENSION);
                        $data = file_get_contents($imagePath);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        }
                        @endphp

                        <table style="border-collapse: collapse; width:100%; table-layout: fixed;">
                            <colgroup>
                                <col style="width: 30px;">
                                <col style="width: 70px;">
                            </colgroup>
                            <tr>
                                <td style="padding: 0; width: 30%; vertical-align: middle;border: none;">
                                    <img src="{{ $base64 }}" alt="img"
                                        style="width: 30px; height: 30px; object-fit: cover; border-radius: 4px; display: block;">
                                </td>
                                <td style="width: 50px;
                                border: none;
                                    padding: 0 0 0 2px;
                                    vertical-align: middle;
                                    word-wrap: break-word;
                                    word-break: break-word;
                                    white-space: normal;
                                    overflow-wrap: break-word;
                                    width: 100%;
                                ">
                                    {{ ucfirst($item->product->name ?? 'Product') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="padding:3px; text-align:center;">
                        {{ $item->quantity }}
                    </td>
                    @if($showDiscountColumn)
                    <td style="padding:3px; text-align:center;">
                        {{ formatCurrency($item->discount_amount ?? 0, $setting) }}
                        @if(($item->discount_percent ?? 0) > 0)
                        <br>
                        <small>({{ number_format($item->discount_percent ?? 0, 2) }}%)</small>
                        @endif
                    </td>
                    @endif
                    <td style="padding:3px; text-align:center;">
                        {{ formatCurrency($item->price, $setting) }}
                    </td>
                    @if($hasGst)
                    <td style="padding:3px; text-align:center; font-size:10px;">
                        @php
                        // Try different ways to get product tax details
                        $productGstDetails = [];

                        // Method 1: Check if it's stored as JSON string
                        if (isset($item->product_gst_details) && is_string($item->product_gst_details)) {
                        $productGstDetails = json_decode($item->product_gst_details, true);
                        }
                        // Method 2: Check if it's already an array
                        elseif (isset($item->product_gst_details) && is_array($item->product_gst_details)) {
                        $productGstDetails = $item->product_gst_details;
                        }
                        // Method 3: Check if taxes are stored in a different field
                        elseif (isset($item->taxes) && !empty($item->taxes)) {
                        $productGstDetails = $item->taxes;
                        }
                        // Method 4: Check for gst_details
                        elseif (isset($item->gst_details) && !empty($item->gst_details)) {
                        $productGstDetails = $item->gst_details;
                        }
                        // Method 5: If product has tax information
                        elseif (isset($item->product) && isset($item->product->taxes)) {
                        $productGstDetails = $item->product->taxes;
                        }

                        // If still empty and we have tax amount, create a default display
                        if (empty($productGstDetails) && $productGstTotal > 0) {
                        // Based on your screenshot, you might have CGST and SGST
                        // You should adjust this based on your actual tax structure
                        $productGstDetails = [
                        ['tax_name' => 'CGST', 'tax_rate' => 18.00, 'tax_amount' => $productGstTotal/2],
                        ['tax_name' => 'SGST', 'tax_rate' => 18.00, 'tax_amount' => $productGstTotal/2]
                        ];
                        }
                        @endphp

                        @if(!empty($productGstDetails) && is_array($productGstDetails))
                        @foreach($productGstDetails as $tax)
                        @php
                        // Ensure we have the correct keys
                        $taxName = $tax['tax_name'] ?? $tax['name'] ?? 'Tax';
                        $taxRate = $tax['tax_rate'] ?? $tax['rate'] ?? 0;
                        $taxAmount = $tax['tax_amount'] ?? $tax['amount'] ?? 0;
                        @endphp
                        <div>
                            {{ $taxName }} ({{ number_format($taxRate, 2) }}%)
                            : {{ formatCurrency($taxAmount, $setting) }}
                        </div>
                        @endforeach
                        @else
                        <!-- Show N/A only if there's truly no tax -->
                        @if($productGstTotal == 0)
                        N/A
                        @else
                        <!-- If there's tax amount but no details, show generic -->
                        <div>Tax Included: {{ $setting->currency_symbol }}{{ number_format($productGstTotal, 2) }}</div>
                        @endif
                        @endif
                    </td>
                    <td style="padding:3px; text-align:right; font-weight:bold;">
                        {{ formatCurrency($productGstTotal, $setting) }}
                    </td>
                    @endif
                    <td style="padding:3px; text-align:right;">
                        {{ formatCurrency($totalExclGst, $setting) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:4px;">No product data available</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @php
        // Calculate final totals from the items
        $subtotalExclGst = $totalExclGstSum; // Sum of all product subtotals (price * quantity)
        $totalGstAmount = $totalTaxSum; // Sum of all product GST
        $totalDiscount = $totalItemDiscount; // Sum of all item discounts

        // Calculate after discount amount including GST
        $afterDiscount = ($subtotalExclGst + $totalGstAmount) - $totalDiscount;

        // Calculate grand total (Price after discount + Shipping)
        $grandTotal = $afterDiscount;

        // Add shipping if any
        $shippingAmount = $invoice->shipping ?? 0;
        $grandTotal += $shippingAmount;

        // Get return amount from controller data
        $returnAmount = $totalReturnAmount ?? 0;

        // Calculate pending amount properly
        // Pending = (Subtotal + GST - Discount + Shipping) - (Paid Amount + Return Amount)
        $pendingAmount = $grandTotal - ($paidAmount + $returnAmount);

        // Extra paid when payment exceeds grand total after returns
        $extraPaid = ($paidAmount + $returnAmount > $grandTotal) ? ($paidAmount + $returnAmount - $grandTotal) : 0;

        // If pending amount is negative, it means extra paid
        if ($pendingAmount < 0) {
            $extraPaid=abs($pendingAmount);
            $pendingAmount=0;
            }

            // Format currency based on settings
            function formatCurrency($amount, $setting) {
            $num=(float)$amount;
            $explode=explode(".", number_format($num, 2, '.' , '' ));
            $whole=$explode[0];
            $decimal=$explode[1];

            $lastThree=substr($whole, -3);
            $restUnits=substr($whole, 0, -3);
            if ($restUnits !='' ) {
            $restUnits=preg_replace("/\B(?=(\d{2})+(?!\d))/", "," , $restUnits);
            $whole=$restUnits . "," . $lastThree;
            }
            $formatted=$whole . "." . $decimal;
            if ($setting->currency_position === 'right') {
            return $formatted . $setting->currency_symbol;
            } else {
            return $setting->currency_symbol . $formatted;
            }
            }

            $subtotalFormatted = formatCurrency($subtotalExclGst, $setting);
            $totalGstFormatted = formatCurrency($totalGstAmount, $setting);
            $discountFormatted = formatCurrency($totalDiscount, $setting);
            $afterDiscountFormatted = formatCurrency($afterDiscount, $setting);
            $shippingFormatted = formatCurrency($shippingAmount, $setting);
            $grandTotalFormatted = formatCurrency(round($grandTotal), $setting);
            $paidAmountFormatted = formatCurrency(round($paidAmount ?? 0), $setting);
            $returnAmountFormatted = formatCurrency(round($returnAmount), $setting);
            $pendingAmountFormatted = formatCurrency(round($pendingAmount), $setting);
            $extraPaidAmountFormatted = formatCurrency(round($extraPaid), $setting);

            // Convert number to words
            function convertNumberToWords($number)
            {
            $hyphen = '-';
            $conjunction = ' and ';
            $negative = 'negative ';
            $dictionary = [
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'forty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety',
            ];

            if (!is_numeric($number)) {
            return false;
            }

            if ($number < 0) {
                return $negative . convertNumberToWords(abs($number));
                }

                $string='' ;
                if ($number < 21) {
                $string=$dictionary[$number];
                } elseif ($number < 100) {
                $tens=((int) ($number / 10)) * 10;
                $units=$number % 10;
                $string=$dictionary[$tens];
                if ($units) {
                $string .=$hyphen . $dictionary[$units];
                }
                } elseif ($number < 1000) {
                $hundreds=(int) ($number / 100);
                $remainder=$number % 100;
                $string=$dictionary[$hundreds] . ' hundred' ;
                if ($remainder) {
                $string .=$conjunction . convertNumberToWords($remainder);
                }
                } else {
                $baseUnits=[10000000=> 'crore', 100000 => 'lakh', 1000 => 'thousand'];
                foreach ($baseUnits as $divisor => $label) {
                if ($number >= $divisor) {
                $units = (int) ($number / $divisor);
                $remainder = $number % $divisor;
                $string = convertNumberToWords($units) . ' ' . $label;
                if ($remainder) {
                $string .= $conjunction . convertNumberToWords($remainder);
                }
                break;
                }
                }
                }
                return $string;
                }

                $numericValue = str_replace(['₹', ',', $setting->currency_symbol], '', $grandTotalFormatted);
                $amountInWords = ucwords(convertNumberToWords((int)$numericValue)) . ' Rupees';
                @endphp

                <div class="footer-section">
                    <table class="footer-summary-table">
                        <tr>
                            @php
                                // Use isMainBranch flag from controller if available
                                if (isset($isMainBranch)) {
                                    $showBankDetails = !$isMainBranch;
                                } else {
                                    // Fallback logic if isMainBranch is not set
                                    $showBankDetails = true;

                                    // If no selectedSubAdminId is passed or it's empty/null, it's main branch
                                    if (!isset($selectedSubAdminId) || empty($selectedSubAdminId) || $selectedSubAdminId == '') {
                                        $showBankDetails = false;
                                    }

                                    // For sub-admin and staff roles, always show bank details (they are sub-branches)
                                    if (isset($userRole) && in_array($userRole, ['sub-admin', 'staff'])) {
                                        $showBankDetails = true;
                                    }

                                    // For admin with selectedSubAdminId, it's a sub-branch
                                    if (isset($userRole) && $userRole === 'admin' && !empty($selectedSubAdminId) && $selectedSubAdminId != 1) {
                                        $showBankDetails = true;
                                    }
                                }
                            @endphp



                            <td class="qr-details-box" @if(!$showBankDetails) style="width:50%;" @else style="width: 20%;" @endif>
                                <table style="width: 100%; font-size: 12px; color: inherit;">
                                    <tr>
                                <td style="">
                                <strong>Remarks :</strong><br>
                                <span style="white-space: pre-line;">{{ $invoice->remark ?? 'N/A' }}</span>
                                    </td>
                                    </tr>
                                </table>
                            </td>

                            <td class="totals-box" @if(!$showBankDetails) style="width: 50%;" @else style="width: 40%;" @endif>
                                <strong class="footer-box-title">Totals:</strong>
                                <table class="totals-details-table">
                                    <tr>
                                        <td>Total Product Amount :</td>
                                        <td>
                                            {{ $subtotalFormatted }}
                                        </td>
                                    </tr>
                                     @if($hasGst)
                                    <tr>
                                        <td style="font-weight:bold;">
                                            Total GST :
                                        </td>
                                        <td style="font-weight:bold;">
                                            {{ $totalGstFormatted }}
                                        </td>
                                    </tr>
                                    @endif
                                    @if($showDiscountColumn)
                                    <tr>
                                        <td>Discount Amount:</td>
                                        <td>
                                            {{ $discountFormatted }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>SubTotal :</td>
                                        <td>
                                            {{ $afterDiscountFormatted }}
                                        </td>
                                    </tr>
                                    @endif

                                    @if($shippingAmount > 0)
                                    <tr>
                                        <td>Shipping :</td>
                                        <td>
                                            {{ $shippingFormatted }}
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td style="font-weight:bold;">Grand Total :</td>
                                        <td style="font-weight:bold;">
                                            {{ $grandTotalFormatted }}
                                        </td>
                                    </tr>
                                    @if($returnAmount > 0)
                                    <tr>
                                        <td style="color:#FF6B6B; font-weight:bold;">
                                            Return Amount :
                                        </td>
                                        <td style="color:#FF6B6B; font-weight:bold;">
                                            {{ $returnAmountFormatted }}
                                        </td>
                                    </tr>
                                    @endif

                                    <tr>
                                        <td style="color:#2E7D32; font-weight:bold;">
                                            Paid Amount :
                                        </td>
                                        <td style="color:#2E7D32; font-weight:bold;">
                                            {{ $paidAmountFormatted }}
                                        </td>
                                    </tr>
                                    @if($extraPaid > 0)
                                    <tr>
                                        <td style="color:#C62828; font-weight:bold;">
                                            Extra Paid Amount :
                                        </td>
                                        <td style="color:#C62828; font-weight:bold;">
                                            {{ $extraPaidAmountFormatted }}
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td style="color:#C62828; font-weight:bold;">
                                            Pending Amount :
                                        </td>
                                        <td style="color:#C62828; font-weight:bold;">
                                            {{ $pendingAmountFormatted }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <table class="footer-notes-table">
                        <tr>
                            <td style="width: 60%; font-size: 11px;">
                                <strong>{{ $amountInWords }} Only</strong>
                            </td>
                            <td style="width: 40%; text-align: right; background-color:#ff9f43; color:#fff;">
                                <strong>Grand Total : {{ $grandTotalFormatted }}</strong>
                            </td>
                        </tr>

                    </table>

                    <table class="footer-signature-table">
                        <tr>
                            <td class="signature-box" style="width: 100%;">
                                <p style="margin: 0;">For, {{ $setting->name ?? 'Auto Care' }}</p>
                                <br><br><br>
                                <p style="margin: 0;">(Authorized Signatory)</p>
                            </td>
                        </tr>
                    </table>
                </div>
    </div>

</body>

</html>
