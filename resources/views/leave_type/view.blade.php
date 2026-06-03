@extends('layout.app')

@section('title', 'Leave Types')

@section('content')
    <style>
        .table-search-wrap {
            max-width: 280px;
        }

        .table-pagination-btn:not(:disabled) {
            background-color: #FF9F43;
            border-color: #FF9F43;
            color: #fff;
        }

        .table-pagination-btn:not(:disabled):hover,
        .table-pagination-btn:not(:disabled):focus {
            background-color: #f58f2f;
            border-color: #f58f2f;
            color: #fff;
            box-shadow: none;
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Manage Leave Types</h4>
            </div>
            <div class="page-btn">
                <a href="{{ route('leave-type.create') }}" class="btn btn-added">
                    <i class="fa fa-plus me-1"></i> Add Leave Type
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-search-wrap mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                        <input type="text" class="form-control" id="leaveTypeSearch" placeholder="Search...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table" id="leaveTypeTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Leave Type</th>
                                <th>Number of Leaves</th>
                                <th>Allow Half Day</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3" id="leaveTypePaginationWrapper">
                    <div class="d-flex align-items-center gap-2">
                        <label for="leaveTypePerPage" class="mb-0">Rows:</label>
                        <select id="leaveTypePerPage" class="form-select form-select-sm" style="width: auto;">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary table-pagination-btn" id="leaveTypePrevPage">Previous</button>
                        <span id="leaveTypePageInfo">Page 1 of 1</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary table-pagination-btn" id="leaveTypeNextPage">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function() {
            const token = (typeof window.getAuthToken === 'function'
                ? window.getAuthToken()
                : (localStorage.getItem('authToken') || localStorage.getItem('token') || ''));
            const editOpenUrl = "{{ route('leave-type.edit.open') }}";
            const csrfToken = "{{ csrf_token() }}";
            const state = {
                page: 1,
                perPage: 10,
                lastPage: 1,
                total: 0,
                search: '',
            };
            let searchDebounceTimer = null;

            if (!token) {
                Swal.fire('Unauthorized', 'Please login again to continue.', 'warning');
                return;
            }

            $('#leaveTypePerPage').on('change', function() {
                state.perPage = Number($(this).val()) || 10;
                state.page = 1;
                loadLeaveTypes();
            });

            $('#leaveTypePrevPage').on('click', function() {
                if (state.page <= 1) {
                    return;
                }
                state.page -= 1;
                loadLeaveTypes();
            });

            $('#leaveTypeNextPage').on('click', function() {
                if (state.page >= state.lastPage) {
                    return;
                }
                state.page += 1;
                loadLeaveTypes();
            });

            $('#leaveTypeSearch').on('input', function() {
                const value = $(this).val().trim();
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(() => {
                    if (state.search === value) {
                        return;
                    }
                    state.search = value;
                    state.page = 1;
                    loadLeaveTypes();
                }, 350);
            });

            function loadLeaveTypes() {
                $.ajax({
                    url: '/api/leavetype',
                    method: 'GET',
                    data: {
                        page: state.page,
                        per_page: state.perPage,
                        search: state.search,
                    },
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                }).done((response) => {
                    const records = response.data || [];
                    const pagination = response.pagination || {};

                    state.page = Number(pagination.current_page || state.page || 1);
                    state.lastPage = Number(pagination.last_page || 1);
                    state.perPage = Number(pagination.per_page || state.perPage || 10);
                    state.total = Number(pagination.total ?? records.length);

                    if (records.length === 0 && state.page > 1 && state.page > state.lastPage) {
                        state.page = state.lastPage;
                        loadLeaveTypes();
                        return;
                    }

                    const startIndex = state.total === 0 ? 0 : ((state.page - 1) * state.perPage) + 1;
                    const rows = records.map((item, index) => `
                        <tr>
                            <td>${startIndex + index}</td>
                            <td>${item.leave_type ?? '-'}</td>
                            <td>${item.number_of_leaves ?? 0}</td>
                            <td>${Number(item.allow_half_day) === 1 ? 'Yes' : 'No'}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-warning me-1 openLeaveTypeEdit" data-id="${item.id}">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger deleteLeaveType" data-id="${item.id}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('');

                    $('#leaveTypeTable tbody').html(rows || '<tr><td colspan="5" class="text-center">No records found</td></tr>');
                    updatePaginationUI();
                }).fail(handleError);
            }

            $(document).on('click', '.deleteLeaveType', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Leave Type?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        url: `/api/leavetype/${id}`,
                        method: 'DELETE',
                        headers: {
                            Authorization: `Bearer ${token}`
                        }
                    }).done((response) => {
                        Swal.fire('Deleted', response.message, 'success');
                        loadLeaveTypes();
                    }).fail(handleError);
                });
            });

            $(document).on('click', '.openLeaveTypeEdit', function() {
                const id = $(this).data('id');
                const form = $('<form>', {
                    method: 'POST',
                    action: editOpenUrl,
                    style: 'display:none;'
                });

                form.append($('<input>', {
                    type: 'hidden',
                    name: '_token',
                    value: csrfToken
                }));
                form.append($('<input>', {
                    type: 'hidden',
                    name: 'id',
                    value: id
                }));

                $('body').append(form);
                form.trigger('submit');
            });

            function updatePaginationUI() {
                $('#leaveTypePageInfo').text(`Page ${state.page} of ${state.lastPage} (${state.total} total)`);
                $('#leaveTypePrevPage').prop('disabled', state.page <= 1);
                $('#leaveTypeNextPage').prop('disabled', state.page >= state.lastPage);
            }

            function handleError(xhr) {
                const message = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Something went wrong. Please try again.';
                Swal.fire('Error', message, 'error');
            }

            loadLeaveTypes();
        });
    </script>
@endpush
