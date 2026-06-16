@extends('layout.app')

@section('title', 'Add Plan')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Add Plan</h4>
            <p class="text-muted mb-0">Create a plan and add as many features as needed.</p>
        </div>
        <div class="back-button">
            <a href="{{ route('plans.planlist') }}" class="btn back-button">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <form id="planForm">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Plan Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control">
                        <span class="error_name text-danger"></span>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Price</label>
                        <input type="number" name="price" id="price" class="form-control" step="0.01" min="0">
                        <span class="error_price text-danger"></span>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Duration <span class="text-danger">*</span></label>
                        <select name="duration" id="duration" class="form-select">
                            <option value="month">Month</option>
                            <option value="year">Year</option>
                        </select>
                        <span class="error_duration text-danger"></span>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="is_active" id="is_active" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <span class="error_is_active text-danger"></span>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">User Limit</label>
                        <input type="number" name="user_limit" id="user_limit" class="form-control" min="0">
                        <span class="error_user_limit text-danger"></span>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Branch Limit</label>
                        <select id="branch_limit_select" class="form-select">
                            <option value="">-- Select --</option>
                            <option value="1">Starter</option>
                            <option value="3">Professional</option>
                            <option value="unlimited">Unlimited</option>
                        </select>
                        <input type="hidden" name="branch_limit" id="branch_limit">
                        <span class="error_branch_limit text-danger"></span>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Storage Limit</label>
                        <input type="number" name="storage_limit" id="storage_limit" class="form-control" min="0">
                        <span class="error_storage_limit text-danger"></span>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Subtitle</label>
                        <textarea name="subtitle" id="subtitle" class="form-control" rows="3"></textarea>
                        <span class="error_subtitle text-danger"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Features</h5>
                <button type="button" class="btn btn-sm btn-primary" id="addFeatureBtn">
                    <i class="fa-solid fa-plus"></i> Add Feature
                </button>
            </div>
            <div class="card-body">
                <div id="featuresWrapper">
                    <div class="input-group mb-2 feature-row">
                        <input type="text" name="features[]" class="form-control" placeholder="Enter feature">
                        <button type="button" class="btn btn-outline-danger remove-feature">Remove</button>
                    </div>
                </div>
                <span class="error_features text-danger"></span>
            </div>
        </div>

        <div class="col-lg-12">
            <a href="javascript:void(0);" class="btn btn-submit me-2 submit">Save Plan</a>
            <a href="{{ route('plans.planlist') }}" class="btn btn-cancel">Cancel</a>
            <br>
            <span class="success_submit text-danger"></span>
        </div>
    </form>
</div>
@endsection

@push('js')
<script>
    // ─── Add / Remove feature rows ───────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        const wrapper  = document.getElementById('featuresWrapper');
        const addBtn   = document.getElementById('addFeatureBtn');

        addBtn.addEventListener('click', function () {
            const row = document.createElement('div');
            row.className = 'input-group mb-2 feature-row';
            row.innerHTML = '<input type="text" name="features[]" class="form-control" placeholder="Enter feature">'
                          + '<button type="button" class="btn btn-outline-danger remove-feature">Remove</button>';
            wrapper.appendChild(row);
        });

        wrapper.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-feature')) {
                // keep at least one row
                if (wrapper.querySelectorAll('.feature-row').length > 1) {
                    e.target.closest('.feature-row').remove();
                } else {
                    e.target.closest('.feature-row').querySelector('input').value = '';
                }
            }
        });
    });

    // ─── Submit via Ajax ─────────────────────────────────────────────────────
    $(document).ready(function () {

        $(document).on('click', '.submit', function (e) {
            e.preventDefault();

            var $btn         = $(this);
            var originalText = $btn.html();
            var authToken    = localStorage.getItem('authToken');

            // disable & show spinner
            $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...')
                .prop('disabled', true);

            // collect form data
            var formData = new FormData($('#planForm')[0]);

            // attach sub_branch_id from localStorage
            var subBranchId = localStorage.getItem('selectedSubAdminId');
            if (subBranchId) {
                formData.append('sub_branch_id', subBranchId);
            }

            // clear all previous errors
            $('.error_name, .error_price, .error_duration, .error_is_active, ' +
              '.error_user_limit, .error_branch_limit, .error_storage_limit, ' +
              '.error_subtitle, .error_features').text('');

            $.ajax({
                url: '/api/createPlan',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'Authorization': 'Bearer ' + authToken,
                },
                success: function (response) {
                    $btn.html(originalText).prop('disabled', false);

                    if (response.status) {
                        Swal.fire({
                            title: 'Success',
                            text: 'Plan created successfully',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#ff9f43'
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                window.location.href = '{{ route('plans.planlist') }}';
                            }
                        });
                    }
                },
                error: function (xhr) {
                    $btn.html(originalText).prop('disabled', false);

                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;

                        // clear first
                        $('.error_name, .error_price, .error_duration, .error_is_active, ' +
                          '.error_user_limit, .error_branch_limit, .error_storage_limit, ' +
                          '.error_subtitle, .error_features').text('');

                        $.each(errors, function (key, value) {
                            // features.0, features.1 … → map to .error_features
                            var errorKey = key.split('.')[0];
                            var errorMsg = Array.isArray(value) ? value.join(' ') : value;
                            $('.error_' + errorKey).text(errorMsg);
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: xhr.responseJSON && xhr.responseJSON.message
                                    ? xhr.responseJSON.message
                                    : 'Something went wrong. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#ff9f43'
                        });
                    }
                }
            });
        });

        // ─── Branch Limit dropdown → hidden input ────────────────────────────
        $('#branch_limit_select').on('change', function () {
            var val = $(this).val();
            // 'unlimited' option → send empty string so backend stores null
            $('#branch_limit').val(val === 'unlimited' ? '' : val);
        });

        // prevent negative price
        $(document).on('input', '#price', function () {
            var value = parseFloat($(this).val());
            if (!isNaN(value) && value < 0) {
                $(this).val('');
                $('.error_price').text('Price cannot be negative.');
            } else {
                $('.error_price').text('');
            }
        });
    });
</script>
@endpush
