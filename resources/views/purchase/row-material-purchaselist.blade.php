@extends('layout.app')

@section('title', 'Row Material Purchases')

@section('content')
    <style>
        .mobile-row-material-card {
            display: none;
        }

        .mobile-row-material-item {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            background: #fff;
            margin-bottom: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .mobile-row-material-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 12px;
            border-bottom: 1px solid #f0f0f0;
        }

        .mobile-row-material-invoice {
            font-weight: 700;
            color: #1b2850;
            font-size: 14px;
            word-break: break-word;
        }

        .mobile-row-material-toggle {
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #ff9f43;
            color: #fff;
            font-size: 18px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .mobile-row-material-toggle.minus {
            background: #dc3545;
        }

        .mobile-row-material-details {
            display: none;
            padding: 12px;
        }

        .mobile-row-material-details.active {
            display: block;
        }

        .mobile-row-material-detail {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 5px 0;
            font-size: 13px;
            border-bottom: 1px dashed #f0f0f0;
        }

        .mobile-row-material-detail:last-child {
            border-bottom: 0;
        }

        .mobile-row-material-label {
            font-weight: 600;
            color: #6b7280;
            flex: 0 0 42%;
        }

        .mobile-row-material-value {
            flex: 1;
            text-align: right;
            word-break: break-word;
        }

        .mobile-row-material-actions {
            display: flex;
            gap: 10px;
            padding-top: 10px;
            margin-top: 10px;
            border-top: 1px solid #f0f0f0;
        }

        .mobile-row-material-actions a {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #f8f9fa;
        }

        .mobile-row-material-actions img {
            width: 18px;
            height: 18px;
        }

        @media screen and (max-width: 1024px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .page-header .page-btn {
                width: 100%;
            }

            .page-header .page-btn .btn.btn-added {
                width: 100%;
                justify-content: center;
            }

            .table-responsive {
                display: none;
            }

            .mobile-row-material-card {
                display: block;
            }

            .mobile-row-material-details .badges {
                display: inline-flex;
            }

            .d-flex.justify-content-between.align-items-center.mt-3 {
                flex-direction: column;
                align-items: stretch !important;
                gap: 10px;
            }

            #row-material-purchase-summary {
                text-align: center;
            }

            .d-flex.gap-2 {
                justify-content: center;
            }
        }
    </style>

    <div class="content">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div class="page-title">
                <h4>Row Material Purchases</h4>
            </div>
            <div>
                <a href="{{ route('purchase.row_material.add') }}" class="btn btn-added">
                    <i class="fa fa-plus me-2"></i>New Row Material Purchase
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" id="search-input" class="form-control" placeholder="Search by invoice, vendor, material...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sr No</th>
                                <th>Invoice No</th>
                                <th>Vendor</th>
                                <th>Row Materials</th>
                                <th>Grand Total</th>
                                <th>Remaining</th>
                                <th>Purchase Status</th>
                                <th>Payment Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="row-material-purchase-table-body">
                            <tr>
                                <td colspan="10" class="text-center text-muted">Loading row material purchases...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mobile-row-material-card mt-3" id="mobile-row-material-container"></div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div id="row-material-purchase-summary" class="text-muted"></div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-secondary" id="prev-page">Prev</button>
                        <button type="button" class="btn btn-sm btn-secondary" id="next-page">Next</button>
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
            let currentPage = 1;
            let lastPage = 1;

            function statusBadge(status) {
                const value = (status || '').toLowerCase();
                let badgeClass = 'bg-secondary';

                if (value === 'completed') badgeClass = 'bg-lightgreen';
                if (value === 'pending') badgeClass = 'bg-lightred';
                if (value === 'partially' || value === 'partial') badgeClass = 'bg-lightyellow';

                return `<span class="badges ${badgeClass}">${status || '-'}</span>`;
            }

            function formatDisplayDate(dateValue) {
                if (!dateValue) return '-';

                const date = new Date(dateValue);
                if (isNaN(date.getTime())) {
                    return dateValue;
                }

                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();

                return `${day}-${month}-${year}`;
            }

            function renderMobileRowMaterialPurchases(items) {
                const container = $('#mobile-row-material-container');
                container.empty();

                if (!items || items.length === 0) {
                    container.html('<div class="text-center p-4 text-muted">No row material purchases found.</div>');
                    return;
                }

                items.forEach(function(item) {
                    const card = `
                        <div class="mobile-row-material-item" data-purchase-id="${item.id}">
                            <div class="mobile-row-material-row">
                                <div class="mobile-row-material-invoice">${item.invoice_number || '-'}</div>
                                <button type="button" class="mobile-row-material-toggle" onclick="toggleRowMaterialDetails('${item.id}')">+</button>
                            </div>
                            <div class="mobile-row-material-details" id="mobile-row-material-details-${item.id}">
                                <div class="mobile-row-material-detail">
                                    <span class="mobile-row-material-label">Vendor</span>
                                    <span class="mobile-row-material-value">${item.vendor_name || '-'}</span>
                                </div>
                                <div class="mobile-row-material-detail">
                                    <span class="mobile-row-material-label">Row Materials</span>
                                    <span class="mobile-row-material-value">${item.material_names || '-'}</span>
                                </div>
                                <div class="mobile-row-material-detail">
                                    <span class="mobile-row-material-label">Grand Total</span>
                                    <span class="mobile-row-material-value">${parseFloat(item.grand_total || 0).toFixed(2)}</span>
                                </div>
                                <div class="mobile-row-material-detail">
                                    <span class="mobile-row-material-label">Remaining</span>
                                    <span class="mobile-row-material-value">${parseFloat(item.remaining_amount || 0).toFixed(2)}</span>
                                </div>
                                <div class="mobile-row-material-detail">
                                    <span class="mobile-row-material-label">Purchase Status</span>
                                    <span class="mobile-row-material-value">${statusBadge(item.purchase_status)}</span>
                                </div>
                                <div class="mobile-row-material-detail">
                                    <span class="mobile-row-material-label">Payment Status</span>
                                    <span class="mobile-row-material-value">${statusBadge(item.payment_status)}</span>
                                </div>
                                <div class="mobile-row-material-detail">
                                    <span class="mobile-row-material-label">Date</span>
                                    <span class="mobile-row-material-value">${formatDisplayDate(item.date)}</span>
                                </div>
                                <div class="mobile-row-material-actions">
                                    <a href="/view-row-material-purchase/${item.id}" title="View">
                                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/eye.svg' }}" alt="view">
                                    </a>
                                    <a href="/edit-row-material-purchase/${item.id}" title="Edit">
                                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/edit.svg' }}" alt="edit">
                                    </a>
                                    <a href="javascript:void(0);" class="delete-row-material-purchase" data-id="${item.id}" title="Delete">
                                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/delete.svg' }}" alt="delete">
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                    container.append(card);
                });
            }

            window.toggleRowMaterialDetails = function(purchaseId) {
                const details = $(`#mobile-row-material-details-${purchaseId}`);
                const btn = $(`.mobile-row-material-item[data-purchase-id="${purchaseId}"] .mobile-row-material-toggle`);

                if (details.hasClass('active')) {
                    details.removeClass('active');
                    btn.removeClass('minus');
                    btn.text('+');
                } else {
                    details.addClass('active');
                    btn.addClass('minus');
                    btn.text('−');
                }
            };

            function loadRowMaterialPurchases() {
                $.ajax({
                    url: '/api/row-material-purchase-list',
                    type: 'GET',
                    data: {
                        page: currentPage,
                        per_page: 10,
                        search: $('#search-input').val(),
                        selectedSubAdminId: selectedSubAdminId
                    },
                    headers: {
                        Authorization: 'Bearer ' + authToken
                    },
                    success: function(response) {
                        const items = response.data || [];
                        const rows = (response.data || []).map(function(item, index) {
                            return `
                                <tr>
                                    <td>${((currentPage - 1) * 10) + index + 1}</td>
                                    <td>${item.invoice_number || '-'}</td>
                                    <td>${item.vendor_name || '-'}</td>
                                    <td>${item.material_names || '-'}</td>
                                    <td>${parseFloat(item.grand_total || 0).toFixed(2)}</td>
                                    <td>${parseFloat(item.remaining_amount || 0).toFixed(2)}</td>
                                    <td>${statusBadge(item.purchase_status)}</td>
                                    <td>${statusBadge(item.payment_status)}</td>
                                    <td>${formatDisplayDate(item.date)}</td>
                                    <td>
                                        <a href="/view-row-material-purchase/${item.id}" class="me-2" title="View">
                                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/eye.svg' }}" alt="view">
                                        </a>
                                        <a href="/edit-row-material-purchase/${item.id}" class="me-2" title="Edit">
                                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/edit.svg' }}" alt="edit">
                                        </a>
                                        <a href="javascript:void(0);" class="delete-row-material-purchase" data-id="${item.id}" title="Delete">
                                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/delete.svg' }}" alt="delete">
                                        </a>
                                    </td>
                                </tr>
                            `;
                        }).join('');

                        $('#row-material-purchase-table-body').html(rows || '<tr><td colspan="10" class="text-center text-muted">No row material purchases found.</td></tr>');
                        renderMobileRowMaterialPurchases(items);

                        currentPage = response.pagination?.current_page || 1;
                        lastPage = response.pagination?.last_page || 1;
                        $('#row-material-purchase-summary').text(`Page ${currentPage} of ${lastPage}`);
                        $('#prev-page').prop('disabled', currentPage <= 1);
                        $('#next-page').prop('disabled', currentPage >= lastPage);
                    },
                    error: function() {
                        $('#row-material-purchase-table-body').html('<tr><td colspan="10" class="text-center text-danger">Failed to load row material purchases.</td></tr>');
                        $('#mobile-row-material-container').html('<div class="text-center p-4 text-danger">Failed to load row material purchases.</div>');
                    }
                });
            }

            $(document).on('click', '.delete-row-material-purchase', function() {
                const purchaseId = $(this).data('id');

                Swal.fire({
                    title: 'Delete row material purchase?',
                    text: 'This will reverse the purchased stock if it has not already been consumed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        url: `/api/row-material-purchase/${purchaseId}`,
                        type: 'DELETE',
                        data: {
                            selectedSubAdminId: selectedSubAdminId
                        },
                        headers: {
                            Authorization: 'Bearer ' + authToken
                        },
                        success: function(response) {
                            Swal.fire('Deleted', response.message || 'Row material purchase deleted successfully.', 'success');
                            loadRowMaterialPurchases();
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete row material purchase.', 'error');
                        }
                    });
                });
            });

            $('#search-input').on('input', function() {
                currentPage = 1;
                loadRowMaterialPurchases();
            });

            $('#prev-page').on('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    loadRowMaterialPurchases();
                }
            });

            $('#next-page').on('click', function() {
                if (currentPage < lastPage) {
                    currentPage++;
                    loadRowMaterialPurchases();
                }
            });

            loadRowMaterialPurchases();
        });
    </script>
@endpush
