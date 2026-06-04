@extends('layout.app')
@section('title', 'Add Leave')

@section('content')
    <style>
        #LeaveForm .field-error {
            display: block;
            margin-top: 4px;
            font-size: 12px;
        }

        #LeaveForm .form-control.is-invalid,
        #LeaveForm .form-select.is-invalid {
            background-image: none !important;
            padding-right: 0.75rem !important;
        }


        .modal-field-error {
            display: block;
            font-size: 12px;
            margin-top: 4px;
        }

        @media (max-width: 576px) {
            .leave-sm-emp {
                min-height: 24px;
                display: flex;
                align-items: center;
            }

            .form-label {
                font-size: 14px;
            }
        }
    </style>
    <div class="modal fade" id="addLeaveModal" tabindex="-1" aria-labelledby="addLeaveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addLeaveModalLabel">Add Leave Type</h5>
                    {{-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> --}}
                </div>

                <div class="modal-body">
                    <form id="AddLeaveForm">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div id="addLeaveGeneralError" class="alert alert-danger py-2 px-3 mb-3" style="display:none;"></div>
                        <div class="mb-3">
                            <label for="address_details" class="form-label">Leave Type</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="leave_type" id="leave_type"
                                    placeholder="Enter Leave type" />
                            </div>
                            <small class="text-danger error modal-field-error" id="leaveError"></small>
                        </div>
                        <div class="mb-3">
                            <label for="address_details" class="form-label">Number Of Leaves</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="number_of_leaves" id="number_of_leaves"
                                    placeholder="Enter Total " />
                            </div>
                            <small class="text-danger error modal-field-error" id="numberOfLeavesError"></small>
                        </div>
                        <button class="btn btn-submit float-end" id="addsubmitBtn">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Add Leave</h4>
            </div>
            <div class="page-btn">
                @if (app('hasPermission')(1, 'add'))
                    <a href="{{ route('leave.view') }}" class="btn btn-sm btn-added">
                        <i class="mdi mdi-arrow-left me-1"></i> Back
                    </a>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">

                        <form class="form-sample" method="POST" id="LeaveForm">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="row gy-3">
                                <!-- Staff Name -->
                                <div class="col-md-4">
                                    <label class="form-label leave-sm-emp">Staff Name</label>
                                    <div class="input-group">
                                        <?php if ($role == 'admin' || $role == 'hr') : ?>
                                        <select class="form-select" name="user_id">
                                            <option value="" disabled selected>Select Staff</option>
                                            <?php foreach ($users as $user) : ?>
                                            <option value="<?= e($user['id']) ?>"><?= e($user['username']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php else : ?>
                                        <input type="text" class="form-control" value="<?= e($users[0]['username']) ?>"
                                            readonly>
                                        <input type="hidden" name="user_id" value="<?= e($users[0]['id']) ?>">
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Leave Type -->
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-label leave-sm-emp mb-0">Leave Type</label>
                                    </div>

                                    <div class="input-group mt-2">
                                        <select class="form-select" name="leave_id" id="leave_id">
                                            <option value="" disabled selected>Select Leave Type</option>
                                            <?php foreach ($leaveTypes as $leaveType) : ?>
                                            <option value="<?= $leaveType['id'] ?>" data-allow-half-day="<?= $leaveType['allow_half_day'] ?>">
                                                <?= e($leaveType['leave_type']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Duration (Full Day / Half Day) -->
                                <div class="col-md-4" id="half_day_container">
                                    <label class="form-label leave-sm-emp">Duration</label>
                                    <div class="input-group">
                                        <select class="form-select" name="half_day" id="half_day">
                                            <option value="0" selected>Full Day</option>
                                            <option value="1">Half Day</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Half Day Type (First Half / Second Half) -->
                                <div class="col-md-4" id="half_day_type_container" style="display: none;">
                                    <label class="form-label leave-sm-emp">Half Day Type</label>
                                    <div class="input-group">
                                        <select class="form-select" name="half_day_type" id="half_day_type">
                                            <option value="" disabled selected>Select Half Day Type</option>
                                            <option value="First Half">First Half</option>
                                            <option value="Second Half">Second Half</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- No. of Days -->
                                <div class="col-md-4">
                                    <label class="form-label leave-sm-emp">No. of Days</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="no_of_day"
                                            placeholder="Enter Number of Days" />
                                    </div>
                                </div>

                                <!-- Start Date -->
                                <div class="col-md-4">
                                    <label class="form-label leave-sm-emp">Start Date</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" name="start_date" id="start_date" />
                                    </div>
                                </div>

                                <!-- End Date -->
                                <div class="col-md-4">
                                    <label class="form-label leave-sm-emp">End Date</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" name="end_date" />
                                    </div>
                                </div>

                                <?php if ($role == 'admin' || $role == 'hr') : ?>
                                <div class="col-md-4">
                                    <label class="form-label leave-sm-emp">Status</label>
                                    <div class="input-group">
                                        <select class="form-select" name="status" id="status">
                                            <option value="" disabled selected>Select Status</option>
                                            <option value="Pending">Pending</option>
                                            <option value="approved">approved</option>
                                            <option value="rejected">rejected</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label leave-sm-emp">Reason</label>
                                    <textarea class="form-control" name="reason" placeholder="Enter Reason" rows="3"></textarea>
                                </div>
                                <?php else : ?>
                                <div class="col-md-12">
                                    <label class="form-label leave-sm-emp">Reason</label>
                                    <textarea class="form-control" name="reason" placeholder="Enter Reason" rows="3"></textarea>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Buttons -->
                            <div class="mt-4 text-end">
                                <a href="{{ url('/leaveview') }}" class="btn btn-cancel interviewsmbtn me-2">Cancel</a>
                                <button type="submit" class="btn btn-submit interviewsmbtn">Submit</button>
                            </div>

                            <div id="responseMessage" class="mt-2"></div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById("start_date").setAttribute("min", today);

            // Change listener on Leave Type select
            $('#leave_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const allowHalfDay = selectedOption.data('allow-half-day');
                
                if (parseInt(allowHalfDay) === 1) {
                    $('#half_day option[value="1"]').prop('disabled', false);
                } else {
                    $('#half_day option[value="1"]').prop('disabled', true);
                    $('#half_day').val('0').trigger('change');
                }
            });

            // Change listener on Leave Day Type select
            $('#half_day').on('change', function() {
                const isHalfDay = $(this).val() === '1';
                const noOfDaysInput = $('input[name="no_of_day"]');
                const endDateInput = $('input[name="end_date"]');
                const startDateInput = $('#start_date');

                if (isHalfDay) {
                    $('#half_day_type_container').show();
                    noOfDaysInput.val('1').prop('readonly', true);
                    if (startDateInput.val()) {
                        endDateInput.val(startDateInput.val()).prop('readonly', true);
                    } else {
                        endDateInput.prop('readonly', true);
                    }
                } else {
                    $('#half_day_type_container').hide();
                    $('#half_day_type').val('');
                    noOfDaysInput.prop('readonly', false);
                    endDateInput.prop('readonly', false);
                }
            });

            // Copy start date to end date if Half Day is selected and start date changes
            $('#start_date').on('change', function() {
                if ($('#half_day').val() === '1') {
                    $('input[name="end_date"]').val($(this).val());
                }
            });
        });
        $(document).ready(function() {
            const token = (typeof window.getAuthToken === 'function') ? window.getAuthToken() : (localStorage.getItem('authToken') || localStorage.getItem('token') || ''); // JWT token
            const form = document.querySelector('#LeaveForm');
            const fieldAliases = {
                no_of_days: 'no_of_day',
                no_of_day: 'no_of_day',
                leave_type_id: 'leave_id',
                leave_type: 'leave_id',
                staff_id: 'user_id'
            };

            function normalizeFieldName(fieldName) {
                return fieldAliases[fieldName] || fieldName;
            }

            function clearMainFormValidation() {
                form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
                form.querySelectorAll('.field-error').forEach((el) => el.remove());
            }

            function showMainFieldError(fieldName, message) {
                const normalizedField = normalizeFieldName(fieldName);
                const fieldElement = form.querySelector(`[name="${normalizedField}"]`);
                if (!fieldElement) return;

                fieldElement.classList.add('is-invalid');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback d-block field-error';
                errorDiv.innerText = Array.isArray(message) ? message[0] : message;

                const inputGroup = fieldElement.closest('.input-group');
                if (inputGroup) {
                    inputGroup.insertAdjacentElement('afterend', errorDiv);
                } else {
                    fieldElement.insertAdjacentElement('afterend', errorDiv);
                }
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent default submission

                const formData = new FormData(form);
                clearMainFormValidation();

                let hasError = false;

                const requiredFields = [
                    'user_id',
                    'leave_id',
                    'reason',
                    'no_of_day',
                    'start_date',
                    'end_date',
                ];

                if ($('#half_day').val() === '1') {
                    requiredFields.push('half_day_type');
                }

                // If user is admin or HR, validate status too
                const role = "{{ $role }}";
                if (role === 'admin' || role === 'hr') {
                    requiredFields.push('status');
                }

                requiredFields.forEach((field) => {
                    const fieldElement = form.querySelector(`[name="${field}"]`);
                    if (fieldElement) {
                        const value = fieldElement.value.trim();
                        if (!value) {
                            hasError = true;
                            showMainFieldError(field, 'This field is required.');
                        }
                    }
                });

                if (hasError) {
                    return; // Stop submission if validation fails
                }

                const data = {};
                formData.forEach((value, key) => {
                    data[key] = value;
                });

                // Append CSRF token
                data['_token'] = '{{ csrf_token() }}';

                // Send POST request
                fetch('/api/leave', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(data),
                    })
                    .then(async (response) => {
                        const responseData = await response.json();
                        if (!response.ok) {
                            if (response.status === 400 && responseData.errors) {
                                // Server-side validation error handling
                                clearMainFormValidation();
                                Object.entries(responseData.errors).forEach(([field, errorMsg]) => {
                                    showMainFieldError(field, errorMsg);
                                });
                                throw new Error('Validation failed');
                            }
                            throw new Error(responseData.message ||
                                `HTTP error! Status: ${response.status}`);
                        }
                        return responseData;
                    })
                    .then((responseData) => {
                        if (responseData.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: responseData.message,
                                timer: 2000,
                                showConfirmButton: false,
                            }).then(() => {
                                window.location.href = "/leave-request";
                            });

                            form.reset();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: responseData.message,
                            });
                        }
                    })
                    .catch((error) => {
                        if (error.message === 'Validation failed') {
                            return;
                        }
                        console.error('Error submitting leave record:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred. Please try again later.',
                        });
                    });
            });
        });


        $(document).ready(function() {
            $("#AddLeaveForm").submit(function(e) {
                e.preventDefault(); // Prevent default form submission
                $(".error").html(""); // Clear previous validation errors
                $("#addLeaveGeneralError").hide().html("");
                $('#leave_type, #number_of_leaves').removeClass('is-invalid');

                // var formData = $(this).serialize(); // Serialize form data
                const form = $(this);
                const formData = form.serializeArray();

                // Add CSRF token manually
                formData.push({
                    name: '_token',
                    value: '{{ csrf_token() }}'
                });
                $('#loader').show();

                $.ajax({
                    url: "{{ url('/api/leavetype/add') }}", // API route to handle insertion
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    success: function(response) {
                        $('#loader').hide();

                        if (response.success) {
                            // Append new leave type to the dropdown inside the modal
                            $("#leave_id").append(
                                `<option value="${response.leave_type.leave_id}" selected>
                            ${response.leave_type.leave_type}
                        </option>`
                            );

                            // Append new leave type to the main dropdown (outside modal)
                            $("select[name='leave_id']").append(
                                `<option value="${response.leave_type.id}" selected>
                            ${response.leave_type.leave_type}
                        </option>`
                            );

                            // Show success message
                            Swal.fire({
                                icon: "success",
                                title: "Success",
                                text: "Leave Type added successfully!",
                                timer: 2000,
                                showConfirmButton: false,

                            });

                            // Reset form and close modal
                            $("#AddLeaveForm")[0].reset();
                            $("#addLeaveModal").modal("hide");
                        } else {
                            const errors = response.errors || {};
                            const leaveTypeError = errors.leave_type?.[0] || errors.leave_type || '';
                            const numberOfLeavesError = errors.number_of_leaves?.[0] || errors.number_of_leaves || '';

                            if (leaveTypeError) {
                                $("#leaveError").html(leaveTypeError);
                                $("#leave_type").addClass('is-invalid');
                            }
                            if (numberOfLeavesError) {
                                $("#numberOfLeavesError").html(numberOfLeavesError);
                                $("#number_of_leaves").addClass('is-invalid');
                            }

                            if (!leaveTypeError && !numberOfLeavesError) {
                                $("#addLeaveGeneralError").html(response.message || "Unable to add leave type.")
                                    .show();
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#loader').hide();

                        const response = xhr.responseJSON || {};
                        const errors = response.errors || {};
                        const leaveTypeError = errors.leave_type?.[0] || errors.leave_type || '';
                        const numberOfLeavesError = errors.number_of_leaves?.[0] || errors.number_of_leaves || '';

                        if (leaveTypeError) {
                            $("#leaveError").html(leaveTypeError);
                            $("#leave_type").addClass('is-invalid');
                        }
                        if (numberOfLeavesError) {
                            $("#numberOfLeavesError").html(numberOfLeavesError);
                            $("#number_of_leaves").addClass('is-invalid');
                        }

                        if (!leaveTypeError && !numberOfLeavesError) {
                            $("#addLeaveGeneralError")
                                .html(response.message || "Something went wrong while adding leave type.")
                                .show();
                        }

                        console.error("AJAX Error: " + error);
                    }
                });
            });
        });
    </script>
@endpush
