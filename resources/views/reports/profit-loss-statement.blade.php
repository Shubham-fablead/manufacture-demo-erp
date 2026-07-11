@extends('layout.app')

@section('title', 'Profit & Loss Statement')

@section('content')
<style>
    /* ── Layout ─────────────────────────────────────── */
    .pl-shell {
        background: linear-gradient(180deg, #f7f7f8 0%, #ececec 100%);
        min-height: 100%;
    }

    .pl-paper {
        max-width: 980px;
        margin: 0 auto;
        background: #fff;
        box-shadow: 0 18px 50px rgba(0,0,0,.08);
        border: 1px solid #d9d9d9;
    }

    /* ── Company header ─────────────────────────────── */
    .bs-header { text-align: center; margin-bottom: 24px; }
    .bs-header h3 { margin: 0 0 5px; font-weight: 700; font-size: 20px; }
    .bs-header p  { margin: 2px 0; font-size: 14px; color: #444; }
    .bs-title  { margin-top: 15px; font-weight: 700; font-size: 18px; text-decoration: underline; }
    .bs-period { font-size: 13px; color: #555; margin-top: 5px; }

    /* ── Statement table ─────────────────────────────── */
    .statement-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
        border-top: 1px solid #cfcfcf;
    }

    .statement-col { padding: 0 14px 16px; }
    .statement-col + .statement-col { border-left: 1px solid #d9d9d9; }

    .statement-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
        font-weight: 700;
        border-bottom: 1px solid #d9d9d9;
        padding: 7px 0;
        margin-bottom: 8px;
    }

    .statement-row {
        display: grid;
        grid-template-columns: 1fr 100px 100px;
        gap: 12px;
        font-size: 12px;
        line-height: 1.35;
        padding: 1px 0;
    }

    .statement-row.detail {
        font-size: 11px;
        font-style: italic;
        color: #222;
        padding-left: 8px;
    }

    .statement-row.total {
        font-weight: 700;
        margin-top: 8px;
        border-top: 1px solid #111;
        padding-top: 4px;
    }

    .statement-row.strong { font-weight: 700; }
    .statement-row .amount { white-space: nowrap; text-align: right; }

    .statement-divider { border-top: 1px solid #8f8f8f; margin: 8px 0 6px; }

    /* ── Summary cards ───────────────────────────────── */
    .summary-card {
        border: 1px solid #dedede;
        background: #fafafa;
        padding: 12px;
        border-radius: 12px;
    }
    .summary-value { font-size: 22px; font-weight: 800; }
    .profit { color: #198754; }
    .loss   { color: #dc3545; }

    /* ── Responsive ──────────────────────────────────── */
    @media (max-width: 768px) {
        .statement-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .statement-col + .statement-col {
            border-left: 1px solid #d9d9d9;
            border-top: none;
        }
        .statement-row {
            grid-template-columns: minmax(0, 1fr) minmax(0, 42px) minmax(0, 42px);
            gap: 4px;
            font-size: 8px;
            align-items: start;
        }
        .statement-col {
            padding: 0 6px 12px;
            min-width: 0;
        }
        .statement-head {
            font-size: 9px;
            gap: 4px;
        }
        .statement-row.detail {
            font-size: 8px;
        }
        .statement-row .amount {
            white-space: normal;
            word-break: break-word;
            line-height: 1.1;
            font-size: 8px;
        }
        .statement-row > span:first-child {
            min-width: 0;
            overflow-wrap: anywhere;
        }
        .summary-value { font-size: 16px; }

        /* filter card stacks on mobile */
        .pl-filter-row .col-date {
            flex: 1 1 auto;
            min-width: 0;
        }
        .pl-filter-actions { display: flex; gap: 8px; }
        .pl-filter-actions .btn { flex: 1; }
    }
</style>

<div class="content">

    {{-- ── Page title ────────────────────────────────── --}}
    <div class="page-header">
        <div class="page-title">
            <h4>Profit &amp; Loss Statement</h4>
        </div>
    </div>

    {{-- ── Filter card (matches balance-sheet image) ──── --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2 px-3">
            <div class="row g-2 align-items-end pl-filter-row">

                {{-- Start Date --}}
                <div class="col-6 col-md-auto col-date">
                    <label class="form-label mb-1 small fw-semibold" for="from_date">Start Date</label>
                    <input type="date" id="from_date" class="form-control form-control-sm">
                </div>

                {{-- End Date --}}
                <div class="col-6 col-md-auto col-date">
                    <label class="form-label mb-1 small fw-semibold" for="to_date">End Date</label>
                    <input type="date" id="to_date" class="form-control form-control-sm">
                </div>

                {{-- Buttons --}}
                <div class="col-12 col-md-auto pl-filter-actions">
                    <a href="#" id="export-pdf" class="btn btn-danger btn-sm">
                        <i class="fas fa-file-pdf me-1"></i>PDF
                    </a>
                    <a href="#" id="export-excel" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i>Excel
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Statement card ─────────────────────────────── --}}
    <div class="card">
        <div class="card-body">

            {{-- Company header --}}
            <div class="bs-header">
                <h3 id="company-name"></h3>
                <p id="company-address"></p>
                <p id="company-contact"></p>
                <div class="bs-title">Profit &amp; Loss A/c</div>
                <div class="bs-period" id="company-period"></div>
            </div>

            {{-- Dual-column statement --}}
            <div class="statement-grid">

                {{-- LEFT : Dr side --}}
                <div class="statement-col">
                    <div class="statement-head">
                        <span>Particulars</span>
                        <span id="left-period-label"></span>
                    </div>
                    <div id="left-items"></div>
                    <div class="statement-divider"></div>
                    <div class="statement-row strong">
                        <span>Nett Profit</span>
                        <span class="amount"></span>
                        <span class="amount profit" id="net-profit-left">0.00</span>
                    </div>
                    <div class="statement-divider"></div>
                    <div class="statement-row total">
                        <span>Total</span>
                        <span class="amount"></span>
                        <span class="amount" id="left-total">0.00</span>
                    </div>
                </div>

                {{-- RIGHT : Cr side --}}
                <div class="statement-col">
                    <div class="statement-head">
                        <span>Particulars</span>
                        <span id="right-period-label"></span>
                    </div>
                    <div id="right-items"></div>
                    <div class="statement-divider"></div>
                    <div class="statement-row total">
                        <span>Total</span>
                        <span class="amount"></span>
                        <span class="amount" id="right-total">0.00</span>
                    </div>
                </div>
            </div>

            {{-- Summary cards --}}
            <div class="row mt-4 g-3">
                <div class="col-6 col-md-3">
                    <div class="summary-card">
                        <div class="text-muted small">Sales</div>
                        <div class="summary-value profit" id="summary-sales">0.00</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card">
                        <div class="text-muted small">Purchase</div>
                        <div class="summary-value loss" id="summary-purchase">0.00</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card">
                        <div class="text-muted small">Expenses</div>
                        <div class="summary-value loss" id="summary-expense">0.00</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card">
                        <div class="text-muted small">Net Result</div>
                        <div class="summary-value" id="summary-result">0.00</div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-4 d-none" id="no-data-alert">
                No data found for the selected period.
            </div>

        </div>
    </div>
</div>
@endsection

@push('js')
<script>
/* ── helpers ───────────────────────────────────────────── */
const money = v => Number(v || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const fmtDate = s => {
    if (!s) return '';
    const d = new Date(s);
    const m = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return `${String(d.getDate()).padStart(2,'0')}-${m[d.getMonth()]}-${String(d.getFullYear()).slice(-2)}`;
};

const renderRows = (items, targetId) => {
    const html = (items || []).map(item => {
        if (item.type === 'empty') {
            return `<div class="statement-row"><span style="min-height:16px"></span><span></span><span></span></div>`;
        }
        const inner = (item.inner_amount !== '' && item.inner_amount !== undefined)
            ? (item.inner_amount < 0 ? '(-)' + money(Math.abs(item.inner_amount)) : money(item.inner_amount))
            : '';
        const outer = (item.outer_amount !== '' && item.outer_amount !== undefined)
            ? money(item.outer_amount) : '';
        const borderStyle = item.is_last ? 'border-bottom:1px solid #333;padding-bottom:2px;' : '';
        return `<div class="statement-row ${item.type || ''}">
                    <span>${item.label || ''}</span>
                    <span class="amount" style="${borderStyle}">${inner}</span>
                    <span class="amount">${outer}</span>
                </div>`;
    }).join('');
    document.getElementById(targetId).innerHTML = html ||
        `<div class="statement-row"><span>No items</span><span class="amount"></span><span class="amount">0.00</span></div>`;
};

/* ── load statement via AJAX ───────────────────────────── */
function loadStatement() {
    $.ajax({
        url:  "{{ route('profit-loss-statement.data') }}",
        type: 'GET',
        data: { from_date: $('#from_date').val(), to_date: $('#to_date').val() },
        success(res) {
            const summary = res.summary || {};
            const company = res.company || {};

            const from = fmtDate(res.period?.from);
            const to   = fmtDate(res.period?.to);
            const periodText = (res.period?.from || res.period?.to)
                ? `From ${from}${to ? ' to ' + to : ''}` : 'All time';
            const asAt = `as at ${to || from || 'Today'}`;

            $('#company-name').text(company.name || '');
            $('#company-address').text(company.address || '');
            const contact = [company.phone, company.email].filter(Boolean).join(' | ');
            $('#company-contact').text(contact ? 'Contact : ' + contact : '');
            $('#company-period').text(periodText);
            $('#left-period-label, #right-period-label').text(asAt);

            renderRows(res.left_rows  || [], 'left-items');
            renderRows(res.right_rows || [], 'right-items');

            $('#summary-sales').text(money(summary.sales_total));
            $('#summary-purchase').text(money(summary.purchase_total));
            $('#summary-expense').text(money(summary.indirect_expenses));

            const net = Number(summary.profit_loss || 0);
            $('#summary-result')
                .removeClass('profit loss')
                .addClass(net >= 0 ? 'profit' : 'loss')
                .text(net >= 0 ? money(net) : '-' + money(Math.abs(net)));

            $('#net-profit-left')
                .removeClass('profit loss')
                .addClass(net >= 0 ? 'profit' : 'loss')
                .text(net >= 0 ? money(net) : money(Math.abs(net)));

            const leftTotal = (summary.opening_stock || 0)
                + (summary.purchase_total || 0)
                + (summary.indirect_expenses || 0)
                + Math.max(net, 0);
            const rightTotal = (summary.sales_total || 0)
                + (summary.closing_stock || 0);

            $('#left-total').text(money(leftTotal));
            $('#right-total').text(money(rightTotal));

            const hasData = [summary.opening_stock, summary.sales_total, summary.purchase_total,
                             summary.indirect_expenses, summary.closing_stock].some(v => Number(v || 0) !== 0);
            $('#no-data-alert').toggleClass('d-none', hasData);
        },
        error() { $('#no-data-alert').removeClass('d-none'); }
    });
}

/* ── download helper ───────────────────────────────────── */
function downloadReport(type, url) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({ title: type === 'pdf' ? 'Generating PDF…' : 'Generating Excel…',
                    html: 'Please wait', allowOutsideClick: false,
                    didOpen: () => Swal.showLoading() });
    }
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Export failed');
            let filename = type === 'pdf' ? 'Profit_Loss_Statement.pdf' : 'Profit_Loss_Statement.xlsx';
            const disp = response.headers.get('content-disposition');
            if (disp && disp.indexOf('attachment') !== -1) {
                const m = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disp);
                if (m && m[1]) filename = m[1].replace(/['"]/g, '');
            }
            return response.blob().then(blob => ({ blob, filename }));
        })
        .then(({ blob, filename }) => {
            if (typeof Swal !== 'undefined') Swal.close();
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(a.href);
        })
        .catch(() => {
            if (typeof Swal !== 'undefined') Swal.fire('Error', 'Failed to generate file', 'error');
            else alert('Failed to generate file');
        });
}

/* ── init ──────────────────────────────────────────────── */
$(document).ready(function () {
    const pad = n => String(n).padStart(2, '0');
    const fmt = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
    const now  = new Date();
    $('#from_date').val(fmt(new Date(now.getFullYear(), now.getMonth(), 1)));
    $('#to_date').val(fmt(new Date(now.getFullYear(), now.getMonth() + 1, 0)));

    $('#from_date, #to_date').on('change', loadStatement);

    $('#export-pdf').on('click', function (e) {
        e.preventDefault();
        const url = `{{ route('profit-loss-statement.pdf') }}?from_date=${$('#from_date').val()}&to_date=${$('#to_date').val()}`;
        downloadReport('pdf', url);
    });

    $('#export-excel').on('click', function (e) {
        e.preventDefault();
        const url = `{{ route('profit-loss-statement.excel') }}?from_date=${$('#from_date').val()}&to_date=${$('#to_date').val()}`;
        window.location.href = url;
    });

    loadStatement();
});
</script>
@endpush
