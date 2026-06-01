@extends('layout.app')

@section('title', 'Follow Up Report')

@section('content')
    <style>
        a.btn.back-button {
            background: #ff9f43;
            color: #fff;
        }

        .filter-section {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .filter-row {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 200px;
        }

        .filter-group label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .filter-group select,
        .filter-group input {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 14px;
        }

        .btn-filter {
            background: #ff9f43;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-filter:hover {
            background: #e68a35;
        }

        .btn-reset {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-reset:hover {
            background: #5a6268;
        }

        .priority-badge, .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
        }

        .priority-high {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .priority-medium {
            background-color: #fed7aa;
            color: #ea580c;
        }

        .priority-low {
            background-color: #d1fae5;
            color: #059669;
        }

        .status-pending {
            background-color: #fed7aa;
            color: #ea580c;
        }

        .status-rescheduled {
            background-color: #dbeafe;
            color: #2563eb;
        }

        .status-completed {
            background-color: #d1fae5;
            color: #059669;
        }

        .status-cancelled {
            background-color: #fee2e2;
            color: #dc2626;
        }

        @media (max-width: 768px) {
            .filter-row {
                flex-direction: column;
                gap: 10px;
            }

            .filter-group {
                min-width: 100%;
            }
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Follow Up Report</h4>
            </div>
            <div class="back-button">
                <a href="{{ route('followup.list') }}" class="btn back-button">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="priority-filter">Priority</label>
                            <select id="priority-filter" class="form-control">
                                <option value="">All Priorities</option>
                                <option value="High">High</option>
                                <option value="Medium">Medium</option>
                                <option value="Low">Low</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="status-filter">Status</label>
                            <select id="status-filter" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="Pending">Pending</option>
                                <option value="Rescheduled">Rescheduled</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="customer-filter">Lead</label>
                            <select id="customer-filter" class="form-control">
                                <option value="">All Leads</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="assigned-filter">Assigned To</label>
                            <select id="assigned-filter" class="form-control">
                                <option value="">All Staff</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="date-from">From Date</label>
                            <input type="date" id="date-from" class="form-control">
                        </div>

                        <div class="filter-group">
                            <label for="date-to">To Date</label>
                            <input type="date" id="date-to" class="form-control">
                        </div>

                        <div class="filter-group">
                            <button type="button" class="btn-filter" onclick="applyFilters()">
                                <i class="fas fa-filter me-1"></i> Apply
                            </button>
                        </div>

                        <div class="filter-group">
                            <button type="button" class="btn-reset" onclick="resetFilters()">
                                <i class="fas fa-redo me-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Report Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="followUpReportTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Lead</th>
                                <th>Purpose</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Follow Up Date</th>
                                <th>Assigned To</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading follow up data...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');

            // Load dropdown data
            loadCustomers();
            loadStaff();

            function loadCustomers() {
                let url = '/api/follow-up/customers';
                if (selectedSubAdminId) {
                    url += `?selectedSubAdminId=${selectedSubAdminId}`;
                }

                $.ajax({
                    url: url,
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            let options = '<option value="">All Leads</option>';
                            response.data.forEach(function(lead) {
                                options += `<option value="${lead.id}">${lead.name}</option>`;
                            });
                            $('#customer-filter').html(options);
                        }
                    },
                    error: function(xhr) {
                        console.error('Failed to load customers:', xhr);
                    }
                });
            }

            function loadStaff() {
                let url = '/api/follow-up/staff';
                if (selectedSubAdminId) {
                    url += `?selectedSubAdminId=${selectedSubAdminId}`;
                }

                $.ajax({
                    url: url,
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            let options = '<option value="">All Staff</option>';
                            response.data.forEach(function(staff) {
                                options += `<option value="${staff.id}">${staff.name}</option>`;
                            });
                            $('#assigned-filter').html(options);
                        }
                    },
                    error: function(xhr) {
                        console.error('Failed to load staff:', xhr);
                    }
                });
            }

            function loadReportData() {
                let url = '/api/getAllFollowUps';
                if (selectedSubAdminId) {
                    url += `?selectedSubAdminId=${selectedSubAdminId}`;
                }

                // Add filters
                let params = new URLSearchParams();
                
                const priority = $('#priority-filter').val();
                const status = $('#status-filter').val();
                const customer = $('#customer-filter').val();
                const assigned = $('#assigned-filter').val();
                const dateFrom = $('#date-from').val();
                const dateTo = $('#date-to').val();

                if (priority) params.append('priority', priority);
                if (status) params.append('status', status);
                if (customer) params.append('customer_id', customer);
                if (assigned) params.append('assigned_to', assigned);
                if (dateFrom) params.append('date_from', dateFrom);
                if (dateTo) params.append('date_to', dateTo);

                const paramString = params.toString();
                if (paramString) {
                    url += (selectedSubAdminId ? '&' : '?') + paramString;
                }

                $.ajax({
                    url: url,
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            displayReportData(response.data);
                        } else {
                            showError('Failed to load follow up data.');
                        }
                    },
                    error: function(xhr) {
                        showError('Failed to load follow up data.');
                    }
                });
            }

            function displayReportData(followUps) {
                let tableBody = '';
                
                if (followUps.length === 0) {
                    tableBody = '<tr><td colspan="8" class="text-center text-muted">No follow ups found</td></tr>';
                } else {
                    followUps.forEach(function(followUp) {
                        const priorityBadge = `<span class="priority-badge priority-${followUp.priority.toLowerCase()}">${followUp.priority}</span>`;
                        const statusBadge = `<span class="status-badge status-${followUp.status.toLowerCase()}">${followUp.status}</span>`;
                        
                        tableBody += `
                            <tr>
                                <td>${followUp.id}</td>
                                <td>${followUp.subject_name || 'N/A'}</td>
                                <td>${followUp.purpose || 'N/A'}</td>
                                <td>${priorityBadge}</td>
                                <td>${statusBadge}</td>
                                <td>${followUp.formatted_follow_up_datetime || 'N/A'}</td>
                                <td>${followUp.assigned_user ? followUp.assigned_user.name : 'N/A'}</td>
                                <td>${new Date(followUp.created_at).toLocaleDateString()}</td>
                            </tr>
                        `;
                    });
                }

                $('#followUpReportTable tbody').html(tableBody);
            }

            function showError(message) {
                $('#followUpReportTable tbody').html(`
                    <tr>
                        <td colspan="8" class="text-center text-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${message}
                        </td>
                    </tr>
                `);
            }

            window.applyFilters = function() {
                loadReportData();
            };

            window.resetFilters = function() {
                $('#priority-filter').val('');
                $('#status-filter').val('');
                $('#customer-filter').val('');
                $('#assigned-filter').val('');
                $('#date-from').val('');
                $('#date-to').val('');
                loadReportData();
            };

            // Load initial data
            loadReportData();
        });
    </script>
@endpush
