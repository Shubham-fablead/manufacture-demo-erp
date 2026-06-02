@extends('layout.app')

@section('title', 'TDS Report')

@section('content')
    <style>
        .dataTables_filter {
            display: none !important;
        }

        .tds-summary {
            border: 1px solid #e5e7eb;
            background: #f8fafc;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            width: 100%;
            text-align: center;
        }

        .tds-summary span {
            color: #0f172a;
        }

        .tds-total-highlight {
            font-weight: 600;
            color: #1b2850;
            border: 1px solid #1b2850;
            background: #f8f9fa;
            border-radius: 6px;
            padding: 6px 14px;
            font-size: 17px;
            text-align: center;
            line-height: 1.2;
            width: 100%;
        }

        .tds-total-highlight span {
            color: #ff9f43;
        }

        .tds-header-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .pagination-controls {
            border-top: 1px solid #eef2f7;
            padding-top: 12px;
        }

        /* Custom Pagination Styling */
        .pagination .page-item .page-link {
            background-color: #5d6d7e;
            /* Dark gray for other pages */
            color: #fff;
            border: none;
            margin: 0 3px;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: bold;
        }

        /* Previous and Next buttons */
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
            /* Orange for active page */
            color: #fff;
            border: none;
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

        .tds-filter-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
            margin-bottom: 15px;
        }

        .tds-filter-row .form-select-sm {
            min-width: 120px;
            flex: 1 1 auto;
        }

        .tds-filter-row .search-set {
            min-width: 220px;
            flex: 1 1 280px;
            margin-bottom: 0;
        }

        .tds-filter-row .search-input {
            position: relative;
        }

        .tds-search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            pointer-events: none;
            z-index: 2;
        }

        .tds-search-icon svg {
            width: 14px;
            height: 14px;
        }

        .tds-filter-row #customer-filter {
            /* min-width: 160px; */
        }

        .tds-filter-row .search-input .form-control {
            min-width: 100%;
            height: 32px;
            padding-left: 32px;
            font-size: 13px;
            color: #334155;
            height: 40px;
        }

        .tds-filter-row .search-input .form-control::placeholder {
            font-size: 13px;
            color: #64748b;
        }

        .tds-export-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .tds-export-group .btn {
            min-width: 80px;
            font-weight: 600;
            white-space: nowrap;
        }

        /* Responsive styles for TDS Report */
        .tds-table {
            table-layout: fixed !important;
            width: 100%;
        }

        .tds-table th,
        .tds-table td {
            white-space: normal !important;
            word-break: break-word;
            overflow-wrap: anywhere;
            line-height: 1.4;
        }
        /* ============================================
   TABLET VIEW SPECIFIC FIXES FOR TDS REPORT (768px - 1024px)
   ============================================ */

@media screen and (min-width: 768px) and (max-width: 1024px) {
    /* --- HEADER SECTION FIXES --- */
    .page-header {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 15px !important;
    }

    .tds-header-right {
        width: 100%;
        margin-left: 0;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .tds-total-highlight {
        width: auto;
        min-width: 360px;
        font-size: 16px;
        padding: 6px 12px;
    }

    .tds-summary {
        /* width: auto; */
        flex: 1;
        /* text-align: right; */
        width: 100%;
            text-align: center;
    }

    /* --- FILTER ROW FIXES - 3 COLUMN LAYOUT FOR TABLET --- */
    .tds-filter-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        align-items: end;
        margin-bottom: 20px;
    }

    /* Search input - full width of its grid cell */
    .tds-filter-row .search-set {
        grid-column: span 1;
        min-width: 0;
        width: 100%;
        margin-bottom: 0;
    }

    .tds-filter-row .search-input {
        width: 100%;
    }

    .tds-filter-row .search-input .form-control {
        width: 100%;
        min-width: 0;
    }

    /* All select dropdowns */
    .tds-filter-row .form-select-sm {
        width: 100%;
        min-width: 0 !important;
        flex: none !important;
        margin: 0;
    }

    /* Export buttons group */
    .tds-export-group {
        grid-column: span 1;
        display: flex;
        gap: 10px;
        justify-content: flex-start;
        align-items: center;
    }

    .tds-export-group .btn {
        flex: 1;
        min-width: 80px;
        white-space: nowrap;
        font-size: 13px;
        padding: 6px 10px;
    }

    /* Specific select widths on tablet */
    #date-filter,
    #month-filter,
    #year-filter,
    #customer-filter {
        font-size: 13px;
        padding: 5px 8px;
    }

    /* Label adjustments if any labels exist */
    .tds-filter-row label {
        font-size: 12px;
        margin-bottom: 4px;
        display: block;
    }

    /* --- TABLE FIXES --- */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .tds-table {
        min-width: 750px;
        font-size: 13px;
    }

    .tds-table th,
    .tds-table td {
        padding: 10px 8px;
        white-space: normal;
        word-break: break-word;
    }

    /* Column width management - keep all columns visible but scrollable */
    .tds-table th:nth-child(1),
    .tds-table td:nth-child(1) {
        min-width: 100px;
    }

    .tds-table th:nth-child(2),
    .tds-table td:nth-child(2) {
        min-width: 60px;
        text-align: center;
    }

    .tds-table th:nth-child(3),
    .tds-table td:nth-child(3) {
        min-width: 100px;
    }

    .tds-table th:nth-child(4),
    .tds-table td:nth-child(4) {
        min-width: 120px;
    }

    .tds-table th:nth-child(5),
    .tds-table td:nth-child(5),
    .tds-table th:nth-child(6),
    .tds-table td:nth-child(6),
    .tds-table th:nth-child(7),
    .tds-table td:nth-child(7) {
        min-width: 90px;
        text-align: right;
    }

    .tds-table th:nth-child(8),
    .tds-table td:nth-child(8) {
        min-width: 110px;
    }

    .tds-table th:nth-child(9),
    .tds-table td:nth-child(9) {
        min-width: 80px;
        text-align: center;
    }

    /* Action button inside table */
    .tds-table td:last-child a.btn {
        padding: 4px 8px;
        font-size: 12px;
    }

    /* Hide the DETAILS column (2nd column) on tablets - use expandable rows instead */
    .tds-table thead th:nth-child(2),
    .tds-table tbody td:nth-child(2) {
        display: none;
    }

    /* Toggle button adjustments - visible on tablet via expandable rows */
    .tds-toggle-btn-table {
        width: 30px;
        height: 30px;
        font-size: 16px;
        background: #ff9f43;
        color: #fff;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }

    .tds-toggle-btn-table.minus {
        background: #dc3545;
    }

    .tds-toggle-btn-table:hover {
        transform: scale(1.05);
    }

    /* Expandable details row styling */
    .tds-details-row.show {
        display: table-row;
    }

    .tds-details-content {
        padding: 15px !important;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    /* 2-column grid for expandable details on tablet */
    .tds-details-list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px 20px;
    }

    .tds-detail-row-simple {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        border-bottom: 1px solid #eef2f6;
    }

    .tds-detail-row-simple:last-child {
        border-bottom: none;
    }

    .tds-detail-label-simple {
        font-weight: 600;
        color: #4a5568;
        font-size: 12px;
    }

    .tds-detail-value-simple {
        color: #2d3748;
        font-size: 12px;
        font-weight: 500;
        text-align: right;
    }

    .tds-action-buttons-simple {
        grid-column: span 2;
        display: flex;
        gap: 12px;
        justify-content: flex-start;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #e2e8f0;
    }

    .tds-action-buttons-simple .btn {
        font-size: 12px;
        padding: 4px 12px;
    }

    /* --- TOTALS SECTION FIXES --- */
    .tds-total-highlight {
        font-size: 15px;
    }

    .tds-total-highlight span {
        font-size: 16px;
    }

    /* --- PAGINATION FIXES --- */
    .pagination-controls {
        flex-direction: row !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 15px;
        flex-wrap: wrap;
    }

    .pagination .page-link {
        padding: 5px 12px;
        font-size: 13px;
    }

    .pagination .page-item.active .page-link {
        background-color: #ff9f43 !important;
        border-color: #ff9f43 !important;
    }

    /* Per page selector */
    #per-page-select {
        font-size: 13px;
        padding: 4px 8px;
    }

    /* --- TABLE SCROLL INDICATOR --- */
    .table-responsive {
        position: relative;
    }

    .table-responsive::after {
        content: "← Scroll horizontally →";
        display: none;
        text-align: center;
        font-size: 11px;
        color: #94a3b8;
        padding: 8px 0 4px;
        background: #f1f5f9;
        border-radius: 0 0 8px 8px;
    }

    .table-responsive:hover::after {
        display: block;
    }

    /* Custom scrollbar for table */
    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #e2e8f0;
        border-radius: 10px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #94a3b8;
        border-radius: 10px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }
}

/* ============================================
   LANDSCAPE TABLET (1024px specific)
   ============================================ */
@media screen and (min-width: 1024px) and (max-width: 1024px) and (orientation: landscape) {
    .tds-filter-row {
        grid-template-columns: repeat(4, 1fr);
    }

    .tds-filter-row .search-set {
        grid-column: span 1;
    }

    .tds-export-group {
        grid-column: span 1;
        justify-content: flex-end;
    }
}

/* ============================================
   PORTRAIT TABLET (768px - 820px)
   ============================================ */
@media screen and (min-width: 768px) and (max-width: 820px) and (orientation: portrait) {
    .tds-filter-row {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .tds-filter-row .search-set {
        grid-column: span 2;
    }

    .tds-export-group {
        grid-column: span 2;
        justify-content: stretch;
    }

    .tds-export-group .btn {
        flex: 1;
    }

    .tds-summary {
        text-align: center;
        margin-top: 5px;
        width: 100%;
            /* text-align: center; */
    }

    .tds-header-right {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    /* 1 column for expandable details on portrait tablet */
    .tds-details-list {
        grid-template-columns: 1fr;
    }

    .tds-action-buttons-simple {
        grid-column: span 1;
    }
}

/* ============================================
   IPAD SPECIFIC TOUCH TARGETS
   ============================================ */
@media only screen and (min-device-width: 768px) and (max-device-width: 1024px) {
    /* Ensure touch targets are at least 44px for accessibility */
    .tds-toggle-btn-table,
    .tds-export-group .btn,
    .pagination .page-link,
    .tds-table td:last-child a.btn,
    #per-page-select,
    .form-select-sm {
        min-height: 40px;
    }

    .tds-toggle-btn-table {
        min-height: 32px;
        min-width: 32px;
    }

    /* Better focus states for touch */
    .tds-toggle-btn-table:focus,
    .btn:focus,
    .page-link:focus {
        outline: 2px solid #ff9f43;
        outline-offset: 2px;
    }

    /* Improve select dropdown readability */
    .form-select-sm {
        background-position: right 8px center;
        padding-right: 24px;
    }
}

/* ============================================
   TRANSITION ANIMATIONS
   ============================================ */
.tds-details-row {
    transition: all 0.2s ease;
}

.tds-toggle-btn-table {
    transition: all 0.2s ease;
}

.tds-toggle-btn-table.minus {
    transform: rotate(0deg);
}

.tds-mobile-order-ref {
    display: block;
    font-weight: 500;
}

.tds-mobile-customer {
    display: none;
}

        /* Extra small devices (phones, less than 576px) */
        @media screen and (max-width: 575.98px) {
            .tds-table {
                font-size: 11px;
            }

            .tds-table th,
            .tds-table td {
                padding: 6px 3px;
            }

            /* Hide columns on mobile */
            .tds-table thead th:nth-child(3),
            .tds-table tbody td:nth-child(3),
            .tds-table thead th:nth-child(4),
            .tds-table tbody td:nth-child(4),
            .tds-table thead th:nth-child(5),
            .tds-table tbody td:nth-child(5),
            .tds-table thead th:nth-child(6),
            .tds-table tbody td:nth-child(6),
            .tds-table thead th:nth-child(7),
            .tds-table tbody td:nth-child(7),
            .tds-table thead th:nth-child(8),
            .tds-table tbody td:nth-child(8),
            .tds-table thead th:nth-child(9),
            .tds-table tbody td:nth-child(9) {
                display: none;
            }

            /* Center Details column */
            .tds-table thead th.details-column,
            .tds-table tbody td.details-cell {
                display: table-cell !important;
                text-align: center;
                width: 60px;
                min-width: 60px;
            }

            .tds-toggle-btn-table {
                margin: 0 auto;
                display: block;
                background: #ff9f43;
                color: #fff;
                border: none;
                border-radius: 4px;
                width: 24px;
                height: 24px;
                padding: 0;
                line-height: 24px;
                font-weight: bold;
            }

            .tds-toggle-btn-table.minus {
                background: #d10b2c;
            }

            .tds-mobile-customer {
                display: block;
                margin-top: 4px;
                font-size: 14px;
                line-height: 1.4;
                color: #495057;
                font-weight: 500;
                white-space: normal;
                word-break: break-word;
                overflow-wrap: anywhere;
            }
        }

        /* Small devices (landscape phones, 576px and up) */
        @media screen and (min-width: 576px) and (max-width: 767.98px) {
            .tds-table {
                font-size: 12px;
            }

            .tds-table th,
            .tds-table td {
                padding: 8px 4px;
            }

            /* Hide some columns */
            .tds-table thead th:nth-child(5),
            .tds-table tbody td:nth-child(5),
            .tds-table thead th:nth-child(6),
            .tds-table tbody td:nth-child(6),
            .tds-table thead th:nth-child(7),
            .tds-table tbody td:nth-child(7),
            .tds-table thead th:nth-child(8),
            .tds-table tbody td:nth-child(8),
            .tds-table thead th:nth-child(9),
            .tds-table tbody td:nth-child(9) {
                display: none;
            }

            /* Show Details column */
            .tds-table thead th.details-column,
            .tds-table tbody td.details-cell {
                display: table-cell !important;
                text-align: center;
                width: 60px;
                min-width: 60px;
            }

            .tds-toggle-btn-table {
                margin: 0 auto;
                display: block;
                background: #ff9f43;
                color: #fff;
                border: none;
                border-radius: 4px;
                width: 24px;
                height: 24px;
                padding: 0;
                line-height: 24px;
                font-weight: bold;
            }

            .tds-mobile-customer {
                display: block;
                margin-top: 4px;
                font-size: 14px;
                line-height: 1.4;
                color: #495057;
                font-weight: 500;
                white-space: normal;
                word-break: break-word;
                overflow-wrap: anywhere;
            }
        }

        /* Medium devices and up */
        @media screen and (min-width: 768px) {
            .details-column, .details-cell {
                display: none !important;
            }
        }

        /* Expandable row details */
        .tds-details-row {
            display: none;
        }

        .tds-details-row.show {
            display: table-row;
        }

        .tds-details-content {
            padding: 15px !important;
            background: #f8fafc;
            border-top: 2px solid #e2e8f0;
        }

        .tds-details-list {
            margin-bottom: 10px;
        }

        .tds-detail-row-simple {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .tds-detail-row-simple:last-child {
            border-bottom: none;
        }

        .tds-detail-label-simple {
            font-weight: 600;
            color: #4a5568;
        }

        .tds-detail-value-simple {
            color: #2d3748;
        }

        .tds-action-buttons-simple {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            justify-content: center;
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
            color: #334155;
            font-weight: 600;
        }

        .download-loader-box p {
            margin: 8px 0 18px 0;
            font-size: 34px;
            color: #334155;
            font-weight: 500;
        }

        @media (max-width: 991.98px) {
            .tds-filter-row {
                flex-wrap: wrap;
                gap: 10px;
            }

            .tds-filter-row .search-set {
                width: 100%;
                flex: 1 1 100%;
            }

            .tds-filter-row .form-select-sm {
                flex: 0 0 calc(50% - 5px) !important;
                max-width: calc(100% - 5px) !important;
                /* min-width: 100% !important; */
            }

            .tds-total-highlight {
                width: 100%;
                min-width: 0;
                font-size: 18px;
            }

            .tds-header-right {
                width: 100%;
                margin-left: 0;
            }

            .tds-export-group {
                margin-left: 0;
                width: 100%;
            }

            .tds-export-group .btn {
                flex: 1 1 auto;
            }
        }
        .table tbody tr td a:hover{
            color: #fff !important;
        }.table tbody tr td a{
            color: #fff !important;
        }
    </style>

    <div class="content">
        <div class="page-header d-flex align-items-center flex-wrap gap-2">
            <div class="page-title">
                <h4>TDS Report</h4>
                <h6>Order-wise TDS details</h6>
            </div>
            <div class="tds-header-right">
                <div class="tds-total-highlight">
                    Total TDS: <span id="highlight-total-tds">&#8377;0.00</span>
                </div>
                <div id="tds-summary" class="tds-summary">
                    Orders: <span id="summary-total-orders">0</span> |
                    Avg TDS %: <span id="summary-average-tds">0.00%</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="tds-filter-row">
                    <div class="search-set">
                        <div class="search-input">
                            <span class="tds-search-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"></circle>
                                    <path d="M20 20L16.65 16.65" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round"></path>
                                </svg>
                            </span>
                            <input type="text" id="search-input" class="form-control"
                                placeholder="Search order no, customer, status...">
                        </div>
                    </div>

                    <select id="date-filter" class="form-select form-select-sm">
                        <option value="">All Time</option>
                        <option value="this_week">This Week</option>
                        <option value="this_month">This Month</option>
                        <option value="last_6_months">Last 6 Months</option>
                        <option value="this_year">This Year</option>
                        <option value="previous_year">Previous Year</option>
                    </select>

                    <select id="month-filter" class="form-select form-select-sm">
                        <option value="">All Months</option>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>

                    <select id="year-filter" class="form-select form-select-sm">
                        <option value="">All Years</option>
                        @for ($y = date('Y'); $y >= date('Y') - 10; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>

                    <select id="customer-filter" class="form-select form-select-sm">
                        <option value="">All Customers</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>

                    <div class="tds-export-group">
                        <button id="export-excel-btn" type="button" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button id="export-pdf-btn" type="button" class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table tds-table" id="tds-report-table">
                        <thead>
                            <tr>
                                <th>Order No.</th>
                                <th class="details-column">Details</th>
                                <th>Order Date</th>
                                <th>Customer</th>
                                <th>Total Amount</th>
                                <th>TDS %</th>
                                <th>TDS Amount</th>
                                <th>Payment Status</th>
                                <th>Invoice</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center" colspan="9">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <span class="me-2" style="font-size: 14px; color: #555;">Show per page :</span>
                        <select id="per-page-select" class="form-select form-select-sm"
                            style="width: auto; border: 1px solid #ddd;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-3" style="font-size: 14px; color: #555;">
                            <span id="pagination-from">0</span> - <span id="pagination-to">0</span> of <span
                                id="pagination-total">0</span> items
                        </span>
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0" id="pagination-numbers"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div id="downloadLoaderOverlay" class="download-loader-overlay d-none" aria-live="polite" aria-busy="true">
        <div class="download-loader-box">
            <h4 id="downloadLoaderText">Generating PDF...</h4>
            <p>Please wait</p>
            <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        // Global for mobile view
        var tdsDataMap = {};

        // Helper to build expandable row
        function buildTdsExpandableRowContent(item) {
            function formatCurrencyLocal(amount) {
                const numericAmount = parseFloat(amount || 0);
                return '&#8377;' + numericAmount.toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            return `
                <td colspan="9" class="tds-details-content">
                    <div class="tds-details-list">
                        <div class="tds-detail-row-simple">
                            <span class="tds-detail-label-simple">Order Date:</span>
                            <span class="tds-detail-value-simple">${item.order_date || '-'}</span>
                        </div>
                        <div class="tds-detail-row-simple">
                            <span class="tds-detail-label-simple">Customer:</span>
                            <span class="tds-detail-value-simple">${item.customer_name || '-'}</span>
                        </div>
                        <div class="tds-detail-row-simple">
                            <span class="tds-detail-label-simple">Total Amount:</span>
                            <span class="tds-detail-value-simple" style="font-weight: bold;">${formatCurrencyLocal(item.total_amount)}</span>
                        </div>
                        <div class="tds-detail-row-simple">
                            <span class="tds-detail-label-simple">TDS %:</span>
                            <span class="tds-detail-value-simple">${item.tds_percentage_display || '0.00'}%</span>
                        </div>
                        <div class="tds-detail-row-simple">
                            <span class="tds-detail-label-simple">TDS Amount:</span>
                            <span class="tds-detail-value-simple" style="color: #ff9f43; font-weight: bold;">${formatCurrencyLocal(item.tds_amount)}</span>
                        </div>
                        <div class="tds-detail-row-simple">
                            <span class="tds-detail-label-simple">Payment Status:</span>
                            <span class="tds-detail-value-simple"><span class="badge bg-light text-dark text-capitalize">${item.payment_status || '-'}</span></span>
                        </div>
                    </div>
                    <div class="tds-action-buttons-simple">
                        <a class="btn btn-sm btn-primary" href="${item.invoice_url}" target="_blank">View Invoice</a>
                    </div>
                </td>
            `;
        }

        // Toggle function
        window.toggleTdsRowDetails = function(orderId) {
            const btn = $(`.tds-toggle-btn-table[data-order-id="${orderId}"]`);
            if (btn.length === 0) return;

            const row = btn.closest('tr');
            let detailsRow = row.next(`tr.tds-details-row[data-order-id="${orderId}"]`);
            const icon = btn.find('.toggle-icon');

            if (detailsRow.length === 0) {
                const item = tdsDataMap[orderId];
                if (item) {
                    detailsRow = $('<tr>')
                        .addClass('tds-details-row')
                        .attr('data-order-id', orderId)
                        .html(buildTdsExpandableRowContent(item));
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
            const authToken = localStorage.getItem('authToken');
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            const reportApiUrl = '/api/tds-order-report';
            const exportExcelApiUrl = '/api/tds-order-report/export-excel';
            const exportPdfApiUrl = '/api/tds-order-report/export-pdf';
            const $downloadLoader = $('#downloadLoaderOverlay');
            const $downloadLoaderText = $('#downloadLoaderText');
            const $exportButtons = $('#export-excel-btn, #export-pdf-btn');
            let currentPage = 1;
            let lastPage = 1;
            let perPage = 10;
            let searchQuery = '';
            let searchDebounceTimer = null;

            function formatCurrency(amount) {
                const numericAmount = parseFloat(amount || 0);
                return '&#8377;' + numericAmount.toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function getFilters(overrides = {}) {
                return {
                    filter: $('#date-filter').val(),
                    month: $('#month-filter').val(),
                    year: $('#year-filter').val(),
                    customer_id: $('#customer-filter').val(),
                    search: searchQuery,
                    per_page: perPage,
                    page: currentPage,
                    selectedSubAdminId: selectedSubAdminId,
                    ...overrides
                };
            }

            function getExportFilters() {
                return {
                    filter: $('#date-filter').val(),
                    month: $('#month-filter').val(),
                    year: $('#year-filter').val(),
                    customer_id: $('#customer-filter').val(),
                    search: searchQuery,
                    selectedSubAdminId: selectedSubAdminId
                };
            }

            function toggleDownloadLoader(show, text) {
                if (show) {
                    $downloadLoaderText.text(text || 'Generating PDF...');
                    $downloadLoader.removeClass('d-none');
                    $exportButtons.prop('disabled', true).addClass('disabled').attr('aria-disabled', 'true');
                    return;
                }

                $downloadLoader.addClass('d-none');
                $exportButtons.prop('disabled', false).removeClass('disabled').removeAttr('aria-disabled');
            }

            function getFilenameFromHeader(contentDisposition, fallbackName) {
                if (!contentDisposition) {
                    return fallbackName;
                }

                const match = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/i);
                if (!match || !match[1]) {
                    return fallbackName;
                }

                return match[1].replace(/['"]/g, '');
            }

            function downloadReport(url, params, fallbackFilename, loaderMessage) {
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: params,
                    headers: {
                        Authorization: 'Bearer ' + authToken
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    beforeSend: function() {
                        toggleDownloadLoader(true, loaderMessage);
                    },
                    success: function(blob, status, xhr) {
                        const contentDisposition = xhr.getResponseHeader('Content-Disposition');
                        const filename = getFilenameFromHeader(contentDisposition, fallbackFilename);
                        const objectUrl = window.URL.createObjectURL(blob);
                        const link = document.createElement('a');

                        link.href = objectUrl;
                        link.download = filename;
                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                        window.URL.revokeObjectURL(objectUrl);
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Export Failed',
                            text: xhr.responseJSON?.message || 'Unable to generate file for the selected filters.',
                            confirmButtonColor: '#ff9f43'
                        });
                    },
                    complete: function() {
                        toggleDownloadLoader(false);
                    }
                });
            }

            function renderEmptyTable(message) {
                $('#tds-report-table tbody').html(`
                    <tr>
                        <td class="text-center" colspan="9">${message}</td>
                    </tr>
                `);
            }

            function destroyReportTableIfExists() {
                if ($.fn.DataTable.isDataTable('#tds-report-table')) {
                    $('#tds-report-table').DataTable().destroy();
                }
            }

            function updateSummary(response) {
                const summary = response.summary || {};
                const totalTdsHtml = formatCurrency(summary.total_tds_amount || 0);

                $('#summary-total-orders').text(summary.total_orders || 0);
                $('#summary-total-tds').html(totalTdsHtml);
                $('#highlight-total-tds').html(totalTdsHtml);
                $('#summary-average-tds').text((summary.average_tds_percentage || 0).toFixed(2) + '%');
            }

            function fetchTdsReport(page = 1) {
                currentPage = page;

                $.ajax({
                    url: reportApiUrl,
                    type: 'GET',
                    data: getFilters({
                        page: page
                    }),
                    headers: {
                        Authorization: 'Bearer ' + authToken
                    },
                    success: function(response) {
                        if (!response.status || !response.data || !response.data.length) {
                            destroyReportTableIfExists();
                            updateSummary(response);
                            renderEmptyTable('No TDS records found.');
                            $('#pagination-numbers').html('');
                            $('#pagination-from, #pagination-to, #pagination-total').text('0');
                            return;
                        }

                        updateSummary(response);
                        const pagination = response.pagination || {};
                        currentPage = pagination.current_page || 1;
                        lastPage = pagination.last_page || 1;

                        $('#pagination-from').text(pagination.from || 0);
                        $('#pagination-to').text(pagination.to || 0);
                        $('#pagination-total').text(pagination.total || 0);

                        let paginationHtml = '';
                        // let startPage = Math.max(1, currentPage - 2);
                        // let endPage = Math.min(lastPage, startPage + 4);
                        // if (endPage - startPage < 4 && startPage > 1) {
                        //     startPage = Math.max(1, endPage - 4);
                        // }
                        let isMobile = $(window).width() < 576;

                        // Previous Button
                        paginationHtml += `
                            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                                <a class="page-link tds-page-link" href="javascript:void(0);" data-page="${currentPage - 1}">Previous</a>
                            </li>
                        `;

                         if (lastPage <= (isMobile ? 3 : 5)) {
                for (let i = 1; i <= lastPage; i++) {
                    paginationHtml += `
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `;
                }
            } else {
                // First Page
                paginationHtml += `
                    <li class="page-item ${currentPage === 1 ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="1">1</a>
                    </li>
                `;

                if (currentPage > 2) {
                    paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }

                // Current Page (if not first or last)
                if (currentPage !== 1 && currentPage !== lastPage) {
                    paginationHtml += `
                        <li class="page-item active">
                            <a class="page-link" href="#" data-page="${currentPage}">${currentPage}</a>
                        </li>
                    `;
                }

                if (currentPage < lastPage - 1) {
                    paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }

                // Last Page
                paginationHtml += `
                    <li class="page-item ${currentPage === lastPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${lastPage}">${lastPage}</a>
                                </li>
                            `;
                        }

                        // Next Button
                        paginationHtml += `
                            <li class="page-item ${currentPage === lastPage || lastPage === 0 ? 'disabled' : ''}">
                                <a class="page-link tds-page-link" href="#" data-page="${currentPage + 1}">Next</a>
                            </li>
                        `;

                        $('#pagination-numbers').html(paginationHtml);

                        let tbodyHtml = '';
                        tdsDataMap = {}; // Reset map
                        $.each(response.data, function(index, item) {
                            const orderId = item.id || index; // Fallback if id missing
                            tdsDataMap[orderId] = item;

                            const detailsToggle = `
                                <td class="details-cell">
                                    <button class="tds-toggle-btn-table" style="border-radius: 50%;" onclick="toggleTdsRowDetails('${orderId}')" data-order-id="${orderId}">
                                        <span class="toggle-icon">+</span>
                                    </button>
                                </td>
                            `;

                            tbodyHtml += `
                                <tr>
                                    <td>
                                        <span class="tds-mobile-customer">${item.customer_name || '-'}</span>
                                        <span class="tds-mobile-order-ref">${item.order_number || '-'}</span>
                                    </td>
                                    ${detailsToggle}
                                    <td>${item.order_date || '-'}</td>
                                    <td>${item.customer_name || '-'}</td>
                                    <td>${formatCurrency(item.total_amount)}</td>
                                    <td>${item.tds_percentage_display || '0.00'}%</td>
                                    <td>${formatCurrency(item.tds_amount)}</td>
                                    <td><span class="badge bg-light text-dark text-capitalize">${item.payment_status || '-'}</span></td>
                                    <td>
                                        <a class="btn btn-sm btn-primary" href="${item.invoice_url}" target="_blank">View</a>
                                    </td>
                                </tr>
                            `;
                        });

                        destroyReportTableIfExists();

                        $('#tds-report-table tbody').html(tbodyHtml);

                        $('#tds-report-table').DataTable({
                            responsive: true,
                            dom: 't',
                            paging: false,
                            searching: false,
                            info: false
                        });
                    },
                    error: function() {
                        destroyReportTableIfExists();
                        renderEmptyTable('Failed to load TDS report data.');
                    }
                });
            }

            $('#date-filter, #month-filter, #year-filter, #customer-filter').on('change', function() {
                fetchTdsReport(1);
            });

            $('#per-page-select').on('change', function() {
                perPage = parseInt($(this).val(), 10) || 10;
                fetchTdsReport(1);
            });

            $('#search-input').on('input', function() {
                searchQuery = $(this).val().trim();
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(function() {
                    fetchTdsReport(1);
                }, 400);
            });

            $(document).on('click', '.tds-page-link', function(e) {
                e.preventDefault();
                const page = parseInt($(this).data('page'), 10);
                if (page && page !== currentPage) {
                    fetchTdsReport(page);
                }
            });

            $('#export-excel-btn').on('click', function() {
                if ($(this).prop('disabled')) {
                    return;
                }

                downloadReport(
                    exportExcelApiUrl,
                    getExportFilters(),
                    'tds_report.xlsx',
                    'Generating Excel...'
                );
            });

            $('#export-pdf-btn').on('click', function() {
                if ($(this).prop('disabled')) {
                    return;
                }

                downloadReport(
                    exportPdfApiUrl,
                    getExportFilters(),
                    'tds_report.pdf',
                    'Generating PDF...'
                );
            });

            // Resize handler for responsive behavior
            let tdsResizeTimer;
            function handleTdsResize() {
                clearTimeout(tdsResizeTimer);
                tdsResizeTimer = setTimeout(function() {
                    const currentWidth = $(window).width();
                    if (currentWidth >= 768) {
                        $('.tds-details-row').remove();
                        $('.tds-toggle-btn-table').removeClass('minus').find('.toggle-icon').text('+');
                    }
                }, 50);
            }

            $(window).on('resize', handleTdsResize);
            $(window).on('orientationchange', function() {
                setTimeout(handleTdsResize, 300);
            });

            fetchTdsReport(1);
        });
    </script>
@endpush
