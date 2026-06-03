@extends('layout.app')
@section('title', 'Attendance View')
@section('content')
    <style>
  #saveBtn {
        position: relative;
        min-width: 80px;
    }

    .btn-text {
        display: inline-block;
    }

    #saveSpinner {
        margin-left: 5px;
    }

    .btn-submit:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    /* Staff Name column word wrap */
    .staff-name-cell {
        white-space: normal !important;
        word-break: break-word;
        overflow-wrap: anywhere;
        max-width: 160px;
        line-height: 1.3;
    }

    .staff-edit-cell,
    .staff-edit-head {
        width: 48px;
        min-width: 48px;
        text-align: center;
    }

    .staff-edit-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border: 1px solid #d9d9d9;
        border-radius: 4px;
        background: #fff;
        cursor: pointer;
    }

    .staff-edit-btn i {
        color: #6c757d;
        font-size: 14px;
    }

    /* ============================================================
       MANAGE ATTENDANCE — Summary Table & History Rows
       ============================================================ */
    #manageAttendanceModal .modal-dialog { max-width: 96vw; }

    .att-summary-table thead th {
        background-color: #1b2850;
        color: #fff;
        font-size: 12px;
        white-space: nowrap;
        vertical-align: middle;
        padding: 10px 12px;
    }
    .att-summary-table tbody td {
        vertical-align: middle;
        padding: 10px 12px;
        font-size: 13px;
    }
    .att-summary-table tbody tr:hover { background-color: #f8f9ff; }

    .emp-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 8px;
    }

    .btn-view-history {
        background: #17a2b8;
        color: #fff;
        border: none;
        border-radius: 20px;
        padding: 5px 14px;
        font-size: 12px;
        cursor: pointer;
        white-space: nowrap;
        transition: background .2s;
    }
    .btn-view-history:hover { background: #138496; color: #fff; }
    .btn-view-history.active { background: #e67e22; }

    .history-expand-row td {
        padding: 0 !important;
        background: #f4f7fb;
        border-top: none !important;
    }
    .history-expand-inner {
        padding: 16px 24px;
        border-left: 4px solid #17a2b8;
    }
    .history-expand-inner h6 {
        font-weight: 700;
        margin-bottom: 12px;
        color: #1b2850;
        font-size: 13px;
    }

    .att-history-table thead th {
        background-color: #1b2850;
        color: #fff;
        font-size: 11px;
        white-space: nowrap;
        padding: 8px 10px;
        vertical-align: middle;
    }
    .att-history-table tbody td {
        font-size: 12px;
        padding: 7px 10px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .att-history-table tbody tr:nth-child(even) { background: #edf1f7; }

    .status-present  { color: #27ae60; font-weight: 600; }
    .status-absent   { color: #e74c3c; font-weight: 600; }
    .status-weekoff  { color: #7f8c8d; font-weight: 600; }
    .status-halfday  { color: #f39c12; font-weight: 600; }

    .ma-filter-bar {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .ma-filter-bar select { min-width: 90px; font-size: 13px; }
    .btn-all-attendance {
        background: #ff6b35;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 6px 14px;
        font-size: 13px;
        cursor: pointer;
        white-space: nowrap;
        transition: background .2s;
    }
    .btn-all-attendance:hover { background: #e55a25; }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Attendance</h4>
            </div>
            <div class="page-btn d-flex gap-2">
                @if(auth()->user()->role !== 'staff')
                <a href="javascript:void(0);" class="btn btn-added" id="addAllBtn">
                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" class="me-1" alt="img">
                    Add All Attendance
                </a>
                <a href="{{ route('attendance.manage') }}" class="btn btn-primary" style="background:#e74c3c;border-color:#e74c3c;">
                    <i class="fa fa-chart-bar me-1"></i> Manage Attendance
                </a>
                @endif
            </div>
        </div>
        <div class="card mt-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0" id="month-year-header">{{ date('F Y', strtotime($currentMonth)) }} Attendance</h5>
                 <div class="d-flex align-items-center flex-wrap gap-3 p-2 rounded shadow-sm border bg-light">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success px-3">P</span>
                        <span class="fw-semibold text-dark">Present</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-danger px-3">A</span>
                        <span class="fw-semibold text-dark">Absent</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success px-3">HP</span>
                        <span class="fw-semibold text-dark">Half Day Present</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success px-3">2P</span>
                        <span class="fw-semibold text-dark">Extra Day Present</span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <div style="width: 150px;">
                        <input type="month" class="form-control form-control-sm" id="select-date" name="month"
                            value="{{ $currentMonth }}">
                    </div>
                    <div style="width: 146px;">
                        <input type="text" id="searchStaff" class="form-control form-control-sm"
                            placeholder="Search Staff...">
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0" style="max-height: 600px;">
                <div id="attendance-table-wrapper">
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="sticky-top"
                style="z-index:1 !important; top: 0; background-color: #1b2850; color: #fff;">
                <tr>
                    <th style="position: sticky; left: 0; z-index: 10; background-color: #1b2850; color: #fff;">
                        Staff Name
                    </th>
                    @if(auth()->user()->role !== 'staff')
                        <th class="staff-edit-head text-white">Edit</th>
                    @endif
                    <th class="text-white" style="min-width: 80px;">Total OT</th>

                    @for ($i = 1; $i <= $daysInMonth; $i++)
                        <th class="text-white">{{ $i }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody id="attendance-table-body">
                @foreach ($staffUsers as $staff)
                    <tr>
                        <td style="position: sticky; left: 0; background-color: #fff; cursor: pointer;"
                            class="text-start ps-2 staff-name-cell"
                            data-user-id="{{ $staff->id }}"
                            data-user-name="{{ ucwords($staff->name) }}">
                            {{ ucwords($staff->name) }}
                        </td>
                        @if(auth()->user()->role !== 'staff')
                            <td class="staff-edit-cell">
                                <button type="button" class="staff-edit-btn open-staff-attendance-modal"
                                    data-user-id="{{ $staff->id }}"
                                    data-user-name="{{ ucwords($staff->name) }}"
                                    title="Edit {{ ucwords($staff->name) }} attendance">
                                    <i class="fa fa-pencil-alt"></i>
                                </button>
                            </td>
                        @endif
                        @php
                            $totalOtForMonth = 0;
                            for ($j = 1; $j <= $daysInMonth; $j++) {
                                $d = $year . '-' . $month . '-' . str_pad($j, 2, '0', STR_PAD_LEFT);
                                $k = $staff->id . '_' . $d;
                                $att = $attendances[$k][0] ?? null;
                                if ($att && $att->overtime_hours > 0) {
                                    $totalOtForMonth += $att->overtime_hours;
                                }
                            }
                        @endphp
                        <td class="text-center fw-bold text-primary">{{ $totalOtForMonth > 0 ? $totalOtForMonth . 'h' : '-' }}</td>
                        @for ($i = 1; $i <= $daysInMonth; $i++)
                            @php
                                $date = $year . '-' . $month . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                                $key = $staff->id . '_' . $date;
                                $attendance = $attendances[$key][0] ?? null;
                                $status = $attendance->status ?? 'A';
                            @endphp
                            @php
                                $displayStatus = $status;
                                if ($status == 'P' && ($attendance->extraday ?? 0) == 1) {
                                    $displayStatus = '2P';
                                }
                                if ($status == 'H') {
                                    $displayStatus = 'H';
                                }
                            @endphp
                            <td class="attendance-cell text-center" data-user-id="{{ $staff->id }}"
                                data-date="{{ $date }}" data-status="{{ $status }}"
                                data-checkin="{{ $attendance->check_in_time ?? '' }}"
                                data-checkout="{{ $attendance->check_out_time ?? '' }}"
                                data-reason="{{ $attendance->reason ?? '' }}"
                                data-extraday="{{ $attendance->extraday ?? '0' }}"
                                data-overtime="{{ $attendance->overtime_hours ?? '0' }}" style="cursor:pointer;">
                                @if ($displayStatus == 'P' || $displayStatus == '2P')
                                    <strong class="text-success">{{ $displayStatus }}</strong>
                                @elseif ($displayStatus == 'H')
                                    <strong class="text-success">HP</strong>
                                @else
                                    <strong class="text-danger">A</strong>
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

            </div>
        </div>
    </div>
    <!-- Attendance Modal -->
    <div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="attendanceForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Attendance</h5>
                        <div class="mb-2 d-flex fw-bold ">
                            <label class="me-2">Date:</label>
                            <div id="display_date" class="fw-bold"></div>
                        </div>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="user_id">
                        <input type="hidden" name="date" id="date">

                        <div class="row">
                            <!-- Row 1: Status | Extra Day -->
                            <div class="col-md-6 mb-3">
                                <label>Status</label>
                                <select class="form-select" name="status" id="status">
                                    <option value="P">Present</option>
                                    <option value="H">Half Day</option>
                                    <option value="A">Absent</option>
                                </select>
                                <div class="text-danger" id="error_status"></div>
                            </div>
                            <div class="col-md-6 mb-3" id="extraday-field" style="display: none;">
                                <label>Extra Day</label>
                                <select class="form-select" name="extraday" id="extraday">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                                <div class="text-danger" id="error_extraday"></div>
                            </div>

                            <!-- Row 2: Check-In Time | Check-Out Time -->
                            <div class="col-md-6 mb-3">
                                <label>Check-In Time</label>
                                <input type="time" class="form-control" name="check_in_time" id="check_in_time">
                                <div class="text-danger" id="error_check_in_time"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Check-Out Time</label>
                                <input type="time" class="form-control" name="check_out_time" id="check_out_time">
                                <div class="text-danger" id="error_check_out_time"></div>
                            </div>

                            <!-- Row 3: Duration | Overtime -->
                            <div class="col-md-6 mb-3">
                                <label>Duration</label>
                                <input type="text" class="form-control bg-light" name="duration" id="duration_display" readonly placeholder="0h 0m">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Overtime</label>
                                <input type="text" class="form-control bg-light" name="overtime" id="overtime_display" readonly placeholder="0h 0m">
                            </div>

                            <!-- Row 4: Reason -->
                            <div class="col-md-12 mb-3">
                                <label>Reason</label>
                                <textarea class="form-control" name="reason" id="reason" rows="2"></textarea>
                                <div class="text-danger" id="error_reason"></div>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 1rem;">
                        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-submit" id="saveBtn">
                            <span class="btn-text">Save</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status" id="saveSpinner"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @if(auth()->user()->role !== 'staff')
    <!-- Bulk Attendance Modal -->
    <div class="modal fade" id="bulkAttendanceModal" tabindex="-1" aria-labelledby="bulkAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="bulkAttendanceForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkModalTitle">All Attendance Update</h5>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="bulk_user_id">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label>Start Date</label>
                                <div class="input-groupicon">
                                    <input type="text" class="form-control datetimepicker-bulk" name="start_date"
                                        id="bulk_start_date" placeholder="DD/MM/YYYY" required>
                                </div>
                                <div class="text-danger" id="error_bulk_start_date"></div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label>End Date</label>
                                <div class="input-groupicon">
                                    <input type="text" class="form-control datetimepicker-bulk" name="end_date"
                                        id="bulk_end_date" placeholder="DD/MM/YYYY" required>
                                </div>
                                <div class="text-danger" id="error_bulk_end_date"></div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label>Status</label>
                            <select class="form-select" name="status" id="bulk_status">
                                <option value="P">Present</option>
                                <option value="H">Half Day</option>
                                <option value="A">Absent</option>
                            </select>
                            <div class="text-danger" id="error_bulk_status"></div>
                        </div>
                        <div class="mb-2 bulk-time-fields">
                            <label>Check-In Time</label>
                            <input type="time" class="form-control" name="check_in_time" id="bulk_check_in_time">
                            <div class="text-danger" id="error_bulk_check_in_time"></div>
                        </div>
                        <div class="mb-2 bulk-time-fields">
                            <label>Check-Out Time</label>
                            <input type="time" class="form-control" name="check_out_time" id="bulk_check_out_time">
                            <div class="text-danger" id="error_bulk_check_out_time"></div>
                        </div>
                        <div class="mb-2" id="bulk-extraday-field">
                            <label>Extra Day</label>
                            <select class="form-select" name="extraday" id="bulk_extraday">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                            <div class="text-danger" id="error_bulk_extraday"></div>
                        </div>
                        <div class="mb-2" id="bulk-reason-field" style="display: none;">
                            <label>Reason</label>
                            <textarea class="form-control" name="reason" id="bulk_reason"></textarea>
                            <div class="text-danger" id="error_bulk_reason"></div>
                        </div>
                    </div>
                    <div style="padding: 1rem;">
                        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-submit" id="bulkSaveBtn">
                            <span class="bulk-btn-text">Save</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status" id="bulkSaveSpinner"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @endif
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @php
            $setting = \App\Models\Setting::first();
            $working_hours = $setting && $setting->working_hours ? $setting->working_hours : '09:00';
        @endphp
        var companyWorkingHours = '{{ $working_hours }}';

        $(document).ready(function() {
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

            // Function to update the month-year header
            function updateMonthYearHeader() {
                const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August",
                    "September", "October", "November", "December"
                ];
                const selectedDate = $('#select-date').val();
                const [year, month] = selectedDate.split('-');
                const monthName = monthNames[parseInt(month, 10) - 1];
                $('#month-year-header').text(`${monthName} ${year} Attendance`);
            }

            // Initialize month-year header
            updateMonthYearHeader();

            // Initialize bulk date pickers
            if ($('.datetimepicker-bulk').length > 0) {
                $('.datetimepicker-bulk').datetimepicker({
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    icons: {
                        up: "fas fa-angle-up",
                        down: "fas fa-angle-down",
                        next: 'fas fa-angle-right',
                        previous: 'fas fa-angle-left'
                    }
                });
            }

            // Function to fetch and update attendance data
            function fetchAttendanceData() {
                const month = $('#select-date').val();
                const search = $('#searchStaff').val();

                $.ajax({
                    url: "{{ route('attendance.list') }}",
                    method: 'GET',
                    data: {
                        month: month,
                        search: search
                    },
                    success: function(response) {
                        $('#attendance-table-wrapper').html($(response).find('#attendance-table-wrapper').html());
                        updateMonthYearHeader();
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });
            }

            // Listen for changes on the month input and search input
            $('#select-date, #searchStaff').on('change input', function() {
                fetchAttendanceData();
            });

            // Auto calculate duration and overtime
            function calculateDurationOvertime() {
                var checkIn = $('#check_in_time').val();
                var checkOut = $('#check_out_time').val();

                if (checkIn && checkOut) {
                    var inTime = new Date("1970-01-01T" + checkIn + "Z");
                    var outTime = new Date("1970-01-01T" + checkOut + "Z");

                    if (outTime < inTime) {
                        outTime.setDate(outTime.getDate() + 1); // Cross midnight
                    }

                    var diffMs = outTime - inTime;
                    var diffHrs = diffMs / 3600000;

                    var durH = Math.floor(diffHrs);
                    var durM = Math.round((diffHrs - durH) * 60);
                    $('#duration_display').val(durH + 'h ' + durM + 'm');

                    // Parse company working hours
                    var whParts = companyWorkingHours.split(':');
                    var whHrs = parseFloat(whParts[0]) + (parseFloat(whParts[1] || 0) / 60);

                    if (diffHrs > whHrs) {
                        var otHrs = diffHrs - whHrs;
                        var oH = Math.floor(otHrs);
                        var oM = Math.round((otHrs - oH) * 60);
                        $('#overtime_display').val(oH + 'h ' + oM + 'm');
                    } else {
                        $('#overtime_display').val('0h 0m');
                    }

                    // Auto-select status based on duration
                    if (diffHrs >= whHrs) {
                        $('#status').val('P').change();
                    } else if (diffHrs >= (whHrs / 2)) {
                        $('#status').val('H').change();
                    } else {
                        $('#status').val('A').change();
                    }
                } else {
                    $('#duration_display').val('');
                    $('#overtime_display').val('');
                }
            }

            $('#check_in_time, #check_out_time').on('change', calculateDurationOvertime);

            $('#addAllBtn').on('click', function() {
                $('#bulk_user_id').val('');
                $('#bulkModalTitle').text('All Attendance Update');
                $('#bulkAttendanceModal').modal('show');
            });

            $(document).on('click', '.open-staff-attendance-modal', function() {
                const currentUserRole = '{{ auth()->user()->role }}';
                if (currentUserRole === 'staff') {
                    return;
                }

                const userId = $(this).data('user-id');
                const userName = $(this).data('user-name');

                $('#bulk_user_id').val(userId);
                $('#bulkModalTitle').text(`${userName} Attendance Update`);
                $('#bulkAttendanceForm')[0].reset();
                $('#bulkAttendanceForm .text-danger').html('');
                $('#bulk_status').val('P').trigger('change');
                $('#bulkAttendanceModal').modal('show');
            });

            $('#bulk_status').on('change', function() {
                const status = $(this).val();
                if (status === 'P' || status === 'H') {
                    $('.bulk-time-fields').show();
                    $('#bulk-extraday-field').show();
                    $('#bulk-reason-field').hide();
                } else {
                    $('.bulk-time-fields').hide();
                    $('#bulk-extraday-field').hide();
                    $('#bulk-reason-field').show();
                }
            });

            $('#bulkAttendanceForm').on('submit', function(e) {
                e.preventDefault();

                $('#bulkAttendanceForm .text-danger').html('');

                let hasError = false;
                const startDate = $('#bulk_start_date').val();
                const endDate = $('#bulk_end_date').val();

                if (!startDate) {
                    $('#error_bulk_start_date').text('Start Date is required');
                    hasError = true;
                }
                if (!endDate) {
                    $('#error_bulk_end_date').text('End Date is required');
                    hasError = true;
                }

                if (hasError) {
                    return false;
                }

                const saveBtn = $('#bulkSaveBtn');
                const btnText = $('.bulk-btn-text');
                const saveSpinner = $('#bulkSaveSpinner');

                saveBtn.prop('disabled', true);
                btnText.text('Saving...');
                saveSpinner.removeClass('d-none');

                var authToken = localStorage.getItem("authToken");
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                let formData = new FormData(this);
                formData.append('selectedSubAdminId', selectedSubAdminId);

                $.ajax({
                    url: "{{ route('attendance.bulk-store') }}",
                    method: 'POST',
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        saveBtn.prop('disabled', false);
                        btnText.text('Save');
                        saveSpinner.addClass('d-none');
                        $('#bulkAttendanceModal').modal('hide');
                        Swal.fire({
                            title: 'Success!',
                            text: res.message,
                            icon: 'success',
                            confirmButtonColor: '#ff9f43',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        saveBtn.prop('disabled', false);
                        btnText.text('Save');
                        saveSpinner.addClass('d-none');
                        let response = xhr.responseJSON;
                        let errors = response ? response.errors : null;

                        if (errors) {
                            let allErrors = [];
                            for (let key in errors) {
                                $(`#error_bulk_${key}`).text(errors[key][0]);
                                allErrors.push(errors[key][0]);
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                html: allErrors.join('<br>'),
                                confirmButtonColor: '#ff9f43'
                            });
                        } else {
                            Swal.fire('Error', 'Something went wrong.', 'error');
                        }
                    }
                });
            });

            // Function to toggle fields based on status
            function toggleAttendanceFields(status) {
                if (status === 'P' || status === 'H') {
                    $('#check_in_time').closest('.col-md-6').show();
                    $('#check_out_time').closest('.col-md-6').show();
                    $('#extraday-field').show();
                    $('#reason').closest('.col-md-12').hide();
                } else {
                    $('#check_in_time').closest('.col-md-6').show();
                    $('#check_out_time').closest('.col-md-6').show();
                    $('#extraday-field').hide();
                    $('#reason').closest('.col-md-12').show();
                }
            }

            // When status changes in the modal
            $('#status').on('change', function() {
                toggleAttendanceFields($(this).val());
            });

            // When opening modal
            $(document).on('click', '.attendance-cell', function() {
                const currentUserRole = '{{ auth()->user()->role }}';

                if (currentUserRole === 'staff') {
                    return;
                }

                const status = $(this).data('status');

                $('#user_id').val($(this).data('user-id'));
                $('#date').val($(this).data('date'));
                $('#status').val(status);
                $('#check_in_time').val($(this).data('checkin'));
                $('#check_out_time').val($(this).data('checkout'));
                $('#reason').val($(this).data('reason'));
                $('#extraday').val($(this).data('extraday'));

                toggleAttendanceFields(status);

                $('#status').prop('disabled', false);
                $('#check_in_time').prop('disabled', false);
                $('#check_out_time').prop('disabled', false);
                $('#reason').prop('disabled', false);
                $('#attendanceForm button[type="submit"]').show();

                const dateVal = $(this).data('date');
                if (dateVal) {
                    const parts = dateVal.split('-');
                    $('#display_date').text(parts[2] + '/' + parts[1] + '/' + parts[0]);
                }

                $('#attendanceForm .text-danger').html('');
                $('#attendanceModal').modal('show');

                // Calculate duration and overtime based on loaded times
                calculateDurationOvertime();
            });

            // Submit attendance form
            $('#attendanceForm').on('submit', function(e) {
                e.preventDefault();

                const saveBtn = $('#saveBtn');
                const btnText = $('.btn-text');
                const saveSpinner = $('#saveSpinner');

                saveBtn.prop('disabled', true);
                btnText.text('Saving...');
                saveSpinner.removeClass('d-none');

                var authToken = localStorage.getItem("authToken");
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                let formData = new FormData($('#attendanceForm')[0]);
                formData.append('selectedSubAdminId', selectedSubAdminId);

                $('#attendanceForm .text-danger').html('');

                $.ajax({
                    url: "{{ route('attendance.store') }}",
                    method: 'POST',
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        saveBtn.prop('disabled', false);
                        btnText.text('Save');
                        saveSpinner.addClass('d-none');
                        $('#attendanceModal').modal('hide');
                        Swal.fire({
                            title: 'Success!',
                            text: res.message,
                            icon: 'success',
                            confirmButtonColor: '#ff9f43',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        saveBtn.prop('disabled', false);
                        btnText.text('Save');
                        saveSpinner.addClass('d-none');
                        let response = xhr.responseJSON;
                        let errors = response ? response.errors : null;

                        if (errors) {
                            let allErrors = [];
                            for (let key in errors) {
                                if (errors.hasOwnProperty(key)) {
                                    $(`#error_${key}`).text(errors[key][0]);
                                    allErrors.push(errors[key][0]);
                                }
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                html: allErrors.join('<br>'),
                                confirmButtonColor: '#ff9f43'
                            });
                        } else {
                            Swal.fire('Error', 'Something went wrong.', 'error');
                        }
                    }
                });
            });

            // Reset button state when modal is closed
            $('#attendanceModal').on('hidden.bs.modal', function () {
                const saveBtn = $('#saveBtn');
                const btnText = $('.btn-text');
                const saveSpinner = $('#saveSpinner');

                saveBtn.prop('disabled', false);
                btnText.text('Save');
                saveSpinner.addClass('d-none');
            });
        });
    </script>

@endpush
