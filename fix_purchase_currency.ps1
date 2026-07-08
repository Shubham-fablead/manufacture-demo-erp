$path = 'resources/views/purchase/purchaselist.blade.php'
$lines = Get-Content $path
$rupee = [char]0x20B9

for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -like '*formatCurrency(p.payment_amount)*') {
        $lines[$i] = '                    &#8377;${formatCurrency(p.payment_amount)}${p.payment_method ? ` (${p.payment_method})` : ''''}'
    }
    if ($lines[$i] -like '*formatCurrency(summary.order_total)*') {
        $lines[$i] = '            &#8377;${formatCurrency(summary.order_total)}'
    }
    if ($lines[$i] -like '*formatCurrency(summary.total_paid)*') {
        $lines[$i] = '            &#8377;${formatCurrency(summary.total_paid)}'
    }
    if ($lines[$i] -like '*formatCurrency(summary.total_return)*') {
        $lines[$i] = '            &#8377;${formatCurrency(summary.total_return)}'
    }
    if ($lines[$i] -like '*formatCurrency(summary.extra_paid)*') {
        $lines[$i] = '                &#8377;${formatCurrency(summary.extra_paid)}'
    }
    if ($lines[$i] -like '*formatCurrency(summary.remaining)*') {
        $lines[$i] = '                &#8377;${formatCurrency(summary.remaining)}'
    }
    if ($lines[$i] -like '*Summary section (same as sales)*') {
        $lines[$i] = '                        // Summary section (same as sales)'
    }
}

Set-Content $path $lines
