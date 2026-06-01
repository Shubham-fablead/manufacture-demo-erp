@extends('layout.app')

@section('title', 'Schedule Follow Up')

@section('content')
    @php
        $canViewFollowUp = app('hasPermission')(30, 'view');
        $canAddFollowUp = app('hasPermission')(30, 'add');
        $authUser = auth()->user();
        $isStaffUser = ($authUser->role ?? '') === 'staff';
        $loggedInStaffId = $authUser->id ?? null;
        $loggedInStaffLabel = $authUser?->name ?? '';
    @endphp
    <style>
        @media screen and (max-width: 768px) {
            .form-group {
                margin-bottom: 10px !important
            }
        }

        a.btn.back-button {
            background: #ff9f43;
            color: #fff;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
            padding-left: 12px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .form-control:focus {
            border-color: #ff9f43;
            box-shadow: 0 0 0 0.2rem rgba(255, 159, 67, 0.25);
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #ff9f43;
            box-shadow: 0 0 0 0.2rem rgba(255, 159, 67, 0.25);
        }

        .field-error {
            min-height: 20px;
            margin-top: 4px;
            font-size: 0.875rem;
        }

        .select2-container .select2-selection--single.is-invalid {
            border-color: #dc3545 !important;
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Schedule Follow Up</h4>
            </div>
            <div class="back-button">
                @if ($canViewFollowUp)
                    <a href="{{ route('followup.list') }}" class="btn back-button"> <i class="fa-solid fa-arrow-left"></i>
                        Back</a>
                @endif
                <span class="success_submit text-danger"></span>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="followUpForm" method="POST" novalidate>
                    @csrf
                    <div class="row">
                        <!-- Lead Name -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Lead Name <span class="text-danger">*</span></label>
                                <select name="lead_id" id="lead_id" class="form-control" required>
                                    <option value="">Select Lead</option>
                                </select>
                                <div class="invalid-feedback d-block field-error" data-field="lead_id"></div>
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

                        <!-- Priority -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Priority <span class="text-danger">*</span></label>
                                <select name="priority" id="priority" class="form-control" required>
                                    <option value="">Select Priority</option>
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                </select>
                                <div class="invalid-feedback d-block field-error" data-field="priority"></div>
                            </div>
                        </div>

                        <!-- Purpose -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Purpose <span class="text-danger">*</span></label>
                                <input type="text" name="purpose" id="purpose" maxlength="255" class="form-control" required>
                                <div class="invalid-feedback d-block field-error" data-field="purpose"></div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Rescheduled">Rescheduled</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                                <div class="invalid-feedback d-block field-error" data-field="status"></div>
                            </div>
                        </div>

                        <!-- Follow Up Date & Time -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="follow_up_datetime" id="follow_up_datetime" class="form-control" required>
                                <div class="invalid-feedback d-block field-error" data-field="follow_up_datetime"></div>
                            </div>
                        </div>

                        <!-- Comment -->
                        <div class="col-lg-6 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Comment</label>
                                <textarea name="comment" id="comment" rows="3" maxlength="1000" class="form-control"></textarea>
                                <div class="invalid-feedback d-block field-error" data-field="comment"></div>
                            </div>
                        </div>

                        <!-- Submit & Cancel Buttons -->
                        <div class="col-lg-12">
                            @if ($canAddFollowUp)
                                <button type="submit" id="submitFollowUpBtn" class="btn btn-submit me-2">Submit</button>
                            @endif
                            @if ($canViewFollowUp)
                                <a href="{{ route('followup.list') }}" class="btn btn-cancel">Cancel</a>
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
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            const isStaffUser = @json($isStaffUser);
            const loggedInStaffId = @json($loggedInStaffId);
            const loggedInStaffLabel = @json($loggedInStaffLabel);

            function formatAssigneeLabel(user) {
                if (!user) {
                    return '';
                }

                return user.name || '';
            }

            // Initialize Select2 for dropdowns
            $('#lead_id, #assigned_to').select2({
                width: '100%',
                placeholder: function() {
                    $(this).data('placeholder');
                }
            });

            // Load leads
            function loadLeads() {
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
                            let options = '<option value="">Select Lead</option>';
                            response.data.forEach(function(lead) {
                                options += `<option value="${lead.id}">${lead.name}</option>`;
                            });
                            $('#lead_id').html(options);
                        }
                    },
                    error: function(xhr) {
                        console.error('Failed to load leads:', xhr);
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
                            let options = '';
                            const defaultAssignee = response.default_assignee || null;

                            if (defaultAssignee) {
                                options += `<option value="${defaultAssignee.id}">${formatAssigneeLabel(defaultAssignee)}</option>`;
                            }

                            response.data.forEach(function(staff) {
                                options += `<option value="${staff.id}">${formatAssigneeLabel(staff)}</option>`;
                            });

                            $('#assigned_to').html(options);

                            if (defaultAssignee) {
                                $('#assigned_to').val(String(defaultAssignee.id)).trigger('change');
                            } else {
                                $('#assigned_to').trigger('change');
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Failed to load staff:', xhr);
                    }
                });
            }

            // Set minimum datetime to current datetime
            function setMinDateTime() {
                const now = new Date();
                const offset = now.getTimezoneOffset();
                const localDateTime = new Date(now.getTime() - (offset * 60000)).toISOString().slice(0, 16);
                $('#follow_up_datetime').attr('min', localDateTime);
            }

            // Initialize
            loadLeads();
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

                if ($error.length) {
                    $error.text(message);
                }

                if ($field.length) {
                    $field.addClass('is-invalid');

                    if ($field.hasClass('select2-hidden-accessible')) {
                        $field.next('.select2-container').find('.select2-selection').addClass('is-invalid');
                    }
                }
            }

            function clearValidationErrors() {
                $('.field-error').text('');
                $('#followUpForm').find('.is-invalid').removeClass('is-invalid');
                $('#followUpForm').find('.select2-selection').removeClass('is-invalid');
            }

            // Handle form submission
            $("#followUpForm").submit(function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                clearValidationErrors();

                if (isStaffUser && loggedInStaffId) {
                    formData.set('assigned_to', loggedInStaffId);
                } else if (!$.trim($('#assigned_to').val() || '')) {
                    formData.delete('assigned_to');
                }

                const requiredMessages = {
                    lead_id: 'Lead is required.',
                    priority: 'Priority is required.',
                    purpose: 'Purpose is required.',
                    status: 'Status is required.',
                    follow_up_datetime: 'Follow up date & time is required.'
                };

                let hasValidationError = false;

                $.each(requiredMessages, function(field, message) {
                    const value = $.trim($('[name="' + field + '"]').val() || '');
                    if (!value) {
                        setFieldError(field, message);
                        hasValidationError = true;
                    }
                });

                if (hasValidationError) {
                    return;
                }

                if (selectedSubAdminId) {
                    formData.append("selectedSubAdminId", selectedSubAdminId);
                }

                const $btn = $("#submitFollowUpBtn");
                const originalText = $btn.html();

                // Show spinner and disable button
                $btn.html(
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
                ).prop("disabled", true);

                $.ajax({
                    url: "{{ route('followup.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        $btn.html(originalText).prop("disabled", false);
                        if (response.status) {
                            Swal.fire({
                                title: "Success!",
                                text: "Follow up scheduled successfully!",
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43",
                            }).then(() => {
                                window.location.href = "{{ route('followup.list') }}";
                            });
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: response.message || "Failed to schedule follow up.",
                                icon: "error",
                                confirmButtonText: "OK"
                            });
                        }
                    },
                    error: function(xhr) {
                        $btn.html(originalText).prop("disabled", false);
                        if (xhr.status === 422) {
                            clearValidationErrors();
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                setFieldError(key, value[0]);
                            });
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: "Something went wrong. Please try again.",
                                icon: "error",
                                confirmButtonText: "OK"
                            });
                        }
                    },
                });
            });

            // Remove error message when user starts typing
            $("input, select, textarea").on("input change", function() {
                let fieldName = $(this).attr("name");
                clearFieldError(fieldName);
            });
        });
    </script>
@endpush
