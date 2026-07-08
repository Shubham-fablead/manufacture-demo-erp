@extends('layout.app')

@section('title', 'Sales List')

@section('content')
    <style>
        /* Status Badge Styles */
        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
            display: inline-block;
            color: white !important;
            min-width: 80px;
            text-align: center;
        }

        /* Payment Status Colors - Using only 4 colors */
        .status-pending {
            background-color: #ea5455 !important;
            /* Danger - Red */
        }

        .status-completed {
            background-color: #28c76f !important;
            /* Success - Green */
        }

        .status-partially {
            background-color: #f90 !important;
            /* Info - Teal */
        }

        /* Payment Method Colors - Using only 4 colors */
        .status-cash {
            background-color: #f90 !important;
            /* Info - Teal */
        }

        .status-online {
            background-color: #28c76f !important;
            /* Success - Green */
        }

        .status-emi {
            background-color: #ea5455 !important;
            /* Danger - Red */
        }

        .status-cash_online {
            background-color: #28c76f !important;
            /* Success - Green */
        }

        a.btn.btn-sm.btn-success.me-2 {
            color: white;
            border: none;
            padding: 4px;
            font-size: 11px;
        }

        /* Quotation / Sales badges */
        .status-quotation {
            background-color: #ff9f43 !important;
            /* Orange */
        }

        .status-sales {
            background-color: #28c76f !important;
            /* Green */
        }

        /* Unknown/Other payment method */
        .status-other {
            background-color: #8f99a2 !important;
            /* Gray */
        }

        /* Mobile View Status Styles */
        .mobile-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            text-transform: capitalize;
            font-weight: 500;
            color: white !important;
            display: inline-block;
            min-width: 70px;
            text-align: center;
        }

        .table-scroll-top {
            display: none;
        }

        /* For Customer Name column */
        .datanew td:nth-child(4) {
            white-space: normal !important;
            word-wrap: break-word;
            max-width: 200px;
            /* optional */
        }

        /* For Biller column */
        .datanew td:nth-child(9) {
            max-width: 260px;
        }

        .biller-wrap {
            display: inline-block;
            max-width: 260px;
            white-space: normal !important;
            overflow-wrap: anywhere;
            word-break: break-word;
            line-height: 1.3;
        }


        .form-control {
            color: #595b5d !important;
            /* Bootstrap's default placeholder/input text color */
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

        .custom-select2 .select2-container--default .select2-selection--single {
            height: 31px !important;
            border: 1px solid #ced4da !important;
            border-radius: 4px !important;
        }

        .custom-select2 .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px !important;
            font-size: 14px !important;
            color: #595b5d !important;
            padding-left: 8px !important;
        }

        .custom-select2 .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 30px !important;
        }

        .form-group {
            margin-bottom: 8px !important;
        }

        /* Mobile View Styles */
        .mobile-order-card {
            display: none;
        }

        .order-mobile-summary {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 2px;
        }

        .order-mobile-link {
            color: inherit;
            text-decoration: none;
        }

        .order-mobile-customer {
            display: none;
            font-size: 12px;
            color: #595b5d;
            line-height: 1.3;
            word-break: break-word;
        }





        /* Responsive breakpoints for all screen sizes */

        /* Extra small devices (phones, less than 576px) */
        @media screen and (max-width: 575.98px) {
            .table-responsive {
                display: block !important;
                overflow-x: hidden;
                -webkit-overflow-scrolling: touch;
                width: 100% !important;
            }

            .mobile-order-card {
                display: none;
            }

            #order-table,
            #order-table_wrapper,
            .datanew {
                font-size: 11px;
                width: 100% !important;
                max-width: 100% !important;
                table-layout: fixed;
            }

            .datanew th,
            .datanew td {
                padding: 6px 3px;
            }

            .datanew th:nth-child(1),
            .datanew td:nth-child(1) {
                width: calc(100% - 56px) !important;
            }

            .order-mobile-customer {
                display: block;
            }

            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                width: 100% !important;
                min-width: 100% !important;
                max-width: 100% !important;
                /* text-align: center; */
                display: table-cell !important;
            }

            /* Show only Order Number and Details */
            .datanew th:nth-child(3),
            .datanew td:nth-child(3),
            .datanew th:nth-child(4),
            .datanew td:nth-child(4),
            .datanew th:nth-child(5),
            .datanew td:nth-child(5),
            .datanew th:nth-child(6),
            .datanew td:nth-child(6),
            .datanew th:nth-child(7),
            .datanew td:nth-child(7),
            .datanew th:nth-child(8),
            .datanew td:nth-child(8),
            .datanew th:nth-child(9),
            .datanew td:nth-child(9),
            .datanew th:nth-child(10),
            .datanew td:nth-child(10) {
                display: none;
            }

            .datanew th:nth-child(n+3),
            .datanew td:nth-child(n+3) {
                display: none !important;
                width: 0 !important;
                min-width: 0 !important;
                max-width: 0 !important;
                padding: 0 !important;
                border: 0 !important;
            }
        }

        /* Center Details column */
        .datanew th:nth-child(2),
        .datanew td:nth-child(2) {
            text-align: right;
        }

        /* Small devices (landscape phones, 576px and up) */
        @media screen and (min-width: 576px) and (max-width: 767.98px) {
            .table-responsive {
                display: block !important;
                overflow-x: hidden;
                width: 100% !important;
            }

            .mobile-order-card {
                display: none;
            }

            #order-table,
            #order-table_wrapper,
            .datanew {
                font-size: 12px;
                width: 100% !important;
                max-width: 100% !important;
                table-layout: fixed;
            }

            .datanew th,
            .datanew td {
                padding: 8px 4px;
            }

            .datanew th:nth-child(1),
            .datanew td:nth-child(1) {
                width: calc(100% - 56px) !important;
            }

            .order-mobile-customer {
                display: block;
            }

            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                width: 56px !important;
                min-width: 56px !important;
                max-width: 56px !important;
                /* text-align: center; */
                display: table-cell !important;
            }

            /* Show only Order Number and Details */
            .datanew th:nth-child(3),
            .datanew td:nth-child(3),
            .datanew th:nth-child(4),
            .datanew td:nth-child(4),
            .datanew th:nth-child(5),
            .datanew td:nth-child(5),
            .datanew th:nth-child(6),
            .datanew td:nth-child(6),
            .datanew th:nth-child(7),
            .datanew td:nth-child(7),
            .datanew th:nth-child(8),
            .datanew td:nth-child(8),
            .datanew th:nth-child(9),
            .datanew td:nth-child(9),
            .datanew th:nth-child(10),
            .datanew td:nth-child(10) {
                display: none !important;
                width: 0 !important;
                min-width: 0 !important;
                max-width: 0 !important;
                padding: 0 !important;
                border: 0 !important;
            }

            .datanew th:nth-child(11),
            .datanew td:nth-child(11) {
                display: none !important;
                width: 0 !important;
                min-width: 0 !important;
                max-width: 0 !important;
                padding: 0 !important;
                border: 0 !important;
            }

            /* Center Details column */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                /* text-align: center; */
            }
        }

        /* Medium devices (tablets, 768px and up) */
        @media screen and (min-width: 768px) and (max-width: 991.98px) {
            .table-responsive {
                display: block !important;
                overflow-x: auto;
                overflow-y: hidden;
                -webkit-overflow-scrolling: touch;
            }

            .mobile-order-card {
                display: none;
            }

            #order-table,
            #order-table_wrapper,
            .datanew {
                min-width: 980px;
            }

            .datanew {
                font-size: 13px;
            }

            .datanew th,
            .datanew td {
                padding: 8px 6px;
            }

            /* Hide Details column (2nd column) on 768px and above */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                display: none;
            }

            /* Hide expandable rows on larger screens */
            .order-details-row {
                display: none !important;
            }
        }

        /* Large devices (desktops, 992px and up) */
        @media screen and (min-width: 992px) and (max-width: 1199.98px) {
            .table-responsive {
                display: block !important;
                overflow-x: auto;
                overflow-y: hidden;
                -webkit-overflow-scrolling: touch;
            }

            .mobile-order-card {
                display: none;
            }

            .datanew {
                font-size: 14px;
            }

            .datanew th,
            .datanew td {
                padding: 10px 8px;
            }

            /* Hide Details column (2nd column) on 768px and above */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                display: none;
            }

            /* Hide expandable rows on larger screens */
            .order-details-row {
                display: none !important;
            }
        }

        /* Extra large devices (large desktops, 1200px and up) */
        @media screen and (min-width: 1200px) {
            .table-responsive {
                display: block !important;
            }

            .mobile-order-card {
                display: none;
            }

            .datanew {
                font-size: 14px;
            }

            .datanew th,
            .datanew td {
                padding: 12px 10px;
            }

            /* Hide Details column (2nd column) on 768px and above */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                display: none;
            }

            /* Hide expandable rows on larger screens */
            .order-details-row {
                display: none !important;
            }
        }

        /* Expandable row details - available for all screen sizes */
        .order-details-row {
            display: none;
        }

        .order-details-row.show {
            display: table-row;
        }


        /* Toggle button styles - available for all screen sizes */
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
            line-height: 1;
            padding: 0;
            flex-shrink: 0;
            appearance: none;
            transition: all 0.3s;
        }

        .mobile-toggle-btn-table .toggle-icon {
            display: inline-block;
            width: 100%;
            text-align: center;
            line-height: 1;
            font-size: 20px;
            font-weight: 700;
            font-family: Arial, sans-serif;
            color: inherit;
            pointer-events: none;
            transform: translateY(-1px);
        }

        .mobile-toggle-btn-table:hover {
            background: #ff8c2e;
        }

        .mobile-toggle-btn-table.minus {
            background: #dc3545;
        }

        .mobile-toggle-btn-table.minus:hover {
            background: #c82333;
        }

        /* Expandable content styles - available for all screen sizes */
        .order-details-content {
            padding: 15px;
            background: #fff;
            border-top: 2px solid #e0e0e0;
        }

        .order-details-list {
            margin-bottom: 15px;
        }

        .order-detail-row-simple {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            flex-wrap: wrap;
            gap: 8px;
        }

        .order-detail-row-simple:last-of-type {
            border-bottom: none;
        }

        .order-detail-label-simple {
            font-weight: 600;
            color: #595b5d;
            font-size: 14px;
            flex: 0 1 auto;
            min-width: 120px;
        }

        .order-detail-value-simple {
            color: #1b2850;
            font-size: 14px;
            text-align: right;
            flex: 1 1 auto;
            word-break: break-word;
        }

        .mobile-action-buttons-simple {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-start;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .btn-icon-mobile,
        button.btn-icon-mobile {
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

        button.btn-icon-mobile {
            border: 2px solid #1b2850;
            background: transparent;
        }

        .btn-icon-mobile:hover {
            background: #1b2850;
            color: white;
            transform: scale(1.1);
        }

        .btn-icon-mobile i {
            font-size: 16px;
        }

        .btn-icon-mobile.btn-history i {
            font-size: 18px;
        }

        .btn-icon-mobile.btn-view i,
        .btn-icon-mobile.btn-edit i,
        .btn-icon-mobile.btn-download i,
        .btn-icon-mobile.btn-print i,
        .btn-icon-mobile.btn-delete i,
        .btn-icon-mobile.btn-payment i {
            font-size: 16px;
        }

        .sales-action-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .sales-action-toggle {
            width: 34px;
            height: 34px;
            border: 1px solid #cfd7e3;
            border-radius: 6px;
            background: #fff;
            color: #344054;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
        }

        .sales-action-toggle:hover {
            background: #f8fafc;
            border-color: #b8c4d6;
            color: #111827;
        }

        .sales-action-toggle:focus {
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(27, 40, 80, 0.15);
        }

        .sales-action-toggle i {
            font-size: 18px;
            line-height: 1;
        }

        .sales-action-menu {
            min-width: 170px;
        }

        .sales-action-menu .dropdown-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Additional responsive adjustments for larger screens */
        @media screen and (min-width: 992px) {
            .order-details-content {
                padding: 20px;
            }

            .order-detail-label-simple,
            .order-detail-value-simple {
                font-size: 15px;
            }

            .btn-icon-mobile {
                width: 42px;
                height: 42px;
            }

            .btn-icon-mobile i {
                font-size: 17px;
            }
        }

        .mobile-order-header-row {
            display: table;
            width: 100%;
            table-layout: fixed;
            background: #f8f9fa;
            border-radius: 8px 8px 0 0;
            padding: 12px 15px;
            margin-bottom: 10px;
            font-weight: bold;
            color: #1b2850;
            border-bottom: 2px solid #e0e0e0;
        }

        .mobile-order-header-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .mobile-order-header-cell:first-child {
            width: 70%;
        }

        .mobile-order-header-cell:last-child {
            width: 30%;
            text-align: center;
        }

        .mobile-order-item {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 15px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .mobile-order-row {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 10px;
        }

        .mobile-order-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .mobile-order-number {
            font-weight: bold;
            font-size: 16px;
            color: #1b2850;
            width: 70%;
        }

        .mobile-order-details-cell {
            text-align: center;
            width: 30%;
        }

        .mobile-toggle-btn {
            background: #ff9f43;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            font-size: 20px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .mobile-toggle-btn:hover {
            background: #ff8c2e;
        }

        .mobile-toggle-btn.minus {
            background: #dc3545;
        }

        .mobile-toggle-btn.minus:hover {
            background: #c82333;
        }

        .mobile-order-details {
            display: none;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .mobile-order-details.active {
            display: block;
        }

        .mobile-detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            flex-wrap: wrap;
            gap: 8px;
        }

        .mobile-detail-row:last-child {
            border-bottom: none;
        }

        .mobile-detail-label {
            font-weight: 600;
            color: #595b5d;
            font-size: 14px;
            flex: 0 1 auto;
            min-width: 120px;
        }

        .mobile-detail-value {
            color: #1b2850;
            font-size: 14px;
            text-align: right;
            flex: 1 1 auto;
            word-break: break-word;
        }

        .mobile-action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .mobile-action-buttons a,
        .mobile-action-buttons button {
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .mobile-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            text-transform: capitalize;
        }

        .bg-lightgreen {
            background-color: #d4edda;
            color: #155724;
        }

        /* Search input styling */
        .search-input input {
            padding-left: 35px !important;
            border-radius: 5px;
        }

        .search-input {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .search-set {
            margin-bottom: 5px;
        }

        .btn-searchset {
            position: absolute;
            left: 10px;
            z-index: 10;
            padding: 0;
            top: 4px !important;
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
            margin: 0 0 18px 0;
            font-size: 34px;
            color: #2c3e50;
            font-weight: 600;
        }

        /* Modal: prevent number inputs from overflowing on large values */
        #makePaymentModal .modal-body input[type="number"],
        #makePaymentModal .modal-body input[type="text"] {
            min-width: 0;
            width: 100%;
            max-width: 100%;
        }

        #makePaymentModal .border.bg-light span {
            word-break: break-all;
            overflow-wrap: anywhere;
        }

        /* Filter total box: no overflow on large amounts */
        .filter-total-box {
            min-width: 0;
        }

        .filter-total-box #filtered-total {
            word-break: break-all;
            overflow-wrap: anywhere;
        }

        /* Summary badges: ensure amounts wrap */
        .summary-badge-box span:last-child {
            word-break: break-all;
            overflow-wrap: anywhere;
        }


        .sales-filter-toolbar .filter-field,
        .sales-filter-toolbar .filter-field .custom-select2,
        .sales-filter-toolbar .filter-field .form-group,
        .sales-filter-toolbar .filter-field .search-set {
            margin-bottom: 2px !important;
        }

        .sales-filter-toolbar .filter-field .form-control,
        .sales-filter-toolbar .filter-field .select2-container,
        .sales-filter-toolbar .filter-field .search-input,
        .sales-filter-toolbar .filter-field .filter-total-box,
        .sales-filter-toolbar .filter-field .filter-date-input {
            width: 100% !important;
        }

        .sales-filter-toolbar .filter-export-group {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            width: 100%;
        }

        .sales-filter-toolbar .filter-export-group .btn {
            min-width: 74px;
        }

        @media screen and (min-width: 992px) and (max-width: 1199.98px) {

            .content,
            .card,
            .card-body,
            .sales-filter-toolbar {
                max-width: 100%;
                overflow-x: hidden;
            }

            .sales-filter-toolbar .row {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                /* width: auto !important; */
                margin: 0;
            }

            .sales-filter-toolbar .filter-field {
                max-width: 100%;
                width: 100%;
                padding: 0 5px;
            }

            .sales-filter-toolbar .filter-field.filter-search,
            .sales-filter-toolbar .filter-field.filter-export {
                grid-column: span 2;
            }

            .sales-filter-toolbar .filter-export-group {
                justify-content: flex-start;
            }

            .table-responsive {
                width: 100%;
                max-width: 100%;
                overflow-x: auto !important;
                overflow-y: hidden;
            }
        }

        @media screen and (min-width: 768px) and (max-width: 991.98px) {

            .content,
            .card,
            .card-body,
            .sales-filter-toolbar {
                max-width: 100%;
                overflow-x: hidden;
            }

            .sales-filter-toolbar .row {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                /* width: auto !important; */
                margin: 0;
            }

            .sales-filter-toolbar .filter-field {
                max-width: 100%;
                width: 100%;
                padding: 0 5px;
            }

            .sales-filter-toolbar .filter-field.filter-search,
            .sales-filter-toolbar .filter-field.filter-export {
                grid-column: span 2;
            }

            .table-responsive {
                width: 100%;
                max-width: 100%;
                overflow-x: auto !important;
                overflow-y: hidden;
            }
        }


        @media (max-width: 767.98px) {

            html,
            body {
                overflow-x: hidden;
            }

            .content,
            .page-header,
            .card,
            .card-body,
            .sales-filter-toolbar,
            .sales-filter-toolbar .row,
            .table-scroll-top,
            .table-responsive,
            #order-table_wrapper,
            #order-table,
            .datanew,
            .pagination-controls {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
            }

            .content,
            .card,
            .card-body,
            .sales-filter-toolbar,
            .table-responsive {
                overflow-x: hidden !important;
            }

            .sales-filter-toolbar .row {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            .sales-filter-toolbar .filter-field {
                padding-left: 6px;
                padding-right: 6px;
            }

            .download-loader-box h4 {
                font-size: 28px;
            }
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

        .summary-badges-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 12px;
        }

        .summary-badge-box {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 34px;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid;
            font-size: 14px;
            font-weight: 600;
            background: #fff;
            word-break: break-all;
            overflow-wrap: anywhere;
            white-space: normal;
            flex-wrap: wrap;
        }

        .summary-badge-box.total {
            color: #1b2850;
            border-color: #ffebd6;
            background: #fff8f2;
        }

        .summary-badge-box.total span:last-child {
            color: #ff9f43;
        }

        .summary-badge-box.pending {
            color: #ea5455;
            border-color: #f5c2c7;
            background: #fff5f5;
        }

        .summary-badge-box.paid {
            color: #28c76f;
            border-color: #b7ebcd;
            background: #effcf4;
        }

        @media screen and (max-width: 767.98px) {
            .summary-badges-row {
                flex-direction: column;
                gap: 8px;
                width: 100%;
            }

            .summary-badge-box {
                display: flex;
                justify-content: flex-start;
                width: 100%;
                max-width: 100%;
                white-space: normal;
                word-break: break-word;
            }
        }

        /* ===== EMI Modal Redesign ===== */
        #emiModal .modal-dialog {
            max-width: 680px;
        }

        #emiModal .modal-content {
            border-radius: 12px;
            overflow: hidden;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.18);
        }

        #emiModal .modal-header {
            background: #fff;
            border-bottom: 1px solid #eee;
            padding: 14px 20px;
        }

        #emiModal .modal-title {
            font-weight: 700;
            font-size: 17px;
            color: #1b2850;
        }

        .emi-account-card {
            background: linear-gradient(135deg, #1b2850 0%, #2d3f6e 100%);
            border-radius: 10px;
            padding: 18px 20px;
            color: #fff;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 10px;
        }

        .emi-account-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: rgba(255,255,255,0.65);
            margin-bottom: 3px;
        }

        .emi-account-number {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .emi-account-customer {
            font-size: 12px;
            color: rgba(255,255,255,0.75);
            margin-top: 2px;
        }

        .emi-next-due-badge {
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 20px;
            padding: 5px 14px;
            font-size: 12px;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
        }

        .emi-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 18px;
        }

        .emi-info-card {
            background: #fff;
            border: 1px solid #e8ebf0;
            border-radius: 8px;
            padding: 12px 14px;
            transition: box-shadow 0.2s;
        }

        .emi-info-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .emi-info-card-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #8a94a6;
            margin-bottom: 5px;
        }

        .emi-info-card-value {
            font-size: 16px;
            font-weight: 700;
            color: #1b2850;
        }

        .emi-monthly-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            flex-wrap: wrap;
            gap: 8px;
        }

        .emi-monthly-title {
            font-size: 15px;
            font-weight: 700;
            color: #1b2850;
        }

        .emi-filters {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 10px;
            align-items: center;
        }

        .emi-filter-select {
            border: 1px solid #dce0e7;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 12px;
            color: #1b2850;
            background: #fff;
            cursor: pointer;
            height: 30px;
        }

        .emi-filter-search {
            border: 1px solid #dce0e7;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 12px;
            color: #1b2850;
            background: #fff;
            height: 30px;
            width: 140px;
        }

        .emi-filter-search::placeholder {
            color: #aab;
        }

        .emi-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .emi-table thead th {
            background: #f4f6fb;
            color: #8a94a6;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            padding: 8px 12px;
            border-bottom: 2px solid #e8ebf0;
            text-align: left;
        }

        .emi-table tbody tr {
            border-bottom: 1px solid #f0f2f7;
            transition: background 0.15s;
        }

        .emi-table tbody tr:hover {
            background: #f8f9ff;
        }

        .emi-table tbody td {
            padding: 10px 12px;
            color: #2d3559;
            font-weight: 500;
        }

        .emi-table tbody td.emi-td-dash {
            color: #b0b8c9;
        }

        .emi-status-paid {
            background: #28c76f;
            color: #fff;
            border-radius: 5px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
        }

        .emi-status-pending {
            background: #ea5455;
            color: #fff;
            border-radius: 5px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
        }

        .emi-table-wrap {
            max-height: 280px;
            overflow-y: auto;
            border: 1px solid #e8ebf0;
            border-radius: 8px;
        }

        .emi-table-wrap::-webkit-scrollbar {
            width: 5px;
        }

        .emi-table-wrap::-webkit-scrollbar-thumb {
            background: #c0c8da;
            border-radius: 3px;
        }

        .emi-no-rows {
            text-align: center;
            padding: 20px;
            color: #b0b8c9;
            font-size: 13px;
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
        <div class="page-header">
            <div class="page-title">
                <h4>All Sales & Bills</h4>
                <!-- <h6>Manage your sales</h6> -->
            </div>
            <div class="page-btn">
                @if (app('hasPermission')(2, 'add'))
                    <a href="{{ route('sales.add', ['new_bill' => 1]) }}" class="btn btn-sm btn-added"><img
                            src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" alt="img"
                            class="me-1">New
                        Bill</a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">

                <!-- Top Search and Date Filter Row -->

                <div class="table-top mb-3 sales-filter-toolbar">
                    <div class="row w-100 align-items-center">
                        <!-- Search -->
                        <div class="col-md-2 col-12 mb-1 mb-md-0 filter-field filter-search">
                            {{-- <div class="search-set w-100">
                                <div class="search-path"></div>
                                <div class="search-input d-flex align-items-center" style="width:170px;">
                                    <a class="btn btn-searchset">
                                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}"
                                            alt="img">
                                    </a>
                                    <input type="text" id="sales-search-input" class="form-control mb-1"
                                        style="height:30px" placeholder="Search...">
                                </div>
                            </div> --}}
                            <div class="search-set">
                                <!-- Your existing filters -->
                                <div class="search-input mb-2">
                                    <a class="btn btn-searchset">
                                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}"
                                            alt="img">
                                    </a>
                                    <input type="text" id="search-input" class="form-control" placeholder="Search..."
                                        style="height: 30px;">
                                </div>
                            </div>
                        </div>



                        <!-- Sort Filter -->
                        <div class="col-md-2 col-6 filter-field">
                            <div class="mb-1 custom-select2">
                                <select id="filter-sort" data-placeholder="Latest First"
                                    class="form-control form-control-sm filter-select2">
                                    <option value="latest">Latest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="order_no_asc">Order No. 1 to Last</option>
                                    <option value="order_no_desc">Order No. Last to 1</option>
                                </select>
                            </div>
                        </div>

                        <!-- Month Filter -->
                        <div class="col-md-2 col-6 filter-field">
                            <div class="mb-1 custom-select2">
                                <!-- <label for="filter-month" class="form-label">Month</label> -->
                                <select id="filter-month" data-placeholder="All Months"
                                    class="form-control form-control-sm filter-select2">
                                    <option value="all">All Months</option>
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">
                                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Year Filter -->
                        <div class="col-md-2 col-6 filter-field">
                            <div class="mb-1 custom-select2">
                                <!-- <label for="filter-year" class="form-label">Year</label> -->
                                <select id="filter-year" data-placeholder="All Years"
                                    class="form-control form-control-sm filter-select2">
                                    <option value="all">All Years</option>
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2 col-6 filter-field {{ $financialYearEnabled ?? true ? '' : 'd-none' }}"
                            id="sales-financial-year-filter">
                            <div class="mb-1">
                                <select id="filter-financial-year" class="form-control form-control-sm">
                                    <option value="all">All Financial Years</option>
                                </select>
                            </div>
                        </div>

                        <!-- Date Filter -->
                        <!-- <div class="col-md-2 col-6">
                                                                                                                                            <div class="form-group mb-0">
                                                                                                                                                <label for="filter-date" class="form-label">Date</label>
                                                                                                                                                <input type="text" id="filter-date" placeholder="Choose Date"
                                                                                                                                                    class="datetimepicker form-control form-control-sm">
                                                                                                                                            </div>
                                                                                                                                        </div> -->
                        <!-- Date -->
                        <div class="col-md-2 col-6 filter-field">
                            <!-- <div class="form-group mb-0"> -->
                            <!-- <label for="filter-date" class="form-label">Date</label> -->
                            <input type="text" id="filter-date" placeholder="Choose Date"
                                class="datetimepicker form-control form-control-sm filter-date-input">
                            <!-- </div> -->
                        </div>

                        <!-- Order Type Filter -->
                        <div class="col-md-2 col-6 filter-field">
                            <div class="mb-1 custom-select2">
                                <select id="filter-order-type" data-placeholder="All Order Types"
                                    class="form-control form-control-sm filter-select2">
                                    <option value="all">All Order Types</option>
                                    <option value="Self Pickup">Self Pickup</option>
                                    <option value="Delivery">Delivery</option>
                                </select>
                            </div>
                        </div>

                        <!-- Export Buttons -->
                        <div class="col-md-2 col-12 mb-1 filter-field filter-export">
                            <div class="filter-export-group mt-1">
                                <button id="exportAllChallan" class="btn btn-sm btn-success flex-fill">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                                <button id="exportPdf" class="btn btn-sm btn-danger flex-fill">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                @if (in_array(auth()->user()->role, ['admin', 'sub-admin']))
                    <div class="summary-badges-row">
                        <div class="summary-badge-box total">
                            <span>Total:</span>
                            <span id="filtered-total">₹0.00</span>
                        </div>
                        <div class="summary-badge-box pending">
                            <span>Total Pending:</span>
                            <span id="filtered-pending-total">₹0.00</span>
                        </div>
                        <div class="summary-badge-box paid">
                            <span>Total Paid:</span>
                            <span id="filtered-paid-total">₹0.00</span>
                        </div>
                    </div>
                @endif

                <!-- Filter Inputs Card -->
                {{-- <div class="card" id="filter_inputs">
                    <div class="card-body pb-0">
                        <div class="row">
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <input type="text" placeholder="Enter Name" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <input type="text" placeholder="Enter Reference No" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group custom-select2">
                                    <select class="form-select">
                                        <option>Completed</option>
                                        <option>Paid</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group d-flex justify-content-end">
                                    <a class="btn btn-filters ms-auto">
                                        <img src="admin/assets/img/icons/search-whites.svg" alt="img">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}
                <div class="table-scroll-top"
                    style="overflow-x: auto; overflow-y: hidden; height: 20px; margin-bottom: 5px;">
                    <div style="height: 1px;"></div> <!-- Adjust width to match your table width -->
                </div>
                <!-- Orders Table -->
                <div class="table-responsive mt-3" style="overflow-x: auto;">
                    <table class="table datanew" id="order-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Order Number</th>
                                <th class="">Details</th>
                                <th>Date</th>
                                <th>Customer Name</th>
                                <th>Quotation/Sale</th>
                                <th>Payment Status</th>
                                {{-- <th>Return Status</th> --}}
                                <th>Total</th>
                                <th>Assigned Staff</th>
                                <th>Order Type</th>
                                <th>Biller</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- JS will populate this -->
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Order Cards -->
                <div class="mobile-order-card mt-3" id="mobile-order-container">
                    <!-- JS will populate this -->
                </div>

                <div
                    class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <span class="me-2" style="font-size: 14px; color: #555;">Show per page :</span>
                        <select id="sales-per-page-select" class="form-select form-select-sm"
                            style="width: auto; border: 1px solid #ddd;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-3" style="font-size: 14px; color: #555;">
                            <span id="sales-pagination-from">0</span> - <span id="sales-pagination-to">0</span> of
                            <span id="sales-pagination-total">0</span> items
                        </span>
                    </div>
                    <nav aria-label="Sales pagination">
                        <ul class="pagination pagination-sm mb-0" id="sales-pagination-numbers"></ul>
                    </nav>
                </div>

            </div>
        </div>

    </div>
    <form id="makePaymentForm">
        <div class="modal fade" id="makePaymentModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">

                        <div class="text-end mb-3">
                            <button type="button" class="btn btn-sm btn-cancel text-white" id="viewHistoryBtn">View
                                Payment History</button>
                        </div>

                        <!-- ✅ Payment History Container -->
                        <div id="paymentHistoryBox" class="border p-2 rounded bg-white d-none mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Payment History</h6>

                                <!-- 👇 Visible Close Button -->
                                <button type="button" class="btn btn-sm btn-danger" id="closeHistoryBtn">
                                    x <!-- This renders an “×” symbol -->
                                </button>
                            </div>

                            <ul id="paymentHistoryList" class="list-unstyled mb-0"
                                style="max-height: 200px; overflow-y: auto;">
                                <!-- Populated via JavaScript -->
                            </ul>
                        </div>



                        <div class="border p-2 rounded bg-light"
                            style="word-break: break-all; overflow-wrap: anywhere; font-size: 14px;">
                            <strong>Total Amount:</strong> ₹<span id="emiTotal"
                                style="word-break: break-all;"></span><br>
                            <div id="tdsAmountSection" class="d-none">
                                <strong>TDS (<span id="tdsPercentageDisplay">0</span>%):</strong>
                                ₹<span id="tdsAmountDisplay">0.00</span><br>
                            </div>
                            <div id="returnAmountSection" class="d-none">
                                <strong>Return Amount:</strong> ₹<span id="returnAmountDisplay">0.00</span><br>
                            </div>
                            <strong>Remaining Amount:</strong> ₹<span id="remainingAmountDisplay">0.00</span>

                        </div>

                        <!-- ✅ View Payment History Button -->



                        <br>

                        <!-- Payment Method -->
                        <div class="mb-3" id="paymentMethodDiv">
                            <label for="paymentMethodSelect" class="form-label">Select Payment Method</label>
                            <select class="form-select" id="paymentMethodSelect" name="payment_method">
                                <option value="" selected disabled>Select</option>
                                <option value="cash">Cash</option>
                                <option value="online">Online</option>
                                <option value="cash_online">Cash + Online</option>
                                <option value="emi">EMI</option>
                            </select>
                            <div class="text-danger" id="paymentMethodError"></div>
                        </div>

                        <div class="mb-3 d-none" id="cashOnlineTypeDiv">
                            <label for="cashOnlineTypeSelect" class="form-label">Select Cash + Online Type</label>
                            <select class="form-select" id="cashOnlineTypeSelect" name="cash_online_type">
                                <option value="" selected disabled>Select</option>
                                <option value="cash_online_fully">Cash + Online Fully</option>
                                <option value="cash_online_partially">Cash + Online Partially</option>
                            </select>
                            <div class="text-danger" id="cashOnlineTypeError"></div>
                        </div>

                        <!-- Fully Cash + Online -->
                        <!-- Fully Cash + Online -->
                        <div class="mb-3 d-none" id="fullyCashOnlineFields">
                            <label>Cash Amount</label>
                            <input type="number" id="cashOnlineFullAmount" name="fully_cash_amount"
                                class="form-control" min="0" step="0.01">
                            <div class="text-danger" id="cashOnlineFullAmountError"></div>
                            <label class="mt-2">Online Amount</label>
                            <input type="number" id="upiOnlineFullAmount" name="full_online_amount"
                                class="form-control" readonly>
                            <div class="text-danger" id="upiOnlineFullAmountError"></div>
                        </div>

                        <!-- Partial Cash + Online -->
                        <div class="mb-3 d-none" id="partialCashOnlineFields">
                            <label>Cash Amount</label>
                            <input type="number" id="cashOnlinePartialAmount" name="cash_amount" class="form-control"
                                min="0" step="0.01">
                            <div class="text-danger" id="cashOnlinePartialAmountError"></div>
                            <label class="mt-2">Online Amount</label>
                            <input type="number" id="upiOnlinePartialAmount" name="online_amount" class="form-control"
                                min="0" step="0.01">
                            <div class="text-danger" id="upiOnlinePartialAmountError"></div>
                            <label class="mt-2">Remaining Amount</label>
                            <input type="number" id="remainingCashOnlineAmount" name="remaining_amount"
                                class="form-control" readonly>
                        </div>







                        <div class="mb-3 d-none" id="onlineTypeDiv">
                            <label for="onlineTypeSelect" class="form-label">Select Online Type</label>
                            <select class="form-select" id="onlineTypeSelect" name="online_type">
                                <option value="" selected disabled>Select</option>
                                <option value="online_fully">Online Fully</option>
                                <option value="online_partially">Online Partially</option>
                            </select>
                            <div class="text-danger" id="onlineTypeError"></div>
                        </div>

                        <!-- Paid Type Dropdown (Hidden by default) -->
                        <div class="mb-3 d-none" id="paidTypeDiv">
                            <label for="paidTypeSelect" class="form-label">Paid Type</label>
                            <select class="form-select" id="paidTypeSelect" name="paid_type">
                                <option value="" selected disabled>Select</option>
                                <option value="cash_partially">Cash Partially</option>
                                <option value="cash_fully">Cash Fully</option>
                            </select>
                            <div class="text-danger" id="paidTypeError"></div>
                        </div>

                        <!-- UPI Amount Input -->
                        <div class="mb-3 d-none" id="upiAmountDiv">
                            <label for="upiAmountInput" class="form-label">Online Amount</label>
                            <input type="number" class="form-control" id="upiAmountInput" name="upi_online_amount"
                                readonly>
                            <div class="text-danger" id="upiAmountError"></div>
                        </div>



                        <!-- Partially Paid Fields -->
                        <div class="mb-3 d-none" id="partialPaidFields">
                            <label for="partialAmount" class="form-label">Enter Amount</label>
                            <input type="number" class="form-control mb-2" id="partialAmount" name="amount"
                                min="1" step="0.01">
                            <div class="text-danger" id="partialAmountError"></div>

                            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                                <div style="flex: 1;">
                                    <label for="pendingAmount" class="form-label">Pending Amount</label>
                                    <input type="number" class="form-control" id="pendingAmount"
                                        name="cash_pending_amount" readonly>
                                </div>

                            </div>
                        </div>

                        <div class="mb-3 d-none" id="bank_container">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="bank_id" class="form-label mb-0">Select Bank</label>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="openAddBankModal">
                                    Add Bank
                                </button>
                            </div>
                            <select name="bank_id" id="bank_id" class="form-select">
                                <option value="">Select Bank</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->bank_name }}</option>
                                @endforeach
                            </select>
                            <div class="text-danger" id="bankError"></div>
                        </div>

                        <div class="mb-3 d-none" id="emiFields">
                            <label for="emiMonthSelect" class="form-label">EMI Month</label>
                            <select class="form-select" id="emiMonthSelect" name="emi_month">
                                <option value="" selected disabled>Select EMI Month</option>
                            </select>
                            <div class="text-danger" id="emiMonthError"></div>

                            <label for="emiMonthlyAmount" class="form-label mt-2">Monthly EMI</label>
                            <input type="number" class="form-control" id="emiMonthlyAmount" name="emi_monthly"
                                readonly>
                            <div class="text-danger" id="emiMonthlyAmountError"></div>
                        </div>

                        <!-- Fully Paid Fields -->
                        <div class="mb-3 d-none" id="fullyPaidFields">
                            <label class="form-label">Cash Amount</label>
                            <input type="number" class="form-control" id="cashAmount" name="cashAmount" min="0"
                                step="0.01">
                            <div class="text-danger" id="cashAmountError"></div>
                        </div>

                        <div class="mb-3">
                            <label for="paymentDateInput" class="form-label">Payment Date</label>
                            <input type="text" class="form-control datetimepicker" id="paymentDateInput"
                                name="payment_date" placeholder="Choose Payment Date" autocomplete="off">
                            <div class="text-danger" id="paymentDateError"></div>
                        </div>

                        <div class="mb-3">
                            <label for="paymentRemarks" class="form-label">Remark</label>
                            <input type="text" class="form-control" id="paymentRemarks" name="remarks"
                                placeholder="Enter remark">
                            <div class="text-danger" id="paymentRemarksError"></div>
                        </div>













                        <!-- Cleaned Hidden Inputs (no duplicate name attributes) -->
                        <input type="hidden" id="paymentJobCardId" name="order_id">
                        <input type="hidden" id="remainingAmountHidden" name="remaining_amount">
                        <input type="hidden" id="paymentMethodHidden" name="payment_type">




                        <div class="text-end">
                            <button type="submit" class="btn btn-submit text-white"
                                style="background-color: #ff9f43;">Submit Payment</button>
                            <button type="button" class="btn btn-secondary btn-cancel"
                                data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="modal fade" id="addBankModal" tabindex="-1" aria-labelledby="addBankModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addBankForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addBankModalLabel">Add Bank</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="add_bank_name" class="form-label">Bank Name</label>
                                <input type="text" class="form-control" id="add_bank_name" name="bank_name">
                                <div class="text-danger small" id="addBankNameError"></div>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="add_account_number" class="form-label">Account Number</label>
                                <input type="text" class="form-control" id="add_account_number"
                                    name="account_number">
                                <div class="text-danger small" id="addAccountNumberError"></div>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="add_ifsc_code" class="form-label">IFSC Code</label>
                                <input type="text" class="form-control" id="add_ifsc_code" name="ifsc_code">
                                <div class="text-danger small" id="addIfscCodeError"></div>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="add_branch_name" class="form-label">Branch Name</label>
                                <input type="text" class="form-control" id="add_branch_name" name="branch_name">
                                <div class="text-danger small" id="addBranchNameError"></div>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="add_opening_balance" class="form-label">Opening Balance</label>
                                <input type="number" class="form-control" id="add_opening_balance"
                                    name="opening_balance" min="0" step="0.01" value="0">
                                <div class="text-danger small" id="addOpeningBalanceError"></div>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="add_bank_status" class="form-label">Status</label>
                                <select class="form-select" id="add_bank_status" name="status">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                <div class="text-danger small" id="addBankStatusError"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-submit text-white" id="saveBankBtn"
                            style="background-color: #ff9f43;">Save Bank</button>
                        <button type="button" class="btn btn-secondary btn-cancel"
                            data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Global Payment History Modal -->
    <div class="modal fade" id="paymentHistoryModal" tabindex="-1" aria-labelledby="paymentHistoryLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentHistoryLabel">Payment History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                </div>
                <div class="modal-body">
                    <ul id="globalPaymentHistoryList" class="list-group"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 520px;">
            <div class="modal-content">
                <div class="modal-header" style="padding: 16px 18px 14px;">
                    <h5 class="modal-title" id="editPaymentLabel">Edit Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                </div>
                <div class="modal-body" style="padding: 18px;">
                    <input type="hidden" id="editPaymentId">
                    <input type="hidden" id="editPaymentOrderId">
                    <input type="hidden" id="editPaymentTotalValue" value="0">
                    <input type="hidden" id="editPaymentBaseRemaining" value="0">
                    <input type="hidden" id="editPaymentOriginalAmount" value="0">
                    <div class="border rounded-2 p-3 mb-3" style="background:#f8f9fc; border-color:#d9dfeb !important;">
                        <div class="fw-semibold" style="font-size:14px; color:#1f2937;">
                            Total Amount: <span id="editPaymentTotalAmount">₹0.00</span>
                        </div>
                        <div class="fw-semibold" style="font-size:14px; color:#1f2937;">
                            Remaining Amount: <span id="editPaymentRemainingAmount">₹0.00</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editPaymentDate" class="form-label">Payment Date</label>
                        <input type="date" class="form-control" id="editPaymentDate" style="height:44px;">
                    </div>
                    <div class="mb-3">
                        <label for="editPaymentMethod" class="form-label">Select Payment Method</label>
                        <select class="form-select" id="editPaymentMethod" style="height:44px;">
                            <option value="cash">Cash</option>
                            <option value="online">Online</option>
                            {{-- <option value="emi">EMI</option> --}}
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editPaymentType" class="form-label">Paid Type</label>
                        <select class="form-select" id="editPaymentType" style="height:44px;">
                            <option value="fully">Cash Fully</option>
                            <option value="partially">Cash Partially</option>
                            {{-- <option value="emi">EMI</option> --}}
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editPaymentAmount" class="form-label">Enter Amount</label>
                        <input type="number" class="form-control" id="editPaymentAmount" min="0"
                            step="0.01" style="height:44px;">
                    </div>
                    <div class="mb-3">
                        <label for="editPaymentBank" class="form-label">Pending Amount</label>
                        <input type="text" class="form-control" id="editPaymentPendingAmount" readonly
                            style="height:44px; background:#e9edf2;">
                        <div id="editPaymentBankWrap" class="d-none"></div>
                    </div>
                    <div class="mb-3">
                        <label for="editPaymentRemarks" class="form-label">Remark</label>
                        <textarea class="form-control" id="editPaymentRemarks" rows="4" style="resize:none;"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="justify-content:flex-start; gap:8px; padding: 0 18px 18px;">
                    <button type="button" class="btn btn-warning" id="saveEditPaymentBtn" style="min-width: 140px; height: 48px;">Update Payment</button>
                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal" style="min-width: 120px; height: 48px;">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div id="downloadLoaderOverlay" class="download-loader-overlay d-none" aria-live="polite" aria-busy="true">
        <div class="download-loader-box">
            <h4 id="downloadLoaderText">Generating PDF...</h4>
            <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
        </div>
    </div>

    <!-- Redesigned EMI Details Modal -->
    <div class="modal fade" id="emiModal" tabindex="-1" aria-labelledby="emiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" style="max-width:680px;">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="emiModalLabel">EMI Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                </div>

                <div class="modal-body" style="padding: 20px;">

                    <!-- Account Header Card -->
                    <div class="emi-account-card">
                        <div>
                            <div class="emi-account-label">EMI Account</div>
                            <div class="emi-account-number" id="emiOrderNumber">—</div>
                            <div class="emi-account-customer">Customer: <span id="emiCustomerName">—</span></div>
                        </div>
                        <div class="emi-next-due-badge" id="emiNextDueBadge">Next due: —</div>
                    </div>

                    <!-- Info Cards Grid -->
                    <div class="emi-info-grid">
                        <div class="emi-info-card">
                            <div class="emi-info-card-label">EMI Tenure</div>
                            <div class="emi-info-card-value" id="emiTenureDisplay">—</div>
                        </div>
                        <div class="emi-info-card">
                            <div class="emi-info-card-label">Monthly EMI</div>
                            <div class="emi-info-card-value" id="emiMonthlyDisplay">—</div>
                        </div>
                        <div class="emi-info-card">
                            <div class="emi-info-card-label">Down Payment</div>
                            <div class="emi-info-card-value" id="emiDownPaymentDisplay">—</div>
                        </div>
                        <div class="emi-info-card">
                            <div class="emi-info-card-label">Loan Amount</div>
                            <div class="emi-info-card-value" id="emiLoanAmountDisplay">—</div>
                        </div>
                        <div class="emi-info-card">
                            <div class="emi-info-card-label">Interest Rate</div>
                            <div class="emi-info-card-value" id="emiInterestDisplay">—</div>
                        </div>
                        <div class="emi-info-card">
                            <div class="emi-info-card-label">Guarantor Name</div>
                            <div class="emi-info-card-value" id="emiGuarantorDisplay" style="font-size:14px;">—</div>
                        </div>
                        <div class="emi-info-card">
                            <div class="emi-info-card-label">Aadhar Number</div>
                            <div class="emi-info-card-value" id="emiAadharDisplay" style="font-size:14px;">—</div>
                        </div>
                        <div class="emi-info-card">
                            <div class="emi-info-card-label">PAN Number</div>
                            <div class="emi-info-card-value" id="emiPanDisplay" style="font-size:14px;">—</div>
                        </div>
                    </div>

                    <!-- Month Wise Section -->
                    <div class="emi-monthly-section-header">
                        <div class="emi-monthly-title">Month Wise EMI Details</div>
                        <div class="emi-next-due-badge" id="emiNextDueBadge2" style="background:#1b2850;border-color:#3a4f8c;">Next due: —</div>
                    </div>

                    <!-- Filters -->
                    <div class="emi-filters">
                        <select id="emiStatusFilter" class="emi-filter-select">
                            <option value="all">All Status</option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                        </select>
                        <input type="text" id="emiMonthSearch" class="emi-filter-search" placeholder="Search month...">
                    </div>

                    <!-- Month Table -->
                    <div class="emi-table-wrap">
                        <table class="emi-table">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Paid On</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tbody id="emiMonthTableBody">
                                <tr><td colspan="5" class="emi-no-rows">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        var userRole = "{{ auth()->check() ? auth()->user()->role : 'guest' }}";
        window.staffOptions = @json($staffs ?? []);
        var currencySymbol = "{{ $currencySymbol ?? '₹' }}";
    </script>

    <script>
        // Helper function to get status badge HTML
        // Helper function to get status badge HTML
        // In your JavaScript code, update the getStatusBadge and getMobileStatusBadge functions:

        // ===== EMI Details Modal Logic =====
        var emiAllMonths = []; // store all month rows for filtering

        function renderEmiTable(rows) {
            if (!rows || rows.length === 0) {
                $('#emiMonthTableBody').html('<tr><td colspan="5" class="emi-no-rows">No records found.</td></tr>');
                return;
            }
            var html = '';
            rows.forEach(function(row) {
                var statusBadge = row.status && row.status.toLowerCase() === 'paid'
                    ? '<span class="emi-status-paid">Paid</span>'
                    : '<span class="emi-status-pending">Pending</span>';
                var paidOn = (row.paid_on && row.paid_on !== '-') ? row.paid_on : '<span class="emi-td-dash">-</span>';
                var remark = (row.remark && row.remark !== '-') ? row.remark : '<span class="emi-td-dash">-</span>';
                var amt = '₹' + Number(row.amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                html += '<tr>';
                html += '<td>' + (row.label || '') + '</td>';
                html += '<td>' + statusBadge + '</td>';
                html += '<td>' + amt + '</td>';
                html += '<td>' + paidOn + '</td>';
                html += '<td>' + remark + '</td>';
                html += '</tr>';
            });
            $('#emiMonthTableBody').html(html);
        }

        function filterEmiTable() {
            var status = $('#emiStatusFilter').val();
            var search = ($('#emiMonthSearch').val() || '').toLowerCase().trim();
            var filtered = emiAllMonths.filter(function(row) {
                var matchStatus = (status === 'all') || (row.status && row.status.toLowerCase() === status);
                var matchSearch = !search || (row.label && row.label.toLowerCase().indexOf(search) !== -1);
                return matchStatus && matchSearch;
            });
            renderEmiTable(filtered);
        }

        $('#emiStatusFilter').on('change', filterEmiTable);
        $('#emiMonthSearch').on('input', filterEmiTable);

        function openEmiDetails(id) {
            // Reset filters
            $('#emiStatusFilter').val('all');
            $('#emiMonthSearch').val('');

            // Reset modal fields
            $('#emiOrderNumber').text('—');
            $('#emiCustomerName').text('—');
            $('#emiNextDueBadge').text('Next due: —');
            $('#emiNextDueBadge2').text('Next due: —');
            $('#emiTenureDisplay').text('—');
            $('#emiMonthlyDisplay').text('—');
            $('#emiDownPaymentDisplay').text('—');
            $('#emiLoanAmountDisplay').text('—');
            $('#emiInterestDisplay').text('—');
            $('#emiGuarantorDisplay').text('—');
            $('#emiAadharDisplay').text('—');
            $('#emiPanDisplay').text('—');
            $('#emiMonthTableBody').html('<tr><td colspan="5" class="emi-no-rows">Loading...</td></tr>');
            emiAllMonths = [];

            // Open Modal
            $('#emiModal').modal('show');

            $.ajax({
                url: "{{ route('sales.emi.details', ':id') }}".replace(':id', id),
                type: "GET",
                dataType: "json",
                success: function(res) {
                    var fmt = function(n) {
                        return '₹' + Number(n || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    };
                    var nextDueLabel = res.next_due_label || 'Completed';
                    var nextDueText = 'Next due: ' + nextDueLabel;

                    $('#emiOrderNumber').text(res.order_number || '—');
                    $('#emiCustomerName').text(res.customer_name || '—');
                    $('#emiNextDueBadge').text(nextDueText);
                    $('#emiNextDueBadge2').text(nextDueText);
                    $('#emiTenureDisplay').text((res.months || 0) + ' Months');
                    $('#emiMonthlyDisplay').text(fmt(res.emi_amount));
                    $('#emiDownPaymentDisplay').text(fmt(res.down_payment));
                    $('#emiLoanAmountDisplay').text(fmt(res.loan_amount));
                    $('#emiInterestDisplay').text(Number(res.interest_rate || 0).toFixed(2) + '%');
                    $('#emiGuarantorDisplay').text(res.guarantor_name || 'N/A');
                    $('#emiAadharDisplay').text(res.aadhar_number || 'N/A');
                    $('#emiPanDisplay').text(res.pan_number || 'N/A');

                    emiAllMonths = Array.isArray(res.month_details) ? res.month_details : [];
                    renderEmiTable(emiAllMonths);
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    $('#emiMonthTableBody').html('<tr><td colspan="5" class="emi-no-rows">Unable to load EMI Details.</td></tr>');
                }
            });
        }

        function formatPaymentHistoryDate(value) {
            if (!value) return 'N/A';

            const datePart = String(value).split(' ')[0];

            if (/^\d{4}-\d{2}-\d{2}$/.test(datePart)) {
                const [year, month, day] = datePart.split('-');
                return `${day}-${month}-${year}`;
            }

            const parsedDate = new Date(value);
            if (!isNaN(parsedDate.getTime())) {
                const day = String(parsedDate.getDate()).padStart(2, '0');
                const month = String(parsedDate.getMonth() + 1).padStart(2, '0');
                const year = parsedDate.getFullYear();
                return `${day}-${month}-${year}`;
            }

            return value;
        }

        function formatDateForInput(value) {
            if (!value) return '';

            const datePart = String(value).split(' ')[0];
            if (/^\d{4}-\d{2}-\d{2}$/.test(datePart)) {
                return datePart;
            }

            const parsedDate = new Date(value);
            if (!isNaN(parsedDate.getTime())) {
                return parsedDate.toISOString().slice(0, 10);
            }

            return '';
        }

        function formatPaymentMethodLabel(value) {
            const method = String(value || '').toLowerCase();

            switch (method) {
                case 'cash':
                    return 'Cash';
                case 'online':
                case 'upi':
                    return 'Online';
                case 'emi':
                    return 'EMI';
                case 'cash_online':
                    return 'Cash + Online';
                default:
                    return value || 'N/A';
            }
        }

        function formatPaymentTypeLabel(value) {
            const type = String(value || '').toLowerCase();

            switch (type) {
                case 'fully':
                    return 'Fully';
                case 'partially':
                case 'partial':
                    return 'Partially';
                case 'emi':
                    return 'EMI';
                default:
                    return value || 'N/A';
            }
        }

        function getStatusBadge(status, type = 'payment', extraPaid = 0) {
            status = status ? status.toLowerCase() : '';

            if (type === 'quotation') {
                switch (status) {
                    case 'quotation':
                        return `<span class="status-badge status-quotation">Quotation</span>`;
                    case 'sales':
                    case 'sale':
                    case 'sales':
                        return `<span class="status-badge status-sales">Sales</span>`;
                    default:
                        return `<span class="status-badge status-other">${status || 'N/A'}</span>`;
                }
            }

            if (type === 'payment') {
                // Check if there's extra paid (advance/overpayment)
                if (extraPaid > 0) {
                    return `<span class="status-badge status-pending">Extra Paid: ₹${extraPaid.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>`; // Red for extra paid
                }

                // Payment Status badges
                switch (status) {
                    case 'pending':
                        return `<span class="status-badge status-pending">Pending</span>`; // Red
                    case 'completed':
                    case 'paid':
                        return `<span class="status-badge status-completed">Completed</span>`; // Green
                    case 'partially':
                    case 'partial':
                        return `<span class="status-badge status-partially">Partially</span>`; // Teal
                    default:
                        return `<span class="status-badge status-other">${status || 'N/A'}</span>`; // Gray
                }
            } else if (type === 'return') {
                switch (status) {
                    case 'returned':
                        return `<span class="status-badge status-pending">Returned</span>`; // Red
                    default:
                        return `<span class="status-badge status-completed">No Return</span>`; // Gray
                }
            } else {
                // Payment Method badges
                switch (status) {
                    case 'cash':
                        return `<span class="status-badge status-cash">Cash</span>`; // Teal
                    case 'online':
                        return `<span class="status-badge status-online">Online</span>`; // Green

                    case 'cash_online':
                    case 'cash + online':
                        return `<span class="status-badge status-cash_online">Cash+Online</span>`; // Green
                    default:
                        return `<span class="status-badge status-other">${status || 'N/A'}</span>`; // Gray
                }
            }
        }

        // Helper function for mobile view
        function getMobileStatusBadge(status, type = 'payment', extraPaid = 0) {
            status = status ? status.toLowerCase() : '';

            if (type === 'quotation') {
                switch (status) {
                    case 'quotation':
                        return `<span class="mobile-badge status-quotation">Quotation</span>`;
                    case 'sales':
                    case 'sale':
                        return `<span class="mobile-badge status-sales">Sales</span>`;
                    default:
                        return `<span class="mobile-badge status-other">${status || 'N/A'}</span>`;
                }
            }

            if (type === 'payment') {
                // Check if there's extra paid (advance/overpayment)
                if (extraPaid > 0) {
                    return `<span class="mobile-badge status-pending">Extra: ₹${extraPaid.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>`; // Red for extra paid
                }

                switch (status) {
                    case 'pending':
                        return `<span class="mobile-badge status-pending">Pending</span>`; // Red
                    case 'completed':
                    case 'paid':
                        return `<span class="mobile-badge status-completed">Paid</span>`; // Green
                    case 'partially':
                    case 'partial':
                        return `<span class="mobile-badge status-partially">Partially</span>`; // Teal
                    default:
                        return `<span class="mobile-badge status-other">${status || 'N/A'}</span>`; // Gray
                }
            } else if (type === 'return') {
                switch (status) {
                    case 'returned':
                        return `<span class="mobile-badge status-pending">Returned</span>`; // Red
                    default:
                        return `<span class="mobile-badge status-other">No Return</span>`; // Gray
                }
            } else {
                switch (status) {
                    case 'cash':
                        return `<span class="mobile-badge status-cash">Cash</span>`; // Teal
                    case 'online':
                        return `<span class="mobile-badge status-online">Online</span>`; // Green

                    case 'cash_online':
                    case 'cash + online':
                        return `<span class="mobile-badge status-cash_online">Cash+Online</span>`; // Green
                    default:
                        return `<span class="mobile-badge status-other">${status || 'N/A'}</span>`; // Gray
                }
            }
        }
        // Function to render mobile order cards
        function renderMobileOrders(orders, currencySymbol, currencyPosition) {
            const container = $('#mobile-order-container');
            container.html('');

            if (!orders || orders.length === 0) {
                container.html('<div class="text-center p-4">No orders found</div>');
                return;
            }

            // Add header row
            const headerHtml = `
                <div class="mobile-order-header-row">
                    <div class="mobile-order-header-cell">Order Number</div>
                    <div class="mobile-order-header-cell">Details</div>
                </div>
            `;
            container.append(headerHtml);

            orders.forEach((order, index) => {
                const status = String(order.quotation_status || 'sales').toLowerCase();
                const remaining = parseFloat(order.remaining_amount || 0);
                const amount = parseFloat(order.total_amount || 0).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                const displayAmount = currencyPosition === 'right' ?
                    amount + currencySymbol : currencySymbol + amount;


                // Build action buttons HTML
                let actionBtns = '';
                @if (app('hasPermission')(2, 'view'))
                    // if (status === 'sales') {
                    if (parseFloat(order.remaining_amount || 0) > 0 && status === 'sales') {
                        actionBtns += `<a href="javascript:void(0);" class="btn btn-sm btn-primary make-payment-btn"
                        data-bs-toggle="modal" data-bs-target="#makePaymentModal"
                        data-id="${order.id}" data-amount="${order.remaining_amount}"
                        data-method="${order.payment_method || ''}"
                        data-emi-months="${order.remaining_emi_months}"
                        data-emi-duration="${order.emi_duration || 0}"
                        data-total-amount="${order.total_amount || 0}"
                        data-remaining-amount="${order.remaining_amount}"
                        data-return-amount="${order.total_return || 0}"
                        data-remaining-emi-months="${order.remaining_emi_months}"
                        data-tds-percentage="${order.tds_percentage || 0}"
                        data-tds-amount="${order.tds_amount || 0}"
                        data-emi-tenure="${order.emi_tenure || 0}"
                        data-emi-monthly="${order.emi_monthly_amount || 0}"
                        title="Make Payment">
                        <i class="fas fa-money-bill"></i> Pay
                    </a>`;
                    }
                @endif

                @if (app('hasPermission')(2, 'view'))
                    actionBtns += `<button class="btn btn-sm btn-secondary open-history" data-id="${order.id}" title="Payment History">
                    <i class="fas fa-history"></i> History
                </button>`;
                @endif

                if ((order.quotation_status || '').toLowerCase() === 'quotation') {
                    actionBtns += `<a class="btn btn-sm btn-success convert-to-sales" href="javascript:void(0);" data-id="${order.id}" title="Convert to Sales">
                        Convert to Sales
                    </a>`;
                }

                @if (app('hasPermission')(2, 'view'))
                    actionBtns += `<a class="btn btn-sm btn-info" href="/sales-details/${order.id}">
                        <i class="fas fa-eye"></i> View
                    </a>`;
                @endif

                // if (!order.has_payment || order.has_payment === 0) {
                @if (app('hasPermission')(2, 'edit'))
                    if (parseFloat(order.total_return || 0) === 0) {
                        actionBtns += `<a class="btn btn-sm btn-warning" href="/edit-sales/${order.id}">
                                <i class="fas fa-edit"></i> Edit
                            </a>`;
                    }
                @endif
                // }

                @if (app('hasPermission')(2, 'view'))
                    actionBtns += `<a class="btn btn-sm btn-success" href="/sales-invoice/${order.id}">
                        <i class="fas fa-file-invoice"></i> Invoice
                    </a>`;
                @endif

                if (userRole !== 'sales-manager' && userRole !== 'purchase-manager' && userRole !==
                    'inventory-manager') {
                    @if (app('hasPermission')(2, 'delete'))
                        actionBtns += `<a class="btn btn-sm btn-danger delete-order" href="javascript:void(0);" data-id="${order.id}">
                            <i class="fas fa-trash"></i> Delete
                        </a>`;
                    @endif
                }

                const cardHtml = `
                    <div class="mobile-order-item" data-order-id="${order.id}">
                        <div class="mobile-order-row">
                            <div class="mobile-order-cell mobile-order-number">${order.order_number || 'N/A'}</div>
                            <div class="mobile-order-cell mobile-order-details-cell">
                                <button class="mobile-toggle-btn" onclick="toggleMobileDetails('${order.id}')" data-order-id="${order.id}">
                                    <span class="toggle-icon">+</span>
                                </button>
                            </div>
                        </div>
                        <div class="mobile-order-details" id="mobile-details-${order.id}">
                            <div class="mobile-detail-row">
                                <span class="mobile-detail-label">Order Number:</span>
                                <span class="mobile-detail-value">${order.order_number || 'N/A'}</span>
                            </div>
                            <div class="mobile-detail-row">
                                <span class="mobile-detail-label">Date:</span>
                                <span class="mobile-detail-value">${order.created_date || 'N/A'}</span>
                            </div>
                            <div class="mobile-detail-row">
                                <span class="mobile-detail-label">Customer Name:</span>
                                <span class="mobile-detail-value">${order.user?.name || 'N/A'}</span>
                            </div>
                            <div class="mobile-detail-row">
                                <span class="mobile-detail-label">Quotation/Sale:</span>
                                <span class="mobile-detail-value">${getMobileStatusBadge(order.quotation_status || 'sales', 'quotation')}</span>
                            </div>
                            <div class="mobile-detail-row">
                                <span class="mobile-detail-label">Payment Status:</span>
                                <span class="mobile-detail-value">
                                    ${getMobileStatusBadge(order.payment_status, 'payment', order.extra_paid || 0)}
                                </span>
                            </div>
                            <div class="mobile-detail-row">
                                <span class="mobile-detail-label">Return Status:</span>
                                <span class="mobile-detail-value">
                                    ${getMobileStatusBadge(parseFloat(order.total_return || 0) > 0 ? 'returned' : '', 'return')}
                                </span>
                            </div>
                            ${parseFloat(order.tds_amount || 0) > 0 ? `
                                                                <div class="mobile-detail-row">
                                                                    <span class="mobile-detail-label" style="color: #6f42c1;">TDS (${parseFloat(order.tds_percentage || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}%):</span>
                                                                    <span class="mobile-detail-value" style="color: #6f42c1; font-weight: bold;">
                                                                        ${currencySymbol}${parseFloat(order.tds_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                                    </span>
                                                                </div>
                                                            ` : ''}
                            ${order.extra_paid > 0 ? `
                                                                <div class="mobile-detail-row">
                                                                    <span class="mobile-detail-label" style="color: #dc3545;">Extra Paid:</span>
                                                                    <span class="mobile-detail-value" style="color: #dc3545; font-weight: bold;">
                                                                        ${currencySymbol}${parseFloat(order.extra_paid || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                                    </span>
                                                                </div>
                                                            ` : ''}
                            <div class="mobile-detail-row">
                                <span class="mobile-detail-label">Payment Method:</span>
                                <span class="mobile-detail-value">
                                    ${getMobileStatusBadge(order.payment_method, 'method')}
                                </span>
                            </div>
                            <div class="mobile-detail-row">
                                <span class="mobile-detail-label">Total:</span>
                                <span class="mobile-detail-value" style="font-weight: bold; color: #ff9f43;">${displayAmount}</span>
                            </div>
                            <div class="mobile-detail-row">
                                <span class="mobile-detail-label">Biller:</span>
                                <span class="mobile-detail-value">${order.biller || 'Admin'}</span>
                            </div>
                            ${parseFloat(order.remaining_amount || 0) > 0 &&
                            (order.quotation_status || 'sales').toLowerCase() === 'sales' ? `
                                                                <div class="mobile-detail-row">
                                                                    <span class="mobile-detail-label">Remaining:</span>
                                                                    <span class="mobile-detail-value" style="color: #dc3545; font-weight: bold;">
                                                                        ${currencySymbol}${parseFloat(order.remaining_amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                                    </span>
                                                                </div>
                                                            ` : ''}
                            <div class="mobile-action-buttons">
                                ${actionBtns}
                            </div>
                        </div>
                    </div>
                `;
                container.append(cardHtml);
            });
        }

        // Toggle mobile details function
        function toggleMobileDetails(orderId) {
            const details = $(`#mobile-details-${orderId}`);
            const btn = $(`.mobile-toggle-btn[data-order-id="${orderId}"]`);
            const icon = btn.find('.toggle-icon');

            if (details.hasClass('active')) {
                details.removeClass('active');
                btn.removeClass('minus');
                icon.text('+');
            } else {
                details.addClass('active');
                btn.addClass('minus');
                icon.text('-');
            }
        }

        // Helper function to build expandable row content
        function buildExpandableRowContent(order, currencySymbol, currencyPosition) {
            const amount = parseFloat(order.total_amount || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            const displayAmount = currencyPosition === 'right' ?
                amount + currencySymbol : currencySymbol + amount;

            let actionBtns = '';

            // History icon button
            actionBtns += `<button class="btn-icon-mobile btn-history open-history" data-id="${order.id}" title="Payment History">
                <i class="fas fa-history"></i>
            </button>`;

            // View icon button
            @if (app('hasPermission')(2, 'view'))
                actionBtns += `<a class="btn-icon-mobile btn-view" href="/sales-details/${order.id}" title="View">
                    <i class="fas fa-eye"></i>
                </a>`;
            @endif

            // Edit icon button
            // if (!order.has_payment || order.has_payment === 0) {
            @if (app('hasPermission')(2, 'edit'))
                if (parseFloat(order.total_return || 0) === 0) {
                    actionBtns += `<a class="btn-icon-mobile btn-edit" href="/edit-sales/${order.id}" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>`;
                }
            @endif
            // }

            // Download icon button (Invoice)
            @if (app('hasPermission')(2, 'view'))
                actionBtns += `<a class="btn-icon-mobile btn-download" href="/sales-invoice/${order.id}" title="Download Invoice">
                    <i class="fas fa-file-invoice"></i>
                </a>`;
            @endif

            // Print icon button
            @if (app('hasPermission')(2, 'view'))
                actionBtns += `<a class="btn-icon-mobile btn-print" href="javascript:void(0);" onclick="window.open('/sales/invoice/pdf/' + ${order.id});" title="Print Invoice">
                    <i class="fas fa-print"></i>
                </a>`;
            @endif

            // Delete icon button
            if (userRole !== 'sales-manager' && userRole !== 'purchase-manager' && userRole !== 'inventory-manager') {
                @if (app('hasPermission')(2, 'delete'))
                    actionBtns += `<a class="btn-icon-mobile btn-delete delete-order" href="javascript:void(0);" data-id="${order.id}" title="Delete">
                        <i class="fas fa-trash"></i>
                    </a>`;
                @endif
            }

            // Payment icon button if there's remaining amount

            if (
                (order.quotation_status || 'sales').toLowerCase() === 'sales'
            ) {
                actionBtns += `<a href="javascript:void(0);" class="btn-icon-mobile btn-payment make-payment-btn"
                    data-bs-toggle="modal" data-bs-target="#makePaymentModal"
                    data-id="${order.id}" data-amount="${order.remaining_amount}"
                    data-method="${order.payment_method || ''}"
                    data-emi-months="${order.remaining_emi_months}"
                    data-emi-duration="${order.emi_duration || 0}"
                    data-total-amount="${order.total_amount || 0}"
                    data-remaining-amount="${order.remaining_amount}"
                    data-return-amount="${order.total_return || 0}"
                    data-remaining-emi-months="${order.remaining_emi_months}"
                    data-tds-percentage="${order.tds_percentage || 0}"
                    data-tds-amount="${order.tds_amount || 0}"
                    data-emi-tenure="${order.emi_tenure || 0}"
                    data-emi-monthly="${order.emi_monthly_amount || 0}"
                    title="Make Payment">
                    <i class="fas fa-money-bill-wave"></i>
                </a>`;
            }

            return `
        <td colspan="9" class="order-details-content">
            <div class="order-details-list">
                <div class="order-detail-row-simple">
                    <span class="order-detail-label-simple">Order Number:</span>
                    <span class="order-detail-value-simple">${order.order_number || 'N/A'}</span>
                </div>
                <div class="order-detail-row-simple">
                    <span class="order-detail-label-simple">Date:</span>
                    <span class="order-detail-value-simple">${order.created_date || 'N/A'}</span>
                </div>
                <div class="order-detail-row-simple">
                    <span class="order-detail-label-simple">Customer Name:</span>
                    <span class="order-detail-value-simple">${order.user?.name || 'N/A'}</span>
                </div>
                <div class="order-detail-row-simple">
                    <span class="order-detail-label-simple">Quotation/Sale:</span>
                    <span class="order-detail-value-simple">
                        ${getStatusBadge(order.quotation_status || 'sales', 'quotation')}
                    </span>
                </div>
                <div class="order-detail-row-simple">
                    <span class="order-detail-label-simple">Payment Status:</span>
                    <span class="order-detail-value-simple">
                        ${getStatusBadge(order.payment_status, 'payment', order.extra_paid || 0)}
                    </span>
                </div>
                <div class="order-detail-row-simple">
                    <span class="order-detail-label-simple">Return Status:</span>
                    <span class="order-detail-value-simple">
                        ${getStatusBadge(parseFloat(order.total_return || 0) > 0 ? 'returned' : '', 'return')}
                    </span>
                </div>
                ${parseFloat(order.tds_amount || 0) > 0 ? `
                                                    <div class="order-detail-row-simple">
                                                        <span class="order-detail-label-simple" style="color: #6f42c1;">TDS (${parseFloat(order.tds_percentage || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}%):</span>
                                                        <span class="order-detail-value-simple" style="color: #6f42c1; font-weight: bold;">
                                                            ${currencySymbol}${parseFloat(order.tds_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                        </span>
                                                    </div>
                                                ` : ''}
                ${(order.extra_paid || 0) > 0 ? `
                                                    <div class="order-detail-row-simple">
                                                        <span class="order-detail-label-simple" style="color: #dc3545;">Extra Paid:</span>
                                                        <span class="order-detail-value-simple" style="color: #dc3545; font-weight: bold;">
                                                            ${currencySymbol}${parseFloat(order.extra_paid).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} (Advance/Refund)
                                                        </span>
                                                    </div>
                                                ` : ''}
                <div class="order-detail-row-simple">
                    <span class="order-detail-label-simple">Payment Method:</span>
                    <span class="order-detail-value-simple">
                        ${getStatusBadge(order.payment_method, 'method')}
                    </span>
                </div>
                <div class="order-detail-row-simple">
                    <span class="order-detail-label-simple">Total:</span>
                    <span class="order-detail-value-simple" style="font-weight: bold; color: #ff9f43;">${displayAmount}</span>
                </div>
                <div class="order-detail-row-simple">
                    <span class="order-detail-label-simple">Biller:</span>
                    <span class="order-detail-value-simple">${order.biller || 'Admin'}</span>
                </div>
                ${parseFloat(order.remaining_amount || 0) > 0 &&
(order.quotation_status || 'sales').toLowerCase() === 'sales' ? `
                                                    <div class="order-detail-row-simple">
                                                        <span class="order-detail-label-simple">Remaining:</span>
                                                        <span class="order-detail-value-simple" style="color: #dc3545; font-weight: bold;">
                                                            ${currencySymbol}${parseFloat(order.remaining_amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                        </span>
                                                    </div>
                                                ` : ''}
            </div>
            <div class="mobile-action-buttons-simple">
                ${actionBtns}
            </div>
        </td>
    `;
        }

        // Toggle function for table rows
        function toggleTableRowDetails(orderId) {
            // Find the button that was clicked
            const btn = $(`.mobile-toggle-btn-table[data-order-id="${orderId}"]`);
            if (btn.length === 0) {
                // console.error('Toggle button not found for order:', orderId);
                return;
            }

            const row = btn.closest('tr');
            let detailsRow = row.next(`tr.order-details-row[data-order-id="${orderId}"]`);
            const icon = btn.find('.toggle-icon');

            // If expandable row doesn't exist, create it
            if (detailsRow.length === 0) {
                const orderData = window.orderDataMap && window.orderDataMap[orderId];
                if (orderData) {
                    detailsRow = $('<tr>')
                        .addClass('order-details-row')
                        .attr('data-order-id', orderId)
                        .html(buildExpandableRowContent(orderData, orderData.currencySymbol, orderData.currencyPosition));
                    row.after(detailsRow);
                } else {
                    // console.error('Order data not found for order:', orderId);
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
                icon.text('-');
            }
        }

        // Global variables
        var table;
        window.salesSummaryTotals = window.salesSummaryTotals || null;

        // Function to calculate total for visible rows - must be global
        function calculateFilteredTotal() {
            if (window.salesSummaryTotals) {
                updateSalesSummaryTotals(
                    window.salesSummaryTotals.total_amount || 0,
                    window.salesSummaryTotals.total_pending_amount || 0,
                    window.salesSummaryTotals.total_paid_amount || 0,
                    window.salesSummaryTotals.currency_symbol || '₹',
                    window.salesSummaryTotals.currency_position || 'left'
                );
                return;
            }

            if (!table) {
                table = $('.datanew').DataTable();
            }

            let total = 0;

            // Find the Total column index by header name
            let totalColumnIndex = -1;
            table.columns().every(function() {
                const header = $(this.header());
                if (header.text().trim() === 'Total') {
                    totalColumnIndex = this.index();
                    return false; // break
                }
            });

            // If column not found by name, use index 6 as fallback
            if (totalColumnIndex === -1) {
                totalColumnIndex = 6;
            }

            table.rows({
                filter: 'applied'
            }).every(function() {
                const row = this.data();
                if (row[totalColumnIndex]) {
                    const amountText = row[totalColumnIndex];
                    const rawAmount = parseFloat(amountText.replace(/[^0-9.-]+/g, '')) || 0;
                    total += rawAmount;
                }
            });

            const currencySymbol = "₹";
            $('#filtered-total').text(
                `${currencySymbol}${total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
            );
        }

        // Normalize filter values: treat "all" or empty as no-filter ('')
        function normalizeFilterValue(val) {
            if (typeof val === 'undefined' || val === null) return '';
            return (String(val) === '' || String(val) === 'all') ? '' : val;
        }

        function formatSummaryAmount(amount, currencySymbol, currencyPosition) {
            const numericAmount = parseFloat(amount || 0);
            const formattedAmount = numericAmount.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            return currencyPosition === 'right' ?
                `${formattedAmount}${currencySymbol}` :
                `${currencySymbol}${formattedAmount}`;
        }

        function updateSalesSummaryTotals(totalAmount, totalPendingAmount, totalPaidAmount, currencySymbol,
            currencyPosition) {
            $('#filtered-total').text(formatSummaryAmount(totalAmount, currencySymbol, currencyPosition));
            $('#filtered-pending-total').text(formatSummaryAmount(totalPendingAmount, currencySymbol,
                currencyPosition));
            $('#filtered-paid-total').text(formatSummaryAmount(totalPaidAmount, currencySymbol,
                currencyPosition));
        }

        const salesCalendarYears = @json($years ?? []);

        function buildFinancialYearOptions(yearValues) {
            const fyStartYears = new Set();
            (Array.isArray(yearValues) ? yearValues : []).forEach((yearVal) => {
                const year = parseInt(yearVal, 10);
                if (!Number.isNaN(year)) {
                    fyStartYears.add(year - 1);
                    fyStartYears.add(year);
                }
            });

            const currentYear = new Date().getFullYear();
            fyStartYears.add(currentYear - 1);
            fyStartYears.add(currentYear);

            return Array.from(fyStartYears)
                .sort((a, b) => b - a)
                .map((startYear) => `${startYear}-${startYear + 1}`);
        }

        function populateSalesFinancialYears() {
            const $financialYear = $('#filter-financial-year');
            const options = buildFinancialYearOptions(salesCalendarYears);
            $financialYear.empty().append('<option value="all">All Financial Years</option>');
            options.forEach((financialYear) => {
                $financialYear.append(`<option value="${financialYear}">${financialYear}</option>`);
            });
        }

        // Helper function to update Select2 display for filters
        function updateSelect2Display(selectedMonth, selectedYear) {
            try {
                let paddedMonth = '';
                if (typeof selectedMonth !== 'undefined' && selectedMonth !== null && selectedMonth !== '') {
                    paddedMonth = (String(selectedMonth).length === 1) ? ('0' + String(selectedMonth)) : String(
                        selectedMonth);
                }

                if (paddedMonth !== '') {
                    $('#filter-month').val(paddedMonth).trigger('change.select2');
                } else {
                    $('#filter-month').val('all').trigger('change.select2');
                }

                if (typeof selectedYear !== 'undefined' && selectedYear !== null && selectedYear !== '') {
                    $('#filter-year').val(String(selectedYear)).trigger('change.select2');
                } else {
                    $('#filter-year').val('all').trigger('change.select2');
                }

                // Update Select2 rendered text directly to ensure UI is in sync
                const monthText = $('#filter-month option:selected').text() || 'All Months';
                const yearText = $('#filter-year option:selected').text() || 'All Years';
                const monthRendered = $('#filter-month').next('.select2-container').find('.select2-selection__rendered');
                const yearRendered = $('#filter-year').next('.select2-container').find('.select2-selection__rendered');
                if (monthRendered.length) monthRendered.text(monthText.trim());
                if (yearRendered.length) yearRendered.text(yearText.trim());
            } catch (e) {
                // console.warn('Failed to update Select2 display:', e);
            }
        }

        $(document).ready(function() {
            // Initialize each Select2 with its own placeholder so empty value shows label
            $('.filter-select2').each(function() {
                const placeholder = $(this).data('placeholder') || '';
                $(this).select2({
                    width: '100%',
                    placeholder: placeholder,
                    allowClear: true,
                    templateSelection: function(state) {
                        return (state && state.text) ? state.text.trim() : placeholder;
                    },
                    templateResult: function(state) {
                        return (state && state.text) ? state.text.trim() : '';
                    }
                });
            });

            const syncFilterSelect2Display = () => {
                $('#filter-month, #filter-year').each(function() {
                    $(this).trigger('change.select2');
                });
            };
            syncFilterSelect2Display();

            let skipDateResetFromMonthYear = false;


            var authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            const addBankModalElement = document.getElementById('addBankModal');
            const addBankModal = addBankModalElement ? new bootstrap.Modal(addBankModalElement) : null;
            let currentPage = 1;
            let lastPage = 1;
            let perPage = 10;
            let searchQuery = '';
            let isFinancialYearEnabled = false;
            table = $('.datanew').DataTable();
            populateSalesFinancialYears();

            function resetAddBankForm() {
                $('#addBankForm')[0].reset();
                $('#add_opening_balance').val('0');
                $('#add_bank_status').val('1');
                $('#addBankForm .text-danger').text('');
            }

            function upsertBankOption(bank) {
                if (!bank || !bank.id) {
                    return;
                }

                const bankId = String(bank.id);
                const bankName = bank.bank_name || 'Unnamed Bank';
                const existingOption = $('#bank_id option[value="' + bankId + '"]');

                if (existingOption.length) {
                    existingOption.text(bankName);
                } else {
                    $('#bank_id').append(new Option(bankName, bankId));
                }

                $('#bank_id').val(bankId).trigger('change');
            }

            function toggleSalesFinancialYearFilter(enabled) {
                isFinancialYearEnabled = (enabled === true || enabled === 1 || String(enabled).toLowerCase() ===
                    'true' || String(enabled).toLowerCase() === 'on' || String(enabled) === '1');
                const $financialYearWrapper = $('#sales-financial-year-filter');
                if (isFinancialYearEnabled) {
                    $financialYearWrapper.removeClass('d-none');
                } else {
                    $financialYearWrapper.addClass('d-none');
                    $('#filter-financial-year').val('all');
                }
            }

            $('#search-input').on('keyup', function() {
                searchQuery = $(this).val();
                loadOrders(1);
            });

            $('#sales-per-page-select').on('change', function() {
                perPage = $(this).val();
                table.page.len(parseInt(perPage, 10)).draw(false);
                loadOrders(1);
            });

            $(document).on('click', '.make-payment-btn', function() {
                let jobCardId = $(this).data('id');
                let totalAmount = $(this).data('total-amount');
                let remainingAmount = $(this).data('remaining-amount');
                let returnAmount = parseFloat($(this).data('return-amount')) || 0;
                let method = $(this).data('method') || '';
                let tdsPercentage = parseFloat($(this).data('tds-percentage')) || 0;
                let tdsAmount = parseFloat($(this).data('tds-amount')) || 0;
                let emiTenure = parseInt($(this).data('emi-tenure')) || 0;
                let emiMonthly = parseFloat($(this).data('emi-monthly')) || 0;

                // ✅ Fill modal hidden inputs + text spans
                $('#paymentMethodSelect').data('emi-tenure', emiTenure);
                $('#paymentMethodSelect').data('emi-monthly', emiMonthly);
                $('#paymentMethodSelect').data('order-id', jobCardId);
                $('#paymentJobCardId').val(jobCardId);
                $('#emiTotal').text(parseFloat(totalAmount).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#remainingAmountDisplay').text(parseFloat(remainingAmount).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                // ✅ Show TDS row only when TDS is applied
                if (tdsAmount > 0) {
                    $('#tdsPercentageDisplay').text(tdsPercentage.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    $('#tdsAmountDisplay').text(tdsAmount.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    $('#tdsAmountSection').removeClass('d-none');
                } else {
                    $('#tdsAmountSection').addClass('d-none');
                }

                if (returnAmount > 0) {
                    $('#returnAmountDisplay').text(returnAmount.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    $('#returnAmountSection').removeClass('d-none');
                } else {
                    $('#returnAmountSection').addClass('d-none');
                }

                $('#remainingAmountHidden').val(remainingAmount);
                $('#paymentMethodHidden').val(method);

                // ✅ Set payment date to today's date by default
                const today = new Date();
                const dd = String(today.getDate()).padStart(2, '0');
                const mm = String(today.getMonth() + 1).padStart(2, '0');
                const yyyy = today.getFullYear();
                const todayFormatted = `${dd}-${mm}-${yyyy}`;
                $('#paymentDateInput').val(todayFormatted);

                // ✅ Reset payment method dropdown to default
                $('#paymentMethodSelect').val('');

                // ✅ Hide history box initially
                $('#paymentHistoryBox').addClass('d-none');
                $('#paymentHistoryList').html('');

                // ✅ Bind View History button
                $('#viewHistoryBtn').off('click').on('click', function() {
                    $.ajax({
                        url: '/api/sales/payment-history/' + jobCardId,
                        method: 'GET',
                        headers: {
                            "Authorization": "Bearer " + authToken,
                        },
                        success: function(response) {
                            const history = response.data;

                            if (!history || history.length === 0) {
                                $('#paymentHistoryList').html(
                                    '<li>No payment history found.</li>');
                            } else {
                                historyHtml = '';

                                history.forEach(function(payment) {

                                    const paymentMethod = payment
                                        .payment_method ?
                                        payment.payment_method.charAt(0)
                                        .toUpperCase() + payment.payment_method
                                        .slice(1).toLowerCase() :
                                        'N/A';

                                    const paymentType = payment.payment_type ?
                                        payment.payment_type.charAt(0)
                                        .toUpperCase() + payment.payment_type
                                        .slice(1).toLowerCase() :
                                        'N/A';

                                    const paymentRemark = payment.remarks &&
                                        String(payment.remarks).trim() !== '' ?
                                        payment.remarks :
                                        'N/A';

                                    historyHtml += `
        <li class="mb-2">
            <strong>Amount:</strong> ₹${parseFloat(payment.payment_amount).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}<br>

            <strong>Date:</strong> ${formatPaymentHistoryDate(payment.payment_date || payment.created_at)}<br>

            <strong>Method:</strong> ${paymentMethod}<br>

            <strong>Payment Type:</strong> ${paymentType}<br>

            <strong>Remark:</strong> ${paymentRemark}<br>

            ${payment.payment_method === 'emi'
                ? `<strong>EMI Months:</strong> ${payment.emi_month || 0}<br>`
                : ''}
        </li>

        <hr class="my-1"/>
    `;
                                });

                                $('#paymentHistoryList').html(historyHtml);
                                // Add Summary
                                if (response.summary) {
                                    let summaryHtml = `
                                        <hr class="my-2"/>
                                        <li class="mb-1"><strong>Order Total:</strong> ₹${parseFloat(response.summary.order_total).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</li>
                                        <li class="mb-1"><strong>Total Paid:</strong> ₹${parseFloat(response.summary.total_paid).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</li>
                                        <li class="mb-1"><strong>Return Amount:</strong> ₹${parseFloat(response.summary.return_amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</li>
                                        <li class="mb-1"><strong>Remaining:</strong> ₹${parseFloat(response.summary.remaining).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</li>
                                    `;
                                    $('#paymentHistoryList').append(summaryHtml);
                                }
                            }

                            $('#paymentHistoryBox').removeClass('d-none');
                        },
                        error: function() {
                            $('#paymentHistoryList').html(
                                '<li class="text-danger">Failed to load payment history.</li>'
                            );
                            $('#paymentHistoryBox').removeClass('d-none');
                        }
                    });
                });

                // ✅ Close history
                $('#closeHistoryBtn').off('click').on('click', function() {
                    $('#paymentHistoryBox').addClass('d-none');
                });
            });

            $('#paymentMethodSelect').on('change', function() {
                let method = $(this).val();

                // Hide all optional sections first
                $('#cashOnlineTypeDiv, #fullyCashOnlineFields, #partialCashOnlineFields, #onlineTypeDiv, #paidTypeDiv, #upiAmountDiv, #partialPaidFields, #fullyPaidFields, #bank_container, #emiFields')
                    .addClass('d-none');
                $('#bank_id').val('');
                $("#bankError").text("");

                if (method === 'cash') {
                    $('#paidTypeDiv').removeClass('d-none'); // Show paid type options

                } else if (method === 'online') {
                    $('#onlineTypeDiv').removeClass('d-none'); // Show online type dropdown
                    $('#bank_container').removeClass('d-none');

                } else if (method === 'cash_online') {
                    $('#cashOnlineTypeDiv').removeClass('d-none'); // Show Cash + Online type dropdown
                    $('#bank_container').removeClass('d-none');
                } else if (method === 'emi') {
                    $('#bank_container').removeClass('d-none');
                    $('#emiFields').removeClass('d-none');

                    let emiTenure = parseInt($(this).data('emi-tenure')) || 0;
                    let emiMonthly = parseFloat($(this).data('emi-monthly')) || 0;
                    let orderId = $(this).data('order-id');

                    $('#emiMonthlyAmount').val(emiMonthly);

                    if (orderId && emiTenure > 0) {
                        $.ajax({
                            url: '/api/sales/payment-history/' + orderId,
                            method: 'GET',
                            headers: {
                                "Authorization": "Bearer " + authToken
                            },
                            success: function(response) {
                                let history = response.data || [];
                                let paidCount = history.filter(p => p.payment_type === 'emi' ||
                                    p.payment_method === 'emi').length;

                                let options =
                                    '<option value="" selected disabled>Select EMI Month</option>';
                                for (let i = 1; i <= emiTenure; i++) {
                                    let suffix = 'th';
                                    if (![11, 12, 13].includes(i % 100)) {
                                        switch (i % 10) {
                                            case 1:
                                                suffix = 'st';
                                                break;
                                            case 2:
                                                suffix = 'nd';
                                                break;
                                            case 3:
                                                suffix = 'rd';
                                                break;
                                        }
                                    }
                                    let label = i + suffix + ' Month - ₹' + emiMonthly;
                                    if (i <= paidCount) {
                                        options +=
                                            `<option value="${i}" disabled>${label} (Paid)</option>`;
                                    } else if (i === paidCount + 1) {
                                        options +=
                                            `<option value="${i}">${label} (Pay now)</option>`;
                                    } else {
                                        options += `<option value="${i}">${label}</option>`;
                                    }
                                }
                                $('#emiMonthSelect').html(options);
                                if (paidCount < emiTenure) {
                                    $('#emiMonthSelect').val(paidCount + 1);
                                }
                            }
                        });
                    }
                }
            });

            $('#bank_id').on('change', function() {
                if ($(this).val()) {
                    $("#bankError").text("");
                }
            });

            $('#openAddBankModal').on('click', function() {
                resetAddBankForm();
                if (addBankModal) {
                    addBankModal.show();
                }
            });

            $('#addBankModal').on('hidden.bs.modal', function() {
                if ($('#makePaymentModal').hasClass('show')) {
                    $('body').addClass('modal-open');
                }
            });

            $('#addBankForm').on('submit', function(e) {
                e.preventDefault();

                $('#addBankForm .text-danger').text('');

                const formData = new FormData(this);
                if (selectedSubAdminId) {
                    formData.append('selectedSubAdminId', selectedSubAdminId);
                }

                const saveButton = $('#saveBankBtn');
                saveButton.prop('disabled', true).text('Saving...');

                $.ajax({
                    url: '/api/banks',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        upsertBankOption(response.data || null);
                        $("#bankError").text("");

                        if (addBankModal) {
                            addBankModal.hide();
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message || 'Bank added successfully.',
                            confirmButtonText: 'OK'
                        });
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors || {};

                        $('#addBankNameError').text(errors.bank_name ? errors.bank_name[0] :
                            '');
                        $('#addAccountNumberError').text(errors.account_number ? errors
                            .account_number[0] : '');
                        $('#addIfscCodeError').text(errors.ifsc_code ? errors.ifsc_code[0] :
                            '');
                        $('#addBranchNameError').text(errors.branch_name ? errors.branch_name[
                            0] : '');
                        $('#addOpeningBalanceError').text(errors.opening_balance ? errors
                            .opening_balance[0] : '');
                        $('#addBankStatusError').text(errors.status ? errors.status[0] : '');

                        if (!Object.keys(errors).length) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message ||
                                    'Failed to add bank.',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    complete: function() {
                        saveButton.prop('disabled', false).text('Save Bank');
                    }
                });
            });

            $('#paidTypeSelect').on('change', function() {
                let type = $(this).val();
                let remaining = parseFloat($('#remainingAmountHidden').val()) || 0;

                // Hide both sections
                $('#partialPaidFields, #fullyPaidFields').addClass('d-none');

                // Disable all inputs first
                $('#partialPaidFields input, #fullyPaidFields input').prop('disabled', true);

                if (type === 'cash_partially') {
                    // Show partial fields
                    $('#partialPaidFields').removeClass('d-none');
                    $('#partialPaidFields input').prop('disabled', false);

                    // Clear & reset values
                    $('#partialAmount').val('');
                    $('#pendingAmount').val(remaining.toFixed(2));

                    // Remove any full cash amount
                    $('#cashAmount').val('').prop('readonly', false).prop('disabled', true);

                    // Live calculation for pending
                    $('#partialAmount').off('input').on('input', function() {
                        let entered = parseFloat($(this).val()) || 0;
                        let newPending = Math.max(remaining - entered, 0);
                        $('#pendingAmount').val(newPending.toFixed(2));
                    });

                } else if (type === 'cash_fully') {
                    // Show fully fields
                    $('#fullyPaidFields').removeClass('d-none');
                    $('#fullyPaidFields input').prop('disabled', false);

                    // Fill full amount & disable editing
                    $('#cashAmount').val(remaining.toFixed(2)).prop('readonly', true);

                    // Reset partial fields
                    $('#partialAmount, #pendingAmount').val('');
                    $('#partialPaidFields input').prop('disabled', true);
                }
            });

            $('#onlineTypeSelect').on('change', function() {
                let type = $(this).val();
                let remaining = parseFloat($('#remainingAmountHidden').val()) || 0;

                // Hide both sections
                $('#upiAmountDiv, #partialPaidFields').addClass('d-none');
                // Disable all inputs first
                $('#upiAmountDiv input, #partialPaidFields input').prop('disabled', true);

                if (type === 'online_partially') {
                    // Show partial fields
                    $('#partialPaidFields').removeClass('d-none');
                    $('#partialPaidFields input').prop('disabled', false);

                    // Reset values
                    $('#partialAmount').val('');
                    $('#pendingAmount').val(remaining.toFixed(2));

                    // Clear and disable UPI field
                    $('#upiAmountInput').val('').prop('readonly', false).prop('disabled', true);

                    // Live pending update
                    $('#partialAmount').off('input').on('input', function() {
                        let entered = parseFloat($(this).val()) || 0;
                        let newPending = Math.max(remaining - entered, 0);
                        $('#pendingAmount').val(newPending.toFixed(2));
                    });

                } else if (type === 'online_fully') {
                    // Show fully online section
                    $('#upiAmountDiv').removeClass('d-none');
                    $('#upiAmountDiv input').prop('disabled', false);

                    // Fill with remaining and lock editing
                    $('#upiAmountInput').val(remaining.toFixed(2)).prop('readonly', true);

                    // Reset partial section
                    $('#partialAmount, #pendingAmount').val('');
                    $('#partialPaidFields input').prop('disabled', true);
                }
            });

            $('#cashOnlineTypeSelect').on('change', function() {
                let type = $(this).val();
                let remaining = parseFloat($('#remainingAmountHidden').val()) || 0;

                // Hide both sections
                $('#fullyCashOnlineFields, #partialCashOnlineFields').addClass('d-none');
                // Disable all inputs first
                $('#fullyCashOnlineFields input, #partialCashOnlineFields input').prop('disabled', true);

                if (type === 'cash_online_fully') {
                    // Show fully section
                    $('#fullyCashOnlineFields').removeClass('d-none');
                    $('#fullyCashOnlineFields input').prop('disabled', false);

                    // Reset values
                    $('#cashOnlineFullAmount').val('');
                    $('#upiOnlineFullAmount').val(remaining.toFixed(2));

                    // Live adjustment of online amount
                    $('#cashOnlineFullAmount').off('input').on('input', function() {
                        let cash = parseFloat($(this).val()) || 0;
                        let online = Math.max(remaining - cash, 0);
                        $('#upiOnlineFullAmount').val(online.toFixed(2));
                    });

                    // Disable partial fields
                    $('#partialCashOnlineFields input').prop('disabled', true);

                } else if (type === 'cash_online_partially') {
                    // Show partial section
                    $('#partialCashOnlineFields').removeClass('d-none');
                    $('#partialCashOnlineFields input').prop('disabled', false);

                    // Reset values
                    $('#cashOnlinePartialAmount, #upiOnlinePartialAmount').val('');
                    $('#remainingCashOnlineAmount').val(remaining.toFixed(2));

                    // Live update on cash input
                    $('#cashOnlinePartialAmount').off('input').on('input', function() {
                        let cash = parseFloat($(this).val()) || 0;
                        let newRemaining = Math.max(remaining - cash, 0);
                        $('#remainingCashOnlineAmount').val(newRemaining.toFixed(2));
                    });

                    // Live update on online input
                    $('#upiOnlinePartialAmount').off('input').on('input', function() {
                        let online = parseFloat($(this).val()) || 0;
                        let cash = parseFloat($('#cashOnlinePartialAmount').val()) || 0;
                        let newRemaining = Math.max(remaining - cash - online, 0);
                        // console.log("newRemaining:", newRemaining);
                        $('#remainingCashOnlineAmount').val(newRemaining.toFixed(2));
                    });

                    // Disable fully fields
                    $('#fullyCashOnlineFields input').prop('disabled', true);
                }
            });

            $('#makePaymentForm').on('submit', function(e) {
                e.preventDefault();


                let isValid = true;



                // Clear all previous errors
                $('.text-danger').text('');

                // Get values
                let paymentMethod = $('#paymentMethodSelect').val();
                let paymentTypeemionly = $('#paymentType').val();
                let paidType = $('#paidTypeSelect').val();
                let onlineType = $('#onlineTypeSelect').val();
                let cashOnlineType = $('#cashOnlineTypeSelect').val();



                // EMI Validation



                // Payment Method validation


                if (!paymentMethod) {
                    isValid = false;
                    // console.log("Validation failed: Payment method not selected");
                    $('#paymentMethodError').text("Please select a payment method.");
                    return false;
                } else {
                    console.log("Payment method selected:", paymentMethod);
                }


                // Cash Payment Validation
                if (paymentMethod === 'cash') {
                    // console.log("Cash payment selected");

                    if (!paidType) {
                        isValid = false;
                        // console.log("Validation failed: Paid type not selected");
                        $('#paidTypeError').text("Please select paid type.");
                        return false;
                    } else {
                        // console.log("Paid type selected:", paidType);
                    }

                    if (paidType === 'cash_partially') {
                        let partialAmount = parseFloat($('#partialAmount').val()) || 0;
                        let remainingAmount = parseFloat($('#remainingAmountHidden').val()) || 0;

                        // console.log("Cash partially selected, entered amount:", partialAmount, "Remaining:",
                        //     remainingAmount);

                        if (!partialAmount || isNaN(partialAmount) || partialAmount <= 0) {
                            isValid = false;
                            $('#partialAmountError').text("Enter a valid positive partial cash amount.");
                            return false;
                        }

                        if (partialAmount > remainingAmount) {
                            isValid = false;
                            $('#partialAmountError').text(
                                "Partial cash amount cannot exceed remaining amount (" + remainingAmount
                                .toLocaleString(undefined, {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }) + ")."
                            );
                            return false;
                        }

                        if (partialAmount < 0) {
                            isValid = false;
                            $('#partialAmountError').text("Amount cannot be negative.");
                            return false;
                        }

                        // console.log("Partial cash amount valid");
                    } else {
                        let cashAmount = $('#cashAmount').val();
                        // console.log("Cash fully selected, amount:", cashAmount);
                        if (!cashAmount || parseFloat(cashAmount) <= 0) {
                            isValid = false;
                            // console.log("Validation failed: Invalid full cash amount");
                            $('#cashAmountError').text("Enter a valid cash amount.");
                            return false;
                        } else {
                            // console.log("Full cash amount valid");
                        }
                    }
                }

                // Online Payment Validation
                if (paymentMethod === 'online') {
                    // console.log("Online payment selected");

                    if (!onlineType) {
                        isValid = false;
                        // console.log("Validation failed: Online type not selected");
                        $('#onlineTypeError').text("Please select online type.");
                        return false;
                    } else {
                        // console.log("Online type selected:", onlineType);
                    }

                    if (!$("#bank_id").val()) {
                        isValid = false;
                        $("#bankError").text("Please select a bank");
                        return false;
                    }

                    let onlineAmount = parseFloat($('#partialAmount').val()) || parseFloat($(
                        '#upiAmountInput').val()) || 0;
                    let remainingAmount = parseFloat($('#remainingAmountHidden').val()) || 0;

                    // console.log("Online amount entered:", onlineAmount, "Remaining:", remainingAmount);

                    // ✅ Check 1: Must be a valid positive number
                    if (!onlineAmount || isNaN(onlineAmount) || onlineAmount <= 0) {
                        isValid = false;
                        if (onlineType === 'online_partially') {
                            // console.log("Validation failed: Invalid partial online amount");
                            $('#partialAmountError').text("Enter a valid positive online partial amount.");
                        } else {
                            // console.log("Validation failed: Invalid full online amount");
                            $('#upiAmountError').text("Enter a valid positive online amount.");
                        }
                        return false;
                    }

                    // ✅ Check 2: Cannot exceed remaining
                    if (onlineType === 'online_partially' && onlineAmount > remainingAmount) {
                        // console.log(onlineType);
                        isValid = false;
                        // console.log("Validation failed: Partial online amount exceeds remaining");
                        $('#partialAmountError').text(
                            "Partial online amount cannot exceed remaining amount (" + remainingAmount
                            .toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + ")."
                        );
                        return false;
                    }

                    // ✅ Check 3: Cannot be negative
                    if (onlineAmount < 0) {
                        isValid = false;
                        // console.log("Validation failed: Negative online amount");
                        $('#partialAmountError').text("Amount cannot be negative.");
                        return false;
                    }

                    // console.log("Online amount valid");
                }


                // Cash + Online Validation
                if (paymentMethod === 'cash_online') {
                    // console.log("Cash + Online payment selected");

                    if (!cashOnlineType) {
                        isValid = false;
                        // console.log("Validation failed: Cash + Online type not selected");
                        $('#cashOnlineTypeError').text("Please select Cash + Online type.");
                        return false;
                    } else {
                        // console.log("Cash + Online type selected:", cashOnlineType);
                    }

                    if (!$("#bank_id").val()) {
                        isValid = false;
                        $("#bankError").text("Please select a bank");
                        return false;
                    }

                    if (cashOnlineType === 'cash_online_fully') {
                        let cashAmt = $('#cashOnlineFullAmount').val();
                        let onlineAmt = $('#upiOnlineFullAmount').val();
                        // console.log("Cash+Online fully amounts:", cashAmt, onlineAmt);

                        if (!cashAmt || parseFloat(cashAmt) <= 0 || !onlineAmt || parseFloat(onlineAmt) <=
                            0) {
                            isValid = false;
                            // console.log("Validation failed: Invalid fully cash + online amounts");
                            $('#cashOnlineFullAmountError').text("Enter a valid cash amount.");
                            $('#upiOnlineFullAmountError').text("Enter a valid online amount.");
                            return false;
                        } else {
                            // console.log("Fully cash + online amounts valid");
                        }
                    }

                    if (cashOnlineType === 'cash_online_partially') {
                        let cashAmt = parseFloat($('#cashOnlinePartialAmount').val()) || 0;
                        let onlineAmt = parseFloat($('#upiOnlinePartialAmount').val()) || 0;

                        // Clean pending amount
                        let rawPending = $('#remainingCashOnlineAmount').val() || "0";
                        rawPending = rawPending.replace(/[₹,]/g, '').trim();
                        let pendingAmt = parseFloat(rawPending) || 0;

                        // console.log("Cash+Online partially amounts:", cashAmt, onlineAmt, "Pending:",
                        //     pendingAmt);

                        // ✅ Check for invalid or negative input
                        if ((cashAmt <= 0 && onlineAmt <= 0)) {
                            isValid = false;
                            // console.log("Validation failed: Invalid partially cash + online amounts");
                            $('#cashOnlinePartialAmountError').text("Enter at least one valid amount.");
                            $('#upiOnlinePartialAmountError').text("Enter at least one valid amount.");
                            return false;
                        }

                        if (cashAmt < 0 || onlineAmt < 0) {
                            isValid = false;
                            // console.log("Validation failed: Negative amounts are not allowed");
                            $('#cashOnlinePartialAmountError').text("Negative amount not allowed.");
                            $('#upiOnlinePartialAmountError').text("Negative amount not allowed.");
                            return false;
                        }

                        // ✅ Total should not exceed pending amount
                        let total = cashAmt + onlineAmt;
                        // console.log("Total payment:", total, "Pending amount:", pendingAmt);

                        if (total > pendingAmt) {
                            isValid = false;
                            // console.log("Validation failed: Total exceeds pending amount");
                            $('#cashOnlinePartialAmountError').text(
                                "Total payment cannot exceed pending amount (" + pendingAmt + ").");
                            $('#upiOnlinePartialAmountError').text(
                                "Total payment cannot exceed pending amount (" + pendingAmt + ").");
                            return false;
                        }

                        // console.log("Partially cash + online amounts valid");
                    }

                }



                // console.log('done pay');


                if (isValid) {
                    // this.submit(); // submit the form
                }
                let selectedPaymentType = $('#paymentType').val();
                $('#paymentMethodHidden').val(selectedPaymentType);

                if ($("#paymentMethodDiv").hasClass("d-none")) {
                    $("#newEmiHidden").prop("disabled", true).val("");
                }



                let formElement = $(this)[0];
                let formData = new FormData(formElement);

                let submitButton = $(this).find('button[type="submit"]');
                submitButton.prop('disabled', true).text('Processing...');
                if (paymentMethodSelect === 'emi') {
                    let emiTotal = $('#emiMonthlyAmount').val();
                    formData.append('emi_paid_value', emiTotal);
                }

                if (selectedPaymentType === 'emi') {
                    let emi_val = $('#emiMonthlyAmount').val();
                    formData.append('amount', emi_val);
                }

                $.ajax({
                    url: "/api/sales/make-payment",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        $('#makePaymentModal').modal('hide');
                        submitButton.prop('disabled', false).text('Submit Payment');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Payment submitted successfully.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        submitButton.prop('disabled', false).text('Submit Payment');

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let errorMsg = '';
                            $.each(errors, function(key, value) {
                                errorMsg += value + '\n';
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: errorMsg
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Something went wrong. Please try again.'
                            });
                        }
                    }
                });
            });
            $('#makePaymentModal').on('hidden.bs.modal', function() {

                // Reset entire form
                $('#makePaymentForm')[0].reset();

                // Hide all dynamic sections
                $('#cashOnlineTypeDiv, #fullyCashOnlineFields, #partialCashOnlineFields, #onlineTypeDiv, #paidTypeDiv, #upiAmountDiv, #partialPaidFields, #fullyPaidFields, #bank_container')
                    .addClass('d-none');

                // Clear error messages
                $('.text-danger').text('');

                // Hide payment history
                $('#paymentHistoryBox').addClass('d-none');
                $('#paymentHistoryList').html('');

                // Reset hidden fields
                $('#paymentJobCardId').val('');
                $('#remainingAmountHidden').val('');
                $('#paymentMethodHidden').val('');
                $('#paymentDateInput').val('');
                $('#paymentDateError').text('');
            });

            function loadOrders(page = 1) {
                currentPage = page;
                const selectedMonth = normalizeFilterValue($('#filter-month').val() || '');
                const selectedYear = normalizeFilterValue($('#filter-year').val() || '');
                const selectedSort = $('#filter-sort').val() || 'latest';
                const selectedOrderType = $('#filter-order-type').val() || 'all';
                let selectedDate = ($('#filter-date').val() || '').trim();
                if (selectedDate && selectedDate.includes('-')) {
                    const parts = selectedDate.split('-');
                    if (parts.length === 3 && parts[0].length <= 2 && parts[2].length === 4) {
                        selectedDate = `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
                    }
                }

                $.ajax({
                    url: "/api/get_orders",
                    method: "GET",
                    data: {
                        page: currentPage,
                        per_page: perPage,
                        search: searchQuery,
                        month: selectedMonth,
                        year: selectedYear,
                        order_sort: selectedSort,
                        order_type: selectedOrderType,
                        date: selectedDate,
                        financial_year: normalizeFilterValue($('#filter-financial-year').val() || ''),
                        selectedSubAdminId: selectedSubAdminId || ''
                    },
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {

                        // console.log('response ', response);
                        if (response.status) {
                            toggleSalesFinancialYearFilter(response.financial_year_enabled);
                            currentPage = response.pagination?.current_page || 1;
                            lastPage = response.pagination?.last_page || 1;
                            updateSalesPaginationUI(response.pagination || {
                                current_page: 1,
                                last_page: 1,
                                per_page: perPage,
                                total: response.data?.length || 0
                            });
                            let tableBody = [];
                            const currencySymbol = response.currency_symbol || '₹';
                            const currencyPosition = response.currency_position || 'left';
                            updateSalesSummaryTotals(
                                response.total_amount || 0,
                                response.total_pending_amount || 0,
                                response.total_paid_amount || 0,
                                currencySymbol,
                                currencyPosition
                            );
                            window.salesSummaryTotals = {
                                total_amount: response.total_amount || 0,
                                total_pending_amount: response.total_pending_amount || 0,
                                total_paid_amount: response.total_paid_amount || 0,
                                currency_symbol: currencySymbol,
                                currency_position: currencyPosition
                            };


                            response.data.forEach(order => {

                                const status = String(order.quotation_status || 'sales')
                                    .toLowerCase();
                                const remaining = parseFloat(order.remaining_amount || 0);
                                let amount = parseFloat(order.total_amount).toLocaleString(
                                    undefined, {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                let displayAmount = currencyPosition === 'right' ?
                                    amount + currencySymbol // No space before/after
                                    :
                                    currencySymbol + amount;


                                let actionBtns = `<div class="dropdown sales-action-wrap">
                                    <button type="button" class="sales-action-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow sales-action-menu">`;

                                if (parseFloat(order.remaining_amount || 0) > 0 && status ===
                                    'sales') {
                                    actionBtns += `<li><a href="javascript:void(0);" class="dropdown-item make-payment-btn" data-bs-toggle="modal" data-bs-target="#makePaymentModal"
                                        data-id="${order.id}" data-amount="${order.remaining_amount}" data-method="${order.payment_method || ''}"
                                        data-emi-months="${order.remaining_emi_months}" data-emi-duration="${order.emi_duration || 0}"
                                        data-total-amount="${order.total_amount || 0}" data-remaining-amount="${order.remaining_amount}"
                                        data-return-amount="${order.total_return || 0}"
                                        data-remaining-emi-months="${order.remaining_emi_months}"
                                        data-tds-percentage="${order.tds_percentage || 0}"
                                        data-tds-amount="${order.tds_amount || 0}"
                                        data-emi-tenure="${order.emi_tenure || 0}"
                                        data-emi-monthly="${order.emi_monthly_amount || 0}" 
                                        title="Make Payment">
                                        <i class="fas fa-money-bill-wave me-2 text-secondary"></i> Pay
                                    </a></li>`;

                                }
                                actionBtns += `<li><button class="dropdown-item open-history" data-id="${order.id}" title="Payment History">
                                    <i class="fas fa-history me-2 text-secondary"></i> History
                               </button></li>`;

                                if ((order.payment_method || '').toLowerCase() === 'emi') {
                                    actionBtns += `<li><a class="dropdown-item" href="javascript:void(0);" onclick="openEmiDetails(${order.id})" title="EMI Details">
                                       <i class="fas fa-calendar-alt me-2 text-secondary"></i> EMI Details
                                   </a></li>`;
                                }

                                if ((order.quotation_status || '').toLowerCase() ===
                                    'quotation') {
                                    actionBtns += `<li><a class="dropdown-item convert-to-sales" href="javascript:void(0);" data-id="${order.id}" title="Convert to Sales">
                                        <i class="fas fa-exchange-alt me-2 text-success"></i> Convert to Sales
                                    </a></li>`;
                                }

                                @if (app('hasPermission')(2, 'view'))
                                    actionBtns += `<li><a class="dropdown-item" href="/sales-details/${order.id}">
                                        <i class="fas fa-eye me-2 text-secondary"></i> View
                                   </a></li>`;
                                @endif

                                actionBtns += `<li><a class="dropdown-item" href="/tickets/create?order_id=${order.id}">
                                   <i class="fas fa-ticket-alt me-2 text-secondary"></i> Raise Ticket
                               </a></li>`;

                                @if (app('hasPermission')(2, 'edit'))
                                    if (parseFloat(order.total_return || 0) === 0) {
                                        actionBtns += `<li><a class="dropdown-item" href="/edit-sales/${order.id}">
                                            <i class="fas fa-edit me-2 text-secondary"></i> Edit
                                        </a></li>`;
                                    }
                                @endif
                                @if (app('hasPermission')(2, 'view'))
                                    actionBtns += `<li><a class="dropdown-item" href="/sales-invoice/${order.id}">
                                        <i class="fas fa-file-invoice me-2 text-secondary"></i> Invoice
                                    </a></li>`;
                                    actionBtns += `<li><a class="dropdown-item" href="javascript:void(0);" onclick="window.open('/sales/invoice/pdf/' + ${order.id});"  title="Print Invoice">
                                        <i class="fas fa-print me-2 text-secondary"></i> Print Invoice
                                    </a></li>`;
                                @endif

                                if (!['sales-manager', 'purchase-manager', 'inventory-manager']
                                    .includes(userRole)) {
                                    @if (app('hasPermission')(2, 'delete'))
                                        actionBtns += `<li><a class="dropdown-item text-danger delete-order" href="javascript:void(0);" data-id="${order.id}">
                                            <i class="fas fa-trash-alt me-2 text-danger"></i> Delete
                                        </a></li>`;
                                    @endif
                                }

                                actionBtns += `</ul></div>`;
                                // Store order data for expandable row
                                const orderData = {
                                    ...order,
                                    displayAmount: displayAmount,
                                    currencySymbol: currencySymbol,
                                    currencyPosition: currencyPosition
                                };

                                tableBody.push([
                                    `<div class="order-mobile-summary">
                                        <span class="order-mobile-customer">${order.user?.name || 'N/A'}</span>

                                        <a href="/sales-details/${order.id}" class="order-mobile-link">${order.order_number || ''}</a>
                                    </div>`,
                                    `<button class="mobile-toggle-btn-table" onclick="toggleTableRowDetails('${order.id}')" data-order-id="${order.id}">
                                    <span class="toggle-icon">+</span>
                                </button>`,
                                    order.created_date,
                                    order.user?.name || '',
                                    getStatusBadge(order.quotation_status || 'sales',
                                        'quotation'),
                                    getStatusBadge(order.payment_status, 'payment',
                                        order
                                        .extra_paid || 0),
                                    displayAmount || '0.00',
                                    `<select class="form-select form-select-sm assigned-staff-select" data-order-id="${order.id}" style="width: 140px;">
                                        <option value="">— Unassigned —</option>
                                        ${window.staffOptions ? window.staffOptions.map(staff => `<option value="${staff.id}" ${order.staff_id == staff.id ? 'selected' : ''}>${staff.name}</option>`).join('') : ''}
                                     </select>`,
                                    `<select class="form-select form-select-sm order-type-select" data-order-id="${order.id}" style="width: 140px;">
                                        <option value="Self Pickup" ${order.order_type === 'Self Pickup' ? 'selected' : ''}>Self Pickup</option>
                                        <option value="Delivery" ${order.order_type === 'Delivery' ? 'selected' : ''}>Delivery</option>
                                       </select>`,
                                    `<span class="biller-wrap">${order.biller || 'Admin'}</span>`,
                                    actionBtns
                                ]);

                                // Store order data for later use in expandable row
                                if (!window.orderDataMap) {
                                    window.orderDataMap = {};
                                }
                                window.orderDataMap[order.id] = orderData;

                            });


                            table.clear().rows.add(tableBody).draw();

                            // Add expandable rows after table is drawn
                            function addExpandableRows() {
                                const tbody = $('#order-table tbody');
                                table.rows().every(function() {
                                    const row = this.node();
                                    const toggleBtn = $(row).find('.mobile-toggle-btn-table');
                                    if (toggleBtn.length > 0) {
                                        const orderId = toggleBtn.data('order-id');
                                        const orderData = window.orderDataMap[orderId];
                                        if (orderData && !$(row).next(
                                                'tr.order-details-row[data-order-id="' +
                                                orderId +
                                                '"]').length) {
                                            const expandableRow = $('<tr>')
                                                .addClass('order-details-row')
                                                .attr('data-order-id', orderId)
                                                .html(buildExpandableRowContent(orderData,
                                                    orderData
                                                    .currencySymbol, orderData
                                                    .currencyPosition
                                                ));
                                            $(row).after(expandableRow);
                                        }
                                    }
                                });
                            }

                            setTimeout(addExpandableRows, 100);

                            // Re-add expandable rows on table redraw
                            table.on('draw', function() {
                                setTimeout(addExpandableRows, 50);
                            });
                            // Render mobile cards
                            renderMobileOrders(response.data, currencySymbol, currencyPosition);
                            calculateFilteredTotal();
                            setTimeout(() => {
                                const topScroll = document.querySelector('.table-scroll-top');
                                const tableResponsive = document.querySelector(
                                    '.table-responsive');
                                const orderTable = document.getElementById('order-table');

                                if (topScroll && tableResponsive && orderTable) {
                                    const topInnerDiv = topScroll.querySelector('div');

                                    if (topInnerDiv) {
                                        topInnerDiv.style.width = orderTable.scrollWidth + 'px';

                                        // Avoid duplicate listeners
                                        topScroll.onscroll = () => {
                                            tableResponsive.scrollLeft = topScroll
                                                .scrollLeft;
                                        };
                                        tableResponsive.onscroll = () => {
                                            topScroll.scrollLeft = tableResponsive
                                                .scrollLeft;
                                        };
                                    }
                                }
                            }, 100);
                        } else {
                            table.clear().draw();
                            $(".datanew tbody").html('<tr><td colspan="9">No order found</td></tr>');
                            updateSalesSummaryTotals(0, 0, 0, '₹', 'left');
                            window.salesSummaryTotals = {
                                total_amount: 0,
                                total_pending_amount: 0,
                                total_paid_amount: 0,
                                currency_symbol: '₹',
                                currency_position: 'left'
                            };
                            $('#mobile-order-container').html(
                                '<div class="text-center p-4">No orders found</div>');
                        }
                    },
                    error: function() {
                        alert("Failed to load orders.");
                    }
                });
            }

            function updateSalesPaginationUI(pagination) {
                let from = (pagination.current_page - 1) * pagination.per_page + 1;
                let to = pagination.current_page * pagination.per_page;

                if (to > pagination.total) {
                    to = pagination.total;
                }

                if (pagination.total === 0) {
                    from = 0;
                }

                $('#sales-pagination-from').text(from);
                $('#sales-pagination-to').text(to);
                $('#sales-pagination-total').text(pagination.total);

                let paginationHtml = '';

                paginationHtml += `
                    <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                        <a class="page-link sales-page-link" href="javascript:void(0);" data-page="${pagination.current_page - 1}">Previous</a>
                    </li>
                `;

                // Show only 3 page numbers at a time
                const visiblePageCount = 2;
                let startPage = Math.floor((pagination.current_page - 1) / visiblePageCount) * visiblePageCount + 1;
                let endPage = Math.min(pagination.last_page, startPage + visiblePageCount - 1);

                // Show previous ellipsis if there are pages before startPage
                if (startPage > 1) {
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link sales-page-link" href="javascript:void(0);" data-page="${startPage - 1}" data-action="prev-group">..</a>
                        </li>
                    `;
                }

                // Generate page numbers
                for (let i = startPage; i <= endPage; i++) {
                    paginationHtml += `
                        <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                            <a class="page-link sales-page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                        </li>
                    `;
                }

                // Show next ellipsis if there are more pages after endPage
                if (endPage < pagination.last_page) {
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link sales-page-link" href="javascript:void(0);" data-page="${endPage + 1}" data-action="next-group">..</a>
                        </li>
                    `;
                }

                paginationHtml += `
                    <li class="page-item ${pagination.current_page === pagination.last_page || pagination.last_page === 0 ? 'disabled' : ''}">
                        <a class="page-link sales-page-link" href="javascript:void(0);" data-page="${pagination.current_page + 1}">Next</a>
                    </li>
                `;

                $('#sales-pagination-numbers').html(paginationHtml);
                $('.pagination-controls').toggle(pagination.total > 0);
            }

            // Handle page number clicks with ellipsis support
            $(document).on('click', '.sales-page-link', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                let action = $(this).data('action');

                // Handle ellipsis clicks to load next/previous groups
                if (action === 'next-group') {
                    // Load the page that starts the next group
                    if (page && page <= lastPage) {
                        loadOrders(page);
                    }
                    return;
                }

                if (action === 'prev-group') {
                    // Load the previous group's starting page
                    let prevStartPage = Math.max(1, page - 2);
                    if (prevStartPage >= 1 && prevStartPage <= lastPage) {
                        loadOrders(prevStartPage);
                    }
                    return;
                }

                // Regular page navigation
                if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                    loadOrders(page);
                }
            });

            loadOrders(currentPage);

            $('#filter-sort, #filter-order-type').on('change select2:select', function() {
                loadOrders(1);
            });
            // Function to calculate the total for visible (filtered) rows

            // Month short names
            const monthNames = {
                "01": "Jan",
                "02": "Feb",
                "03": "Mar",
                "04": "Apr",
                "05": "May",
                "06": "Jun",
                "07": "Jul",
                "08": "Aug",
                "09": "Sep",
                "10": "Oct",
                "11": "Nov",
                "12": "Dec"
            };

            // Recalculate total after table redraw
            table.on('draw', function() {
                calculateFilteredTotal();
            });


            $('#filter-date').on('dp.change', function() {
                if (skipDateResetFromMonthYear) {
                    skipDateResetFromMonthYear = false;
                    return;
                }

                let selectedDate = $(this).val().trim(); // e.g. DD-MM-YYYY

                // Clear month & year dropdowns
                updateSelect2Display('', '');

                if (selectedDate === '') {
                    fetchAllOrders();
                    return;
                }

                // Convert to YYYY-MM-DD
                if (selectedDate.includes('-')) {
                    let parts = selectedDate.split('-');
                    if (parts.length === 3) {
                        selectedDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
                    }
                }

                $.ajax({
                    url: '/api/orders/filter',
                    method: 'GET',
                    data: {
                        date: selectedDate,
                        financial_year: normalizeFilterValue($('#filter-financial-year').val() ||
                            ''),
                        selectedSubAdminId: selectedSubAdminId
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            if (typeof response.financial_year_enabled !== 'undefined') {
                                toggleSalesFinancialYearFilter(response.financial_year_enabled);
                            }
                            $('.pagination-controls').hide();
                            renderOrders(response.data, selectedDate);
                            calculateFilteredTotal();
                        }
                    }
                });
            });

            // Listen to both native change and Select2 events so UI and programmatic
            // updates all trigger the same handler.
            $('#filter-month, #filter-year').off('change select2:select select2:unselect').on(
                'change select2:select select2:unselect',
                function(e) {
                    syncFilterSelect2Display();

                    const selectedMonthRaw = $('#filter-month').val() || '';
                    const selectedYearRaw = $('#filter-year').val() || '';
                    const selectedMonth = normalizeFilterValue(selectedMonthRaw);
                    const selectedYear = normalizeFilterValue(selectedYearRaw);
                    const selectedDate = $('#filter-date').val() || '';

                    // If both month and year are empty, treat as "no filter" and fetch all orders.
                    if (selectedMonth === '' && selectedYear === '') {
                        updateSelect2Display('', '');
                        // Only fetch all if date is also empty
                        if (!selectedDate) {
                            fetchAllOrders();
                            setTimeout(function() {
                                calculateFilteredTotal();
                            }, 200);
                        }
                        return;
                    }

                    // If month/year is selected, clear the date filter to avoid confusion
                    if (selectedMonth || selectedYear) {
                        if ($('#filter-date').val()) {
                            skipDateResetFromMonthYear = true;
                            $('#filter-date').val('');
                        }
                    }

                    $.ajax({
                        url: '/api/orders/filter',
                        method: 'GET',
                        headers: {
                            "Authorization": "Bearer " + authToken
                        },
                        data: {
                            month: selectedMonth,
                            year: selectedYear,
                            financial_year: normalizeFilterValue($('#filter-financial-year').val() ||
                                ''),
                            selectedSubAdminId: selectedSubAdminId
                        },
                        success: function(response) {
                            if (response.status) {
                                if (typeof response.financial_year_enabled !== 'undefined') {
                                    toggleSalesFinancialYearFilter(response.financial_year_enabled);
                                }
                                $('.pagination-controls').hide();
                                renderOrdersByMonthAndYear(response.data, selectedMonth,
                                    selectedYear); // render table with filters

                                updateSelect2Display(selectedMonth, selectedYear);
                                // Recalculate total after rendering
                                setTimeout(function() {
                                    calculateFilteredTotal();
                                }, 200);
                            } else {
                                // console.error('Filter failed:', response.message ||
                                //     'Unknown error');
                            }
                        },
                        error: function(xhr, status, error) {
                            // console.error('AJAX error:', error);
                            alert('Failed to filter orders. Please try again.');
                        }
                    });
                });

            $('#filter-financial-year').off('change').on('change', function() {
                if (!isFinancialYearEnabled) {
                    return;
                }
                fetchAllOrders();
                setTimeout(function() {
                    calculateFilteredTotal();
                }, 200);
            });

            function fetchAllOrders() {
                loadOrders(1);
            }

            // Window resize handler to automatically apply responsive CSS
            let resizeTimer;
            let lastWidth = $(window).width();

            function handleResize() {
                const currentWidth = $(window).width();

                // Only process if width actually changed significantly (more than 5px)
                if (Math.abs(currentWidth - lastWidth) < 5) {
                    return;
                }
                lastWidth = currentWidth;

                // Trigger DataTables to recalculate column visibility and table layout
                if (table) {
                    // Remove all expandable rows first
                    $('tr.order-details-row').remove();

                    // Force DataTables to completely recalculate
                    try {
                        table.columns.adjust();
                        // Try responsive extension if available
                        if (table.responsive && typeof table.responsive.recalc === 'function') {
                            table.responsive.recalc();
                        }
                    } catch (e) {
                        // Fallback if responsive extension not available
                        table.columns.adjust();
                    }

                    // Force a reflow to ensure CSS media queries are recalculated
                    const orderTable = $('#order-table')[0];
                    if (orderTable) {
                        void orderTable.offsetHeight;
                    }

                    // Redraw table and recalculate
                    setTimeout(function() {
                        // Redraw table completely
                        table.draw(false);

                        // Force another recalculation after draw
                        table.columns.adjust();

                        // Check if we're on mobile/tablet (1024px or below)
                        const isMobileOrTablet = currentWidth <= 1024;

                        // Re-add expandable rows only if on mobile/tablet
                        if (isMobileOrTablet && window.orderDataMap) {
                            setTimeout(function() {
                                table.rows().every(function() {
                                    const row = this.node();
                                    const toggleBtn = $(row).find(
                                        '.mobile-toggle-btn-table');
                                    if (toggleBtn.length > 0) {
                                        const orderId = toggleBtn.data('order-id');
                                        const orderData = window.orderDataMap[orderId];
                                        if (orderData) {
                                            const expandableRow = $('<tr>')
                                                .addClass('order-details-row')
                                                .attr('data-order-id', orderId)
                                                .html(buildExpandableRowContent(orderData,
                                                    orderData.currencySymbol, orderData
                                                    .currencyPosition));
                                            $(row).after(expandableRow);
                                        }
                                    }
                                });
                            }, 50);
                        }
                    }, 100);
                }
            }

            // Make handleResize available globally for manual triggering
            window.refreshTableLayout = handleResize;

            $(window).on('resize', function() {
                clearTimeout(resizeTimer);
                // Use shorter delay for more responsive feel
                resizeTimer = setTimeout(handleResize, 100);
            });

            // Force initial check after page load
            setTimeout(function() {
                handleResize();
            }, 500);

            // Also trigger on orientation change for mobile devices
            $(window).on('orientationchange', function() {
                setTimeout(handleResize, 300);
            });

            // Use matchMedia to detect breakpoint changes more reliably
            const mobileQuery = window.matchMedia('(max-width: 767px)');
            const tabletQuery = window.matchMedia('(min-width: 768px) and (max-width: 1024px)');
            const desktopQuery = window.matchMedia('(min-width: 1025px)');

            function handleMediaChange() {
                handleResize();
            }

            // Listen for media query changes
            if (mobileQuery.addEventListener) {
                mobileQuery.addEventListener('change', handleMediaChange);
                tabletQuery.addEventListener('change', handleMediaChange);
                desktopQuery.addEventListener('change', handleMediaChange);
            } else {
                // Fallback for older browsers
                mobileQuery.addListener(handleMediaChange);
                tabletQuery.addListener(handleMediaChange);
                desktopQuery.addListener(handleMediaChange);
            }
        });

        function renderOrdersByMonthAndYear(data, selectedMonth, selectedYear) {
            let tableBody = [];
            let mobileOrders = [];
            const currencySymbol = '₹';
            const currencyPosition = 'left';

            data.forEach(order => {
                const formattedDate = order.created_date || order.created_at || 'N/A';
                const amount = parseFloat(order.total_amount).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                const displayAmount = currencyPosition === 'right' ? amount + currencySymbol : currencySymbol +
                    amount;
                const status = String(order.quotation_status || 'sales').toLowerCase();
                const remaining = parseFloat(order.remaining_amount || 0);

                let actionBtns = `<div class="dropdown sales-action-wrap">
                                    <button type="button" class="sales-action-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow sales-action-menu">`;

                if (parseFloat(order.remaining_amount || 0) > 0 && status === 'sales') {
                    actionBtns += `<li><a href="javascript:void(0);" class="dropdown-item make-payment-btn" data-bs-toggle="modal" data-bs-target="#makePaymentModal"
                                        data-id="${order.id}" data-amount="${order.remaining_amount}" data-method="${order.payment_method || ''}"
                                        data-emi-months="${order.remaining_emi_months}" data-emi-duration="${order.emi_duration || 0}"
                                        data-total-amount="${order.total_amount || 0}" data-remaining-amount="${order.remaining_amount}"
                                        data-return-amount="${order.total_return || 0}"
                                        data-remaining-emi-months="${order.remaining_emi_months}"
                                        data-tds-percentage="${order.tds_percentage || 0}"
                                        data-tds-amount="${order.tds_amount || 0}"
                                        data-emi-tenure="${order.emi_tenure || 0}"
                                        data-emi-monthly="${order.emi_monthly_amount || 0}" 
                                        title="Make Payment">
                                        <i class="fas fa-money-bill-wave me-2 text-secondary"></i> Pay
                                    </a></li>`;

                }
                actionBtns += `<li><button class="dropdown-item open-history" data-id="${order.id}" title="Payment History">
                                    <i class="fas fa-history me-2 text-secondary"></i> History
                               </button></li>`;

                if ((order.payment_method || '').toLowerCase() === 'emi') {
                    actionBtns += `<li><a class="dropdown-item" href="javascript:void(0);" onclick="openEmiDetails(${order.id})" title="EMI Details">
                                       <i class="fas fa-calendar-alt me-2 text-secondary"></i> EMI Details
                                   </a></li>`;
                }

                if ((order.quotation_status || '').toLowerCase() === 'quotation') {
                    actionBtns += `<li><a class="dropdown-item convert-to-sales" href="javascript:void(0);" data-id="${order.id}" title="Convert to Sales">
                        <i class="fas fa-exchange-alt me-2 text-success"></i> Convert to Sales
                    </a></li>`;
                }

                @if (app('hasPermission')(2, 'view'))
                    actionBtns += `<li><a class="dropdown-item" href="/sales-details/${order.id}">
                                        <i class="fas fa-eye me-2 text-secondary"></i> View
                                   </a></li>`;
                @endif

                actionBtns += `<li><a class="dropdown-item" href="/tickets/create?order_id=${order.id}">
                                   <i class="fas fa-ticket-alt me-2 text-secondary"></i> Raise Ticket
                               </a></li>`;

                @if (app('hasPermission')(2, 'edit'))
                    if (parseFloat(order.total_return || 0) === 0) {
                        actionBtns += `<li><a class="dropdown-item" href="/edit-sales/${order.id}">
                                            <i class="fas fa-edit me-2 text-secondary"></i> Edit
                                        </a></li>`;
                    }
                @endif
                @if (app('hasPermission')(2, 'view'))
                    actionBtns += `<li><a class="dropdown-item" href="/sales-invoice/${order.id}">
                                        <i class="fas fa-file-invoice me-2 text-secondary"></i> Invoice
                                    </a></li>`;
                    actionBtns += `<li><a class="dropdown-item" href="javascript:void(0);" onclick="window.open('/sales/invoice/pdf/' + ${order.id});"  title="Print Invoice">
                                        <i class="fas fa-print me-2 text-secondary"></i> Print Invoice
                                    </a></li>`;
                @endif

                if (!['sales-manager', 'purchase-manager', 'inventory-manager'].includes(userRole)) {
                    @if (app('hasPermission')(2, 'delete'))
                        actionBtns += `<li><a class="dropdown-item text-danger delete-order" href="javascript:void(0);" data-id="${order.id}">
                                            <i class="fas fa-trash-alt me-2 text-danger"></i> Delete
                                        </a></li>`;
                    @endif
                }

                actionBtns += `</ul></div>`;
                // Your existing HTML table rendering here...
                const orderData = {
                    ...order,
                    created_date: formattedDate,
                    displayAmount: displayAmount,
                    currencySymbol: currencySymbol,
                    currencyPosition: currencyPosition
                };

                tableBody.push([
                    `<div class="order-mobile-summary">
                        <a href="/sales-details/${order.id}" class="order-mobile-link">${order.order_number || ''}</a>
                        <span class="order-mobile-customer">${order.user?.name || 'N/A'}</span>
                    </div>`,
                    `<button class="mobile-toggle-btn-table" onclick="toggleTableRowDetails('${order.id}')" data-order-id="${order.id}">
                        <span class="toggle-icon">+</span>
                    </button>`,
                    order.created_date || 'N/A',
                    order.user?.name || 'N/A',
                    getStatusBadge(order.quotation_status || 'sales', 'quotation'),
                    getStatusBadge(order.payment_status, 'payment', order.extra_paid || 0),
                    displayAmount || '0.00',
                    `<span class="biller-wrap">${order.biller || 'Admin'}</span>`,
                    actionBtns
                ]);

                // Store order data for expandable row
                if (!window.orderDataMap) {
                    window.orderDataMap = {};
                }
                window.orderDataMap[order.id] = orderData;

                // Add to mobile orders array
                const mobileOrder = {
                    ...order
                };
                mobileOrder.created_date = formattedDate;
                mobileOrders.push(mobileOrder);
            });

            const table = $('#order-table').DataTable();
            table.clear().rows.add(tableBody).draw();

            // Add expandable rows
            function addExpandableRowsForMonthYear() {
                table.rows().every(function() {
                    const row = this.node();
                    const toggleBtn = $(row).find('.mobile-toggle-btn-table');
                    if (toggleBtn.length > 0) {
                        const orderId = toggleBtn.data('order-id');
                        const orderData = window.orderDataMap[orderId];
                        if (orderData && !$(row).next('tr.order-details-row[data-order-id="' + orderId + '"]')
                            .length) {
                            const expandableRow = $('<tr>')
                                .addClass('order-details-row')
                                .attr('data-order-id', orderId)
                                .html(buildExpandableRowContent(orderData, orderData.currencySymbol, orderData
                                    .currencyPosition));
                            $(row).after(expandableRow);
                        }
                    }
                });
            }

            setTimeout(addExpandableRowsForMonthYear, 100);

            // Re-add expandable rows on table redraw
            table.off('draw').on('draw', function() {
                setTimeout(addExpandableRowsForMonthYear, 50);
            });

            // Render mobile cards
            renderMobileOrders(mobileOrders, currencySymbol, currencyPosition);
        }

        function renderOrders(data, selectedDate) {
            let tableBody = [];
            let mobileOrders = [];
            const currencySymbol = '₹';
            const currencyPosition = 'left';

            data.forEach(order => {
                if (!order.created_date) return;

                const orderDate = new Date(order.created_date).toISOString().split('T')[0]; // 'YYYY-MM-DD'

                // If selectedDate exists and doesn't match, skip this
                if (selectedDate && selectedDate !== orderDate) return;

                // Format and push to table
                let date = new Date(order.created_date);
                let day = String(date.getDate()).padStart(2, '0');
                let monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                    "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
                ];
                let month = monthNames[date.getMonth()];
                let year = date.getFullYear();

                let hours = date.getHours();
                let minutes = String(date.getMinutes()).padStart(2, '0');
                let ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12 || 12;

                let amount = parseFloat(order.total_amount).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                let displayAmount = currencyPosition === 'right' ?
                    amount + currencySymbol : currencySymbol + amount;
                const status = String(order.quotation_status || 'sales').toLowerCase();
                const remaining = parseFloat(order.remaining_amount || 0);

                const formattedDate = `${day}-${month}-${year} ${hours}:${minutes} ${ampm}`;

                // 🔹 Build action buttons properly
                let actionBtns = `<div class="dropdown sales-action-wrap">
                                    <button type="button" class="sales-action-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow sales-action-menu">`;

                if (parseFloat(order.remaining_amount || 0) > 0 && status === 'sales') {
                    actionBtns += `<li><a href="javascript:void(0);" class="dropdown-item make-payment-btn" data-bs-toggle="modal" data-bs-target="#makePaymentModal"
                                        data-id="${order.id}" data-amount="${order.remaining_amount}" data-method="${order.payment_method || ''}"
                                        data-emi-months="${order.remaining_emi_months}" data-emi-duration="${order.emi_duration || 0}"
                                        data-total-amount="${order.total_amount || 0}" data-remaining-amount="${order.remaining_amount}"
                                        data-return-amount="${order.total_return || 0}"
                                        data-remaining-emi-months="${order.remaining_emi_months}"
                                        data-tds-percentage="${order.tds_percentage || 0}"
                                        data-tds-amount="${order.tds_amount || 0}"
                                        data-emi-tenure="${order.emi_tenure || 0}"
                                        data-emi-monthly="${order.emi_monthly_amount || 0}" 
                                        title="Make Payment">
                                        <i class="fas fa-money-bill-wave me-2 text-secondary"></i> Pay
                                    </a></li>`;

                }
                actionBtns += `<li><button class="dropdown-item open-history" data-id="${order.id}" title="Payment History">
                                    <i class="fas fa-history me-2 text-secondary"></i> History
                               </button></li>`;

                if ((order.payment_method || '').toLowerCase() === 'emi') {
                    actionBtns += `<li><a class="dropdown-item" href="javascript:void(0);" onclick="openEmiDetails(${order.id})" title="EMI Details">
                                       <i class="fas fa-calendar-alt me-2 text-secondary"></i> EMI Details
                                   </a></li>`;
                }

                if ((order.quotation_status || '').toLowerCase() === 'quotation') {
                    actionBtns += `<li><a class="dropdown-item convert-to-sales" href="javascript:void(0);" data-id="${order.id}" title="Convert to Sales">
                        <i class="fas fa-exchange-alt me-2 text-success"></i> Convert to Sales
                    </a></li>`;
                }

                @if (app('hasPermission')(2, 'view'))
                    actionBtns += `<li><a class="dropdown-item" href="/sales-details/${order.id}">
                                        <i class="fas fa-eye me-2 text-secondary"></i> View
                                   </a></li>`;
                @endif

                actionBtns += `<li><a class="dropdown-item" href="/tickets/create?order_id=${order.id}">
                                   <i class="fas fa-ticket-alt me-2 text-secondary"></i> Raise Ticket
                               </a></li>`;

                @if (app('hasPermission')(2, 'edit'))
                    if (parseFloat(order.total_return || 0) === 0) {
                        actionBtns += `<li><a class="dropdown-item" href="/edit-sales/${order.id}">
                                            <i class="fas fa-edit me-2 text-secondary"></i> Edit
                                        </a></li>`;
                    }
                @endif
                @if (app('hasPermission')(2, 'view'))
                    actionBtns += `<li><a class="dropdown-item" href="/sales-invoice/${order.id}">
                                        <i class="fas fa-file-invoice me-2 text-secondary"></i> Invoice
                                    </a></li>`;
                    actionBtns += `<li><a class="dropdown-item" href="javascript:void(0);" onclick="window.open('/sales/invoice/pdf/' + ${order.id});"  title="Print Invoice">
                                        <i class="fas fa-print me-2 text-secondary"></i> Print Invoice
                                    </a></li>`;
                @endif

                if (!['sales-manager', 'purchase-manager', 'inventory-manager'].includes(userRole)) {
                    @if (app('hasPermission')(2, 'delete'))
                        actionBtns += `<li><a class="dropdown-item text-danger delete-order" href="javascript:void(0);" data-id="${order.id}">
                                            <i class="fas fa-trash-alt me-2 text-danger"></i> Delete
                                        </a></li>`;
                    @endif
                }

                actionBtns += `</ul></div>`;

                const orderData = {
                    ...order,
                    created_date: formattedDate,
                    displayAmount: displayAmount,
                    currencySymbol: currencySymbol,
                    currencyPosition: currencyPosition
                };

                tableBody.push([
                    `<div class="order-mobile-summary">
                        <span class="order-mobile-customer">${order.user?.name || 'N/A'}</span>
                        <a href="/sales-details/${order.id}" class="order-mobile-link">${order.order_number || ''}</a>

                    </div>`,
                    `<button class="mobile-toggle-btn-table" onclick="toggleTableRowDetails('${order.id}')" data-order-id="${order.id}">
                        <span class="toggle-icon">+</span>
                    </button>`,
                    // formattedDate,
                    order.created_date || 'N/A',
                    order.user?.name || 'N/A',
                    getStatusBadge(order.quotation_status || 'sales', 'quotation'),
                    getStatusBadge(order.payment_status, 'payment', order.extra_paid || 0),
                    displayAmount || '0.00',
                    `<span class="biller-wrap">${order.biller || 'Admin'}</span>`,
                    actionBtns
                ]);

                // Store order data for expandable row
                if (!window.orderDataMap) {
                    window.orderDataMap = {};
                }
                window.orderDataMap[order.id] = orderData;

                // Add to mobile orders array (order already passed date filter above)
                const mobileOrder = {
                    ...order
                };
                mobileOrder.created_date = formattedDate;
                mobileOrders.push(mobileOrder);
            });

            const table = $('#order-table').DataTable();
            table.clear().rows.add(tableBody).draw();

            // Add expandable rows
            function addExpandableRowsForDate() {
                table.rows().every(function() {
                    const row = this.node();
                    const toggleBtn = $(row).find('.mobile-toggle-btn-table');
                    if (toggleBtn.length > 0) {
                        const orderId = toggleBtn.data('order-id');
                        const orderData = window.orderDataMap[orderId];
                        if (orderData && !$(row).next('tr.order-details-row[data-order-id="' + orderId + '"]')
                            .length) {
                            const expandableRow = $('<tr>')
                                .addClass('order-details-row')
                                .attr('data-order-id', orderId)
                                .html(buildExpandableRowContent(orderData, orderData.currencySymbol, orderData
                                    .currencyPosition));
                            $(row).after(expandableRow);
                        }
                    }
                });
            }

            setTimeout(addExpandableRowsForDate, 100);

            // Re-add expandable rows on table redraw
            table.off('draw').on('draw', function() {
                setTimeout(addExpandableRowsForDate, 50);
            });

            // Render mobile cards
            renderMobileOrders(mobileOrders, currencySymbol, currencyPosition);

            // Recalculate total after rendering
            setTimeout(function() {
                calculateFilteredTotal();
            }, 200);
        }

        function renderSalesPaymentHistory(history, summary) {
            let html = '';

            if (!history || history.length === 0) {
                html = '<li class="list-group-item">No payment history found.</li>';
            } else {
                html = history.map(function(payment) {
                    return `
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">${formatPaymentHistoryDate(payment.payment_date || payment.created_at)}</div>
                                    <div class="small text-muted">
                                        Method: ${formatPaymentMethodLabel(payment.payment_method)} |
                                        Type: ${formatPaymentTypeLabel(payment.payment_type)}
                                    </div>
                                    <div class="small">Remarks: ${payment.remarks ? payment.remarks : 'N/A'}</div>
                                </div>
                                <div class="d-flex gap-2 flex-shrink-0 align-items-start">
                                    <button type="button" class="btn btn-sm btn-outline-warning edit-payment-btn"
                                        data-bs-toggle="modal" data-bs-target="#editPaymentModal"
                                        data-payment-id="${payment.id}" data-order-id="${payment.order_id || ''}">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-payment-btn"
                                        data-payment-id="${payment.id}" data-order-id="${payment.order_id || ''}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2 fw-semibold">
                                ₹${parseFloat(payment.payment_amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                            </div>
                        </li>
                    `;
                }).join('');
            }

            html += `
                <li class="list-group-item mt-2 bg-light">
                    <strong>Order Total:</strong> ₹${parseFloat(summary.order_total || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                </li>
                <li class="list-group-item bg-light">
                    <strong>Total Paid:</strong> ₹${parseFloat(summary.total_paid || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                </li>
                <li class="list-group-item bg-light">
                    <strong>Return Amount:</strong> ₹${parseFloat(summary.return_amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                </li>
            `;

            if (summary.extra_paid > 0) {
                html += `
                    <li class="list-group-item bg-warning">
                        <strong>Extra Paid:</strong>
                        ₹${parseFloat(summary.extra_paid).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                        <span class="text-danger">(Advance / Refund)</span>
                    </li>
                `;
            } else {
                html += `
                    <li class="list-group-item bg-light">
                        <strong>Remaining:</strong> ₹${parseFloat(summary.remaining || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                    </li>
                `;
            }

            $('#globalPaymentHistoryList').html(html);
        }

        function loadSalesPaymentHistory(orderId) {
            const authToken = localStorage.getItem("authToken");

            $('#globalPaymentHistoryList').html('<li class="list-group-item">Loading...</li>');
            $.ajax({
                url: '/api/order/payment-history/' + orderId,
                method: 'GET',
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                success: function(response) {
                    renderSalesPaymentHistory(response.data || [], response.summary || {});
                    new bootstrap.Modal(document.getElementById('paymentHistoryModal')).show();
                },
                error: function() {
                    $('#globalPaymentHistoryList').html(
                        '<li class="list-group-item text-danger">Failed to load payment history.</li>');
                    new bootstrap.Modal(document.getElementById('paymentHistoryModal')).show();
                }
            });
        }

        $(document).on('click', '.open-history', function(event) {
            event.preventDefault();
            event.stopImmediatePropagation();
            loadSalesPaymentHistory($(this).data('id'));
        });

        $(document).on('click', '.edit-payment-btn', function(event) {
            event.preventDefault();
            event.stopImmediatePropagation();

            const paymentId = $(this).data('payment-id');
            const authToken = localStorage.getItem("authToken");

            $.ajax({
                url: '/api/sales/payment/' + paymentId,
                method: 'GET',
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                success: function(response) {
                    const payment = response.data || {};
                    const totalAmount = parseFloat(payment.total_amount || payment.order_total || 0);
                    const remainingAmount = parseFloat(payment.remaining_amount || 0);
                    const originalAmount = parseFloat(payment.payment_amount || 0);
                    $('#editPaymentId').val(payment.id || '');
                    $('#editPaymentOrderId').val(payment.order_id || '');
                    $('#editPaymentTotalValue').val(totalAmount || 0);
                    $('#editPaymentBaseRemaining').val(remainingAmount + originalAmount);
                    $('#editPaymentOriginalAmount').val(originalAmount || 0);
                    $('#editPaymentTotalAmount').text(`₹${totalAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);
                    $('#editPaymentRemainingAmount').text(`₹${remainingAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);
                    $('#editPaymentMethod').val((payment.payment_method || 'cash').toLowerCase());
                    $('#editPaymentType').val((payment.payment_type || 'fully').toLowerCase());
                    $('#editPaymentAmount').val(originalAmount.toFixed(2));
                    $('#editPaymentBank').val(payment.bank_id || '');
                    $('#editPaymentDate').val(formatDateForInput(payment.payment_date || payment
                        .created_at));
                    $('#editPaymentRemarks').val(payment.remarks || '');
                    $('#editPaymentBankWrap').toggle((payment.payment_method || '').toLowerCase() !==
                        'cash');
                    $('#editPaymentPendingAmount').val(remainingAmount.toFixed(2));

                    new bootstrap.Modal(document.getElementById('editPaymentModal')).show();
                }
            });
        });

        $(document).on('click', '.delete-payment-btn', function(event) {
            event.preventDefault();
            event.stopImmediatePropagation();

            const paymentId = $(this).data('payment-id');
            const orderId = $(this).data('order-id');
            const authToken = localStorage.getItem("authToken");

            Swal.fire({
                title: 'Delete payment?',
                text: 'This will remove the payment from history.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: '/api/payment-store/' + paymentId + '/delete',
                    method: 'POST',
                    headers: {
                        "Authorization": "Bearer " + authToken,
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted',
                                text: response.message ||
                                    'Payment deleted successfully.'
                            });
                            loadSalesPaymentHistory(orderId);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Unable to delete payment.'
                            });
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message ||
                            'Unable to delete payment.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });
                    }
                });
            });
        });

        function updateEditPaymentPendingAmount() {
            const baseRemaining = parseFloat($('#editPaymentBaseRemaining').val() || 0);
            const originalAmount = parseFloat($('#editPaymentOriginalAmount').val() || 0);
            const enteredAmount = parseFloat($('#editPaymentAmount').val() || 0);
            const updatedRemaining = Math.max(baseRemaining - enteredAmount, 0);

            $('#editPaymentPendingAmount').val(updatedRemaining.toFixed(2));
            $('#editPaymentRemainingAmount').text(`₹${updatedRemaining.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);
        }

        $(document).on('change input', '#editPaymentAmount', function() {
            updateEditPaymentPendingAmount();
        });

        $(document).on('change', '#editPaymentMethod', function() {
            $('#editPaymentBankWrap').toggle($(this).val() !== 'cash');
        });

        $(document).on('change', '#editPaymentType', function() {
            updateEditPaymentPendingAmount();
        });

        $('#saveEditPaymentBtn').on('click', function() {
            const paymentId = $('#editPaymentId').val();
            const authToken = localStorage.getItem("authToken");

            $.ajax({
                url: '/api/sales/payment/' + paymentId + '/update',
                method: 'POST',
                headers: {
                    "Authorization": "Bearer " + authToken,
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                data: {
                    payment_amount: $('#editPaymentAmount').val(),
                    payment_date: $('#editPaymentDate').val(),
                    payment_method: $('#editPaymentMethod').val(),
                    payment_type: $('#editPaymentType').val(),
                    bank_id: $('#editPaymentBank').val(),
                    remarks: $('#editPaymentRemarks').val()
                },
                success: function(response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated',
                            text: response.message || 'Payment updated successfully.'
                        });
                        bootstrap.Modal.getInstance(document.getElementById('editPaymentModal')).hide();
                        loadSalesPaymentHistory($('#editPaymentOrderId').val());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Unable to update payment.'
                        });
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Unable to update payment.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message
                    });
                }
            });
        });

        // Global history modal open
        $(document).on('click', '.open-history', function() {
            var authToken = localStorage.getItem("authToken");

            const jobCardId = $(this).data('id');
            $('#globalPaymentHistoryList').html('<li class="list-group-item">Loading...</li>');
            $.ajax({
                url: '/api/order/payment-history/' + jobCardId,
                method: 'GET',
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                success: function(response) {

                    const history = response.data || [];
                    const summary = response.summary || {};

                    let html = '';

                    if (history.length === 0) {
                        html = '<li class="list-group-item">No payment history found.</li>';
                    } else {
                        html = history.map(p => `
            <li class="list-group-item d-flex justify-content-between">
                <span>${formatPaymentHistoryDate(p.payment_date || p.created_at)}</span>
                <span>
                    <strong>₹${parseFloat(p.payment_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong>
                    (${p.payment_method || ''})
                </span>
            </li>
        `).join('');
                    }

                    html += `
        <li class="list-group-item mt-2 bg-light">
            <strong>Order Total:</strong> ₹${parseFloat(summary.order_total).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
        </li>
        <li class="list-group-item bg-light">
            <strong>Total Paid:</strong> ₹${parseFloat(summary.total_paid).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
        </li>
        <li class="list-group-item bg-light">
            <strong>Return Amount:</strong> ₹${parseFloat(summary.return_amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
        </li>
    `;

                    if (summary.extra_paid > 0) {
                        html += `
            <li class="list-group-item bg-warning">
                <strong>Extra Paid:</strong>
                ₹${parseFloat(summary.extra_paid).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                <span class="text-danger">(Advance / Refund)</span>
            </li>
        `;
                    } else {
                        html += `
            <li class="list-group-item bg-light">
                <strong>Remaining:</strong>
                ₹${parseFloat(summary.remaining).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
            </li>
        `;
                    }

                    $('#globalPaymentHistoryList').html(html);

                    new bootstrap.Modal(document.getElementById('paymentHistoryModal'))
                        .show();
                },

                error: function() {
                    $('#globalPaymentHistoryList').html(
                        '<li class="list-group-item text-danger">Failed to load payment history.</li>'
                    );
                    const modal = new bootstrap.Modal(document.getElementById(
                        'paymentHistoryModal'));
                    modal.show();
                }
            });
        });
        $(document).on('click', '.delete-order', function() {
            var orderId = $(this).data('id'); // ✅ Correct usage
            var authToken = localStorage.getItem("authToken");

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#ff9f43",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'api/delete/' + orderId,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            "Authorization": "Bearer " + authToken,
                        },
                        success: function(response) {
                            if (response.status === true) {
                                Swal.fire({
                                    title: "Deleted!",
                                    text: response.message,
                                    icon: "success",
                                    confirmButtonColor: '#ff9f43', // Set OK button color here
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: "Error",
                                    text: response.error,
                                    icon: "error",
                                    confirmButtonColor: '#ff9f43',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = "An error occurred while deleting the order";
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            }

                            Swal.fire({
                                title: "Error",
                                text: errorMessage,
                                icon: "error",
                                confirmButtonColor: '#ff9f43',
                                confirmButtonText: 'OK'
                            });
                        }

                    });
                }
            });
        });

        $(document).on('mousedown focus', '.assigned-staff-select, .order-type-select', function() {
            $(this).data('prev', $(this).val());
        });

        $(document).on('change', '.assigned-staff-select', function() {
            const $select = $(this);
            const orderId = $select.data('order-id');
            const staffId = $select.val();
            const prevStaffId = $select.data('prev') !== undefined ? $select.data('prev') : '';
            const authToken = localStorage.getItem("authToken");

            Swal.fire({
                title: "Are you sure?",
                text: "Do you want to update the assigned staff?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#ff9f43",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, update it!",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/api/sales/' + orderId + '/update-staff',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            "Authorization": "Bearer " + authToken,
                        },
                        data: {
                            staff_id: staffId
                        },
                        success: function(response) {
                            if (response.status === true) {
                                Swal.fire({
                                    title: "Updated!",
                                    text: response.message || 'Staff assigned successfully.',
                                    icon: "success",
                                    confirmButtonColor: '#ff9f43',
                                    confirmButtonText: 'OK'
                                });
                                $select.data('prev', staffId);
                            } else {
                                Swal.fire('Error', response.message || 'Something went wrong', 'error');
                                $select.val(prevStaffId);
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong', 'error');
                            $select.val(prevStaffId);
                        }
                    });
                } else {
                    $select.val(prevStaffId);
                }
            });
        });

        $(document).on('change', '.order-type-select', function() {
            const $select = $(this);
            const orderId = $select.data('order-id');
            const orderType = $select.val();
            const prevOrderType = $select.data('prev') !== undefined ? $select.data('prev') : 'Self Pickup';
            const authToken = localStorage.getItem("authToken");

            Swal.fire({
                title: "Are you sure?",
                text: "Do you want to update the order type?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#ff9f43",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, update it!",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/api/sales/' + orderId + '/update-order-type',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            "Authorization": "Bearer " + authToken,
                        },
                        data: {
                            order_type: orderType
                        },
                        success: function(response) {
                            if (response.status === true) {
                                Swal.fire({
                                    title: "Updated!",
                                    text: response.message || 'Order type updated successfully.',
                                    icon: "success",
                                    confirmButtonColor: '#ff9f43',
                                    confirmButtonText: 'OK'
                                });
                                $select.data('prev', orderType);
                            } else {
                                Swal.fire('Error', response.message || 'Something went wrong', 'error');
                                $select.val(prevOrderType);
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong', 'error');
                            $select.val(prevOrderType);
                        }
                    });
                } else {
                    $select.val(prevOrderType);
                }
            });
        });

        $(document).on('click', '.convert-to-sales', function() {
            const orderId = $(this).data('id');
            const authToken = localStorage.getItem("authToken");

            Swal.fire({
                title: "Are you sure?",
                text: "You want to convert this quotation to sales!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, convert it!",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: '/api/convert-quotation-to-sale/' + orderId,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status === true) {
                            Swal.fire({
                                title: "Converted!",
                                text: response.message ||
                                    "Quotation converted to sales successfully.",
                                icon: "success",
                                confirmButtonColor: '#ff9f43',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: "Error",
                                text: response.message ||
                                    "Failed to convert quotation.",
                                icon: "error",
                                confirmButtonColor: '#ff9f43',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = "Failed to convert quotation.";
                        if (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON
                                .error)) {
                            errorMessage = xhr.responseJSON.message || xhr.responseJSON.error;
                        }

                        Swal.fire({
                            title: "Error",
                            text: errorMessage,
                            icon: "error",
                            confirmButtonColor: '#ff9f43',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });

        const $downloadLoader = $("#downloadLoaderOverlay");
        const $downloadLoaderText = $("#downloadLoaderText");
        const $exportButtons = $("#exportPdf, #exportAllChallan");

        function toggleDownloadLoader(isLoading, message) {
            if (isLoading) {
                $downloadLoaderText.text(message || "Generating report...");
                $downloadLoader.removeClass("d-none");
                $exportButtons.prop("disabled", true).addClass("disabled").attr("aria-disabled", "true");
            } else {
                $downloadLoader.addClass("d-none");
                $exportButtons.prop("disabled", false).removeClass("disabled").removeAttr("aria-disabled");
            }
        }

        $('#exportPdf').click(function() {
            var authToken = localStorage.getItem("authToken");
            let selectedYearRaw = $('#filter-year').val() || '';
            let selectedMonthRaw = $('#filter-month').val() || '';
            let selectedYear = normalizeFilterValue(selectedYearRaw);
            let selectedMonth = normalizeFilterValue(selectedMonthRaw);
            let selectedDate = $('#filter-date').val() || '';
            let selectedCustomerId = $('#filter-customer').val() || '';
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

            $.ajax({
                url: `/api/pdf-orders`,
                type: 'GET',
                beforeSend: function() {
                    toggleDownloadLoader(true, "Generating PDF...");
                },
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                data: {
                    year: selectedYear,
                    month: selectedMonth,
                    date: selectedDate,
                    customerId: selectedCustomerId,
                    selectedSubAdminId: selectedSubAdminId,
                    type: 'pdf'
                },
                success: function(response) {
                    if (response.status && response.file_url) {
                        // Open PDF in new tab or trigger download
                        const link = document.createElement('a');
                        link.href = response.file_url;
                        link.download = response.file_name || 'sales_report.pdf';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: "Failed to generate PDF: " + response.message,
                            icon: "error",
                            confirmButtonColor: '#ff9f43',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        title: "Error",
                        text: "PDF export failed: " + (xhr.responseJSON?.message ??
                            "Unknown error"),
                        icon: "error",
                        confirmButtonColor: '#ff9f43',
                        confirmButtonText: 'OK'
                    });
                },
                complete: function() {
                    toggleDownloadLoader(false);
                }
            });
        });
        $('#exportAllChallan').click(function() {
            let selectedYearRaw = $('#filter-year').val() || '';
            let selectedMonthRaw = $('#filter-month').val() || '';
            let selectedYear = normalizeFilterValue(selectedYearRaw);
            let selectedMonth = normalizeFilterValue(selectedMonthRaw);
            let selectedDate = $('#filter-date').val() || '';
            let selectedVendorId = $('#filter-customer').val() || '';
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            let authToken = localStorage.getItem("authToken");

            let url =
                `/api/export-order?year=${selectedYear}&month=${selectedMonth}&date=${selectedDate}&customer_id=${selectedVendorId}&selectedSubAdminId=${selectedSubAdminId}&format_currency=indian`;
            $.ajax({
                url: url,
                method: "GET",
                beforeSend: function() {
                    toggleDownloadLoader(true, "Generating Excel...");
                },
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                success: function(response) {
                    if (response.status && response.file_url) {
                        // Trigger download via file_url
                        const link = document.createElement('a');
                        link.href = response.file_url;
                        link.download = response.file_name; // optional
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: "Export failed: " + (response.message || "Unknown error"),
                            icon: "error",
                            confirmButtonColor: '#ff9f43',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    // console.error("Export failed:", xhr.responseText);
                    Swal.fire({
                        title: "Error",
                        text: "Export failed. Please try again.",
                        icon: "error",
                        confirmButtonColor: '#ff9f43',
                        confirmButtonText: 'OK'
                    });
                },
                complete: function() {
                    toggleDownloadLoader(false);
                }
            });
        });
    </script>
@endpush
