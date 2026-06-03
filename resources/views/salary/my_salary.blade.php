@extends('layout.app')
@section('title', 'My Salary')
@section('content')
    <style>
        .form-control {
            color: #595b5d !important;
        }

        /* ── Table header – orange ── */
        #mySalaryTable thead tr th {
            background-color: #ff9f43;
            color: #fff;
            font-weight: 600;
            white-space: nowrap;
            vertical-align: middle;
        }

        #mySalaryTable td {
            vertical-align: middle;
        }

        /* ── Status badge ── */
        .badge-paid {
            background-color: #d4edda;
            color: #155724;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        /* ── Download button ── */
        .download-my-slip {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: white !important;
            padding: 5px 12px !important;
            font-size: 12px !important;
        }

        /* ── Custom Pagination ── */
        .pagination .page-item .page-link {
            background-color: #5d6d7e;
            color: #fff;
            border: none;
            margin: 0 3px;
            padding: 4px 10px;
            font-weight: bold;
        }
        .pagination .page-item.active .page-link {
            background-color: #ff9f43 !important;
            color: #fff;
        }
        .pagination .page-item .page-link:hover {
            background-color: #4a5766;
            color: #fff;
        }
        .pagination .page-item.active .page-link:hover {
            background-color: #e68a35 !important;
        }
        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            background-color: #fff;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }
        .pagination .page-item.disabled .page-link {
            background-color: #fff !important;
            color: #dee2e6 !important;
            border: 1px solid #dee2e6 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }

        /* ── Mobile toggle button ── */
        .mobile-toggle-btn-table {
            background: #ff9f43;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s;
        }
        .mobile-toggle-btn-table:hover       { background: #ff8c2e; }
        .mobile-toggle-btn-table.minus       { background: #dc3545; }
        .mobile-toggle-btn-table.minus:hover { background: #c82333; }

        /* ── Expandable detail row ── */
        .sal-detail-row          { display: none; }
        .sal-detail-row.show     { display: table-row; }

        .sal-detail-content {
            padding: 15px;
            background: #fff;
            border-top: 2px solid #e0e0e0;
        }
        .sal-detail-list { margin-bottom: 15px; }
        .sal-detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .sal-detail-item:last-of-type { border-bottom: none; }
        .sal-detail-label {
            font-weight: 600;
            color: #595b5d;
            font-size: 14px;
        }
        .sal-detail-value {
            color: #1b2850;
            font-size: 14px;
            text-align: right;
        }
        .sal-detail-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        /* ── Responsive column visibility ── */
        /* Mobile < 576px : Month/Year + toggle only */
        @media screen and (max-width: 575.98px) {
            #mySalaryTable { font-size: 11px; width: 100% !important; table-layout: fixed; }
            #mySalaryTable th, #mySalaryTable td { padding: 8px 4px; }
            #mySalaryTable th:nth-child(1), #mySalaryTable td:nth-child(1) { width: 65% !important; }
            #mySalaryTable th:nth-child(2), #mySalaryTable td:nth-child(2) { width: 35% !important; text-align: center; }
            #mySalaryTable th:nth-child(n+3), #mySalaryTable td:nth-child(n+3) { display: none; }
        }

        /* Small 576–767px : Month + toggle + Present */
        @media screen and (min-width: 576px) and (max-width: 767.98px) {
            #mySalaryTable { font-size: 12px; width: 100% !important; table-layout: fixed; }
            #mySalaryTable th:nth-child(1), #mySalaryTable td:nth-child(1) { width: 45% !important; }
            #mySalaryTable th:nth-child(2), #mySalaryTable td:nth-child(2) { width: 20% !important; text-align: center; }
            #mySalaryTable th:nth-child(3), #mySalaryTable td:nth-child(3) { width: 35% !important; }
            #mySalaryTable th:nth-child(n+4), #mySalaryTable td:nth-child(n+4) { display: none; }
        }

        /* Tablet / Desktop : hide Details column */
        @media screen and (min-width: 768px) {
            #mySalaryTable th:nth-child(2), #mySalaryTable td:nth-child(2) { display: none !important; }
            .sal-detail-row { display: none !important; }
        }

        /* Search input */
        .search-input { position: relative; display: flex; align-items: center; }
        .btn-searchset { position: absolute; left: 10px; z-index: 10; padding: 0; top: 7px !important; }
        .search-input input { padding-left: 35px !important; border-radius: 5px; }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Salary</h4>
            </div>
        </div>

        <div class="card">
            <div class="card-body">

                {{-- Filters + Search in one row --}}
                <div class="mb-4">
                    <div class="card p-3 ">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-2 col-md-3 col-6">
                                <label class="form-label">Month</label>
                                <select class="form-select form-select-sm" id="filterMonth">
                                    <option value="">All Months</option>
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">
                                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-3 col-6">
                                <label class="form-label">Year</label>
                                <select class="form-select form-select-sm" id="filterYear">
                                    <option value="">All Years</option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-4 col-12">
                                <label class="form-label">Search</label>
                                <div class="search-input">
                                    <a class="btn btn-searchset">
                                        <img class="" style="margin-right: 5px"
                                            src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}"
                                            alt="img">
                                    </a>
                                    <input type="text" id="salarySearch" class="form-control form-control-sm"
                                        placeholder="Search...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Table (plain HTML — no DataTables class) --}}
                <div class="table-responsive">
                    <table id="mySalaryTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Month / Year</th>
                                <th class="text-center">Details</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Extra Days</th>
                                <th>Salary</th>
                                <th>Extra Amount</th>
                                <th>Advance Paid</th>
                                <th>Total Salary</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="salaryTableBody">
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <span class="me-2" style="font-size:14px;color:#555;">Show per page:</span>
                        <select id="perPageSelect" class="form-select form-select-sm"
                            style="width:auto;border:1px solid #ddd;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="ms-3" style="font-size:14px;color:#555;">
                            <span id="paginFrom">0</span> –
                            <span id="paginTo">0</span> of
                            <span id="paginTotal">0</span> records
                        </span>
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="paginNumbers"></ul>
                    </nav>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
(function () {
    'use strict';

    // ── Destroy any DataTables instance on this table immediately ──
    $(document).ready(function () {

        // Kill any DataTables that global script.js may have attached
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#mySalaryTable')) {
            $('#mySalaryTable').DataTable().destroy();
        }

        const authToken = localStorage.getItem('authToken');

        let currentPage = 1;
        let lastPage    = 1;
        let perPage     = 10;
        let searchQuery = '';

        // ── Salary data map for expandable rows ──
        const salaryDataMap = {};

        // ── Populate year dropdown ──
        const currentYear = new Date().getFullYear();
        const $yearSelect = $('#filterYear');
        for (let y = currentYear; y >= currentYear - 5; y--) {
            $yearSelect.append(
                `<option value="${y}" ${y === currentYear ? 'selected' : ''}>${y}</option>`
            );
        }
        // Set current month
        $('#filterMonth').val(new Date().getMonth() + 1);

        // ── Build expandable row HTML ──
        function buildDetail(salary) {
            const monthName = new Date(salary.year, salary.month - 1)
                .toLocaleString('default', { month: 'long' });

            const fmt = v => parseFloat(v || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });

            return `<td colspan="11" class="sal-detail-content">
                <div class="sal-detail-list">
                    <div class="sal-detail-item">
                        <span class="sal-detail-label">Month / Year:</span>
                        <span class="sal-detail-value">${monthName} ${salary.year}</span>
                    </div>
                    <div class="sal-detail-item">
                        <span class="sal-detail-label">Present Days:</span>
                        <span class="sal-detail-value">${salary.present} Day(s)</span>
                    </div>
                    <div class="sal-detail-item">
                        <span class="sal-detail-label">Absent Days:</span>
                        <span class="sal-detail-value">${salary.absent} Day(s)</span>
                    </div>
                    <div class="sal-detail-item">
                        <span class="sal-detail-label">Extra Days:</span>
                        <span class="sal-detail-value">${salary.extra_present} Day(s)</span>
                    </div>
                    <div class="sal-detail-item">
                        <span class="sal-detail-label">Salary:</span>
                        <span class="sal-detail-value">₹${fmt(salary.monthly_salary)}</span>
                    </div>
                    <div class="sal-detail-item">
                        <span class="sal-detail-label">Extra Amount:</span>
                        <span class="sal-detail-value">₹${fmt(salary.extra_amount)}</span>
                    </div>
                    <div class="sal-detail-item">
                        <span class="sal-detail-label">Advance Paid:</span>
                        <span class="sal-detail-value">₹${fmt(salary.paid_advance)}</span>
                    </div>
                    <div class="sal-detail-item">
                        <span class="sal-detail-label">Total Salary:</span>
                        <span class="sal-detail-value" style="font-weight:bold;color:#ff9f43;">
                            ₹${fmt(salary.total_salary)}
                        </span>
                    </div>
                    <div class="sal-detail-item">
                        <span class="sal-detail-label">Status:</span>
                        <span class="sal-detail-value"><span class="badge-paid">Paid</span></span>
                    </div>
                </div>
                <div class="sal-detail-actions">
                    <button class="btn btn-sm download-my-slip"
                        data-month="${salary.month}" data-year="${salary.year}">
                        <i class="fas fa-download me-1"></i> Download
                    </button>
                </div>
            </td>`;
        }

        // ── Toggle expandable row (global so onclick="" works) ──
        window.toggleMySalaryRow = function (rowId) {
            const $btn = $(`[data-row-id="${rowId}"]`);
            if (!$btn.length) return;

            const $row       = $btn.closest('tr');
            const $icon      = $btn.find('.toggle-icon');
            let   $detail    = $row.next(`.sal-detail-row[data-row-id="${rowId}"]`);

            if (!$detail.length) {
                const data = salaryDataMap[rowId];
                if (!data) return;
                $detail = $('<tr>')
                    .addClass('sal-detail-row')
                    .attr('data-row-id', rowId)
                    .html(buildDetail(data));
                $row.after($detail);
            }

            if ($detail.hasClass('show')) {
                $detail.removeClass('show');
                $btn.removeClass('minus');
                $icon.text('+');
            } else {
                $detail.addClass('show');
                $btn.addClass('minus');
                $icon.text('−');
            }
        };

        // ── Fetch salary records ──
        function fetchMySalary(page) {
            page = page || 1;

            const month = $('#filterMonth').val();
            const year  = $('#filterYear').val();

            let url = '/api/my-salary?page=' + page + '&per_page=' + perPage;
            if (month)       url += '&month=' + month;
            if (year)        url += '&year='  + year;
            if (searchQuery) url += '&search=' + encodeURIComponent(searchQuery);

            $.ajax({
                url:     url,
                method:  'GET',
                headers: { Authorization: 'Bearer ' + authToken },
                success: function (res) {
                    const $tbody = $('#salaryTableBody');
                    $tbody.empty();
                    $('.sal-detail-row').remove();

                    // clear map
                    Object.keys(salaryDataMap).forEach(function (k) { delete salaryDataMap[k]; });

                    if (!res.status || !res.data || res.data.length === 0) {
                        $tbody.html('<tr><td colspan="11" class="text-center text-muted py-4">No salary records found.</td></tr>');
                        renderPagination({ current_page: 1, last_page: 1, per_page: perPage, total: 0 });
                        return;
                    }

                    renderPagination(res.pagination);

                    res.data.forEach(function (salary) {
                        const monthName = new Date(salary.year, salary.month - 1)
                            .toLocaleString('default', { month: 'long' });
                        const rowId = 'msal_' + (salary.salary_id || (salary.month + '_' + salary.year));
                        const fmt   = function (v) {
                            return parseFloat(v || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
                        };

                        salaryDataMap[rowId] = salary;

                        $tbody.append(
                            '<tr>' +
                                '<td><strong>' + monthName + ' ' + salary.year + '</strong></td>' +
                                '<td class="text-center">' +
                                    '<button class="mobile-toggle-btn-table" ' +
                                        'onclick="toggleMySalaryRow(\'' + rowId + '\')" ' +
                                        'data-row-id="' + rowId + '">' +
                                        '<span class="toggle-icon">+</span>' +
                                    '</button>' +
                                '</td>' +
                                '<td>' + salary.present + ' Day(s)</td>' +
                                '<td>' + salary.absent  + ' Day(s)</td>' +
                                '<td>' + salary.extra_present + ' Day(s)</td>' +
                                '<td>₹' + fmt(salary.monthly_salary) + '</td>' +
                                '<td>₹' + fmt(salary.extra_amount)   + '</td>' +
                                '<td>₹' + fmt(salary.paid_advance)   + '</td>' +
                                '<td><strong>₹' + fmt(salary.total_salary) + '</strong></td>' +
                                '<td><span class="badge-paid">Paid</span></td>' +
                                '<td>' +
                                    '<button class="btn btn-sm download-my-slip" ' +
                                        'data-month="' + salary.month + '" data-year="' + salary.year + '">' +
                                        '<i class="fas fa-download me-1"></i> Download' +
                                    '</button>' +
                                '</td>' +
                            '</tr>'
                        );
                    });
                },
                error: function () {
                    $('#salaryTableBody').html(
                        '<tr><td colspan="11" class="text-center text-danger py-4">Failed to load salary records.</td></tr>'
                    );
                }
            });
        }

        // ── Render pagination ──
        function renderPagination(p) {
            const from = p.total === 0 ? 0 : (p.current_page - 1) * p.per_page + 1;
            const to   = Math.min(p.current_page * p.per_page, p.total);

            $('#paginFrom').text(from);
            $('#paginTo').text(to);
            $('#paginTotal').text(p.total);

            currentPage = p.current_page;
            lastPage    = p.last_page;

            let html = '<li class="page-item ' + (currentPage === 1 ? 'disabled' : '') + '">' +
                           '<a class="page-link" href="javascript:void(0);" data-page="' + (currentPage - 1) + '">Previous</a>' +
                       '</li>';

            for (let i = 1; i <= lastPage; i++) {
                html += '<li class="page-item ' + (i === currentPage ? 'active' : '') + '">' +
                            '<a class="page-link" href="javascript:void(0);" data-page="' + i + '">' + i + '</a>' +
                        '</li>';
            }

            html += '<li class="page-item ' + (currentPage >= lastPage ? 'disabled' : '') + '">' +
                        '<a class="page-link" href="javascript:void(0);" data-page="' + (currentPage + 1) + '">Next</a>' +
                    '</li>';

            $('#paginNumbers').html(html);
        }

        // ── Pagination click ──
        $(document).on('click', '#paginNumbers .page-link', function (e) {
            e.preventDefault();
            const page = parseInt($(this).data('page'));
            if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                fetchMySalary(page);
            }
        });

        // ── Per-page change ──
        $('#perPageSelect').on('change', function () {
            perPage = parseInt($(this).val());
            fetchMySalary(1);
        });

        // ── Filter change ──
        $('#filterMonth, #filterYear').on('change', function () {
            fetchMySalary(1);
        });

        // ── Search ──
        $('#salarySearch').on('keyup', function () {
            searchQuery = $(this).val();
            fetchMySalary(1);
        });

        // ── Download salary slip ──
        $(document).on('click', '.download-my-slip', function () {
            const month = $(this).data('month');
            const year  = $(this).data('year');
            const $btn  = $(this);
            const orig  = $btn.html();

            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            fetch('/api/my-salary/pdf?month=' + month + '&year=' + year, {
                method:  'GET',
                headers: { Authorization: 'Bearer ' + authToken }
            })
            .then(function (response) {
                const ct = response.headers.get('content-type');
                if (!response.ok) {
                    return (ct && ct.includes('application/json'))
                        ? response.json().then(function (e) { throw new Error(e.message || 'Failed'); })
                        : Promise.reject(new Error('Failed to download PDF'));
                }
                if (ct && ct.includes('application/pdf')) {
                    return response.blob().then(function (blob) {
                        const url = URL.createObjectURL(blob);
                        const a   = document.createElement('a');
                        a.href     = url;
                        a.download = 'salary_slip_' + month + '_' + year + '.pdf';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);
                        Swal.fire({ icon: 'success', title: 'Downloaded',
                            text: 'Salary slip downloaded successfully.',
                            timer: 2000, showConfirmButton: false });
                    });
                }
                return response.json().then(function (d) {
                    throw new Error(d.message || 'Failed to generate PDF');
                });
            })
            .catch(function (err) {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            })
            .finally(function () {
                $btn.prop('disabled', false).html(orig);
            });
        });

        // ── Initial load ──
        fetchMySalary(1);
    });

}());
</script>
@endpush
