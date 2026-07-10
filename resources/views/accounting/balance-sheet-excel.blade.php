@php
    $companyName = $companyName ?? 'Company Name';
    $companyAddress = $companyAddress ?? 'Address';
    $companyPhone = $companyPhone ?? '';
    $sym = $sym ?? '₹';
    $pos = $pos ?? 'left';

    $money = function ($value) use ($sym, $pos) {
        $value = number_format((float) $value, 2, '.', ',');
        return $pos === 'left' ? $sym . $value : $value . $sym;
    };

    $leftRows = [];
    foreach ($liabilityGroups as $groupName => $accounts) {
        $leftRows[] = [$groupName, array_sum($accounts), true];
        foreach ($accounts as $accName => $accAmt) {
            $leftRows[] = ['   ' . $accName, $accAmt, false];
        }
    }

    $rightRows = [];
    foreach ($assetGroups as $groupName => $accounts) {
        $rightRows[] = [$groupName, array_sum($accounts), true];
        foreach ($accounts as $accName => $accAmt) {
            $rightRows[] = ['   ' . $accName, $accAmt, false];
        }
    }

    $max = max(count($leftRows), count($rightRows));
@endphp

<table>
    <tr>
        <td colspan="4"><strong>{{ $companyName }}</strong></td>
    </tr>
    <tr>
        <td colspan="4">{{ $companyAddress }}</td>
    </tr>
    <tr>
        <td colspan="4">Contact: {{ $companyPhone }}</td>
    </tr>
    <tr>
        <td colspan="4"><strong>Balance Sheet</strong></td>
    </tr>
    <tr>
        <td colspan="4">From {{ \Carbon\Carbon::parse($startDate)->format('d-M-y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-M-y') }}</td>
    </tr>
    <tr>
        <td>Liabilities</td>
        <td style="text-align:right;">as at {{ now()->format('d-M-y') }}</td>
        <td>Assets</td>
        <td style="text-align:right;">as at {{ now()->format('d-M-y') }}</td>
    </tr>

    @for ($i = 0; $i < $max; $i++)
        <tr>
            <td>
                @if(isset($leftRows[$i]))
                    <strong>{{ $leftRows[$i][2] ? $leftRows[$i][0] : $leftRows[$i][0] }}</strong>
                @endif
            </td>
            <td style="text-align:right;">
                @if(isset($leftRows[$i]))
                    <strong>{{ $money($leftRows[$i][1]) }}</strong>
                @endif
            </td>
            <td>
                @if(isset($rightRows[$i]))
                    <strong>{{ $rightRows[$i][2] ? $rightRows[$i][0] : $rightRows[$i][0] }}</strong>
                @endif
            </td>
            <td style="text-align:right;">
                @if(isset($rightRows[$i]))
                    <strong>{{ $money($rightRows[$i][1]) }}</strong>
                @endif
            </td>
        </tr>
    @endfor

    <tr>
        <td><strong>Total</strong></td>
        <td style="text-align:right;"><strong>{{ $money($totals['liabilities_equity'] ?? 0) }}</strong></td>
        <td><strong>Total</strong></td>
        <td style="text-align:right;"><strong>{{ $money($totals['assets'] ?? 0) }}</strong></td>
    </tr>
</table>
