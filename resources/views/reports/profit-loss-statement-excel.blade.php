<table>
    @php
        $company = $data['company'] ?? [];
        $summary = $data['summary'] ?? [];
        
        $from = $data['period']['from'] ? date('d-M-y', strtotime($data['period']['from'])) : 'All time';
        $to = $data['period']['to'] ? date('d-M-y', strtotime($data['period']['to'])) : '';
        $periodText = $data['period']['from'] || $data['period']['to'] ? "From {$from}" . ($to ? " to {$to}" : "") : 'All time';
        $asAtText = $data['period']['to'] ? "as at " . date('d-M-y', strtotime($data['period']['to'])) : ($data['period']['from'] ? "as at " . date('d-M-y', strtotime($data['period']['from'])) : 'as at ' . date('d-M-y'));
        
        $leftTotal = $summary['opening_stock'] + $summary['purchase_total'] + $summary['indirect_expenses'] + ($summary['profit_loss'] > 0 ? $summary['profit_loss'] : 0);
        $rightTotal = $summary['sales_total'] + $summary['closing_stock'] + ($summary['profit_loss'] < 0 ? abs($summary['profit_loss']) : 0);

        $leftRows = $data['left_rows'];
        $rightRows = $data['right_rows'];
        
        // Add net profit/loss row to data
        $leftRows[] = [
            'label' => 'Nett Profit',
            'inner_amount' => '',
            'outer_amount' => $summary['profit_loss'] >= 0 ? $summary['profit_loss'] : 0,
            'type' => 'strong'
        ];
        
        if ($summary['profit_loss'] < 0) {
            $rightRows[] = [
                'label' => 'Nett Loss',
                'inner_amount' => '',
                'outer_amount' => abs($summary['profit_loss']),
                'type' => 'strong'
            ];
        }

        $maxRows = max(count($leftRows), count($rightRows));
    @endphp

    <thead>
        <tr>
            <th colspan="6" style="text-align: center; font-weight: bold; font-size: 16px;">{{ $company['name'] ?? 'Company Name' }}</th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: center;">{{ $company['address'] ?? 'Address' }}</th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: center;">Contact: {{ implode(' | ', array_filter([$company['phone'] ?? '', $company['email'] ?? ''])) }}</th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: center; font-weight: bold; font-size: 14px; text-decoration: underline;">Profit &amp; Loss A/c</th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: center;">{{ $periodText }}</th>
        </tr>
        <tr>
            <th colspan="6"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; border-bottom: 1px solid #000; border-top: 1px solid #000;">Particulars</th>
            <th style="font-weight: bold; border-bottom: 1px solid #000; border-top: 1px solid #000;"></th>
            <th style="font-weight: bold; border-bottom: 1px solid #000; border-top: 1px solid #000; text-align: right;">{{ $asAtText }}</th>
            <th style="font-weight: bold; border-bottom: 1px solid #000; border-top: 1px solid #000;">Particulars</th>
            <th style="font-weight: bold; border-bottom: 1px solid #000; border-top: 1px solid #000;"></th>
            <th style="font-weight: bold; border-bottom: 1px solid #000; border-top: 1px solid #000; text-align: right;">{{ $asAtText }}</th>
        </tr>
    </thead>
    <tbody>
        @for($i = 0; $i < $maxRows; $i++)
            <tr>
                @if(isset($leftRows[$i]))
                    @if($leftRows[$i]['type'] === 'empty')
                        <td></td><td></td><td></td>
                    @else
                        <td style="{{ $leftRows[$i]['type'] === 'strong' ? 'font-weight: bold;' : ($leftRows[$i]['type'] === 'detail' ? 'font-style: italic;' : '') }}">
                            {{ ($leftRows[$i]['type'] === 'detail' ? '    ' : '') . $leftRows[$i]['label'] }}
                        </td>
                        <td style="{{ $leftRows[$i]['type'] === 'strong' ? 'font-weight: bold;' : '' }} text-align: right; {{ !empty($leftRows[$i]['is_last']) ? 'border-bottom: 1px solid #000;' : '' }}" data-format="#,##0.00">
                            {{ $leftRows[$i]['inner_amount'] !== '' ? $leftRows[$i]['inner_amount'] : '' }}
                        </td>
                        <td style="{{ $leftRows[$i]['type'] === 'strong' ? 'font-weight: bold;' : '' }} text-align: right;" data-format="#,##0.00">
                            {{ $leftRows[$i]['outer_amount'] !== '' ? $leftRows[$i]['outer_amount'] : '' }}
                        </td>
                    @endif
                @else
                    <td></td><td></td><td></td>
                @endif

                @if(isset($rightRows[$i]))
                    @if($rightRows[$i]['type'] === 'empty')
                        <td></td><td></td><td></td>
                    @else
                        <td style="{{ $rightRows[$i]['type'] === 'strong' ? 'font-weight: bold;' : ($rightRows[$i]['type'] === 'detail' ? 'font-style: italic;' : '') }}">
                            {{ ($rightRows[$i]['type'] === 'detail' ? '    ' : '') . $rightRows[$i]['label'] }}
                        </td>
                        <td style="{{ $rightRows[$i]['type'] === 'strong' ? 'font-weight: bold;' : '' }} text-align: right; {{ !empty($rightRows[$i]['is_last']) ? 'border-bottom: 1px solid #000;' : '' }}" data-format="#,##0.00">
                            {{ $rightRows[$i]['inner_amount'] !== '' ? $rightRows[$i]['inner_amount'] : '' }}
                        </td>
                        <td style="{{ $rightRows[$i]['type'] === 'strong' ? 'font-weight: bold;' : '' }} text-align: right;" data-format="#,##0.00">
                            {{ $rightRows[$i]['outer_amount'] !== '' ? $rightRows[$i]['outer_amount'] : '' }}
                        </td>
                    @endif
                @else
                    <td></td><td></td><td></td>
                @endif
            </tr>
        @endfor
        <tr>
            <td style="font-weight: bold; border-top: 1px solid #000; border-bottom: 2px solid #000;">Total</td>
            <td style="font-weight: bold; border-top: 1px solid #000; border-bottom: 2px solid #000;"></td>
            <td style="font-weight: bold; border-top: 1px solid #000; border-bottom: 2px solid #000; text-align: right;" data-format="#,##0.00">{{ $leftTotal }}</td>
            
            <td style="font-weight: bold; border-top: 1px solid #000; border-bottom: 2px solid #000;">Total</td>
            <td style="font-weight: bold; border-top: 1px solid #000; border-bottom: 2px solid #000;"></td>
            <td style="font-weight: bold; border-top: 1px solid #000; border-bottom: 2px solid #000; text-align: right;" data-format="#,##0.00">{{ $rightTotal }}</td>
        </tr>
    </tbody>
</table>
