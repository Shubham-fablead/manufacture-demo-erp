@extends('layout.app')

@section('title', 'Expense List')

@section('content')
    <style>
        .total_expense {
            font-weight: 600;
            color: #1b2850;
            border: 1px solid #1b2850;
        }

        .table-scroll-top {
            display: none;
            overflow-x: auto;
            overflow-y: hidden;
            height: 20px;
            width: 100%;
            margin-bottom: 5px;
        }

        .table-scroll-top div {
            height: 1px;
        }

        .form-control {
            color: #595b5d !important;
        }

        .dataTables_filter,
        .dataTables_length,
        .dataTables_info,
        .dataTables_paginate {
            display: none !important;
        }

        .dataTables_wrapper .row:first-child {
            display: none !important;
        }

        .dataTables_wrapper {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .search-input input {
            padding-left: 35px !important;
            border-radius: 5px;
        }

        .search-input {
            position: relative;
            display: flex;
            align-items: center;
        }

        .btn-searchset {
            position: absolute;
            left: 10px;
            z-index: 10;
            padding: 0;
            top: 7px !important;
        }

        .pagination .page-item.active .page-link {
            background-color: #ff9f43 !important;
            border-color: #ff9f43 !important;
            color: #fff !important;
        }

        .pagination .page-link {
            color: #1b2850;
            background-color: #5d6d7e;
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

        .pagination .page-item.disabled .page-link {
            background-color: #fff !important;
            color: #dee2e6 !important;
            border: 1px solid #dee2e6 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }

        /* Apply word wrap to all table columns */
        .datanew {
            table-layout: fixed !important;
            width: 100%;
        }

        .datanew th,
        .datanew td {
            white-space: normal !important;
            word-break: break-word;
            overflow-wrap: anywhere;
            line-height: 1.4;
        }

        .expense-mobile-summary {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 2px;
        }

        .expense-mobile-name {
            display: none;
            font-size: 12px;
            color: #595b5d;
            line-height: 1.3;
            word-break: break-word;
        }

        /* Desktop view - show header buttons, hide filter row buttons */
        @media screen and (min-width: 768px) {
            .desktop-export-buttons {
                display: flex !important;
            }

            .mobile-export-buttons {
                display: none !important;
            }
        }

        /* Mobile view - hide header buttons, show filter row buttons */
        @media screen and (max-width: 767px) {
            .desktop-export-buttons {
                display: none !important;
            }

            .mobile-export-buttons {
                display: flex !important;
            }
        }

        /* Responsive breakpoints for all screen sizes */

        /* Extra small devices (phones, less than 576px) */
        @media screen and (max-width: 575.98px) {
            .table-scroll-top {
                display: block;
            }

            .page-title {
                margin-right: 0;
            }

            .page-header {
                display: flex;
                /* flex-direction: column; */
            }

            .page-btn {
                flex-direction: column !important;
                gap: 10px;
            }

            .mb-view {
                display: flex;
                width: 100%;
                /* align-items: center; */
                justify-content: center;
            }

            /* .total-expense-summary {
                                    width: 100%;
                                } */

            /* .total_expense {
                                    font-size: 12px;
                                    padding: 4px;
                                    width: 100%;
                                    text-align: center;
                                } */

            /* .btn-added {
                                    width: 100%;
                                } */

            /* Filter section mobile */
            /* .table-top .row {
                                    flex-direction: column !important;
                                    gap: 10px !important;
                                }

                                .table-top .col-6,
                                .table-top .col-sm-3,
                                .table-top .col-lg-3 {
                                    width: 100% !important;
                                } */

            select#filter-expense-type,
            select#filter-month,
            select#filter-year,
            input#filter-date {
                font-size: 11px;
            }

            .form-label {
                font-size: 12px !important;
            }

            div#DataTables_Table_0_filter {
                margin-top: 10px !important;
            }

            .table-top {
                flex-direction: row;
                margin-bottom: 0 !important;
            }

            .table-top .wordset {
                margin-top: 0 !important;
            }

            .dataTables_length {
                margin-left: .8rem !important;
                margin-bottom: .1rem !important;
            }

            .dataTables_filter {
                text-align: left !important;
            }

            .filter-row .export-buttons-row {
                flex: 0 0 100%;
                max-width: 100%;
                margin-top: 5px;
            }

            .filter-row .export-buttons-row .d-flex {
                justify-content: flex-start !important;
                gap: 10px;
            }

            .filter-row .export-buttons-row button {
                flex: 1;
                font-size: 9px;
                padding: 5px 50px;
            }

            /* Table responsive styles */
            .table-responsive {
                overflow-x: hidden !important;
            }


            .datanew {
                font-size: 11px;
            }

            .pagination .page-item .page-link {
                padding: 4px 10px;
                margin: 0 3px;
                font-size: 12px;
            }

            .datanew th,
            .datanew td {
                padding: 6px 3px;
            }

            /* .datanew thead th:nth-child(1),
            .datanew tbody td:nth-child(1) {
                width: calc(100% - 60px) !important;
            } */

            .expense-mobile-name {
                display: block;
            }

            /* Show only Expense name and Details */
            .datanew thead th:nth-child(3),
            .datanew tbody td:nth-child(3),
            .datanew thead th:nth-child(4),
            .datanew tbody td:nth-child(4),
            .datanew thead th:nth-child(5),
            .datanew tbody td:nth-child(5),
            .datanew thead th:nth-child(6),
            .datanew tbody td:nth-child(6),
            .datanew thead th:nth-child(7),
            .datanew tbody td:nth-child(7),
            .datanew thead th:nth-child(8),
            .datanew tbody td:nth-child(8),
            .datanew thead th:nth-child(9),
            .datanew tbody td:nth-child(9) {
                display: none;
            }

            /* Center Details column */
            .datanew thead th.details-column,
            .datanew tbody td:nth-child(2) {
                display: table-cell !important;
                text-align: center;
                width: 60px;
                min-width: 60px;
            }

            .expense-toggle-btn-table {
                margin: 0 auto;
                display: block;
            }
        }

        @media (min-width: 768px) {
            .table-responsive {
                overflow-x: hidden !important;
            }

        }

        /* Small devices (landscape phones, 576px and up) */
        @media screen and (min-width: 576px) and (max-width: 767.98px) {
            .table-scroll-top {
                display: block;
            }



            /*
                                .table-top .row {
                                    flex-wrap: wrap;
                                    gap: 10px;
                                }

                                .table-top .col-sm-3 {
                                    flex: 0 0 calc(50% - 5px);
                                } */

            .datanew {
                font-size: 12px;
            }

            .datanew th,
            .datanew td {
                padding: 8px 4px;
            }

            /* Show Expense name, Details, Date, Amount */
            .datanew thead th:nth-child(5),
            .datanew tbody td:nth-child(5),
            .datanew thead th:nth-child(6),
            .datanew tbody td:nth-child(6),
            .datanew thead th:nth-child(7),
            .datanew tbody td:nth-child(7),
            .datanew thead th:nth-child(8),
            .datanew tbody td:nth-child(8),
            .datanew thead th:nth-child(9),
            .datanew tbody td:nth-child(9) {
                display: none;
            }

            /* Center Details column */
            .datanew thead th.details-column,
            .datanew tbody td:nth-child(2) {
                display: table-cell !important;
                text-align: center;
                width: 60px;
                min-width: 60px;
            }

            .expense-toggle-btn-table {
                margin: 0 auto;
                display: block;
            }
        }

        /* Medium devices (tablets, 768px and up to 1024px) */
        @media screen and (min-width: 768px) and (max-width: 1024px) {
            .datanew {
                font-size: 13px;
            }


            .datanew th,
            .datanew td {
                padding: 8px 6px;
            }

            /* Hide Details column on tablets */
            .datanew thead th.details-column,
            .datanew tbody td:nth-child(2) {
                display: none !important;
            }

            /* Hide expandable rows on tablets */
            .expense-details-row {
                display: none !important;
            }
        }

        /* Large devices (desktops, 1025px and up) */
        @media screen and (min-width: 1025px) {
            .datanew {
                font-size: 14px;
            }

            .datanew th,
            .datanew td {
                padding: 12px 10px;
            }

            /* Hide Details column on desktop */
            .datanew thead th.details-column,
            .datanew tbody td:nth-child(2) {
                display: none !important;
            }

            /* Hide expandable rows on larger screens */
            .expense-details-row {
                display: none !important;
            }
        }

        /* Expandable row details - available for all screen sizes */
        .expense-details-row {
            display: none;
        }

        .expense-details-row.show {
            display: table-row;
        }

        /* Expandable content styles */
        .expense-details-content {
            padding: 15px;
            background: #fff;
            border-top: 2px solid #e0e0e0;
        }

        .expense-details-list {
            margin-bottom: 15px;
        }

        .expense-detail-row-simple {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .expense-detail-row-simple:last-of-type {
            border-bottom: none;
        }

        .expense-detail-label-simple {
            font-weight: 600;
            color: #595b5d;
            font-size: 14px;
        }

        .expense-detail-value-simple {
            color: #1b2850;
            font-size: 14px;
            text-align: right;
            white-space: normal !important;
        }

        .expense-action-buttons-simple {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-start;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .btn-icon-mobile-expense,
        button.btn-icon-mobile-expense {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #1b2850;
            background: transparent;
            transition: all 0.3s;
            border: 2px solid #1b2850;
            cursor: pointer;
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        button.btn-icon-mobile-expense {
            border: 2px solid #1b2850;
            background: transparent;
        }

        .btn-icon-mobile-expense:hover {
            background: #1b2850;
            color: white;
            transform: scale(1.1);
        }

        .btn-icon-mobile-expense img {
            width: 18px;
            height: 18px;
        }

        /* Toggle button styles */
        .expense-toggle-btn-table {
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

        .expense-toggle-btn-table:hover {
            background: #ff8c2e;
        }

        .expense-toggle-btn-table.minus {
            background: #dc3545;
        }

        .expense-toggle-btn-table.minus:hover {
            background: #c82333;
        }

        .select2-container {
            width: 100% !important;
            min-width: 150px;
        }

        .select2-filter+.select2-container .select2-selection--single {
            height: 35px !important;
            padding: 0 8px;
            width: 100% !important;
            border: 1px solid #dcdcdc !important;
            border-radius: 5px !important;
            display: flex !important;
            align-items: center;
            color: #595b5d !important;
            font-size: 14px;
        }

        .select2-filter+.select2-container .select2-selection__rendered {
            line-height: 33px !important;
            padding-left: 0 !important;
            width: 100%;
            color: #595b5d !important;
        }

        .select2-filter+.select2-container .select2-selection__arrow {
            height: 33px !important;
        }

        #filter-date {
            height: 35px !important;
            border: 1px solid #dcdcdc !important;
            border-radius: 5px !important;
            font-size: 14px;
            color: #595b5d !important;
            padding: 0 8px;
        }

        .select2-dropdown {
            border: 1px solid #dcdcdc !important;
            z-index: 9999;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #595b5d !important;
        }

        .page-header {
            margin-bottom: 10px;
        }

        .download-loader-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.35);
            z-index: 1060;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .download-loader-overlay.d-none {
            display: none !important;
        }

        .download-loader-box {
            width: min(460px, 100%);
            background: #fff;
            border-radius: 8px;
            padding: 24px 20px;
            text-align: center;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.2);
        }

        .download-loader-box h4 {
            margin: 0;
            font-size: 36px;
            color: #2c3e50;
            font-weight: 600;
        }

        .download-loader-box p {
            margin: 8px 0 16px;
            font-size: 30px;
            color: #2c3e50;
        }

        @media (max-width: 768px) {
            .download-loader-box h4 {
                font-size: 28px;
            }

            .download-loader-box p {
                font-size: 24px;
            }
        }
    </style>
    @if (session('error'))
        <div class="alert alert-danger" id="error-message">
            {{ session('error') }}
        </div>

        <style>
            .fade-out {
                opacity: 1;
                transition: opacity 0.5s ease-out;
            }

            .fade-out.hidden {
                opacity: 0;
            }
        </style>

        <script>
            setTimeout(function() {
                let alert = document.getElementById('error-message');
                if (alert) {
                    alert.classList.add('hidden'); // Triggers the fade-out transition
                    // Remove the element from DOM after fadeout (optional)
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500); // match the CSS transition duration (0.5s)
                }
            }, 4000);
        </script>
    @endif
    <div class="content">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div class="page-title">
                <h4>All Expenses</h4>
                <!--<h6>Manage your purchases</h6>-->
            </div>

            <div class="header-actions d-flex align-items-center gap-2">
                @if (app('hasPermission')(5, 'add'))
                    <!-- New Expense Button (Right Side) -->
                    <a href="{{ route('expense.add') }}" class="btn btn-added d-flex align-items-center btn-sm">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" class="me-2"
                            alt="img">New Expense
                    </a>
                @endif

                <!-- Desktop Export Buttons -->
                <div class="desktop-export-buttons d-flex gap-2">
                    <button class="btn btn-sm btn-success exportExcel">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button class="btn btn-sm btn-danger exportPdf">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- Total Expense Amount (Left Side) -->
        <div class="total-expense-summary mb-2 d-flex justify-content-end">
            <h5 id="total-expense-amount" class="mb-0 px-3 py-1 mx-3 rounded bg-light total_expense mb-view">
                <!-- Gets updated by JS -->
            </h5>
        </div>
        <div class="card">
            <div class="card-body">
                <!-- <div class="table-top mb-3">
                                                                                            <div class="row g-3 align-items-end">


                                                                                                <div class="col-lg-3 col-md-4 col-sm-6">
                                                                                                    <label for="filter-expense-type" class="form-label">Expense Type</label>
                                                                                                    <select id="filter-expense-type" class="form-control">
                                                                                                        <option value="">All Expense Types</option>
                                                                                                    </select>
                                                                                                </div>


                                                                                                <div class="col-lg-3 col-md-4 col-sm-6">
                                                                                                    <label for="filter-month" class="form-label">Month</label>
                                                                                                    <select id="filter-month" class="form-control">
                                                                                                        <option value="">All Months</option>
                                                                                                        @for ($m = 1; $m <= 12; $m++)
    <option value="{{ $m }}">{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
    @endfor
                                                                                                    </select>
                                                                                                </div>


                                                                                                <div class="col-lg-3 col-md-4 col-sm-6">
                                                                                                    <label for="filter-year" class="form-label">Year</label>
                                                                                                    <select id="filter-year" class="form-control">
                                                                                                        <option value="">All Years</option>
                                                                                                        @foreach ($years as $year)
    <option value="{{ $year }}">{{ $year }}</option>
    @endforeach
                                                                                                    </select>
                                                                                                </div>


                                                                                                <div class="col-lg-3 col-md-6 col-sm-6">
                                                                                                    <label for="filter-date" class="form-label">Date</label>
                                                                                                    <input type="date" id="filter-date" class="form-control" placeholder="dd-mm-yyyy">
                                                                                                </div>

                                                                                            </div>
                                                                                        </div> -->
                <div class="table-top mb-3">
                    <div class="row g-2 col-12 align-items-end filter-row">

                        <div class="col-12 col-sm-2 col-lg-3">
                            <div class="search-set d-flex justify-content-md-start mt-2 justify-content-start w-100">
                                <div class="search-path"></div>
                                <div class="search-input col-12">
                                    <a class="btn btn-searchset">
                                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}"
                                            alt="img" style="margin-bottom: 2px;">
                                    </a>
                                    <input type="text" id="expense-search-input" class="form-control"
                                        placeholder="Search..." style="height: 35px;">
                                </div>
                            </div>
                        </div>
                        <!-- Expense Type -->
                        <div class="col-6 col-sm-2 col-lg-3">
                            <div class="form-group mb-0">
                                <label for="filter-expense-type" class="form-label">Expense Type</label>
                                <select id="filter-expense-type" class="form-control form-control-sm select2-filter"
                                    data-placeholder="Select Expense Type">
                                    <option value="">All Expense Type</option>
                                </select>
                            </div>
                        </div>

                        <!-- Month -->
                        <div class="col-6 col-sm-2 col-lg-2">
                            <div class="form-group mb-0">
                                <label for="filter-month" class="form-label">Month</label>
                                <select id="filter-month" class="form-control form-control-sm select2-filter"
                                    data-placeholder="Select Month">
                                    <option value="">All Month</option>
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">
                                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Year -->
                        <div class="col-6 col-sm-2 col-lg-2">
                            <div class="form-group mb-0">
                                <label for="filter-year" class="form-label">Year</label>
                                <select id="filter-year" class="form-control form-control-sm select2-filter"
                                    data-placeholder="Select Year">
                                    <option value="">All Year</option>
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="col-6 col-sm-2 col-lg-2">
                            <!-- <div class="form-group mb-0"> -->
                            <label for="filter-date" class="form-label">Date</label>
                            <input type="text" id="filter-date" placeholder="Choose Date"
                                class="datetimepicker form-control form-control-sm">
                            <!-- </div> -->
                        </div>

                        <!-- Mobile Export Buttons Row (visible only on mobile) -->
                        <div class="col-12 export-buttons-row mobile-export-buttons">
                            <div class="d-flex gap-3 justify-content-between align-items-center w-100">
                                <button class="btn btn-sm btn-success flex-grow-1 exportExcel">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                                <button class="btn btn-sm btn-danger flex-grow-1 exportPdf">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-2">

                </div>

                <!-- Right: Filters (Grouped) -->
                <div class="table-scroll-top">
                    <div></div>
                </div>
                <div class="table-responsive">
                    <table class="table datanew" id="expense-list-table">
                        <thead>
                            <tr>
                                <th>Sr No</th>
                                <th class="details-column text-center">Details</th>
                                <th>Expense name</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Payment Mode</th>
                                <th>Expense For</th>
                                <th>Expense Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="expense-table-body">
                            <!-- Dynamic rows here -->
                        </tbody>
                    </table>
                </div>
                <div
                    class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <span class="me-2" style="font-size: 14px; color: #555;">Show per page :</span>
                        <select id="expense-per-page-select" class="form-select form-select-sm"
                            style="width: auto; border: 1px solid #ddd;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-3" style="font-size: 14px; color: #555;">
                            <span id="expense-pagination-from">0</span> - <span id="expense-pagination-to">0</span> of
                            <span id="expense-pagination-total">0</span> items
                        </span>
                    </div>
                    <nav aria-label="Expense pagination">
                        <ul class="pagination pagination-sm mb-0" id="expense-pagination-numbers"></ul>
                    </nav>
                </div>
            </div>
        </div>

    </div>

    <div id="downloadLoaderOverlay" class="download-loader-overlay d-none" aria-live="polite" aria-busy="true">
        <div class="download-loader-box">
            <h4 id="downloadLoaderTitle">Generating PDF...</h4>
            <p>Please wait</p>
            <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
        </div>
    </div>
@endsection
@push('js')
    {{-- <script>



        // Global variables
        var expenseDataMap = {};

        // Helper function to build expandable row content
        function buildExpenseExpandableRowContent(expense, res) {
            const symbol = res.currency_symbol || '₹';
            const position = res.currency_position || 'left';
            const amount = Number(expense.amount).toFixed(2);
            const displayAmount = position === 'right' ? amount + symbol : symbol + amount;

            // Build action buttons HTML
            let actionButtons = `
                <a href="/edit-expense/${expense.id}" class="btn-icon-mobile-expense" title="Edit">
                    <img src="{{ env('ImagePath') }}admin/assets/img/icons/edit.svg" alt="Edit">
                </a>`;

            if (!['sales-manager', 'purchase-manager', 'inventory-manager'].includes(window.userRole)) {
                actionButtons += `
                    <button type="button" class="btn-icon-mobile-expense delete-btn" data-id="${expense.id}" title="Delete">
                        <img src="{{ env('ImagePath') }}admin/assets/img/icons/delete.svg" alt="Delete">
                    </button>`;
            }

            return `
                <td colspan="7" class="expense-details-content">
                    <div class="expense-details-list">
                        <div class="expense-detail-row-simple">
                            <span class="expense-detail-label-simple">Date:</span>
                            <span class="expense-detail-value-simple">${moment(expense.expense_date).format('DD MMM YYYY')}</span>
                        </div>
                        <div class="expense-detail-row-simple">
                            <span class="expense-detail-label-simple">Amount:</span>
                            <span class="expense-detail-value-simple" style="font-weight: bold; color: #dc3545;">${displayAmount}</span>
                        </div>
                        <div class="expense-detail-row-simple">
                            <span class="expense-detail-label-simple">Expense For:</span>
                            <span class="expense-detail-value-simple" style="text-align: left; max-width: 60%;">${expense.description ?? 'N/A'}</span>
                        </div>
                        <div class="expense-detail-row-simple">
                            <span class="expense-detail-label-simple">Expense Type:</span>
                            <span class="expense-detail-value-simple">${expense.expense_type?.type ?? 'N/A'}</span>
                        </div>
                    </div>
                    ${actionButtons ? `<div class="expense-action-buttons-simple">${actionButtons}</div>` : ''}
                </td>
            `;
        }

        // Toggle function for expense rows
        window.toggleExpenseRowDetails = function(expenseId) {
            const btn = $(`.expense-toggle-btn-table[data-expense-id="${expenseId}"]`);
            if (btn.length === 0) return;

            const row = btn.closest('tr');
            let detailsRow = row.next(`tr.expense-details-row[data-expense-id="${expenseId}"]`);
            const icon = btn.find('.toggle-icon');

            if (detailsRow.length === 0) {
                const expenseData = expenseDataMap[expenseId];
                if (expenseData) {
                    detailsRow = $('<tr>')
                        .addClass('expense-details-row')
                        .attr('data-expense-id', expenseId)
                        .html(buildExpenseExpandableRowContent(expenseData.expense, expenseData.res));
                    row.after(detailsRow);
                } else {
                    return;
                }
            }

            if (detailsRow.hasClass('show')) {
                detailsRow.removeClass('show');
                btn.removeClass('minus');
                icon.text('+');
            } else {
                detailsRow.addClass('show');
                btn.addClass('minus');
                icon.text('−');
            }
        };

        $(document).ready(function() {

            const authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            const userRole = "{{ auth()->user()->role }}";
            window.userRole = userRole; // Store globally for use in expandable row

            /* =======================
               DataTable Init (ONCE)
            ======================= */

                  $('.select2-filter').each(function() {
            $(this).select2({
                width: '100%',
                placeholder: $(this).data('placeholder'),
                allowClear: true
            });
        });

           $('#filter-year').select2({
                allowClear: true,
                width: '100%'
            });
            $('#filter-month').select2({
                allowClear: true,
                width: '100%'
            });

            let table;

            if ($.fn.DataTable.isDataTable('.datanew')) {
                table = $('.datanew').DataTable();
            } else {
                table = $('.datanew').DataTable({
                    autoWidth: false,
                    pageLength: 10,
                    ordering: false,
                    searching: true,
                    paging: true,
                    language: {
                        emptyTable: "No expenses found",
                        zeroRecords: "No expense record found"
                    }
                });
            }

            loadExpenseTypes();
            fetchExpenses();

            /* =======================
               FILTER EVENTS
            ======================= */

            // Date filter → clears other filters
            $('#filter-date').on('dp.change', function() {
                clearDropdownFilters();
                fetchExpenses(1);
            });

            // Dropdown filters → clear date
            // $('#filter-expense-type, #filter-month, #filter-year').on('change', function() {
            //     $('#filter-date').val('');
            //     fetchExpenses();
            // });
            $('.select2-filter').on('change', function () {
                if ($(this).val()) {
                    $('#filter-date').val('');
                }
                fetchExpenses();
            });

            /* =======================
               FETCH EXPENSES
            ======================= */
            function fetchExpenses() {

                const params = buildFilterParams();

                $.ajax({
                    url: '/api/expenses/list',
                    type: 'GET',
                    data: params,
                    headers: {
                        Authorization: "Bearer " + authToken
                    },

                    success: function(res) {
                        // Clear previous data map
                        expenseDataMap = {};

                        updateTotal(res);
                        table.clear();

                        if (!res.data?.length) {
                            table.draw();
                            return;
                        }

                        res.data.forEach(item => {
                            // Store expense data for expandable row
                            expenseDataMap[item.id] = {
                                expense: item,
                                res: res
                            };
                            table.row.add(buildRow(item, res));
                        });
                        table.draw();

                        // Remove expandable rows if on desktop
                        if ($(window).width() >= 768) {
                            $('.expense-details-row').remove();
                            $('.expense-toggle-btn-table').removeClass('minus').find('.toggle-icon').text('+');
                        }
                    },

                    error: () => alert('Failed to fetch expenses')
                });
            }

            /* =======================
               PARAM BUILDER
            ======================= */
            function buildFilterParams() {

                let params = {
                    selectedSubAdminId
                };

                const date = $('#filter-date').val();
                if (date) {
                    params.date = moment(date, 'DD-MM-YYYY').format('YYYY-MM-DD');
                    return params;
                }

                if ($('#filter-expense-type').val()) params.expense_type_id = $('#filter-expense-type').val();
                if ($('#filter-month').val()) params.month = $('#filter-month').val();
                if ($('#filter-year').val()) params.year = $('#filter-year').val();

                return params;
            }

            /* =======================
               ROW BUILDER
            ======================= */
            function buildRow(item, res) {

                const symbol = res.currency_symbol || '₹';
                const position = res.currency_position || 'left';
                // const amount = Number(item.amount).toFixed(2);
                // const displayAmount = position === 'right' ? amount + symbol : symbol + amount;
                const amount = Number(item.amount).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                const displayAmount = position === 'right' ? amount + symbol : symbol + amount;

                let actions = `
            <a href="/edit-expense/${item.id}">
                <img src="{{ env('ImagePath') }}admin/assets/img/icons/edit.svg">
            </a>`;

                if (!['sales-manager', 'purchase-manager', 'inventory-manager'].includes(userRole)) {
                    actions += `
                <a class="ms-2 delete-btn" data-id="${item.id}">
                    <img src="{{ env('ImagePath') }}admin/assets/img/icons/delete.svg">
                </a>`;
                }

                // Toggle button for Details column
                const detailsToggle = `
                    <button class="expense-toggle-btn-table" onclick="toggleExpenseRowDetails('${item.id}')" data-expense-id="${item.id}">
                        <span class="toggle-icon">+</span>
                    </button>
                `;

                return [
                    `<div class="expense-mobile-summary">
                        <span>${item.sr_no ?? item.id}</span>
                        <span class="expense-mobile-name">${item.expense_name ?? 'N/A'}</span>
                    </div>`,
                    detailsToggle,
                    moment(item.expense_date).format('DD MMM YYYY'),
                    displayAmount,
                    item.description ?? 'N/A',
                    item.expense_type?.type ?? 'N/A',
                    actions
                ];
            }

            /* =======================
               TOTAL UPDATE
            ======================= */
            function updateTotal(res) {
                // const total = Number(res.total_amount || 0).toFixed(2);

                const total = Number(res.total_amount || 0).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });                const symbol = res.currency_symbol || '₹';
                const display = res.currency_position === 'right' ?
                    total + symbol :
                    symbol + total;

                $('#total-expense-amount').html(
                    `Total Expenses: <span class="text-warning">${display}</span>`
                );
            }

            /* =======================
               LOAD EXPENSE TYPES
            ======================= */
            function loadExpenseTypes() {
                $.ajax({
                    url: '/api/expense-types',
                    data: {
                        selectedSubAdminId
                    },
                    headers: {
                        Authorization: "Bearer " + authToken
                    },
                    success: res => {

                        res.data.forEach(t =>
                            $('#filter-expense-type').append(
                                `<option value="${t.id}">${t.type}</option>`)
                        );
                    }
                });
            }

            // function clearDropdownFilters() {
            //     $('#filter-expense-type, #filter-month, #filter-year').val('');
            // }
            function clearDropdownFilters() {
                $('.select2-filter').val(null).trigger('change.select2');
            }

            /* =======================
               DELETE
            ======================= */
            $(document).on('click', '.delete-btn', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: "Are you sure?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#ff9f43"
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: `/api/expenses/${id}`,
                        type: "POST",
                        headers: {
                            Authorization: "Bearer " + authToken,
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                        },
                        success: () => {
                            if (currentPage > 1 && table.rows().count() === 1) {
                                currentPage--;
                            }
                            fetchExpenses(currentPage);
                        }
                    });
                });
            });

            // Resize handler for responsive behavior
            let expenseResizeTimer;
            let lastExpenseWidth = $(window).width();

            function forceExpenseCSSRecalculation() {
                const temp = document.createElement('div');
                temp.style.width = '1px';
                temp.style.height = '1px';
                temp.style.position = 'absolute';
                temp.style.visibility = 'hidden';
                document.body.appendChild(temp);
                void temp.offsetWidth;
                void temp.offsetHeight;
                document.body.removeChild(temp);

                void window.innerWidth;
                void window.innerHeight;
                void document.documentElement.offsetWidth;
                void document.documentElement.offsetHeight;
            }

            function handleExpenseResize() {
                clearTimeout(expenseResizeTimer);
                expenseResizeTimer = setTimeout(function() {
                    const currentWidth = $(window).width();
                    lastExpenseWidth = currentWidth;

                    // Remove all expandable rows if on desktop/tablet (>= 768px)
                    if (currentWidth >= 768) {
                        $('.expense-details-row').remove();
                        // Reset all toggle buttons to + state
                        $('.expense-toggle-btn-table').removeClass('minus').find('.toggle-icon').text('+');
                    }

                    // Force CSS recalculation
                    forceExpenseCSSRecalculation();

                    const expenseTable = document.getElementById('expense-list-table');
                    const tableResponsive = document.querySelectorAll('.table-responsive');

                    [expenseTable, ...tableResponsive].forEach(function(el) {
                        if (el) {
                            void el.offsetHeight;
                            void el.offsetWidth;
                            el.style.display = 'none';
                            void el.offsetHeight;
                            el.style.display = '';
                        }
                    });

                    // Adjust DataTable columns if table exists
                    if (table) {
                        table.columns.adjust().draw();
                    }

                    forceExpenseCSSRecalculation();
                }, 50);
            }

            // Window resize handler
            $(window).off('resize.expense').on('resize.expense', handleExpenseResize);

            if (window.expenseResizeHandler) {
                window.removeEventListener('resize', window.expenseResizeHandler);
            }
            window.expenseResizeHandler = handleExpenseResize;
            window.addEventListener('resize', window.expenseResizeHandler, { passive: true });

            // Orientation change handler
            $(window).off('orientationchange.expense').on('orientationchange.expense', function() {
                setTimeout(function() {
                    lastExpenseWidth = $(window).width();
                    handleExpenseResize();
                }, 300);
            });

            window.addEventListener('orientationchange', function() {
                setTimeout(function() {
                    lastExpenseWidth = $(window).width();
                    handleExpenseResize();
                }, 500);
            });

            // MatchMedia listeners for breakpoint changes
            const expenseQueries = [
                window.matchMedia('(max-width: 575.98px)'),
                window.matchMedia('(min-width: 576px) and (max-width: 767.98px)'),
                window.matchMedia('(min-width: 768px) and (max-width: 1024px)'),
                window.matchMedia('(min-width: 1025px)')
            ];

            expenseQueries.forEach(function(query) {
                if (query.addEventListener) {
                    query.addEventListener('change', function() {
                        setTimeout(handleExpenseResize, 100);
                    });
                } else if (query.addListener) {
                    query.addListener(function() {
                        setTimeout(handleExpenseResize, 100);
                    });
                }
            });

            // Initial width set and call
            lastExpenseWidth = $(window).width();

            $(window).on('load', function() {
                setTimeout(function() {
                    lastExpenseWidth = $(window).width();
                    handleExpenseResize();
                }, 500);
            });

            window.handleExpenseResize = handleExpenseResize;

        });
    </script> --}}
    <script>
        const canEditExpense = @json(app('hasPermission')(5, 'edit'));
        const canDeleteExpense = @json(app('hasPermission')(5, 'delete'));

        // Global variables
        var expenseDataMap = {};

        // Helper function to build expandable row content
        function buildExpenseExpandableRowContent(expense, res) {
            const symbol = res.currency_symbol || '₹';
            const position = res.currency_position || 'left';
            const amount = Number(expense.amount).toFixed(2);
            const displayAmount = position === 'right' ? amount + symbol : symbol + amount;

            // Build action buttons HTML
            let actionButtons = '';

            if (canEditExpense) {
                actionButtons += `
                <a href="/edit-expense/${expense.id}" class="btn-icon-mobile-expense" title="Edit">
                    <img src="{{ env('ImagePath') }}admin/assets/img/icons/edit.svg" alt="Edit">
                </a>`;
            }

            actionButtons += `
                <a href="/expense/pdf/${expense.id}" class="btn-icon-mobile-expense expense-pdf-download" title="Download PDF">
                    <img src="{{ env('ImagePath') }}admin/assets/img/icons/download.svg" alt="Download PDF">
                </a>`;

            if (canDeleteExpense && !['sales-manager', 'purchase-manager', 'inventory-manager'].includes(window.userRole)) {
                actionButtons += `
                <button type="button" class="btn-icon-mobile-expense delete-btn" data-id="${expense.id}" title="Delete">
                    <img src="{{ env('ImagePath') }}admin/assets/img/icons/delete.svg" alt="Delete">
                </button>`;
            }

            return `
            <td colspan="9" class="expense-details-content">
                <div class="expense-details-list">
                    <div class="expense-detail-row-simple">
                        <span class="expense-detail-label-simple">Sr No:</span>
                        <span class="expense-detail-value-simple">${expense.sr_no ?? expense.id}</span>
                    </div>
                    <div class="expense-detail-row-simple">
                        <span class="expense-detail-label-simple">Date:</span>
                        <span class="expense-detail-value-simple">${moment(expense.expense_date).format('DD MMM YYYY')}</span>
                    </div>
                    <div class="expense-detail-row-simple">
                        <span class="expense-detail-label-simple">Amount:</span>
                        <span class="expense-detail-value-simple" style="font-weight: bold; color: #dc3545;">${displayAmount}</span>
                    </div>
                    <div class="expense-detail-row-simple">
                        <span class="expense-detail-label-simple">Payment Mode:</span>
                        <span class="expense-detail-value-simple">${expense.payment_mode ?? 'N/A'}</span>
                    </div>
                    <div class="expense-detail-row-simple">
                        <span class="expense-detail-label-simple">Expense For:</span>
                        <span class="expense-detail-value-simple" style="text-align: left; max-width: 60%;">${expense.description ?? 'N/A'}</span>
                    </div>
                    <div class="expense-detail-row-simple">
                        <span class="expense-detail-label-simple">Expense Type:</span>
                        <span class="expense-detail-value-simple">${expense.expense_type?.type ?? 'N/A'}</span>
                    </div>
                </div>
                ${actionButtons ? `<div class="expense-action-buttons-simple">${actionButtons}</div>` : ''}
            </td>
        `;
        }

        // Toggle function for expense rows
        window.toggleExpenseRowDetails = function(expenseId) {
            const btn = $(`.expense-toggle-btn-table[data-expense-id="${expenseId}"]`);
            if (btn.length === 0) return;

            const row = btn.closest('tr');
            let detailsRow = row.next(`tr.expense-details-row[data-expense-id="${expenseId}"]`);
            const icon = btn.find('.toggle-icon');

            if (detailsRow.length === 0) {
                const expenseData = expenseDataMap[expenseId];
                if (expenseData) {
                    detailsRow = $('<tr>')
                        .addClass('expense-details-row')
                        .attr('data-expense-id', expenseId)
                        .html(buildExpenseExpandableRowContent(expenseData.expense, expenseData.res));
                    row.after(detailsRow);
                } else {
                    return;
                }
            }

            if (detailsRow.hasClass('show')) {
                detailsRow.removeClass('show');
                btn.removeClass('minus');
                icon.text('+');
            } else {
                detailsRow.addClass('show');
                btn.addClass('minus');
                icon.text('−');
            }
        };

        function initializeSelect2() {
            // console.log('Initializing Select2...');

            // Destroy existing Select2 instances if they exist
            if ($('#filter-expense-type').hasClass('select2-hidden-accessible')) {
                $('#filter-expense-type').select2('destroy');
            }
            if ($('#filter-month').hasClass('select2-hidden-accessible')) {
                $('#filter-month').select2('destroy');
            }
            if ($('#filter-year').hasClass('select2-hidden-accessible')) {
                $('#filter-year').select2('destroy');
            }

            // Initialize Select2 for all filter dropdowns - same style as invoice page
            $('#filter-expense-type').select2({
                placeholder: "All Expense Type",
                allowClear: true,
                width: '100%'
            });

            $('#filter-month').select2({
                placeholder: "All Month",
                allowClear: true,
                width: '100%'
            });

            $('#filter-year').select2({
                placeholder: "All Year",
                allowClear: true,
                width: '100%'
            });

            // console.log('Select2 initialized successfully');
        }

        $(document).ready(function() {
            const authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            const userRole = "{{ auth()->user()->role }}";
            window.userRole = userRole; // Store globally for use in expandable row
            const $downloadLoader = $('#downloadLoaderOverlay');
            const $downloadLoaderTitle = $('#downloadLoaderTitle');
            let currentPage = 1;
            let lastPage = 1;
            let perPage = 10;
            let searchQuery = '';

            let table;

            // Initialize DataTable
            if ($.fn.DataTable.isDataTable('.datanew')) {
                table = $('.datanew').DataTable();
            } else {
                table = $('.datanew').DataTable({
                    autoWidth: false,
                    pageLength: perPage,
                    ordering: false,
                    searching: false,
                    paging: false,
                    info: false,
                    dom: 't',
                    language: {
                        emptyTable: "No expenses found",
                        zeroRecords: "No expense record found"
                    }
                });
            }

            $('#expense-search-input').on('keyup', function() {
                searchQuery = $(this).val();
                fetchExpenses(1);
            });

            $('#expense-per-page-select').on('change', function() {
                perPage = $(this).val();
                fetchExpenses(1);
            });

            // First load expense types, then initialize Select2
            loadExpenseTypes();
            fetchExpenses(currentPage);

            /* =======================
               FILTER EVENTS
            ======================= */

            // Date filter → clears other filters
            $('#filter-date').on('dp.change', function() {
                clearDropdownFilters();
                fetchExpenses(1);
            });

            // Dropdown filters → clear date
            $('.select2-filter').on('change', function() {
                if ($(this).val()) {
                    $('#filter-date').val('');
                }
                fetchExpenses(1);
            });

            /* =======================
               FETCH EXPENSES
            ======================= */
            function fetchExpenses(page = 1) {
                currentPage = page;
                const params = buildFilterParams();

                $.ajax({
                    url: '/api/expenses/list',
                    type: 'GET',
                    data: params,
                    headers: {
                        Authorization: "Bearer " + authToken
                    },
                    success: function(res) {
                        // Clear previous data map
                        expenseDataMap = {};
                        currentPage = res.pagination?.current_page || 1;
                        lastPage = res.pagination?.last_page || 1;

                        updateTotal(res);
                        table.clear();
                        updatePaginationUI(res.pagination || {
                            current_page: 1,
                            last_page: 1,
                            per_page: perPage,
                            total: res.data?.length || 0
                        });

                        if (!res.data?.length) {
                            table.draw();
                            return;
                        }

                        res.data.forEach(item => {
                            // Store expense data for expandable row
                            expenseDataMap[item.id] = {
                                expense: item,
                                res: res
                            };
                            table.row.add(buildRow(item, res));
                        });
                        table.draw();

                        // Remove expandable rows if on desktop
                        if ($(window).width() >= 768) {
                            $('.expense-details-row').remove();
                            $('.expense-toggle-btn-table').removeClass('minus').find('.toggle-icon')
                                .text('+');
                        }
                    },
                    error: () => alert('Failed to fetch expenses')
                });
            }

            /* =======================
               PARAM BUILDER
            ======================= */
            function buildFilterParams() {
                let params = {
                    selectedSubAdminId,
                    page: currentPage,
                    per_page: perPage
                };

                if (searchQuery) params.search = searchQuery;

                const date = $('#filter-date').val();
                if (date) {
                    params.date = moment(date, 'DD-MM-YYYY').format('YYYY-MM-DD');
                    return params;
                }

                if ($('#filter-expense-type').val()) params.expense_type_id = $('#filter-expense-type').val();
                if ($('#filter-month').val()) params.month = $('#filter-month').val();
                if ($('#filter-year').val()) params.year = $('#filter-year').val();

                return params;
            }

            /* =======================
               ROW BUILDER
            ======================= */
            function buildRow(item, res) {
                const symbol = res.currency_symbol || '₹';
                const position = res.currency_position || 'left';
                const amount = Number(item.amount).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                const displayAmount = position === 'right' ? amount + symbol : symbol + amount;

                let actions = '';

                if (canEditExpense) {
                    actions += `
                    <a href="/edit-expense/${item.id}">
                        <img src="{{ env('ImagePath') }}admin/assets/img/icons/edit.svg">
                    </a>`;
                }

                actions += `
                    <a class="ms-2 expense-pdf-download" href="/expense/pdf/${item.id}" title="Download PDF">
                        <img src="{{ env('ImagePath') }}admin/assets/img/icons/download.svg">
                    </a>`;

                if (canDeleteExpense && !['sales-manager', 'purchase-manager', 'inventory-manager'].includes(userRole)) {
                    actions += `
                    <a class="ms-2 delete-btn" data-id="${item.id}">
                        <img src="{{ env('ImagePath') }}admin/assets/img/icons/delete.svg">
                    </a>`;
                }

                // Toggle button for Details column
                const detailsToggle = `
                <button class="expense-toggle-btn-table" onclick="toggleExpenseRowDetails('${item.id}')" data-expense-id="${item.id}">
                    <span class="toggle-icon">+</span>
                </button>
            `;

                return [
                    `<div class="expense-mobile-summary">
                        <span class="expense-mobile-name">${item.expense_name ?? 'N/A'}</span>
                        <span>${item.sr_no ?? item.id}</span>
                    </div>`,
                    detailsToggle,
                    item.expense_name,
                    moment(item.expense_date).format('DD MMM YYYY'),
                    displayAmount,
                    item.payment_mode ?? 'N/A',
                    item.description ?? 'N/A',
                    item.expense_type?.type ?? 'N/A',
                    actions
                ];
            }

            /* =======================
               TOTAL UPDATE
            ======================= */
            function updateTotal(res) {
                const total = Number(res.total_amount || 0).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                const symbol = res.currency_symbol || '₹';
                const display = res.currency_position === 'right' ?
                    total + symbol :
                    symbol + total;

                $('#total-expense-amount').html(
                    `Total Expenses: <span class="text-warning">${display}</span>`
                );
            }

            function updatePaginationUI(pagination) {
                let from = (pagination.current_page - 1) * pagination.per_page + 1;
                let to = pagination.current_page * pagination.per_page;

                if (to > pagination.total) {
                    to = pagination.total;
                }

                if (pagination.total === 0) {
                    from = 0;
                }

                $('#expense-pagination-from').text(from);
                $('#expense-pagination-to').text(to);
                $('#expense-pagination-total').text(pagination.total);

                let paginationHtml = '';
                const current = pagination.current_page || 1;
                const totalPages = pagination.last_page || 1;
                paginationHtml += `
                    <li class="page-item ${current === 1 ? 'disabled' : ''}">
                        <a class="page-link expense-page-link" href="javascript:void(0);" data-page="${current - 1}">Previous</a>
                    </li>
                `;

                const visiblePageCount = 2;
                let startPage = Math.floor((current - 1) / visiblePageCount) * visiblePageCount + 1;
                let endPage = Math.min(totalPages, startPage + visiblePageCount - 1);

                if (startPage > 1) {
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link expense-page-link" href="javascript:void(0);" data-page="${startPage - 1}" data-action="prev-group">..</a>
                        </li>
                    `;
                }

                for (let i = startPage; i <= endPage; i++) {
                    paginationHtml += `
                        <li class="page-item ${i === current ? 'active' : ''}">
                            <a class="page-link expense-page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                        </li>
                    `;
                }

                if (endPage < totalPages) {
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link expense-page-link" href="javascript:void(0);" data-page="${endPage + 1}" data-action="next-group">..</a>
                        </li>
                    `;
                }

                paginationHtml += `
                    <li class="page-item ${current === totalPages || totalPages === 0 ? 'disabled' : ''}">
                        <a class="page-link expense-page-link" href="javascript:void(0);" data-page="${current + 1}">Next</a>
                    </li>
                `;

                $('#expense-pagination-numbers').html(paginationHtml);
                $('.pagination-controls').toggle(pagination.total > 0);
            }

            $(document).on('click', '.expense-page-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                    currentPage = page;
                    fetchExpenses(page);
                }
            });

            /* =======================
               LOAD EXPENSE TYPES - UPDATED
            ======================= */
            function loadExpenseTypes() {
                // console.log('loadExpenseTypes started - calling API');

                $.ajax({
                    url: '/api/expense-types',
                    data: {
                        selectedSubAdminId
                    },
                    headers: {
                        Authorization: "Bearer " + authToken
                    },
                    success: function(res) {
                        // console.log('API call SUCCESSFUL - response received:', res);
                        // console.log('Response structure:', JSON.stringify(res).substring(0, 200) + '...');

                        // Clear existing options
                        $('#filter-expense-type').empty();

                        // Add the placeholder option
                        $('#filter-expense-type').append('<option value="">All Expense Type</option>');

                        // Check if res.data exists and is an array
                        if (res && res.data && Array.isArray(res.data)) {
                            // console.log('res.data is an array with length:', res.data.length);

                            if (res.data.length > 0) {
                                // console.log('Found', res.data.length, 'expense types');

                                res.data.forEach(t => {
                                    // console.log('Adding option:', t.id, t.type);
                                    $('#filter-expense-type').append(
                                        `<option value="${t.id}">${t.type}</option>`
                                    );
                                });

                                // console.log('Options added successfully from API');
                            } else {
                                // console.log('res.data array is empty - falling back to static');
                                loadStaticExpenseTypes();
                                return;
                            }
                        }
                        // Check if response has a different structure
                        else if (res && typeof res === 'object') {
                            // console.log('Response is an object but not in expected format:', Object.keys(res));
                            // Try to find data in other possible locations
                            if (res.expense_types && Array.isArray(res.expense_types)) {
                                // console.log('Found expense_types array instead of data');
                                res.expense_types.forEach(t => {
                                    $('#filter-expense-type').append(
                                        `<option value="${t.id}">${t.type}</option>`
                                    );
                                });
                            } else {
                                // console.log('Could not find data in expected format - falling back to static');
                                loadStaticExpenseTypes();
                                return;
                            }
                        } else {
                            // console.log('No valid data found in response - falling back to static');
                            loadStaticExpenseTypes();
                            return;
                        }

                        // Initialize Select2 AFTER options are loaded
                        initializeSelect2();
                    },
                    error: function(xhr, status, error) {
                        //     console.error('API call FAILED:', error);
                        //     console.log('Status:', status);
                        //     console.log('Response Text:', xhr.responseText);
                        //     console.log('Status Code:', xhr.status);
                        loadStaticExpenseTypes();
                    }
                });
            }

            function clearDropdownFilters() {
                $('#filter-expense-type').val(null).trigger('change');
                $('#filter-month').val(null).trigger('change');
                $('#filter-year').val(null).trigger('change');
            }

            /* =======================
               DELETE
            ======================= */
            $(document).on('click', '.delete-btn', function() {
                if (!canDeleteExpense) {
                    return;
                }

                const id = $(this).data('id');

                Swal.fire({
                    title: "Are you sure?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#ff9f43"
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: `/api/expenses/${id}`,
                        type: "POST",
                        headers: {
                            Authorization: "Bearer " + authToken,
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                        },
                        success: () => {
                            if (currentPage > 1 && table.rows().count() === 1) {
                                currentPage--;
                            }
                            fetchExpenses(currentPage);
                        }
                    });
                });
            });

            // Resize handler for responsive behavior
            let expenseResizeTimer;
            let lastExpenseWidth = $(window).width();

            function forceExpenseCSSRecalculation() {
                const temp = document.createElement('div');
                temp.style.width = '1px';
                temp.style.height = '1px';
                temp.style.position = 'absolute';
                temp.style.visibility = 'hidden';
                document.body.appendChild(temp);
                void temp.offsetWidth;
                void temp.offsetHeight;
                document.body.removeChild(temp);

                void window.innerWidth;
                void window.innerHeight;
                void document.documentElement.offsetWidth;
                void document.documentElement.offsetHeight;
            }

            function handleExpenseResize() {
                clearTimeout(expenseResizeTimer);
                expenseResizeTimer = setTimeout(function() {
                    const currentWidth = $(window).width();
                    lastExpenseWidth = currentWidth;

                    // Remove all expandable rows if on desktop/tablet (>= 768px)
                    if (currentWidth >= 768) {
                        $('.expense-details-row').remove();
                        // Reset all toggle buttons to + state
                        $('.expense-toggle-btn-table').removeClass('minus').find('.toggle-icon').text('+');
                    }

                    // Force CSS recalculation
                    forceExpenseCSSRecalculation();

                    const expenseTable = document.getElementById('expense-list-table');
                    const tableResponsive = document.querySelectorAll('.table-responsive');

                    [expenseTable, ...tableResponsive].forEach(function(el) {
                        if (el) {
                            void el.offsetHeight;
                            void el.offsetWidth;
                            el.style.display = 'none';
                            void el.offsetHeight;
                            el.style.display = '';
                        }
                    });

                    // Adjust DataTable columns if table exists
                    if (table) {
                        table.columns.adjust().draw();
                    }

                    forceExpenseCSSRecalculation();
                }, 50);
            }

            // Window resize handler
            $(window).off('resize.expense').on('resize.expense', handleExpenseResize);

            if (window.expenseResizeHandler) {
                window.removeEventListener('resize', window.expenseResizeHandler);
            }
            window.expenseResizeHandler = handleExpenseResize;
            window.addEventListener('resize', window.expenseResizeHandler, {
                passive: true
            });

            // Orientation change handler
            $(window).off('orientationchange.expense').on('orientationchange.expense', function() {
                setTimeout(function() {
                    lastExpenseWidth = $(window).width();
                    handleExpenseResize();
                }, 300);
            });

            window.addEventListener('orientationchange', function() {
                setTimeout(function() {
                    lastExpenseWidth = $(window).width();
                    handleExpenseResize();
                }, 500);
            });

            // MatchMedia listeners for breakpoint changes
            const expenseQueries = [
                window.matchMedia('(max-width: 575.98px)'),
                window.matchMedia('(min-width: 576px) and (max-width: 767.98px)'),
                window.matchMedia('(min-width: 768px) and (max-width: 1024px)'),
                window.matchMedia('(min-width: 1025px)')
            ];

            expenseQueries.forEach(function(query) {
                if (query.addEventListener) {
                    query.addEventListener('change', function() {
                        setTimeout(handleExpenseResize, 100);
                    });
                } else if (query.addListener) {
                    query.addListener(function() {
                        setTimeout(handleExpenseResize, 100);
                    });
                }
            });

            // Initial width set and call
            lastExpenseWidth = $(window).width();

            $(window).on('load', function() {
                setTimeout(function() {
                    lastExpenseWidth = $(window).width();
                    handleExpenseResize();
                }, 500);
            });

            window.handleExpenseResize = handleExpenseResize;

            function toggleDownloadLoader(isLoading, title) {
                if (isLoading) {
                    $downloadLoaderTitle.text(title || 'Generating report...');
                    $downloadLoader.removeClass('d-none');
                } else {
                    $downloadLoader.addClass('d-none');
                }
            }

            function getFileNameFromDisposition(contentDisposition, fallbackName) {
                if (!contentDisposition) return fallbackName;

                const utf8Match = contentDisposition.match(/filename\*=UTF-8''([^;]+)/i);
                if (utf8Match && utf8Match[1]) {
                    return decodeURIComponent(utf8Match[1]);
                }

                const asciiMatch = contentDisposition.match(/filename=\"?([^\";]+)\"?/i);
                if (asciiMatch && asciiMatch[1]) {
                    return asciiMatch[1];
                }

                return fallbackName;
            }

            async function downloadExpenseReport(endpoint, type) {
                const params = buildFilterParams();
                const queryString = $.param(params);
                const url = endpoint + (queryString ? `?${queryString}` : '');
                const isPdf = type === 'pdf';
                const fallbackName = isPdf ? 'expense-report.pdf' : 'expense-report.xlsx';
                const loaderTitle = isPdf ? 'Generating PDF...' : 'Generating Excel...';

                toggleDownloadLoader(true, loaderTitle);

                try {
                    const response = await fetch(url, {
                        method: 'GET',
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        throw new Error('Download request failed');
                    }

                    const blob = await response.blob();
                    const contentType = response.headers.get('content-type') || '';
                    if (contentType.includes('application/json')) {
                        throw new Error('No export data available');
                    }
                    const contentDisposition = response.headers.get('content-disposition');
                    const fileName = getFileNameFromDisposition(contentDisposition, fallbackName);
                    const downloadUrl = window.URL.createObjectURL(blob);

                    const link = document.createElement('a');
                    link.href = downloadUrl;
                    link.download = fileName;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();

                    window.URL.revokeObjectURL(downloadUrl);
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Download Failed',
                        text: 'Unable to generate the file right now. Please try again.',
                        confirmButtonColor: '#ff9f43'
                    });
                } finally {
                    toggleDownloadLoader(false);
                }
            }

            async function downloadSingleExpensePdf(url) {
                toggleDownloadLoader(true, 'Generating PDF...');

                try {
                    const response = await fetch(url, {
                        method: 'GET',
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        throw new Error('Download request failed');
                    }

                    const blob = await response.blob();
                    const contentType = response.headers.get('content-type') || '';
                    if (contentType.includes('application/json')) {
                        throw new Error('No PDF data available');
                    }
                    const contentDisposition = response.headers.get('content-disposition');
                    const fileName = getFileNameFromDisposition(contentDisposition, 'expense.pdf');
                    const downloadUrl = window.URL.createObjectURL(blob);

                    const link = document.createElement('a');
                    link.href = downloadUrl;
                    link.download = fileName;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();

                    window.URL.revokeObjectURL(downloadUrl);
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Download Failed',
                        text: 'Unable to generate the PDF right now. Please try again.',
                        confirmButtonColor: '#ff9f43'
                    });
                } finally {
                    toggleDownloadLoader(false);
                }
            }

            // Row-level PDF download icons
            $(document).on('click', '.expense-pdf-download', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                if (!url) return;
                downloadSingleExpensePdf(url);
            });

            // Export Excel
            $(document).on('click', '.exportExcel', function(e) {
                e.preventDefault();
                downloadExpenseReport('/export-expense', 'excel');
            });

            // Export PDF
            $(document).on('click', '.exportPdf', function(e) {
                e.preventDefault();
                downloadExpenseReport('/export-expense-pdf', 'pdf');
            });
        });
    </script>
@endpush
