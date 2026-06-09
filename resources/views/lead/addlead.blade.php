@extends('layout.app')

@section('title', 'Add Lead')

@section('content')
    @php
        $canViewLead = app('hasPermission')(32, 'view');
        $canAddLead = app('hasPermission')(32, 'add');
        $authUser = auth()->user();
        $isStaffUser = ($authUser->role ?? '') === 'staff';
        $loggedInStaffId = $authUser->id ?? null;
        $loggedInStaffLabel = $authUser?->name ?? '';
    @endphp

    <style>
        a.btn.back-button { background: #ff9f43; color: #fff; }
        .field-error { min-height: 20px; margin-top: 4px; font-size: 0.875rem; }
        .select2-container--default .select2-selection--single { height: 38px; border: 1px solid #ced4da; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; padding-left: 12px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
        .form-control:focus, .select2-container--default.select2-container--focus .select2-selection--single { border-color: #ff9f43; box-shadow: 0 0 0 0.2rem rgba(255, 159, 67, 0.25); }
        .lead-image-preview { width: 42px; height: 42px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb; }
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
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Add Lead</h4>
                {{-- <h6>Create a new lead entry.</h6> --}}
            </div>
            <div class="back-button">
                @if ($canViewLead)
                    <a href="{{ route('lead.list') }}" class="btn back-button"><i class="fa-solid fa-arrow-left"></i> Back</a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="leadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-6 col-lg-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="Lead Name">
                            <div class="text-danger field-error" data-field="name"></div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <label class="form-label">Assigned To</label>
                            <select class="form-control" name="assigned_to" id="assigned_to" @if ($isStaffUser) disabled @endif>
                                <option value="">Select Staff</option>
                            </select>
                            <div class="text-danger field-error" data-field="assigned_to"></div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="Email Address">
                            <div class="text-danger field-error" data-field="email"></div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone" maxlength="10" placeholder="Phone Number">
                            <div class="text-danger field-error" data-field="phone"></div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <label class="form-label">WhatsApp Number</label>
                            <input type="text" class="form-control" name="whatsapp" maxlength="10" placeholder="WhatsApp Number">
                            <div class="text-danger field-error" data-field="whatsapp"></div>
                        </div>

                        <div class="col-6 col-lg-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" class="form-control" name="company_name" placeholder="Company Name">
                            <div class="text-danger field-error" data-field="company_name"></div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <label class="form-label">SIC Code</label>
                            <input type="text" class="form-control" name="sic_code" placeholder="SIC Code">
                            <div class="text-danger field-error" data-field="sic_code"></div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <label class="form-label">Lead Source <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="lead_source" placeholder="Lead Source">
                            <div class="text-danger field-error" data-field="lead_source"></div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <label class="form-label">Lead Status <span class="text-danger">*</span></label>
                            <select class="form-control" name="lead_status">
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
                        <div class="col-6 col-lg-3">
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="address" rows="3" placeholder="Lead Address"></textarea>
                            <div class="text-danger field-error" data-field="address"></div>
                        </div>

                        <div class="col-6 col-lg-3">
                            <label class="form-label">Comment</label>
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
                            @if ($canAddLead)
                                <button type="submit" class="btn btn-submit" id="leadSubmitBtn">Submit</button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        const authToken = localStorage.getItem('authToken');
        const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
        const isStaffUser = @json($isStaffUser);
        const loggedInStaffId = @json($loggedInStaffId);
        const loggedInStaffLabel = @json($loggedInStaffLabel);

        $('#assigned_to').select2({ width: '100%' });

        function loadStaff() {
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
                headers: { 'Authorization': 'Bearer ' + authToken },
                success: function(resp) {
                    if (!resp.status) return;
                    let options = '<option value="">Select Staff</option>';
                    resp.data.forEach(function(user) {
                        options += `<option value="${user.id}">${user.name}</option>`;
                    });
                    $('#assigned_to').html(options);
                }
            });
        }

        loadStaff();

        function clearErrors() {
            $('.field-error').text('');
            $('.is-invalid').removeClass('is-invalid');
        }

        function setSubmitLoading(isLoading) {
            const $submitBtn = $('#leadSubmitBtn');
            if (!$submitBtn.length) return;

            if (isLoading) {
                $submitBtn
                    .prop('disabled', true)
                    .addClass('is-loading')
                    .html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting...');
            } else {
                $submitBtn
                    .prop('disabled', false)
                    .removeClass('is-loading')
                    .html('Submit');
            }
        }

        $('#leadForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors();
            setSubmitLoading(true);

            const formData = new FormData(this);
            if (selectedSubAdminId) formData.append('selectedSubAdminId', selectedSubAdminId);

            $.ajax({
                url: '/api/lead/store',
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + authToken },
                data: formData,
                contentType: false,
                processData: false,
                success: function(resp) {
                    if (resp.status) {
                        Swal.fire({ title: 'Success!', text: resp.message, icon: 'success' })
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

        function showImagePreview(file) {
            const previewWrap = document.getElementById('leadImagePreviewWrap');
            const previewImg  = document.getElementById('leadImagePreview');
            const previewName = document.getElementById('leadImagePreviewName');
            const errorDiv    = document.querySelector('[data-field="image"]');

            if (!file) {
                previewWrap.style.display = 'none';
                previewImg.src = '';
                previewName.textContent = '';
                return;
            }

            if (!allowedTypes.includes(file.type)) {
                previewWrap.style.display = 'none';
                if (errorDiv) errorDiv.textContent = 'Only JPG, JPEG and PNG files are allowed.';
                // Clear the input
                imageInput.value = '';
                updateImageLabel(null);
                return;
            }

            if (errorDiv) errorDiv.textContent = '';

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewName.textContent = file.name;
                previewWrap.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }

        imageInput?.addEventListener('change', function() {
            const file = this.files?.[0];
            updateImageLabel(file);
            showImagePreview(file);
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
                const file = imageInput.files[0];
                updateImageLabel(file);
                showImagePreview(file);
            }
        });
    });
</script>
@endpush
