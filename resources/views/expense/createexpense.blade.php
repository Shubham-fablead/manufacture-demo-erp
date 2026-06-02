@extends('layout.app')

@section('title', 'Expense Add')

@section('content')
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

.add-expense-type-btn {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    padding: 0;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

#expenseTypeModal .modal-header .btn-close {
    background-color: transparent !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    padding: .5rem .5rem;
    opacity: .6;
}

#expenseTypeModal .modal-header .btn-close:hover {
    opacity: 1;
}
</style>
<div class="content">
    {{-- <div class="page-header">
        <div class="page-title">
            <h4>Expense Add</h4>
            <!-- <h6>Add/Update Expenses</h6> -->
        </div>
    </div> --}}
    <div class="page-header ">
            <div class="page-title">
                <h4>Add Expense</h4>
            </div>
             <div class="back-button">
                <a href="{{ route('expense.list') }}" class="btn back-button"> <i class="fa-solid fa-arrow-left"></i> Back</a></br>
                            <span class="success_submit text-danger"></span>
            </div>
        </div>
    <form id="expenseForm">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Sr No</label>
                            <div class="input-groupicon">
                                <input type="text" value="{{ $nextSrNo ?? 1 }}" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Expense Name <span class="text-danger">*</span></label>
                            <div class="input-groupicon">
                                <input type="text" name="expense_name" placeholder="Expense Name">
                                <span class="text-danger error" id="expense_name_error"></span>

                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Expense Date <span class="text-danger">*</span></label>
                            <div class="input-groupicon">
                                <input type="text" name="expense_date" placeholder="Choose Date" class="datetimepicker">
                                <span class="text-danger error" id="expense_date_error"></span>
                                <div class="addonset">
                                    <img src="{{ env('ImagePath').'admin/assets/img/icons/calendars.svg' }}" alt="img">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Amount <span class="text-danger">*</span></label>
                            <div class="input-groupicon">
                                <input type="number" class="form-control" name="amount" placeholder="Enter Amount">
                                <span class="text-danger error" id="amount_error"></span>
                                <!-- <div class="addonset">
                                    <img src="admin/assets/img/icons/dollar.svg" alt="img">
                                </div> -->
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label class="d-flex align-items-center justify-content-between">
                                <span>Expense Type <span class="text-danger">*</span></span>
                                <button type="button" class="btn btn-sm btn-primary add-expense-type-btn" id="openExpenseTypeModal" title="Add Expense Type">+</button>
                            </label>
                            <select class="select" name="expense_type_id" id="expense_type_id">
                                <option value="">Select Expense Type</option>
                                @foreach($expenseTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->type }}</option>
                                @endforeach
                            </select>
                            <span class="error text-danger" id="expense_type_id_error"></span>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Payment Mode <span class="text-danger">*</span></label>
                            <select class="select" name="payment_mode" id="payment_mode">
                                <option value="">Select Payment Mode</option>
                                <option value="Cash">Cash</option>
                                <option value="Bank">Bank</option>
                            </select>
                            <span class="error text-danger" id="payment_mode_error"></span>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Expense for</label>
                            <textarea name="description" id="description" class="form-control" placeholder="Purpose of Expense" rows="3"></textarea>
                            <span class="text-danger error" id="description_error"></span>
                        </div>
                    </div>


                    <div class="col-lg-12">
                        <button type="submit" class="btn btn-submit me-2">Submit</button>
                        <a href="{{ route('expense.list') }}" class="btn btn-cancel">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="modal fade" id="expenseTypeModal" tabindex="-1" aria-labelledby="expenseTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="expenseTypeModalLabel">Add Expense Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="expenseTypeModalForm">
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label for="modal_expense_type">Expense Type <span class="text-danger">*</span></label>
                            <input type="text" name="type" id="modal_expense_type" class="form-control" placeholder="Expense Type">
                            <span class="text-danger error" id="modal_type_error"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-submit" id="saveExpenseTypeBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')

<script>
    $(document).ready(function() {
        const authToken = localStorage.getItem("authToken");
        const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
        const expenseTypeModalElement = document.getElementById('expenseTypeModal');
        const expenseTypeModal = (typeof bootstrap !== 'undefined' && expenseTypeModalElement) ? new bootstrap.Modal(expenseTypeModalElement) : null;

        function loadExpenseTypes(selectedId = null) {
            $.ajax({
                url: '/api/expense-types',
                method: 'GET',
                data: {
                    per_page: 1000,
                    selectedSubAdminId: selectedSubAdminId
                },
                headers: {
                    "Authorization": "Bearer " + authToken,
                },
                success: function(res) {
                    let options = '<option value="">Select Expense Type</option>';
                    const rows = Array.isArray(res.data) ? res.data : [];

                    rows.forEach(function(item) {
                        const selected = String(selectedId) === String(item.id) ? 'selected' : '';
                        options += `<option value="${item.id}" ${selected}>${item.type}</option>`;
                    });

                    $('#expense_type_id').html(options).trigger('change');
                }
            });
        }

        $('#openExpenseTypeModal').on('click', function() {
            $('#expenseTypeModalForm')[0].reset();
            $('#modal_type_error').text('');
            if (expenseTypeModal) {
                expenseTypeModal.show();
            } else {
                Swal.fire({
                    title: "Info",
                    text: "Popup is not available right now. Please refresh the page.",
                    icon: "info",
                    confirmButtonColor: "#ff9f43"
                });
            }
        });

        $('#expenseTypeModalForm').on('submit', function(e) {
            e.preventDefault();
            $('#modal_type_error').text('');

            const $btn = $('#saveExpenseTypeBtn');
            const originalText = $btn.html();
            const type = $('#modal_expense_type').val().trim();

            $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...')
                .prop('disabled', true);

            $.ajax({
                url: '{{ route("expense-type.store") }}',
                method: 'POST',
                data: {
                    type: type,
                    selectedSubAdminId: selectedSubAdminId,
                    _token: '{{ csrf_token() }}'
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    "Authorization": "Bearer " + authToken,
                },
                success: function(response) {
                    $btn.html(originalText).prop('disabled', false);

                    const createdTypeId = response && response.data ? response.data.id : null;
                    loadExpenseTypes(createdTypeId);

                    if (expenseTypeModal) {
                        expenseTypeModal.hide();
                    }

                    Swal.fire({
                        title: "Success",
                        text: response.message || "Expense type added successfully.",
                        icon: "success",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#ff9f43"
                    });
                },
                error: function(xhr) {
                    $btn.html(originalText).prop('disabled', false);
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.type) {
                        $('#modal_type_error').text(xhr.responseJSON.errors.type[0]);
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: "Something went wrong. Please try again.",
                            icon: "error",
                            confirmButtonColor: "#ff9f43"
                        });
                    }
                }
            });
        });

        $('#expenseForm').on('submit', function(e) {
            e.preventDefault();
     var authToken = localStorage.getItem("authToken");

            var $btn = $('#expenseForm button[type="submit"]');
            var originalText = $btn.html();

            $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...')
                .prop('disabled', true);

            $('.error').text('');
 const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

 
            $.ajax({
                url: '{{ route("expenses.store") }}',
                method: 'POST',
                data: $(this).serialize() + "&selectedSubAdminId=" + selectedSubAdminId,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    "Authorization": "Bearer " + authToken,
                },
                success: function(response) {
                    $btn.html(originalText).prop('disabled', false);
                    Swal.fire({
                        title: "Success",
                        text: "Expense Added successfully",
                        icon: "success",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#ff9f43"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('expense.list') }}";
                        }
                    });
                },
                error: function(xhr) {
                    $btn.html(originalText).prop('disabled', false);
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        let errors = xhr.responseJSON.errors;
                        for (let field in errors) {
                            $('#' + field + '_error').text(errors[field][0]);
                        }
                    }
                }
            });
        });

        loadExpenseTypes();
    });
</script>


@endpush
