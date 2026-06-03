@extends('layout.app')

@section('title', 'Departments')

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
                <h4>Manage Departments</h4>
            </div>
            <div class="page-btn">
                <a href="{{ route('department.create') }}" class="btn btn-added">
                    <i class="fa fa-plus me-1"></i> Add Department
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-search-wrap mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                        <input type="text" class="form-control" id="departmentSearch" placeholder="Search...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table" id="departmentTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Department</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3" id="departmentPaginationWrapper">
                    <div class="d-flex align-items-center gap-2">
                        <label for="departmentPerPage" class="mb-0">Rows:</label>
                        <select id="departmentPerPage" class="form-select form-select-sm" style="width: auto;">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary table-pagination-btn" id="departmentPrevPage">Previous</button>
                        <span id="departmentPageInfo">Page 1 of 1</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary table-pagination-btn" id="departmentNextPage">Next</button>
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

            $('#departmentPerPage').on('change', function() {
                state.perPage = Number($(this).val()) || 10;
                state.page = 1;
                loadDepartments();
            });

            $('#departmentPrevPage').on('click', function() {
                if (state.page <= 1) {
                    return;
                }
                state.page -= 1;
                loadDepartments();
            });

            $('#departmentNextPage').on('click', function() {
                if (state.page >= state.lastPage) {
                    return;
                }
                state.page += 1;
                loadDepartments();
            });

            $('#departmentSearch').on('input', function() {
                const value = $(this).val().trim();
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(() => {
                    if (state.search === value) {
                        return;
                    }
                    state.search = value;
                    state.page = 1;
                    loadDepartments();
                }, 350);
            });

            function loadDepartments() {
                $.ajax({
                    url: '/api/department',
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
                    const departments = response.departments || [];
                    const pagination = response.pagination || {};

                    state.page = Number(pagination.current_page || state.page || 1);
                    state.lastPage = Number(pagination.last_page || 1);
                    state.perPage = Number(pagination.per_page || state.perPage || 10);
                    state.total = Number(pagination.total ?? departments.length);

                    if (departments.length === 0 && state.page > 1 && state.page > state.lastPage) {
                        state.page = state.lastPage;
                        loadDepartments();
                        return;
                    }

                    const startIndex = state.total === 0 ? 0 : ((state.page - 1) * state.perPage) + 1;
                    const rows = departments.map((department, index) => `
                        <tr>
                            <td>${startIndex + index}</td>
                            <td>${department.department_name ?? '-'}</td>
                            <td class="text-end">
                                <a href="{{ url('/department') }}/${department.id}" class="btn btn-sm btn-warning me-1">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger deleteDepartment" data-id="${department.id}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('');

                    $('#departmentTable tbody').html(rows || '<tr><td colspan="3" class="text-center">No records found</td></tr>');
                    updatePaginationUI();
                }).fail(handleError);
            }

            $(document).on('click', '.deleteDepartment', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Department?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        url: `/api/department/${id}`,
                        method: 'DELETE',
                        headers: {
                            Authorization: `Bearer ${token}`
                        }
                    }).done((response) => {
                        Swal.fire('Deleted', response.message, 'success');
                        loadDepartments();
                    }).fail(handleError);
                });
            });

            function updatePaginationUI() {
                $('#departmentPageInfo').text(`Page ${state.page} of ${state.lastPage} (${state.total} total)`);
                $('#departmentPrevPage').prop('disabled', state.page <= 1);
                $('#departmentNextPage').prop('disabled', state.page >= state.lastPage);
            }

            function handleError(xhr) {
                const message = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Something went wrong. Please try again.';
                Swal.fire('Error', message, 'error');
            }

            loadDepartments();
        });
    </script>
@endpush
