@extends('layout.app')

@section('title', 'Bill of Materials')

@section('content')
    <style>
        .bom-page .page-header {
            gap: 12px;
        }

        .bom-page .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .bom-page .bom-mobile-view {
            display: none;
        }

        .bom-page .bom-mobile-card {
            border: 1px solid #eef0f3;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            margin-bottom: 12px;
            overflow: hidden;
        }

        .bom-page .bom-mobile-card:last-child {
            margin-bottom: 0;
        }

        .bom-page .bom-mobile-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border-bottom: 1px solid #f1f3f5;
        }

        .bom-page .bom-mobile-title {
            min-width: 0;
        }

        .bom-page .bom-mobile-title .label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .bom-page .bom-mobile-title .value {
            display: block;
            font-size: 15px;
            font-weight: 600;
            color: #1b2850;
            word-break: break-word;
        }

        .bom-page .bom-toggle-details {
            width: 32px;
            height: 32px;
            border: 0;
            border-radius: 50%;
            background: #ff9f43;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            line-height: 1;
            flex: 0 0 auto;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .bom-page .bom-toggle-details.is-open {
            background: #dc3545;
            transform: rotate(0deg);
        }

        .bom-page .bom-mobile-details {
            display: none;
            padding: 10px 14px 14px;
            background: #fff;
        }

        .bom-page .bom-mobile-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #f1f3f5;
        }

        .bom-page .bom-mobile-row:last-child {
            border-bottom: 0;
        }

        .bom-page .bom-mobile-row .label {
            font-size: 14px;
            font-weight: 600;
            color: #344767;
            flex: 0 0 46%;
        }

        .bom-page .bom-mobile-row .value {
            font-size: 14px;
            color: #1b2850;
            text-align: right;
            word-break: break-word;
            flex: 1 1 auto;
        }

        .bom-page .bom-mobile-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #f1f3f5;
        }

        .bom-page .bom-mobile-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .bom-page .bom-mobile-actions .btn {
            min-width: 92px;
        }

        .bom-page .badges {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
        }

        @media screen and (max-width: 767.98px) {
            .bom-page .page-header {
                flex-direction: column;
                align-items: stretch;
                margin-bottom: 14px;
            }

            .bom-page .page-title h4 {
                font-size: 18px;
                margin-bottom: 4px;
            }

            .bom-page .page-title h6 {
                font-size: 12px;
                line-height: 1.4;
            }

            .bom-page .page-btn {
                width: 100%;
            }

            .bom-page .page-btn .btn.btn-added {
                width: 100%;
                justify-content: center;
            }

            .bom-page .card-body {
                padding: 12px;
            }

            .bom-page .bom-desktop-view {
                display: none;
            }

            .bom-page .bom-mobile-view {
                display: block;
            }
        }
    </style>

    <div class="content">
        <div class="bom-page">
            <div class="page-header">
                <div class="page-title">
                    <h4>Bill of Materials</h4>
                    <h6>Link finished goods with raw material recipes</h6>
                </div>
                <div class="page-btn">
                    <a href="{{ route('inventory.bom.add') }}" class="btn btn-added">
                        <i class="fa fa-plus me-2"></i>Create BOM
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="bom-desktop-view">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>BOM Code</th>
                                        <th>Finished Product</th>
                                        <th>Base Qty</th>
                                        <th>Unit</th>
                                        <th>Materials</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="bom-table-body">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Loading BOMs...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bom-mobile-view" id="bom-mobile-list">
                        <div class="bom-mobile-card">
                            <div class="bom-mobile-summary">
                                <div class="bom-mobile-title">
                                    <span class="label">BOM Code</span>
                                    <span class="value">Loading BOMs...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function() {
            const authToken = localStorage.getItem('authToken');
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');

            function actionButtons(bomId, mobile = false) {
                if (mobile) {
                    return `
                        <a href="/inventory/boms/${bomId}/view" class="btn btn-primary btn-sm py-1 px-2">View</a>
                        <a href="/inventory/boms/${bomId}/edit" class="btn btn-success btn-sm py-1 px-2">Edit</a>
                        <a href="javascript:void(0);" class="btn btn-danger btn-sm py-1 px-2 delete-bom" data-id="${bomId}">Delete</a>
                    `;
                }

                return `
                    <a href="/inventory/boms/${bomId}/view" class="me-2" title="View">
                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/eye.svg' }}" alt="view">
                    </a>
                    <a href="/inventory/boms/${bomId}/edit" class="me-2" title="Edit">
                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/edit.svg' }}" alt="edit">
                    </a>
                    <a href="javascript:void(0);" class="delete-bom" data-id="${bomId}" title="Delete">
                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/delete.svg' }}" alt="delete">
                    </a>
                `;
            }

            function mobileCard(bom) {
                const statusClass = bom.status === 'active' ? 'bg-lightgreen' : 'bg-lightred';
                const detailsId = `bom-details-${bom.id}`;

                return `
                    <div class="bom-mobile-card">
                        <div class="bom-mobile-summary">
                            <div class="bom-mobile-title">
                                <span class="label">BOM Code</span>
                                <span class="value">${bom.bom_code || '-'}</span>
                            </div>
                            <button type="button" class="bom-toggle-details" data-target="#${detailsId}" aria-expanded="false" aria-label="Show details">
                                +
                            </button>
                        </div>
                        <div class="bom-mobile-details" id="${detailsId}">
                            <div class="bom-mobile-row">
                                <span class="label">Finished Product</span>
                                <span class="value">${bom.product?.name || 'N/A'}</span>
                            </div>
                            <div class="bom-mobile-row">
                                <span class="label">Base Qty</span>
                                <span class="value">${parseFloat(bom.base_quantity).toFixed(3)}</span>
                            </div>
                            <div class="bom-mobile-row">
                                <span class="label">Unit</span>
                                <span class="value">${bom.product?.unit?.unit_name || '-'}</span>
                            </div>
                            <div class="bom-mobile-row">
                                <span class="label">Materials</span>
                                <span class="value">${bom.items_count || 0}</span>
                            </div>
                            <div class="bom-mobile-row">
                                <span class="label">Status</span>
                                <span class="value"><span class="badges ${statusClass}">${bom.status || '-'}</span></span>
                            </div>
                            <div class="bom-mobile-actions">
                                ${actionButtons(bom.id, true)}
                            </div>
                        </div>
                    </div>
                `;
            }

            function renderRows(boms) {
                const rows = (boms || []).map(function(bom) {
                    return `
                        <tr>
                            <td>${bom.bom_code || '-'}</td>
                            <td>${bom.product?.name || 'N/A'}</td>
                            <td>${parseFloat(bom.base_quantity).toFixed(3)}</td>
                            <td>${bom.product?.unit?.unit_name || '-'}</td>
                            <td>${bom.items_count || 0}</td>
                            <td><span class="badges ${bom.status === 'active' ? 'bg-lightgreen' : 'bg-lightred'}">${bom.status || '-'}</span></td>
                            <td>
                                ${actionButtons(bom.id)}
                            </td>
                        </tr>
                    `;
                }).join('');

                const mobile = (boms || []).map(mobileCard).join('');

                $('#bom-table-body').html(rows || '<tr><td colspan="7" class="text-center text-muted">No BOMs found.</td></tr>');
                $('#bom-mobile-list').html(mobile || `
                    <div class="bom-mobile-card">
                        <div class="bom-mobile-summary">
                            <div class="bom-mobile-title">
                                <span class="label">BOM Code</span>
                                <span class="value text-muted">No BOMs found.</span>
                            </div>
                        </div>
                    </div>
                `);
            }

            function loadBoms() {
                $.ajax({
                    url: '/api/manufacturing/boms',
                    type: 'GET',
                    data: {
                        selectedSubAdminId: selectedSubAdminId
                    },
                    headers: {
                        Authorization: 'Bearer ' + authToken
                    },
                    success: function(response) {
                        renderRows(response.data || []);
                    },
                    error: function() {
                        $('#bom-table-body').html('<tr><td colspan="7" class="text-center text-danger">Failed to load BOMs.</td></tr>');
                        $('#bom-mobile-list').html(`
                            <div class="bom-mobile-card">
                                <div class="bom-mobile-summary">
                                    <div class="bom-mobile-title">
                                        <span class="label">BOM Code</span>
                                        <span class="value text-danger">Failed to load BOMs.</span>
                                    </div>
                                </div>
                            </div>
                        `);
                    }
                });
            }

            $(document).on('click', '.bom-toggle-details', function() {
                const targetSelector = $(this).data('target');
                const $target = $(targetSelector);
                const isOpen = $(this).hasClass('is-open');

                if ($target.length === 0) {
                    return;
                }

                if (isOpen) {
                    $target.slideUp(180);
                    $(this).removeClass('is-open').text('+').attr('aria-expanded', 'false');
                } else {
                    $target.slideDown(180);
                    $(this).addClass('is-open').text('-').attr('aria-expanded', 'true');
                }
            });

            $(document).on('click', '.delete-bom', function() {
                const bomId = $(this).data('id');

                Swal.fire({
                    title: 'Delete BOM?',
                    text: 'This will permanently remove the BOM if it has not been used in production.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        url: `/api/manufacturing/boms/${bomId}`,
                        type: 'DELETE',
                        data: {
                            selectedSubAdminId: selectedSubAdminId
                        },
                        headers: {
                            Authorization: 'Bearer ' + authToken
                        },
                        success: function(response) {
                            Swal.fire('Deleted', response.message || 'BOM deleted successfully.', 'success');
                            loadBoms();
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete BOM.', 'error');
                        }
                    });
                });
            });

            loadBoms();
        });
    </script>
@endpush
