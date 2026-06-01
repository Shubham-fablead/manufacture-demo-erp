@extends('layout.app')

@section('title', 'Add Meeting')

@section('content')
    @php
        $canViewMeeting = app('hasPermission')(31, 'view');
        $canAddMeeting  = app('hasPermission')(31, 'add');
        $authUser = auth()->user();
        $isStaffUser = ($authUser->role ?? '') === 'staff';
        $loggedInStaffId = $authUser->id ?? null;
        $loggedInStaffLabel = $authUser?->name ?? '';
    @endphp
    <style>
        @media screen and (max-width: 768px) {
            .form-group { margin-bottom: 10px !important }
        }
        a.btn.back-button { background: #ff9f43; color: #fff; }
        .select2-container--default .select2-selection--single {
            height: 38px; border: 1px solid #ced4da; border-radius: 0.25rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px; padding-left: 12px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
        .form-control:focus {
            border-color: #ff9f43;
            box-shadow: 0 0 0 0.2rem rgba(255, 159, 67, 0.25);
        }
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #ff9f43;
            box-shadow: 0 0 0 0.2rem rgba(255, 159, 67, 0.25);
        }
        .field-error { min-height: 20px; margin-top: 4px; font-size: 0.875rem; }
        .select2-container .select2-selection--single.is-invalid { border-color: #dc3545 !important; }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title"><h4>Add Meeting</h4></div>
            <div class="back-button">
                @if ($canViewMeeting)
                    <a href="{{ route('meeting.list') }}" class="btn back-button">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>
                @endif
                <span class="success_submit text-danger"></span>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="meetingForm" method="POST" novalidate>
                    @csrf
                    <div class="row">

                        <!-- Customer Name -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Customer Name <span class="text-danger">*</span></label>
                                <select name="customer_id" id="customer_id" class="form-control" required>
                                    <option value="">Select Customer</option>
                                </select>
                                <div class="invalid-feedback d-block field-error" data-field="customer_id"></div>
                            </div>
                        </div>

                        <!-- Assigned To -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Assigned To</label>
                                <select name="assigned_to" id="assigned_to" class="form-control"
                                    @if ($isStaffUser) disabled @endif>
                                    <option value="">Select Staff</option>
                                </select>
                                <div class="invalid-feedback d-block field-error" data-field="assigned_to"></div>
                            </div>
                        </div>

                        <!-- Meeting Title -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Meeting Title <span class="text-danger">*</span></label>
                                <input type="text" name="meeting_title" id="meeting_title" maxlength="255" class="form-control" required>
                                <div class="invalid-feedback d-block field-error" data-field="meeting_title"></div>
                            </div>
                        </div>

                        <!-- Meeting Type -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Meeting Type <span class="text-danger">*</span></label>
                                <input type="text" name="meeting_type" id="meeting_type" maxlength="255" class="form-control" required>
                                <div class="invalid-feedback d-block field-error" data-field="meeting_type"></div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="Scheduled">Scheduled</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                                <div class="invalid-feedback d-block field-error" data-field="status"></div>
                            </div>
                        </div>

                        <!-- Scheduled Date & Time -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="scheduled_on" id="scheduled_on" class="form-control" required>
                                <div class="invalid-feedback d-block field-error" data-field="scheduled_on"></div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" name="address" id="address" maxlength="500" class="form-control">
                                <div class="invalid-feedback d-block field-error" data-field="address"></div>
                            </div>
                        </div>

                        <!-- Agenda -->
                        <div class="col-lg-3 col-sm-12 col-6">
                            <div class="form-group">
                                <label>Agenda <span class="text-danger">*</span></label>
                                <textarea name="agenda" id="agenda" rows="3" maxlength="1000" class="form-control" required></textarea>
                                <div class="invalid-feedback d-block field-error" data-field="agenda"></div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="col-lg-12">
                            @if ($canAddMeeting)
                                <button type="submit" id="submitMeetingBtn" class="btn btn-submit me-2">Save</button>
                            @endif
                            @if ($canViewMeeting)
                                <a href="{{ route('meeting.list') }}" class="btn btn-cancel">Cancel</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    $(document).ready(function () {
        var authToken = localStorage.getItem("authToken");
        const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
        const normalizedSubAdminId = (selectedSubAdminId && selectedSubAdminId !== 'null' && selectedSubAdminId !== 'undefined') ? selectedSubAdminId : '';
        const isStaffUser = @json($isStaffUser);
        const loggedInStaffId = @json($loggedInStaffId);
        const loggedInStaffLabel = @json($loggedInStaffLabel);

        function formatAssigneeLabel(user) {
            if (!user) {
                return '';
            }

            return user.name || '';
        }

        // Initialize Select2
        $('#customer_id, #assigned_to').select2({ width: '100%' });

        // Load customers
        function loadCustomers() {
            let url = '/api/meeting/customers';
            if (normalizedSubAdminId) url += `?selectedSubAdminId=${encodeURIComponent(normalizedSubAdminId)}`;

            $.ajax({
                url: url, type: "GET", dataType: "json",
                headers: { "Authorization": "Bearer " + authToken },
                success: function (response) {
                    if (response.status) {
                        let options = '<option value="">Select Customer</option>';
                        response.data.forEach(function (c) {
                            options += `<option value="${c.id}">${c.name}</option>`;
                        });
                        $('#customer_id').html(options);
                    }
                }
            });
        }

        // Load staff
        function lockAssignedToForStaff() {
            if (!isStaffUser || !loggedInStaffId) {
                return;
            }

            const options = `<option value="${loggedInStaffId}">${loggedInStaffLabel}</option>`;
            $('#assigned_to').html(options).val(String(loggedInStaffId)).trigger('change');
            $('#assigned_to').prop('disabled', true);
        }

        function loadStaff() {
            if (isStaffUser) {
                lockAssignedToForStaff();
                return;
            }

            let url = '/api/meeting/staff';
            if (normalizedSubAdminId) url += `?selectedSubAdminId=${encodeURIComponent(normalizedSubAdminId)}`;

            $.ajax({
                url: url, type: "GET", dataType: "json",
                headers: { "Authorization": "Bearer " + authToken },
                success: function (response) {
                    if (response.status) {
                        let options = '';
                        const defaultAssignee = response.default_assignee || null;

                        if (defaultAssignee) {
                            options += `<option value="${defaultAssignee.id}">${formatAssigneeLabel(defaultAssignee)}</option>`;
                        }

                        response.data.forEach(function (s) {
                            options += `<option value="${s.id}">${formatAssigneeLabel(s)}</option>`;
                        });

                        $('#assigned_to').html(options);

                        if (defaultAssignee) {
                            $('#assigned_to').val(String(defaultAssignee.id)).trigger('change');
                        } else {
                            $('#assigned_to').trigger('change');
                        }
                    }
                }
            });
        }

        // Set min datetime
        function setMinDateTime() {
            const now = new Date();
            const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
            $('#scheduled_on').attr('min', localDateTime);
        }

        loadCustomers();
        loadStaff();
        setMinDateTime();

        function clearFieldError(fieldName) {
            const $field = $('[name="' + fieldName + '"]');
            const $error = $('.field-error[data-field="' + fieldName + '"]');
            $error.text('');
            $field.removeClass('is-invalid');
            if ($field.hasClass('select2-hidden-accessible')) {
                $field.next('.select2-container').find('.select2-selection').removeClass('is-invalid');
            }
        }

        function setFieldError(fieldName, message) {
            const $field = $('[name="' + fieldName + '"]');
            const $error = $('.field-error[data-field="' + fieldName + '"]');
            $error.text(message);
            $field.addClass('is-invalid');
            if ($field.hasClass('select2-hidden-accessible')) {
                $field.next('.select2-container').find('.select2-selection').addClass('is-invalid');
            }
        }

        function clearValidationErrors() {
            $('.field-error').text('');
            $('#meetingForm .is-invalid').removeClass('is-invalid');
            $('#meetingForm .select2-selection').removeClass('is-invalid');
        }

        // Form submit
        $('#meetingForm').submit(function (e) {
            e.preventDefault();
            clearValidationErrors();

            const required = {
                customer_id:   'Customer is required.',
                meeting_title: 'Meeting title is required.',
                meeting_type:  'Meeting type is required.',
                status:        'Status is required.',
                scheduled_on:  'Scheduled date & time is required.',
                agenda:        'Agenda is required.'
            };

            let hasError = false;
            $.each(required, function (field, msg) {
                if (!$.trim($('[name="' + field + '"]').val() || '')) {
                    setFieldError(field, msg);
                    hasError = true;
                }
            });
            if (hasError) return;

            let formData = new FormData(this);
            if (isStaffUser && loggedInStaffId) {
                formData.set('assigned_to', loggedInStaffId);
            } else if (!$.trim($('#assigned_to').val() || '')) {
                formData.delete('assigned_to');
            }
            if (normalizedSubAdminId) formData.append('selectedSubAdminId', normalizedSubAdminId);

            const $btn = $('#submitMeetingBtn');
            const originalText = $btn.html();
            $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...').prop('disabled', true);

            $.ajax({
                url: '/meeting/store', type: 'POST',
                data: formData, processData: false, contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Authorization': 'Bearer ' + authToken
                },
                success: function (response) {
                    $btn.html(originalText).prop('disabled', false);
                    if (response.status) {
                        Swal.fire({ title: 'Success!', text: 'Meeting created successfully!', icon: 'success', confirmButtonText: 'OK', confirmButtonColor: '#ff9f43' })
                            .then(() => { window.location.href = "{{ route('meeting.list') }}"; });
                    } else {
                        Swal.fire({ title: 'Error!', text: response.message || 'Failed to create meeting.', icon: 'error', confirmButtonText: 'OK' });
                    }
                },
                error: function (xhr) {
                    $btn.html(originalText).prop('disabled', false);
                    if (xhr.status === 422) {
                        clearValidationErrors();
                        $.each(xhr.responseJSON.errors, function (key, val) { setFieldError(key, val[0]); });
                    } else {
                        Swal.fire({ title: 'Error!', text: 'Something went wrong. Please try again.', icon: 'error', confirmButtonText: 'OK' });
                    }
                }
            });
        });

        $('input, select, textarea').on('input change', function () {
            clearFieldError($(this).attr('name'));
        });
    });
</script>
@endpush
