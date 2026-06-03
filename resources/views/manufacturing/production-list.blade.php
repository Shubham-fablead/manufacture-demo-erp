@extends('layout.app')

@section('title', 'Production')

@section('content')
    <style>
        .production-page .page-header {
            gap: 12px;
        }

        .production-page .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .production-page .production-mobile-view {
            display: none;
        }

        .production-page .production-mobile-card {
            border: 1px solid #eef0f3;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            margin-bottom: 12px;
            overflow: hidden;
        }

        .production-page .production-mobile-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border-bottom: 1px solid #f1f3f5;
        }

        .production-page .production-mobile-title {
            min-width: 0;
        }

        .production-page .production-mobile-title .label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .production-page .production-mobile-title .value {
            display: block;
            font-size: 15px;
            font-weight: 600;
            color: #1b2850;
            word-break: break-word;
        }

        .production-page .production-toggle-details {
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

        .production-page .production-toggle-details.is-open {
            background: #dc3545;
        }

        .production-page .production-mobile-details {
            display: none;
            padding: 10px 14px 14px;
            background: #fff;
        }

        .production-page .production-mobile-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #f1f3f5;
        }

        .production-page .production-mobile-row:last-child {
            border-bottom: 0;
        }

        .production-page .production-mobile-row .label {
            font-size: 14px;
            font-weight: 600;
            color: #344767;
            flex: 0 0 46%;
        }

        .production-page .production-mobile-row .value {
            font-size: 14px;
            color: #1b2850;
            text-align: right;
            word-break: break-word;
            flex: 1 1 auto;
        }

        .production-page .production-mobile-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #f1f3f5;
        }

        .production-page .production-mobile-actions .btn {
            min-width: 92px;
        }

        .production-page .badges {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
        }

        @media screen and (max-width: 767.98px) {
            .production-page .page-header {
                flex-direction: column;
                align-items: stretch;
                margin-bottom: 14px;
            }

            .production-page .page-title h4 {
                font-size: 18px;
                margin-bottom: 4px;
            }

            .production-page .page-title h6 {
                font-size: 12px;
                line-height: 1.4;
            }

            .production-page .page-btn {
                width: 100%;
            }

            .production-page .page-btn .btn.btn-added {
                width: 100%;
                justify-content: center;
            }

            .production-page .card-body {
                padding: 12px;
            }

            .production-page .production-desktop-view {
                display: none;
            }

            .production-page .production-mobile-view {
                display: block;
            }
        }
    </style>

    <div class="content">
        <div class="production-page">
            <div class="page-header">
                <div class="page-title">
                    <h4>Production</h4>
                    <h6>Convert raw materials into finished goods</h6>
                </div>
                <div class="page-btn">
                    <a href="{{ route('inventory.production.add') }}" class="btn btn-added">
                        <i class="fa fa-plus me-2"></i>New Production
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="production-desktop-view">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Production No</th>
                                        <th>Product</th>
                                        <th>BOM</th>
                                        <th>Production Qty</th>
                                        <th>Output Qty</th>
                                        <th>Total Cost</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="production-table-body">
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">Loading productions...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="production-mobile-view" id="production-mobile-list">
                        <div class="production-mobile-card">
                            <div class="production-mobile-summary">
                                <div class="production-mobile-title">
                                    <span class="label">Production No</span>
                                    <span class="value">Loading productions...</span>
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
            const eyeIcon = "{{ env('ImagePath') . '/admin/assets/img/icons/eye.svg' }}";
            const editIcon = "{{ env('ImagePath') . '/admin/assets/img/icons/edit.svg' }}";
            const deleteIcon = "{{ env('ImagePath') . '/admin/assets/img/icons/delete.svg' }}";

            function actionButtons(item, mobile = false) {
                const editAction = item.status === 'draft'
                    ? `<a href="/inventory/productions/${item.id}/edit" class="${mobile ? 'btn btn-success btn-sm py-1 px-2' : 'me-3'}" title="Edit">${mobile ? 'Edit' : `<img src="${editIcon}" alt="edit">`}</a>`
                    : (item.status === 'in_production'
                        ? `<a href="/inventory/productions/${item.id}/edit" class="${mobile ? 'btn btn-success btn-sm py-1 px-2' : 'me-3'}" title="Complete Production">${mobile ? 'Edit' : `<img src="${editIcon}" alt="edit">`}</a>`
                        : '');

                const deleteAction = item.status === 'draft'
                    ? `<a href="javascript:void(0);" class="${mobile ? 'btn btn-danger btn-sm py-1 px-2 delete-production' : 'delete-production me-3'}" data-id="${item.id}" title="Delete">${mobile ? 'Delete' : `<img src="${deleteIcon}" alt="delete">`}</a>`
                    : '';

                const viewAction = `<a href="/inventory/productions/${item.id}" class="${mobile ? 'btn btn-primary btn-sm py-1 px-2' : ''}" title="View">${mobile ? 'View' : `<img src="${eyeIcon}" alt="view">`}</a>`;

                return mobile
                    ? `${viewAction}${editAction}${deleteAction}`
                    : `${editAction}${deleteAction}${viewAction}`;
            }

            function formatProductionDate(dateValue) {
                if (!dateValue) return '-';

                const dateString = String(dateValue).trim();
                const match = dateString.match(/^(\d{4})-(\d{2})-(\d{2})/);

                if (match) {
                    return `${match[3]}/${match[2]}/${match[1]}`;
                }

                const parsedDate = new Date(dateString);
                if (isNaN(parsedDate.getTime())) return dateString;

                const day = String(parsedDate.getDate()).padStart(2, '0');
                const month = String(parsedDate.getMonth() + 1).padStart(2, '0');
                const year = parsedDate.getFullYear();

                return `${day}/${month}/${year}`;
            }

            function mobileCard(item) {
                const statusClass = {
                    'completed': 'bg-lightgreen',
                    'in_production': 'bg-lightpurple',
                    'draft': 'bg-lightyellow'
                }[item.status] || 'bg-lightyellow';

                const statusLabel = {
                    'completed': 'Completed',
                    'in_production': 'In Production',
                    'draft': 'Draft'
                }[item.status] || item.status || '-';

                const detailsId = `production-details-${item.id}`;

                return `
                    <div class="production-mobile-card">
                        <div class="production-mobile-summary">
                            <div class="production-mobile-title">
                                <span class="label">Production No</span>
                                <span class="value">${item.production_no || '-'}</span>
                            </div>
                            <button type="button" class="production-toggle-details" data-target="#${detailsId}" aria-expanded="false" aria-label="Show details">+</button>
                        </div>
                        <div class="production-mobile-details" id="${detailsId}">
                            <div class="production-mobile-row">
                                <span class="label">Product</span>
                                <span class="value">${item.product?.name || 'N/A'}</span>
                            </div>
                            <div class="production-mobile-row">
                                <span class="label">BOM</span>
                                <span class="value">${item.bom?.bom_code || '-'}</span>
                            </div>
                            <div class="production-mobile-row">
                                <span class="label">Production Qty</span>
                                <span class="value">${parseFloat(item.production_qty).toFixed(3)} ${item.product?.unit?.unit_name || ''}</span>
                            </div>
                            <div class="production-mobile-row">
                                <span class="label">Output Qty</span>
                                <span class="value">${parseFloat(item.output_qty).toFixed(3)} ${item.product?.unit?.unit_name || ''}</span>
                            </div>
                            <div class="production-mobile-row">
                                <span class="label">Total Cost</span>
                                <span class="value">${parseFloat(item.total_cost).toFixed(2)}</span>
                            </div>
                            <div class="production-mobile-row">
                                <span class="label">Status</span>
                                <span class="value"><span class="badges ${statusClass}">${statusLabel}</span></span>
                            </div>
                            <div class="production-mobile-row">
                                <span class="label">Date</span>
                                <span class="value">${formatProductionDate(item.production_date)}</span>
                            </div>
                            <div class="production-mobile-actions">
                                ${actionButtons(item, true)}
                            </div>
                        </div>
                    </div>
                `;
            }

            function renderRows(items) {
                const rows = (items || []).map(function(item) {
                    const statusClass = {
                        'completed': 'bg-lightgreen',
                        'in_production': 'bg-lightpurple',
                        'draft': 'bg-lightyellow'
                    }[item.status] || 'bg-lightyellow';

                    const statusLabel = {
                        'completed': 'Completed',
                        'in_production': 'In Production',
                        'draft': 'Draft'
                    }[item.status] || item.status || '-';

                    return `
                        <tr>
                            <td>${item.production_no || '-'}</td>
                            <td>${item.product?.name || 'N/A'}</td>
                            <td>${item.bom?.bom_code || '-'}</td>
                            <td>${parseFloat(item.production_qty).toFixed(3)} ${item.product?.unit?.unit_name || ''}</td>
                            <td>${parseFloat(item.output_qty).toFixed(3)} ${item.product?.unit?.unit_name || ''}</td>
                            <td>${parseFloat(item.total_cost).toFixed(2)}</td>
                            <td><span class="badges ${statusClass}">${statusLabel}</span></td>
                            <td>${formatProductionDate(item.production_date)}</td>
                            <td>${actionButtons(item, false)}</td>
                        </tr>
                    `;
                }).join('');

                const mobile = (items || []).map(mobileCard).join('');

                $('#production-table-body').html(rows || '<tr><td colspan="9" class="text-center text-muted">No production records found.</td></tr>');
                $('#production-mobile-list').html(mobile || `
                    <div class="production-mobile-card">
                        <div class="production-mobile-summary">
                            <div class="production-mobile-title">
                                <span class="label">Production No</span>
                                <span class="value text-muted">No production records found.</span>
                            </div>
                        </div>
                    </div>
                `);
            }

            function loadProductions() {
                $.ajax({
                    url: '/api/manufacturing/productions',
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
                        $('#production-table-body').html('<tr><td colspan="9" class="text-center text-danger">Failed to load production records.</td></tr>');
                        $('#production-mobile-list').html(`
                            <div class="production-mobile-card">
                                <div class="production-mobile-summary">
                                    <div class="production-mobile-title">
                                        <span class="label">Production No</span>
                                        <span class="value text-danger">Failed to load production records.</span>
                                    </div>
                                </div>
                            </div>
                        `);
                    }
                });
            }

            $(document).on('click', '.production-toggle-details', function() {
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

            $(document).on('click', '.delete-production', function() {
                const productionId = $(this).data('id');

                Swal.fire({
                    title: 'Delete production?',
                    text: 'This will permanently remove the draft production record.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        url: `/api/manufacturing/productions/${productionId}`,
                        type: 'DELETE',
                        data: {
                            selectedSubAdminId: selectedSubAdminId
                        },
                        headers: {
                            Authorization: 'Bearer ' + authToken
                        },
                        success: function(response) {
                            Swal.fire('Deleted', response.message || 'Production deleted successfully.', 'success');
                            loadProductions();
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete production.', 'error');
                        }
                    });
                });
            });

            loadProductions();
        });
    </script>
@endpush
