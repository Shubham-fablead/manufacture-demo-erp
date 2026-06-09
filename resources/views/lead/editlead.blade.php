@extends('layout.app')

@section('title', 'Edit Lead')

@section('content')
    <style>
        .lead-image-dropzone {
            border: 1px solid #cfd7e6;
            border-radius: 6px;
            min-height: 90px;
            background: #fff;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 16px;
            text-align: center;
        }

        .lead-image-dropzone:hover,
        .lead-image-dropzone.is-dragover {
            border-color: #ff9f43;
            box-shadow: 0 0 0 0.15rem rgba(255, 159, 67, 0.08);
        }

        .lead-image-dropzone .upload-icon {
            color: #ff9f43;
            font-size: 28px;
            line-height: 1;
            margin-bottom: 6px;
        }

        .lead-image-dropzone .upload-text {
            font-size: 14px;
            color: #111827;
        }

        .lead-image-dropzone .upload-hint {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .lead-image-input {
            display: none;
        }
        .btn-submit.is-loading {
            opacity: 0.85;
            pointer-events: none;
        }

        .btn-submit .spinner-border {
            width: 14px;
            height: 14px;
            border-width: 0.18em;
            vertical-align: -2px;
        }

        .history-table td,
        .history-table th {
            vertical-align: middle;
        }

        .history-mobile-summary {
            display: none;
        }

        .history-details-column {
            display: none;
        }

        .history-toggle-btn {
            background: #ff9f43;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            line-height: 1;
            padding: 0;
            transition: all 0.3s;
        }

        .history-toggle-btn.minus {
            background: #dc3545;
        }

        .history-details-row {
            display: none;
        }

        .history-details-row.show {
            display: table-row;
        }

        /* .history-details-content {
            padding: 15px 12px;
            background: #fff;
        } */

        .history-detail-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .history-detail-row:last-child {
            border-bottom: none;
        }

        .history-detail-label {
            font-weight: 600;
            color: #595b5d;
            font-size: 13px;
        }

        .history-detail-value {
            color: #1b2850;
            font-size: 13px;
            text-align: right;
        }

        @media (max-width: 767px) {
            .history-table thead th:nth-child(n+2),
            .history-table tbody tr.history-main-row td:nth-child(n+2) {
                display: none !important;
            }

            .history-table thead th:first-child,
            .history-table tbody tr.history-main-row td:first-child {
                display: table-cell !important;
                vertical-align: top !important;
            }

            .history-table thead th.history-details-column,
            .history-table tbody tr.history-main-row td.history-details-column {
                display: table-cell !important;
                width: 56px !important;
                min-width: 56px !important;
                max-width: 56px !important;
                text-align: center;
                vertical-align: top !important;
                padding: 12px 6px !important;
            }

            .history-mobile-summary {
                display: block;
                margin-top: 4px;
                line-height: 1.25;
            }

            .history-mobile-summary .mobile-history-status {
                font-weight: 700;
                color: #1b2850;
                font-size: 13px;
                display: block;
                margin-bottom: 2px;
            }

            .history-mobile-summary .mobile-history-date {
                font-size: 12px;
                color: #6b7280;
                display: block;
            }

            .history-details-row td {
                display: table-cell !important;
            }
        }

        @media (min-width: 768px) {
            .history-table thead th,
            .history-table tbody td {
                display: table-cell !important;
            }

            .history-table thead th.history-details-column,
            .history-table tbody tr.history-main-row td.history-details-column {
                display: none !important;
            }
        }
    </style>

    @php
        $canViewLead = app('hasPermission')(32, 'view');
        $canEditLead = app('hasPermission')(32, 'edit');
        $authUser = auth()->user();
        $isStaffUser = ($authUser->role ?? '') === 'staff';
        $loggedInStaffId = $authUser->id ?? null;
        $loggedInStaffLabel = $authUser?->name ?? '';
    @endphp

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Edit Lead</h4>
                {{-- <h6>Update lead details and status history.</h6> --}}
            </div>
            <div class="back-button">
                @if ($canViewLead)
                    <a href="{{ route('lead.list') }}" class="btn back-button btn-primary"><i class="fa-solid fa-arrow-left"></i> Back</a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="leadForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $id }}">
                    <div class="row g-3">
                        <div class="col-6 col-lg-3"><label class="form-label">Name <span
                                    class="text-danger">*</span></label><input type="text" class="form-control"
                                name="name" placeholder="Lead Name">
                            <div class="text-danger field-error" data-field="name"></div>
                        </div>
                        <div class="col-6 col-lg-3"><label class="form-label">Assigned To</label><select
                                class="form-control" name="assigned_to" id="assigned_to"
                                @if ($isStaffUser) disabled @endif>
                                <option value="">Select Staff</option>
                            </select>
                            <div class="text-danger field-error" data-field="assigned_to"></div>
                        </div>
                        <div class="col-6 col-lg-3"><label class="form-label">Email</label><input type="email"
                                class="form-control" name="email" placeholder="Email Address">
                            <div class="text-danger field-error" data-field="email"></div>
                        </div>
                        <div class="col-6 col-lg-3"><label class="form-label">Phone <span
                                    class="text-danger">*</span></label><input type="text" class="form-control"
                                name="phone" maxlength="10" placeholder="Phone Number">
                            <div class="text-danger field-error" data-field="phone"></div>
                        </div>
                        <div class="col-6 col-lg-3"><label class="form-label">WhatsApp Number</label><input type="text"
                                class="form-control" name="whatsapp" maxlength="10" placeholder="WhatsApp Number">
                            <div class="text-danger field-error" data-field="whatsapp"></div>
                        </div>


                        <div class="col-6 col-lg-3"><label class="form-label">Company Name</label><input type="text"
                                class="form-control" name="company_name" placeholder="Company Name">
                            <div class="text-danger field-error" data-field="company_name"></div>
                        </div>
                        <div class="col-6 col-lg-3"><label class="form-label">SIC Code</label><input type="text"
                                class="form-control" name="sic_code" placeholder="SIC Code">
                            <div class="text-danger field-error" data-field="sic_code"></div>
                        </div>
                        <div class="col-6 col-lg-3"><label class="form-label">Lead Source <span
                                    class="text-danger">*</span></label><input type="text" class="form-control"
                                name="lead_source" placeholder="Lead Source">
                            <div class="text-danger field-error" data-field="lead_source"></div>
                        </div>
                        <div class="col-6 col-lg-3"><label class="form-label">Lead Status <span
                                    class="text-danger">*</span></label><select class="form-control" name="lead_status">
                                <option value="">Select Status</option>
                                <option value="New">New</option>
                                <option value="Qualified">Qualified</option>
                                <option value="Working">Working</option>
                                <option value="Ready to Close">Ready to Close</option>
                                <option value="Closed Won">Closed Won</option>
                                <option value="Closed Lost">Closed Lost</option>
                            </select>
                            <div class="text-danger field-error" data-field="lead_status"></div>
                        </div>
                        <div class="col-6 col-lg-3"><label class="form-label">Address <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" name="address" rows="3" placeholder="Address"></textarea>
                            <div class="text-danger field-error" data-field="address"></div>
                        </div>
                        <div class="col-6 col-lg-3"><label class="form-label">Comment</label>
                            <textarea class="form-control" name="comment" rows="3" placeholder="Comments"></textarea>
                            <div class="text-danger field-error" data-field="comment"></div>
                        </div>
                           <div class="col-6 col-lg-3">
                            <label class="form-label">Image</label>
                            <label class="lead-image-dropzone w-100" for="lead_image_input" id="leadImageDropzone">
                                <div>
                                    <div class="upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                                    <div class="upload-text" id="leadImageText">Drag and drop a file to upload</div>
                                    <div class="upload-hint" id="leadImageHint">or click to choose a file</div>
                                </div>
                            </label>
                            <input type="file" class="lead-image-input" id="lead_image_input" name="image" accept="image/jpeg,image/jpg,image/png">
                            <div id="leadImagePreviewWrap" style="display:none; margin-top:8px; text-align:center;">
                                <img id="leadImagePreview" src="" alt="Preview"
                                    style="max-width:100%; max-height:150px; border-radius:6px; border:1px solid #e5e7eb; object-fit:cover;">
                                <div id="leadImagePreviewName" style="font-size:12px; color:#6b7280; margin-top:4px;"></div>
                            </div>
                            <div class="text-danger field-error" data-field="image"></div>
                        </div>
                        <div class="col-12 d-flex  gap-2">
                            @if ($canViewLead)
                                <a href="{{ route('lead.list') }}" class="btn btn-cancel">Cancel</a>
                            @endif
                            @if ($canEditLead)
                                <button type="submit" class="btn btn-submit" id="leadUpdateBtn">Update</button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h5 class="mb-3">Status Update History</h5>
                <div class="table-responsive">
                    <table class="table table-bordered history-table">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th class="history-details-column"></th>
                                <th>Status</th>
                                <th>Comment</th>
                                <th>Updated By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="statusHistoryBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            const authToken = localStorage.getItem('authToken');
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            const leadId = "{{ $id }}";
            const isStaffUser = @json($isStaffUser);
            const loggedInStaffId = @json($loggedInStaffId);
            const loggedInStaffLabel = @json($loggedInStaffLabel);

            $('#assigned_to').select2({
                width: '100%'
            });

            function loadStaff(selectedId) {
                if (isStaffUser && loggedInStaffId) {
                    $('#assigned_to').html(`<option value="${loggedInStaffId}">${loggedInStaffLabel}</option>`)
                        .val(String(loggedInStaffId)).trigger('change').prop('disabled', true);
                    return;
                }

                let url = '/api/lead/staff';
                if (selectedSubAdminId) url += `?selectedSubAdminId=${selectedSubAdminId}`;

                $.ajax({
                    url,
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + authToken
                    },
                    success: function(resp) {
                        let options = '<option value="">Select Staff</option>';
                        if (resp.status) {
                            resp.data.forEach(function(user) {
                                options += `<option value="${user.id}">${user.name}</option>`;
                            });
                        }
                        $('#assigned_to').html(options);
                        if (selectedId) $('#assigned_to').val(String(selectedId)).trigger('change');
                    }
                });
            }

            function renderHistory(history) {
                const tbody = $('#statusHistoryBody');
                tbody.empty();
                if (!history || !history.length) {
                    tbody.append(
                        '<tr><td colspan="6" class="text-center text-muted">No status history found</td></tr>');
                    return;
                }
                history.forEach(function(item, index) {
                    const formattedDate = new Date(item.created_at).toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }).replace(/ /g, '-');

                    tbody.append(`
                    <tr class="history-main-row">
                        <td>${index + 1}</td>
                        <td class="history-details-column">
                            <button type="button" class="history-toggle-btn" data-history-id="${index}">
                                <span class="toggle-icon">+</span>
                            </button>
                        </td>
                        <td>${item.status || ''}</td>
                        <td>${item.comment || '-'}</td>
                        <td>${item.updater?.name || 'N/A'}</td>
                        <td>
                        ${formattedDate}
                        </td>
                    </tr>
                    <tr class="history-details-row" data-history-id="${index}">
                        <td colspan="6">
                            <div class="history-details-content">
                                <div class="history-mobile-summary">
                                    <span class="mobile-history-status">${item.status || ''}</span>
                                    <span class="mobile-history-date">${formattedDate}</span>
                                </div>
                                <div class="history-detail-row">
                                    <span class="history-detail-label">Status:</span>
                                    <span class="history-detail-value">${item.status || ''}</span>
                                </div>
                                <div class="history-detail-row">
                                    <span class="history-detail-label">Comment:</span>
                                    <span class="history-detail-value">${item.comment || '-'}</span>
                                </div>
                                <div class="history-detail-row">
                                    <span class="history-detail-label">Updated By:</span>
                                    <span class="history-detail-value">${item.updater?.name || 'N/A'}</span>
                                </div>
                                <div class="history-detail-row">
                                    <span class="history-detail-label">Date:</span>
                                    <span class="history-detail-value">${formattedDate}</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                `);
                });
            }

            function clearErrors() {
                $('.field-error').text('');
                $('.is-invalid').removeClass('is-invalid');
            }

            function setSubmitLoading(isLoading) {
                const $submitBtn = $('#leadUpdateBtn');
                if (!$submitBtn.length) return;

                if (isLoading) {
                    $submitBtn
                        .prop('disabled', true)
                        .addClass('is-loading')
                        .html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Updating...');
                } else {
                    $submitBtn
                        .prop('disabled', false)
                        .removeClass('is-loading')
                        .html('Update');
                }
            }

            function loadLeadData() {
                $.ajax({
                    url: `/api/lead/${leadId}/show${selectedSubAdminId ? '?selectedSubAdminId=' + selectedSubAdminId : ''}`,
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + authToken
                    },
                    success: function(resp) {
                        if (!resp.status) return;
                        const lead = resp.data;
                        $('[name="name"]').val(lead.name || '');
                        $('[name="email"]').val(lead.email || '');
                        $('[name="phone"]').val(lead.phone || '');
                        $('[name="whatsapp"]').val(lead.whatsapp || '');
                        $('[name="address"]').val(lead.address || '');
                        $('[name="company_name"]').val(lead.company_name || '');
                        $('[name="sic_code"]').val(lead.sic_code || '');
                        $('[name="lead_source"]').val(lead.lead_source || '');
                        $('[name="lead_status"]').val(lead.lead_status || '');
                        $('[name="comment"]').val(lead.comment || '');
                        loadStaff(lead.assigned_to);
                        renderHistory(lead.status_histories || []);

                        // Show existing image preview
                        if (lead.image) {
                            const imagePath = "{{ env('ImagePath') }}" + '/storage/' + lead.image;
                            const imageName = lead.image.split('/').pop();
                            showImagePreview(imagePath, imageName);
                        }
                    }
                });
            }

        loadLeadData();

        const imageInput = document.getElementById('lead_image_input');
        const dropzone = document.getElementById('leadImageDropzone');
        const imageText = document.getElementById('leadImageText');
        const imageHint = document.getElementById('leadImageHint');

        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];

        function updateImageLabel(file) {
            if (file) {
                imageText.textContent = file.name;
                imageHint.textContent = 'File selected';
            } else {
                imageText.textContent = 'Drag and drop a file to upload';
                imageHint.textContent = 'or click to choose a file';
            }
        }

        function showImagePreview(src, name) {
            const previewWrap = document.getElementById('leadImagePreviewWrap');
            const previewImg  = document.getElementById('leadImagePreview');
            const previewName = document.getElementById('leadImagePreviewName');

            if (!src) {
                previewWrap.style.display = 'none';
                previewImg.src = '';
                previewName.textContent = '';
                return;
            }
            previewImg.src = src;
            previewName.textContent = name || '';
            previewWrap.style.display = 'block';
        }

        function handleFileSelect(file) {
            const errorDiv = document.querySelector('[data-field="image"]');
            if (!file) {
                updateImageLabel(null);
                return;
            }
            if (!allowedTypes.includes(file.type)) {
                if (errorDiv) errorDiv.textContent = 'Only JPG, JPEG and PNG files are allowed.';
                imageInput.value = '';
                updateImageLabel(null);
                showImagePreview(null);
                return;
            }
            if (errorDiv) errorDiv.textContent = '';
            updateImageLabel(file);
            const reader = new FileReader();
            reader.onload = function(e) {
                showImagePreview(e.target.result, file.name);
            };
            reader.readAsDataURL(file);
        }

        imageInput?.addEventListener('change', function() {
            handleFileSelect(this.files?.[0]);
        });

        dropzone?.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('is-dragover');
        });

        dropzone?.addEventListener('dragleave', function() {
            this.classList.remove('is-dragover');
        });

        dropzone?.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('is-dragover');
            if (e.dataTransfer?.files?.length) {
                imageInput.files = e.dataTransfer.files;
                handleFileSelect(imageInput.files[0]);
            }
        });

        $('#leadForm').on('submit', function(e) {
            e.preventDefault();
                clearErrors();
                setSubmitLoading(true);
                const formData = new FormData(this);
                if (selectedSubAdminId) formData.append('selectedSubAdminId', selectedSubAdminId);

                $.ajax({
                    url: `/api/lead/${leadId}/update`,
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + authToken,
                        'X-HTTP-Method-Override': 'PUT'
                    },
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(resp) {
                        if (resp.status) {
                            Swal.fire({
                                    title: 'Success!',
                                    text: resp.message,
                                    icon: 'success'
                                })
                                .then(() => window.location.href = "{{ route('lead.list') }}");
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        Object.keys(errors).forEach(function(key) {
                            $(`[data-field="${key}"]`).text(errors[key][0]);
                            $(`[name="${key}"]`).addClass('is-invalid');
                        });
                    },
                    complete: function() {
                        setSubmitLoading(false);
                    }
                });
            });

            $(document).on('click', '.history-toggle-btn', function() {
                const historyId = $(this).data('history-id');
                const detailsRow = $(`.history-details-row[data-history-id="${historyId}"]`);
                const icon = $(this).find('.toggle-icon');

                if (detailsRow.hasClass('show')) {
                    detailsRow.removeClass('show');
                    $(this).removeClass('minus');
                    icon.text('+');
                } else {
                    detailsRow.addClass('show');
                    $(this).addClass('minus');
                    icon.text('-');
                }
            });
        });
    </script>
@endpush
