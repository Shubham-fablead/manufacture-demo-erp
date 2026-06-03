@extends('layout.app')

@section('title', 'GST Reports')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" />
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>GST Reports</h4>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <div class="row">
                    <div class="col-md-2 col-4">
                        <label for="from_date" class="form-label fw-bold">Start Date</label>
                        <input type="date" id="from_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 col-4">
                        <label for="to_date" class="form-label fw-bold">End Date</label>
                        <input type="date" id="to_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 col-4">
                        <label for="year_select" class="form-label fw-bold">Year</label>
                        <select id="year_select" class="form-control form-control-sm">
                            <option value="">-- Select Year --</option>
                            @for ($i = date('Y'); $i >= 2020; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-end mb-2">
                    <input type="text" id="gst-report-search" class="form-control form-control-sm"
                        placeholder="Search..." style="max-width: 188px;">
                </div>
                <div class="table-responsive">
                    <table class="table datanew" id="gst-report-table">
                        <thead>
                            <tr>
                                <th style="width: 70px;">No</th>
                                <th>GST Reports Name</th>
                                <th class="text-end">Download PDF/Excel</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Goods and Services Tax - GSTR-2B</td>
                                <td class="text-end">
                                    {{-- <a href="{{ route('gst.gstr3b.export') }}" class="btn btn-primary py-1">
                                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                                    </a> --}}
                                    <!-- <a href="{{ route('gst.sales.report.gstr3b.export') }}"
                                        class="btn btn-success export-link">
                                        <i class="fa-solid fa-file-excel"></i> Export Excel
                                    </a> -->

                                    <a class="btn btn-success" id="exportGSTR2B">
                                        <i class="fa-solid fa-file-excel"></i> Export Excel
                                    </a>

                                </td>
                            </tr>

                            <tr>
                                <td>2</td>
                                <td>Goods and Services Tax - GSTR-1</td>
                                <td class="text-end">
                                    {{-- <a class="btn btn-primary py-1">
                                            <i class="fa-solid fa-file-pdf"></i> Export PDF
                                        </a> --}}
                                    <a class="btn btn-success" id="exportExcel">
                                        <i class="fa-solid fa-file-excel"></i> Export Excel
                                    </a>



                                </td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>GSTR-3B Summary</td>
                                <td class="text-end">
                                    <a href="#" id="exportGstr3bPdf" class="btn btn-primary py-1">
                                        <i class="fa-solid fa-file-pdf"></i> Export PDF
                                    </a>
                                    {{-- <a href="{{ route('sales.gstr3b.export') }}" class="btn btn-success">
                                    <i class="fa-solid fa-file-excel"></i> Export Excel
                                    </a> --}}
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                <div
                    class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <span class="me-2" style="font-size: 14px; color: #555;">Show per page :</span>
                        <select id="gst-per-page-select" class="form-select form-select-sm"
                            style="width: auto !important; border: 1px solid #ddd;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-3" style="font-size: 14px; color: #555;">
                            <span id="gst-pagination-from">0</span> - <span id="gst-pagination-to">0</span> of
                            <span id="gst-pagination-total">0</span> items
                        </span>
                    </div>
                    <nav aria-label="GST reports pagination">
                        <ul class="pagination pagination-sm mb-0" id="gst-pagination-numbers"></ul>
                    </nav>
                </div>
            </div>

            <style>
                .dataTables_filter,
                .dataTables_length,
                .dataTables_info,
                .dataTables_paginate {
                    display: none !important;
                }

                .pagination .page-item .page-link {
                    background-color: #5d6d7e;
                    color: #fff;
                    border: none;
                    margin: 0 3px;
                    padding: 4px 10px;
                    border-radius: 6px;
                    font-weight: bold;
                }

                .pagination .page-item:first-child .page-link,
                .pagination .page-item:last-child .page-link {
                    background-color: #fff;
                    color: #6c757d;
                    border: 1px solid #dee2e6;
                }

                .pagination .page-item:first-child .page-link:hover,
                .pagination .page-item:last-child .page-link:hover {
                    background-color: #f8f9fa;
                    color: #495057;
                    border-color: #dee2e6;
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

                .pagination .page-item.disabled .page-link {
                    background-color: #fff !important;
                    color: #dee2e6 !important;
                    border: 1px solid #dee2e6 !important;
                    cursor: not-allowed !important;
                    pointer-events: none !important;
                }

                .pdf-btn {
                    color: #ffffff !important;
                    /* Text white */
                }

                .form-select-sm {

                    width: 14% !important;

                }

                .pdf-btn i {
                    color: #ffffff !important;
                    /* Icon white */
                }

                .pdf-btn:hover {
                    background-color: #c82333 !important;
                    /* Hover color (red shade for PDF feel) */
                    color: #ffffff !important;
                    /* Text white on hover */
                }

                .pdf-btn:hover i {
                    color: #ffffff !important;
                    /* Icon white on hover */
                }

                .table tbody tr td a {
                    color: #ffffff !important;
                }
            </style>
            @endsection
            @push('js')
            <script>
                $(document).ready(function() {
                    let gstCurrentPage = 1;
                    let gstPerPage = parseInt($('#gst-per-page-select').val(), 10);
                    const $gstRows = $('#gst-report-table tbody tr');

                    function getFilteredGstRows() {
                        const searchValue = ($('#gst-report-search').val() || '').toLowerCase().trim();

                        if (!searchValue) {
                            return $gstRows;
                        }

                        return $gstRows.filter(function() {
                            return $(this).text().toLowerCase().includes(searchValue);
                        });
                    }

                    function updateGstPagination() {
                        const $filteredRows = getFilteredGstRows();
                        const total = $filteredRows.length;
                        const lastPage = Math.max(Math.ceil(total / gstPerPage), 1);

                        if (gstCurrentPage > lastPage) {
                            gstCurrentPage = lastPage;
                        }

                        const startIndex = (gstCurrentPage - 1) * gstPerPage;
                        const endIndex = startIndex + gstPerPage;
                        const from = total === 0 ? 0 : startIndex + 1;
                        const to = Math.min(endIndex, total);

                        $gstRows.hide();
                        $filteredRows.slice(startIndex, endIndex).show();

                        $('#gst-pagination-from').text(from);
                        $('#gst-pagination-to').text(to);
                        $('#gst-pagination-total').text(total);

                        let paginationHtml = `
                            <li class="page-item ${gstCurrentPage === 1 ? 'disabled' : ''}">
                                <a class="page-link gst-page-link" href="javascript:void(0);" data-page="${gstCurrentPage - 1}">Previous</a>
                            </li>
                        `;

                        const visiblePageCount = 2;
                        const startPage = Math.floor((gstCurrentPage - 1) / visiblePageCount) * visiblePageCount + 1;
                        const endPage = Math.min(lastPage, startPage + visiblePageCount - 1);

                        if (startPage > 1) {
                            paginationHtml += `
                                <li class="page-item">
                                    <a class="page-link gst-page-link" href="javascript:void(0);" data-page="${startPage - 1}">..</a>
                                </li>
                            `;
                        }

                        for (let i = startPage; i <= endPage; i++) {
                            paginationHtml += `
                                <li class="page-item ${i === gstCurrentPage ? 'active' : ''}">
                                    <a class="page-link gst-page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                                </li>
                            `;
                        }

                        if (endPage < lastPage) {
                            paginationHtml += `
                                <li class="page-item">
                                    <a class="page-link gst-page-link" href="javascript:void(0);" data-page="${endPage + 1}">..</a>
                                </li>
                            `;
                        }

                        paginationHtml += `
                            <li class="page-item ${gstCurrentPage === lastPage ? 'disabled' : ''}">
                                <a class="page-link gst-page-link" href="javascript:void(0);" data-page="${gstCurrentPage + 1}">Next</a>
                            </li>
                        `;

                        $('#gst-pagination-numbers').html(paginationHtml);
                        $('.pagination-controls').toggle(total > 0);
                    }

                    $('#gst-per-page-select').on('change', function() {
                        gstPerPage = parseInt($(this).val(), 10);
                        gstCurrentPage = 1;
                        updateGstPagination();
                    });

                    $('#gst-report-search').on('keyup', function() {
                        gstCurrentPage = 1;
                        updateGstPagination();
                    });

                    $(document).on('click', '.gst-page-link', function(e) {
                        e.preventDefault();

                        const page = parseInt($(this).data('page'), 10);
                        const total = getFilteredGstRows().length;
                        const lastPage = Math.max(Math.ceil(total / gstPerPage), 1);

                        if (page >= 1 && page <= lastPage && page !== gstCurrentPage) {
                            gstCurrentPage = page;
                            updateGstPagination();
                        }
                    });

                    updateGstPagination();
                });

                $('#exportGSTR2B').click(function(e) {
                    e.preventDefault();

                    const authToken = localStorage.getItem("authToken");
                    const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                    const from_date = $('#from_date').val();
                    const to_date = $('#to_date').val();
                    const year = $('#year_select').val();

                    const btn = $(this);
                    const originalHtml = btn.html();
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Exporting...');

                    $.ajax({
                        url: '/api/gstr3b/export-excel', // your new GSTR-2B route
                        method: 'GET',
                        headers: {
                            "Authorization": "Bearer " + authToken
                        },
                        data: {
                            from_date: from_date,
                            to_date: to_date,
                            year: year,
                            selectedSubAdminId: selectedSubAdminId,
                        },
                        xhrFields: {
                            responseType: 'blob' // Important for binary Excel file
                        },
                        success: function(blob) {
                            if (!(blob instanceof Blob)) {
                                alert('Export failed: Invalid response.');
                                btn.prop('disabled', false).html(originalHtml);
                                return;
                            }

                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = 'GSTR-2B.xlsx'; // file name
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(url);
                            btn.prop('disabled', false).html(originalHtml);
                        },
                        error: function() {
                            alert('Failed to export Excel!');
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                });


                $('#exportExcel').click(function(e) {
                    e.preventDefault();

                    const authToken = localStorage.getItem("authToken");
                    const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                    const from_date = $('#from_date').val();
                    const to_date = $('#to_date').val();
                    const year = $('#year_select').val();

                    const btn = $(this);
                    const originalHtml = btn.html();
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Exporting...');

                    $.ajax({
                        url: '/api/gstr1/export-excel',
                        method: 'GET',
                        data: {
                            from_date: from_date,
                            to_date: to_date,
                            year: year,
                            selectedSubAdminId: selectedSubAdminId,
                        },
                        headers: {
                            "Authorization": "Bearer " + authToken
                        },
                        xhrFields: {
                            responseType: 'blob'
                        },
                        success: function(blob) {
                            if (!(blob instanceof Blob)) {
                                console.error('Expected a Blob but received:', blob);
                                alert('Export failed: Invalid response format.');
                                btn.prop('disabled', false).html(originalHtml);
                                return;
                            }
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = 'GSTR1_Multiple_Sheets.xlsx';
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(url);
                            btn.prop('disabled', false).html(originalHtml);
                        },
                        error: function() {
                            alert('Failed to export Excel!');
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                });

                $('#exportGstr3bPdf').click(function(e) {
                    e.preventDefault();

                    const authToken = localStorage.getItem("authToken");
                    const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                    const from_date = $('#from_date').val();
                    const to_date = $('#to_date').val();
                    const year = $('#year_select').val();

                    const btn = $(this);
                    const originalHtml = btn.html();
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Exporting...');

                    $.ajax({
                        url: '/api/gst/gstr-3b/export',
                        method: 'GET',
                        data: {
                            from_date: from_date,
                            to_date: to_date,
                            year: year,
                            selectedSubAdminId: selectedSubAdminId,
                        },
                        headers: {
                            "Authorization": "Bearer " + authToken
                        },
                        success: function(response) {
                            if (response.status && response.file_url) {
                                const a = document.createElement('a');
                                a.href = response.file_url;
                                a.download = response.file_name || 'GSTR-3B-Summary.pdf';
                                document.body.appendChild(a);
                                a.click();
                                a.remove();
                            } else {
                                alert('Export failed: ' + (response.message || 'Unknown error'));
                            }
                            btn.prop('disabled', false).html(originalHtml);
                        },
                        error: function() {
                            alert('Failed to export PDF!');
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                });

                $('.export-link').click(function(e) {
                    e.preventDefault();
                    let url = $(this).attr('href');
                    const from_date = $('#from_date').val();
                    const to_date = $('#to_date').val();
                    const year = $('#year_select').val();

                    let params = [];
                    if (from_date) params.push('from_date=' + from_date);
                    if (to_date) params.push('to_date=' + to_date);
                    if (year) params.push('year=' + year);

                    if (params.length > 0) {
                        url += (url.includes('?') ? '&' : '?') + params.join('&');
                    }

                    window.location.href = url;
                });
            </script>
            @endpush
