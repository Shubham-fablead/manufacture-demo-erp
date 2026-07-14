@extends('layout.app')

@section('title', 'Products Delivery')

@section('content')
    <style>
        .delivery-page-shell { padding: 24px; }
        .delivery-page-title { font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 4px; }
        .delivery-page-subtitle { color: #6b7280; margin-bottom: 22px; }
        .delivery-filter-card,
        .delivery-table-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03); }
        .delivery-filter-card { padding: 18px 20px; margin-bottom: 18px; }
        .delivery-table-card { padding: 18px 20px; }
        .delivery-label { font-size: 14px; font-weight: 500; color: #111827; margin-bottom: 8px; }
        .delivery-table thead th { background: #f8fafc; color: #111827; font-weight: 600; border-bottom: 1px solid #e5e7eb; padding: 14px 12px; white-space: nowrap; }
        .delivery-table tbody td { padding: 14px 12px; vertical-align: middle; white-space: nowrap; }
        .delivery-status { min-width: 150px; width: 150px; }
        .delivery-details-column { display: none; }
        .delivery-details-row { display: none; }
        .delivery-details-row.show { display: table-row; }
        .delivery-details-content { background: #fff; padding: 12px !important; }
        .delivery-details-list { display: grid; gap: 9px; }
        .delivery-details-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; font-size: 13px; color: #334155; }
        .delivery-details-item strong { color: #0f172a; font-weight: 600; }
        .delivery-toggle-btn-table {
            width: 24px;
            height: 24px;
            border: none;
            border-radius: 50%;
            background: #ff9f43;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            line-height: 24px;
            text-align: center;
            padding: 0;
        }
        .delivery-toggle-btn-table.minus {
            background: #ffb25f;
        }
        .pdf-btn { border: 1px solid #ff9f43; color: #ff9f43; background: #fff; padding: 4px 12px; border-radius: 4px; text-decoration: none; display: inline-block; }
        .pdf-btn:hover { background: #fff7ec; color: #ff9f43; }
        @media (max-width: 767px) {
            .delivery-page-shell { padding: 16px 12px; }
            .delivery-table-card, .delivery-filter-card { padding: 14px; }
            .delivery-filter-card .row { --bs-gutter-x: 10px; --bs-gutter-y: 10px; }
            .delivery-label { font-size: 12px; margin-bottom: 5px; }
            .delivery-filter-card .form-control,
            .delivery-filter-card .form-select { font-size: 12px; padding: 7px 8px; }
            .delivery-table-card { overflow: hidden; }
            .delivery-table { table-layout: fixed; min-width: 0; width: 100%; }
            .delivery-table th,
            .delivery-table td { font-size: 10px; padding: 10px 5px; white-space: normal; word-break: break-word; }
            .delivery-table th:nth-child(3),
            .delivery-table td:nth-child(3),
            .delivery-table th:nth-child(4),
            .delivery-table td:nth-child(4),
            .delivery-table th:nth-child(5),
            .delivery-table td:nth-child(5),
            .delivery-table th:nth-child(6),
            .delivery-table td:nth-child(6),
            .delivery-table th:nth-child(7),
            .delivery-table td:nth-child(7) { display: none; }
            .delivery-details-column {
                display: table-cell;
                text-align: center;
                width: 42px;
            }
            .delivery-table th:nth-child(1) { width: 44%; }
            .delivery-table th:nth-child(2) { width: 38%; }
            .delivery-status { min-width: 130px; width: 130px; }
        }
    </style>

    <div class="delivery-page-shell">
        <div class="mb-4">
            <div class="delivery-page-title">Products Delivery</div>
            <div class="delivery-page-subtitle">View and manage delivery status for all sales orders</div>
        </div>

        <div class="delivery-filter-card">
            <form method="GET" action="{{ route('sales.products_delivery') }}" onsubmit="return false;">
                <div class="row g-3 align-items-end">
                    <div class="col-6 col-lg-3 col-md-6">
                        <label class="delivery-label">Order #</label>
                        <input type="text" name="order_no" value="{{ $orderNo }}" class="form-control" placeholder="Search order number">
                    </div>
                    <div class="col-6 col-lg-3 col-md-6">
                        <label class="delivery-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" @selected($status === 'pending')>Pending</option>
                            <option value="delivered" @selected($status === 'delivered')>Delivered</option>
                            <option value="partially_delivered" @selected($status === 'partially_delivered')>Partially Delivered</option>
                            <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-6 col-lg-2 col-md-6">
                        <label class="delivery-label">From</label>
                        <input type="date" name="from" value="{{ $fromDate }}" class="form-control">
                    </div>
                    <div class="col-6 col-lg-2 col-md-6">
                        <label class="delivery-label">To</label>
                        <input type="date" name="to" value="{{ $toDate }}" class="form-control">
                    </div>
                </div>
            </form>
        </div>

        <div class="delivery-table-card">
            <div class="table-responsive">
                <table class="table delivery-table mb-0">
                    <thead>
                        <tr>
                            <th>Order No.</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Delivered By</th>
                            <th>Status</th>
                            <th>Action</th>
                            <th class="delivery-details-column">Details</th>
                        </tr>
                    </thead>
                    <tbody id="deliveryTableBody">
                        <tr><td colspan="7" class="text-center text-muted py-4">Loading delivery orders...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        let deliveryFilterTimer = null;
        const deliveryListUrl = @json(route('sales.products_delivery.data'));

        $(function() {
            window.history.replaceState({}, '', window.location.pathname);
        });

        function triggerDeliveryLoad() {
            clearTimeout(deliveryFilterTimer);
            deliveryFilterTimer = setTimeout(loadDeliveryRows, 250);
        }

        $(document).on('change', '.delivery-filter-card select, .delivery-filter-card input[type="date"]', function() {
            triggerDeliveryLoad();
        });

        $(document).on('input', '.delivery-filter-card input[name="order_no"]', function() {
            triggerDeliveryLoad();
        });

        function getDeliveryFilters() {
            const params = new URLSearchParams(window.location.search);
            return { order_no: params.get('order_no') || '', status: params.get('status') || '', from: params.get('from') || '', to: params.get('to') || '' };
        }

        function renderDeliveryRows(rows) {
            const $tbody = $('#deliveryTableBody');
            if (!rows || !rows.length) {
                $tbody.html('<tr><td colspan="7" class="text-center text-muted py-4">No delivery orders found</td></tr>');
                return;
            }

            const html = rows.map((row) => {
                const statusValue = String(row.status || 'pending').toLowerCase().replace(/[\s-]/g, '_');
                const currencySymbol = row.currency_symbol || '₹';
                const total = Number(row.total || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const rowId = row.order_id || row.id || Math.random().toString(36).slice(2);
                return `
                    <tr data-delivery-row-id="${rowId}">
                        <td><a href="${row.delivery_url}">${row.order_number || row.order_id || '--'}</a></td>
                        <td>${row.created_at || '--'}</td>
                        <td>${row.customer_name || '--'}</td>
                        <td>${currencySymbol}${total}</td>
                        <td>${row.delivered_by || '--'}</td>
                        <td>
                            <select class="form-control form-control-sm pending-delivery-status delivery-status" data-url="${row.status_update_url || ''}" data-current-status="${statusValue}">
                                <option value="pending" ${statusValue === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="delivered" ${statusValue === 'delivered' ? 'selected' : ''}>Delivered</option>
                                <option value="partially_delivered" ${statusValue === 'partially_delivered' ? 'selected' : ''}>Partially Delivered</option>
                                <option value="cancelled" ${statusValue === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                            </select>
                        </td>
                        <td><a href="${row.pdf_url}" target="_blank" class="pdf-btn">PDF</a></td>
                        <td class="delivery-details-column">
                            <button type="button" class="delivery-toggle-btn-table" data-delivery-id="${rowId}">+</button>
                        </td>
                    </tr>
                    <tr class="delivery-details-row" data-delivery-details-id="${rowId}">
                        <td colspan="8" class="delivery-details-content">
                            <div class="delivery-details-list">
                                <div class="delivery-details-item">
                                    <strong>Customer:</strong>
                                    <span>${row.customer_name || '--'}</span>
                                </div>
                                <div class="delivery-details-item">
                                    <strong>Total:</strong>
                                    <span>${currencySymbol}${total}</span>
                                </div>
                                <div class="delivery-details-item">
                                    <strong>Delivered By:</strong>
                                    <span>${row.delivered_by || '--'}</span>
                                </div>
                                <div class="delivery-details-item">
                                    <strong>Status:</strong>
                                    <select class="form-control form-control-sm pending-delivery-status delivery-status"
                                        data-url="${row.status_update_url || ''}"
                                        data-current-status="${statusValue}">
                                        <option value="pending" ${statusValue === 'pending' ? 'selected' : ''}>Pending</option>
                                        <option value="delivered" ${statusValue === 'delivered' ? 'selected' : ''}>Delivered</option>
                                        <option value="partially_delivered" ${statusValue === 'partially_delivered' ? 'selected' : ''}>Partially Delivered</option>
                                        <option value="cancelled" ${statusValue === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                    </select>
                                </div>
                                <div class="delivery-details-item">
                                    <strong>Action:</strong>
                                    <a href="${row.pdf_url}" target="_blank" class="pdf-btn">PDF</a>
                                </div>
                            </div>
                        </td>
                    </tr>`;
            }).join('');
            $tbody.html(html);
        }

        $(document).on('click', '.delivery-toggle-btn-table', function() {
            const $btn = $(this);
            const deliveryId = $btn.data('delivery-id');
            const $detailsRow = $(`.delivery-details-row[data-delivery-details-id="${deliveryId}"]`);

            $('.delivery-details-row').not($detailsRow).removeClass('show');
            $('.delivery-toggle-btn-table').not($btn).removeClass('minus').text('+');

            $detailsRow.toggleClass('show');
            $btn.toggleClass('minus', $detailsRow.hasClass('show')).text($detailsRow.hasClass('show') ? '-' : '+');
        });

        function loadDeliveryRows() {
            const filters = {
                order_no: $('.delivery-filter-card input[name="order_no"]').val() || '',
                status: $('.delivery-filter-card select[name="status"]').val() || '',
                from: $('.delivery-filter-card input[name="from"]').val() || '',
                to: $('.delivery-filter-card input[name="to"]').val() || ''
            };

            $('#deliveryTableBody').html('<tr><td colspan="7" class="text-center text-muted py-4">Loading delivery orders...</td></tr>');
            $.ajax({
                url: deliveryListUrl,
                method: 'GET',
                data: filters,
                success: function(response) { renderDeliveryRows(response?.data || []); },
                error: function() { $('#deliveryTableBody').html('<tr><td colspan="7" class="text-center text-danger py-4">Unable to load delivery orders.</td></tr>'); }
            });
        }

        $(document).on('change', '.pending-delivery-status', function() {
            const $select = $(this);
            const url = $select.data('url');
            const status = $select.val();
            const currentStatus = $select.data('current-status');
            if (!url) return;
            $select.prop('disabled', true).addClass('is-saving');
            $.ajax({
                url: url,
                method: 'POST',
                data: { status: status, _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response && response.status) {
                        $select.data('current-status', status);
                        Swal.fire({ icon: 'success', title: 'Success', text: response.message || 'Delivery status updated successfully.', confirmButtonColor: '#ff9f43', timer: 1800, showConfirmButton: false });
                        return;
                    }
                    $select.val(currentStatus);
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Unable to update delivery status.', confirmButtonColor: '#ff9f43' });
                },
                error: function(xhr) {
                    $select.val(currentStatus);
                    Swal.fire({ icon: 'error', title: 'Error', text: (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to update delivery status.', confirmButtonColor: '#ff9f43' });
                },
                complete: function() { $select.prop('disabled', false).removeClass('is-saving'); }
            });
        });

        loadDeliveryRows();
    </script>
@endpush
