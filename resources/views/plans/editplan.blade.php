@extends('layout.app')

@section('title', 'Edit Plan')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Edit Plan</h4>
            <p class="text-muted mb-0">Update plan information and feature list.</p>
        </div>
        <div class="back-button">
            <a href="{{ route('plans.planlist') }}" class="btn back-button">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <form id="planForm">
        <input type="hidden" name="plan_id" id="plan_id">

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
                    {{-- rows populated by JS after plan data is fetched --}}
                </div>
                <span class="error_features text-danger"></span>
            </div>
        </div>

        <div class="col-lg-12">
            <a href="javascript:void(0);" class="btn btn-submit me-2 submit">Update Plan</a>
            <a href="{{ route('plans.planlist') }}" class="btn btn-cancel">Cancel</a>
            <br>
            <span class="success_submit text-danger"></span>
        </div>
    </form>
</div>
@endsection

@push('js')
<script>
// ─── Helpers ─────────────────────────────────────────────────────────────────
function makeFeatureRow(value) {
    var row = document.createElement('div');
    row.className = 'input-group mb-2 feature-row';
    row.innerHTML = '<input type="text" name="features[]" class="form-control" placeholder="Enter feature" value="' +
                    $('<div>').text(value || '').html() + '">' +
                    '<button type="button" class="btn btn-outline-danger remove-feature">Remove</button>';
    return row;
}

// ─── Add / Remove feature rows ───────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    var wrapper = document.getElementById('featuresWrapper');
    var addBtn  = document.getElementById('addFeatureBtn');

    addBtn.addEventListener('click', function () {
        wrapper.appendChild(makeFeatureRow(''));
    });

    wrapper.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-feature')) {
            if (wrapper.querySelectorAll('.feature-row').length > 1) {
                e.target.closest('.feature-row').remove();
            } else {
                e.target.closest('.feature-row').querySelector('input').value = '';
            }
        }
    });
});

// ─── Main logic ──────────────────────────────────────────────────────────────
$(document).ready(function () {

    var authToken = localStorage.getItem('authToken');

    // Extract plan ID from the URL  e.g. /plans/edit/5 → 5
    var planId = window.location.pathname.split('/').pop();

    // ── Load plan data and pre-fill form ─────────────────────────────────────
    $.ajax({
        url: '/api/getPlanById/' + planId,
        type: 'GET',
        headers: { 'Authorization': 'Bearer ' + authToken },
        success: function (response) {
            if (!response.status) {
                Swal.fire('Error', 'Plan not found.', 'error').then(function () {
                    window.location.href = '{{ route('plans.planlist') }}';
                });
                return;
            }

            var plan = response.plan;

            $('#plan_id').val(plan.id);
            $('#name').val(plan.name);
            $('#price').val(plan.price);
            $('#duration').val(plan.duration);
            $('#is_active').val(plan.is_active ? '1' : '0');
            $('#user_limit').val(plan.user_limit);
            // Populate branch_limit dropdown
            var bl = plan.branch_limit;
            if (bl === null || bl === '' || bl === undefined) {
                $('#branch_limit_select').val('unlimited');
                $('#branch_limit').val('');
            } else {
                $('#branch_limit_select').val(String(bl));
                $('#branch_limit').val(bl);
            }
            $('#storage_limit').val(plan.storage_limit);
            $('#subtitle').val(plan.subtitle);

            // Populate features
            var wrapper  = document.getElementById('featuresWrapper');
            wrapper.innerHTML = '';

            var features = (plan.features && plan.features.length)
                ? plan.features
                : [{ feature: '' }];

            features.forEach(function (f) {
                wrapper.appendChild(makeFeatureRow(f.feature || ''));
            });
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            Swal.fire('Error', 'Failed to load plan data.', 'error');
        }
    });

    // ── Submit ────────────────────────────────────────────────────────────────
    $(document).on('click', '.submit', function (e) {
        e.preventDefault();

        var $btn         = $(this);
        var originalText = $btn.html();

        $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...')
            .prop('disabled', true);

        // Clear previous errors
        $('.error_name, .error_price, .error_duration, .error_is_active, ' +
          '.error_user_limit, .error_branch_limit, .error_storage_limit, ' +
          '.error_subtitle, .error_features').text('');

        var formData = new FormData($('#planForm')[0]);

        // attach sub_branch_id from localStorage
        var subBranchId = localStorage.getItem('selectedSubAdminId');
        if (subBranchId) {
            formData.append('sub_branch_id', subBranchId);
        }

        $.ajax({
            url: '/api/updatePlan',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'Authorization': 'Bearer ' + authToken },
            success: function (response) {
                $btn.html(originalText).prop('disabled', false);

                if (response.status) {
                    Swal.fire({
                        title: 'Success',
                        text: 'Plan updated successfully',
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

                    $.each(errors, function (key, value) {
                        // features.0, features.1 … → .error_features
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

    // ── Branch Limit dropdown → hidden input ─────────────────────────────────
    $('#branch_limit_select').on('change', function () {
        var val = $(this).val();
        $('#branch_limit').val(val === 'unlimited' ? '' : val);
    });

    // ── Prevent negative price ────────────────────────────────────────────────
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
