@extends('layout.app')

@section('title', 'Follow Up List')

@section('content')
    @php
        $canViewFollowUp   = app('hasPermission')(30, 'view');
        $canAddFollowUp    = app('hasPermission')(30, 'add');
        $canEditFollowUp   = app('hasPermission')(30, 'edit');
        $canDeleteFollowUp = app('hasPermission')(30, 'delete');
    @endphp
    <style>
        /* Priority and Status badges */
        .priority-badge, .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
        }
        .priority-high       { background-color: #fee2e2; color: #dc2626; }
        .priority-medium     { background-color: #fed7aa; color: #ea580c; }
        .priority-low        { background-color: #d1fae5; color: #059669; }
        .status-pending      { background-color: #fed7aa; color: #ea580c; }
        .status-rescheduled  { background-color: #dbeafe; color: #2563eb; }
        .status-completed    { background-color: #d1fae5; color: #059669; }
        .status-cancelled    { background-color: #fee2e2; color: #dc2626; }

        /* Hide default DataTables controls */
        .dataTables_filter,
        .dataTables_length,
        .dataTables_info,
        .dataTables_paginate { display: none !important; }

        /* Custom Pagination */
        .pagination .page-item .page-link {
            background-color: #5d6d7e; color: #fff; border: none;
            margin: 0 3px; padding: 4px 10px; border-radius: 6px; font-weight: bold;
        }
        .pagination .page-item.active .page-link          { background-color: #ff9f43 !important; color: #fff; }
        .pagination .page-item .page-link:hover           { background-color: #4a5766; color: #fff; }
        .pagination .page-item.active .page-link:hover    { background-color: #e68a35 !important; }
        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link      { background-color: #fff; color: #6c757d; border: 1px solid #dee2e6; }
        .pagination .page-item:first-child .page-link:hover,
        .pagination .page-item:last-child .page-link:hover { background-color: #f8f9fa; color: #495057; }
        .pagination .page-item.disabled .page-link        {
            background-color: #fff !important; color: #dee2e6 !important;
            border: 1px solid #dee2e6 !important; cursor: not-allowed !important; pointer-events: none !important;
        }

        /* Search input */
        .search-input { position: relative; display: flex; align-items: center; }
        .search-input input { padding-left: 35px !important; border-radius: 5px; }
        .btn-searchset { position: absolute; left: 10px; z-index: 10; padding: 0; top: 7px !important; }

        /* Table */
        .table-container { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-container table thead th { white-space: nowrap; }
        table.datanew tbody td {
            white-space: normal !important;
            overflow-wrap: anywhere;
            word-break: break-word;
            vertical-align: top;
        }
        table.datanew tbody td:nth-child(7),
        table.datanew tbody td:nth-child(8) {
            white-space: nowrap !important;
        }
        .cell-wrap {
            display: block;
            white-space: normal !important;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        /* Desktop: hide Details toggle column */
        @media (min-width: 769px) {
            table.datanew thead th, table.datanew tbody td { display: table-cell !important; }
            table.datanew thead th.details-column,
            table.datanew tbody td:nth-child(8) { display: none !important; }
        }

        /* Mobile: show only Customer + Details columns */
        @media (max-width: 768px) {
            table.datanew thead th:nth-child(n+2),
            table.datanew tbody td:nth-child(n+2) { display: none !important; }
            table.datanew thead th:first-child,
            table.datanew tbody td:first-child { display: table-cell !important; }
            table.datanew thead th.details-column,
            table.datanew tbody td:nth-child(8) {
                display: table-cell !important;
                width: 56px;
                min-width: 56px;
                max-width: 56px;
                vertical-align: top;
                text-align: right;
                padding-top: 12px !important;
            }
            table.datanew tbody td:first-child {
                width: calc(100% - 56px);
                vertical-align: top;
            }
            .toggle-details i { font-size: 24px; }
            .toggle-details {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 30px;
                height: 30px;
            }
            .search-set { margin-right: 1rem !important; }
            .table-container { overflow-x: auto; -webkit-overflow-scrolling: touch !important; }
        }

        /* Collapsible details
        .collapse-details {
            padding: 12px 14px; background-color: #f8f9fa;
            border-radius: 5px; margin: 0;
        } */
        .detail-item  {
            display: flex; justify-content: space-between; gap: 14px;
            margin-bottom: 8px; font-size: 14px; border-bottom: 1px solid #eceff1;
            padding: 7px 0;
        }
        .detail-item:last-child { margin-bottom: 0; }
        .detail-label { font-weight: 600; min-width: 110px; color: #495057; }
        .detail-value { color: #212529; flex: 1; text-align: right; }
        .mobile-actions {
            display: flex; gap: 15px;
        }
        .mobile-actions a {
            display: flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: 50%; background-color: #f8f9fa; transition: all 0.3s ease;
        }
        .mobile-actions a:hover { background-color: #e9ecef; transform: translateY(-2px); }
        .mobile-actions svg { width: 18px; height: 18px; }
        .delete-followup svg path { fill: #dc3545 !important; }
        table.datanew tbody tr.child td {
            padding: 0 !important;
            border-top: 0 !important;
            background: #fff !important;
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title"><h4>Follow Ups</h4></div>
            <div class="page-btn d-flex gap-2">
                @if ($canAddFollowUp)
                    <a href="{{ route('followup.add') }}" class="btn btn-added btn-sm">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" class="me-1" alt="img">New Follow Up
                    </a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="mb-2">
                    <div class="search-set d-flex justify-content-md-start justify-content-start w-100">
                        <div class="search-path"></div>
                        <div class="search-input">
                            <a class="btn btn-searchset">
                                <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}" alt="img">
                            </a>
                            <input type="text" id="search-input" class="form-control" placeholder="Search...">
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <table class="table datanew">
                        <thead>
                            <tr>
                                <th>Lead</th>
                                <th>Purpose</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Follow Up Date &amp; Time</th>
                                <th>Assigned To</th>
                                <th>Action</th>
                                <th class="details-column">Details</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <span class="me-2" style="font-size:14px;color:#555;">Show per page :</span>
                        <select id="per-page-select" class="form-select form-select-sm" style="width:auto;border:1px solid #ddd;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-3" style="font-size:14px;color:#555;">
                            <span id="pagination-from">0</span> - <span id="pagination-to">0</span> of <span id="pagination-total">0</span> items
                        </span>
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0" id="pagination-numbers"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    const userRole         = "{{ auth()->user()->role }}";
    const canViewFollowUp  = @json((bool) $canViewFollowUp);
    const canEditFollowUp  = @json((bool) $canEditFollowUp);
    const canDeleteFollowUp = @json((bool) $canDeleteFollowUp);

    $(document).ready(function () {
        var authToken          = localStorage.getItem("authToken");
        const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');

        var table = $('.datanew').DataTable({
            destroy: true, bFilter: false, paging: false,
            info: false, searching: false, dom: 't', ordering: false
        });

        let currentPage  = 1;
        let lastPage     = 1;
        let perPage      = 10;
        let searchQuery  = '';
        const mobileDetailsMap = {};

        // Initial load
        fetchFollowUps(1);

        // Search
        $('#search-input').on('keyup', function () {
            searchQuery = $(this).val();
            fetchFollowUps(1);
        });

        // Per-page change
        $('#per-page-select').on('change', function () {
            perPage = parseInt($(this).val());
            fetchFollowUps(1);
        });

        // Pagination click
        $(document).on('click', '#pagination-numbers .page-link', function (e) {
            e.preventDefault();
            let page   = $(this).data('page');
            let action = $(this).data('action');

            if (action === 'prev-group') {
                fetchFollowUps(Math.max(1, page - 1));
                return;
            }
            if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                fetchFollowUps(page);
            }
        });

        function fetchFollowUps(page) {
            let url = `/api/getAllFollowUps?page=${page}&per_page=${perPage}`;
            if (selectedSubAdminId) url += `&selectedSubAdminId=${selectedSubAdminId}`;
            if (searchQuery)        url += `&search=${encodeURIComponent(searchQuery)}`;

            $.ajax({
                url: url, type: "GET", dataType: "json",
                headers: { "Authorization": "Bearer " + authToken },
                success: function (response) {
                    if (response.status) {
                        let followUps  = response.data;
                        let pagination = response.pagination;

                        currentPage = pagination.current_page;
                        lastPage    = pagination.last_page;

                        updatePaginationUI(pagination);

                        Object.keys(mobileDetailsMap).forEach(function (key) {
                            delete mobileDetailsMap[key];
                        });

                        let tableBody = [];
                        followUps.forEach(function (followUp) {
                            let priorityBadge = `<span class="priority-badge priority-${followUp.priority.toLowerCase()}">${followUp.priority}</span>`;
                            let statusBadge   = `<span class="status-badge status-${followUp.status.toLowerCase()}">${followUp.status}</span>`;

                            let detailsToggle = `
                                <a href="javascript:void(0);" class="toggle-details" data-id="${followUp.id}">
                                    <i class="fas fa-plus-circle" style="color:#ff9f43;"></i>
                                </a>`;

                            let deleteBtn = '';
                            if (canDeleteFollowUp && userRole !== 'sales-manager' && userRole !== 'purchase-manager' && userRole !== 'inventory-manager') {
                                deleteBtn = `
                                    <a class="me-2 confirm-text delete-followup" data-id="${followUp.id}" href="javascript:void(0);">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z" fill="#092C4C"></path>
                                            <path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z" fill="#092C4C"></path>
                                        </svg>
                                    </a>`;
                            }

                            let actionButtons = '';
                            if (canViewFollowUp) {
                                actionButtons += `
                                    <a class="me-2" href="/follow-up-view/${followUp.id}">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 9C11.206 9.00524 10.4459 9.32299 9.88447 9.88447C9.32299 10.4459 9.00524 11.206 9 12C9 13.642 10.358 15 12 15C13.641 15 15 13.642 15 12C15 10.359 13.641 9 12 9Z" fill="#092C4C"></path>
                                            <path d="M12 5C4.36704 5 2.07304 11.617 2.05204 11.684L1.94604 12L2.05105 12.316C2.07305 12.383 4.36704 19 12 19C19.633 19 21.927 12.383 21.948 12.316L22.054 12L21.949 11.684C21.927 11.617 19.633 5 12 5ZM12 17C6.64904 17 4.57604 13.154 4.07404 12C4.57804 10.842 6.65204 7 12 7C17.351 7 19.424 10.846 19.926 12C19.422 13.158 17.348 17 12 17Z" fill="#092C4C"></path>
                                        </svg>
                                    </a>`;
                            }
                            if (canEditFollowUp) {
                                actionButtons += `
                                    <a class="me-2" href="/edit-follow-up/${followUp.id}">
                                        <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z" fill="#092C4C"></path>
                                        </svg>
                                    </a>`;
                            }
                            actionButtons += deleteBtn;
                            if (!actionButtons) actionButtons = '<span class="text-muted">N/A</span>';

                            let mobileActions = `
                                <div class="mobile-actions">
                                    ${canViewFollowUp ? `<a href="/follow-up-view/${followUp.id}"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 9C11.206 9.00524 10.4459 9.32299 9.88447 9.88447C9.32299 10.4459 9.00524 11.206 9 12C9 13.642 10.358 15 12 15C13.641 15 15 13.642 15 12C15 10.359 13.641 9 12 9Z" fill="#092C4C"></path><path d="M12 5C4.36704 5 2.07304 11.617 2.05204 11.684L1.94604 12L2.05105 12.316C2.07305 12.383 4.36704 19 12 19C19.633 19 21.927 12.383 21.948 12.316L22.054 12L21.949 11.684C21.927 11.617 19.633 5 12 5ZM12 17C6.64904 17 4.57604 13.154 4.07404 12C4.57804 10.842 6.65204 7 12 7C17.351 7 19.424 10.846 19.926 12C19.422 13.158 17.348 17 12 17Z" fill="#092C4C"></path></svg></a>` : ''}
                                    ${canEditFollowUp ? `<a href="/edit-follow-up/${followUp.id}"><svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z" fill="#092C4C"></path></svg></a>` : ''}
                                    ${deleteBtn}
                                </div>`;

                            mobileDetailsMap[followUp.id] = `
                                <div class="collapse-details">
                                    <div class="detail-item"><span class="detail-label">Date:</span><span class="detail-value">${followUp.formatted_follow_up_datetime || 'N/A'}</span></div>
                                    <div class="detail-item"><span class="detail-label">Lead Name:</span><span class="detail-value">${followUp.subject_name || 'N/A'}</span></div>
                                    <div class="detail-item"><span class="detail-label">Purpose:</span><span class="detail-value">${followUp.purpose || 'N/A'}</span></div>
                                    <div class="detail-item"><span class="detail-label">Priority:</span><span class="detail-value">${priorityBadge}</span></div>
                                    <div class="detail-item"><span class="detail-label">Status:</span><span class="detail-value">${statusBadge}</span></div>
                                    <div class="detail-item"><span class="detail-label">Assigned To:</span><span class="detail-value">${followUp.assigned_user ? followUp.assigned_user.name : 'N/A'}</span></div>
                                    <div class="detail-item"><span class="detail-label">Comment:</span><span class="detail-value">${followUp.comment || 'N/A'}</span></div>
                                    ${mobileActions}
                                </div>`;

                            tableBody.push([
                                `<div>
                                    <div class="cell-wrap">
                                        ${followUp.subject_name || 'N/A'}
                                    </div>
                                </div>`,
                                `<div class="cell-wrap">${followUp.purpose || 'N/A'}</div>`,
                                priorityBadge,
                                statusBadge,
                                `<div class="cell-wrap">${followUp.formatted_follow_up_datetime || 'N/A'}</div>`,
                                `<div class="cell-wrap">${followUp.assigned_user ? followUp.assigned_user.name : 'N/A'}</div>`,
                                actionButtons,
                                detailsToggle
                            ]);
                        });

                        table.clear().rows.add(tableBody).draw();
                    } else {
                        table.clear().draw();
                        $(".datanew tbody").html('<tr><td colspan="8" class="text-center">No follow ups found</td></tr>');
                        Object.keys(mobileDetailsMap).forEach(function (key) {
                            delete mobileDetailsMap[key];
                        });
                        updatePaginationUI({ current_page: 1, last_page: 1, per_page: perPage, total: 0 });
                    }
                },
                error: function () {
                    table.clear().draw();
                    $(".datanew tbody").html('<tr><td colspan="8" class="text-center">Error loading data</td></tr>');
                    Object.keys(mobileDetailsMap).forEach(function (key) {
                        delete mobileDetailsMap[key];
                    });
                }
            });
        }

        function updatePaginationUI(pagination) {
            let from = pagination.total === 0 ? 0 : (pagination.current_page - 1) * pagination.per_page + 1;
            let to   = Math.min(pagination.current_page * pagination.per_page, pagination.total);

            $('#pagination-from').text(from);
            $('#pagination-to').text(to);
            $('#pagination-total').text(pagination.total);

            const visiblePageCount = 2;
            let startPage = Math.floor((pagination.current_page - 1) / visiblePageCount) * visiblePageCount + 1;
            let endPage   = Math.min(pagination.last_page, startPage + visiblePageCount - 1);

            let html = `<li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="javascript:void(0);" data-page="${pagination.current_page - 1}">Previous</a>
                        </li>`;

            if (startPage > 1) {
                html += `<li class="page-item"><a class="page-link" href="javascript:void(0);" data-page="${startPage - 1}" data-action="prev-group">..</a></li>`;
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                            <a class="page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                         </li>`;
            }

            if (endPage < pagination.last_page) {
                if (endPage < pagination.last_page - 1) {
                    html += `<li class="page-item disabled"><a class="page-link" href="javascript:void(0);">..</a></li>`;
                }
                html += `<li class="page-item"><a class="page-link" href="javascript:void(0);" data-page="${pagination.last_page}">${pagination.last_page}</a></li>`;
            }

            html += `<li class="page-item ${pagination.current_page === pagination.last_page || pagination.last_page === 0 ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" data-page="${pagination.current_page + 1}">Next</a>
                     </li>`;

            $('#pagination-numbers').html(html);
            $('.pagination-controls').show();
        }

        // Toggle mobile details icon
        $(document).on('click', '.toggle-details', function (e) {
            e.preventDefault();
            let toggle = $(this);
            let icon = toggle.find('i');
            let followUpId = toggle.data('id');
            let tr = toggle.closest('tr');
            let row = table.row(tr);
            let detailHtml = mobileDetailsMap[followUpId] || '';

            if (!detailHtml) return;

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
                icon.removeClass('fa-minus-circle').addClass('fa-plus-circle').css('color', '#ff9f43');
            } else {
                $('.datanew tbody tr.shown').each(function () {
                    let openRow = table.row(this);
                    if (openRow.child.isShown()) openRow.child.hide();
                    $(this).removeClass('shown')
                        .find('.toggle-details i')
                        .removeClass('fa-minus-circle')
                        .addClass('fa-plus-circle')
                        .css('color', '#ff9f43');
                });

                row.child(detailHtml, 'followup-mobile-child-row').show();
                tr.addClass('shown');
                icon.removeClass('fa-plus-circle').addClass('fa-minus-circle').css('color', 'red');
            }
        });

        // Delete follow up
        $(document).on('click', '.delete-followup', function () {
            var followUpId = $(this).data('id');
            Swal.fire({
                title: "Are you sure?", text: "You won't be able to revert this!",
                icon: "warning", showCancelButton: true,
                confirmButtonColor: "#ff9f43", cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/follow-up/${followUpId}/delete`, type: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                            "Authorization": "Bearer " + authToken,
                        },
                        success: function (response) {
                            if (response.status) {
                                Swal.fire({ title: "Deleted!", text: "Follow up has been deleted.", icon: "success", confirmButtonColor: "#ff9f43" })
                                    .then(() => fetchFollowUps(currentPage));
                            } else {
                                Swal.fire({ title: "Error!", text: response.message || "Failed to delete.", icon: "error", confirmButtonColor: "#ff9f43" });
                            }
                        },
                        error: function () {
                            Swal.fire({ title: "Error!", text: "Failed to delete. Please try again.", icon: "error", confirmButtonColor: "#ff9f43" });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
