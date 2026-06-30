@extends('layout.app')

@section('title', 'Import Customers')

@section('content')
    <style>
        .import-page .card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        .import-page .card-body {
            padding: 24px;
        }

        .image-upload input[type="file"] {
            cursor: pointer;
        }

        .customer-import-wrap .page-header {
            margin-bottom: 18px;
        }

        .customer-import-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .customer-import-toolbar h5 {
            margin-bottom: 0;
            font-size: 15px;
            font-weight: 600;
        }

        .customer-import-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(360px, 520px);
            gap: 18px;
            align-items: start;
        }

        .customer-import-left {
            min-height: 440px;
        }

        .customer-import-right {
            width: 100%;
        }

        .customer-import-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 26px;
        }

        .customer-import-actions .btn {
            min-width: 120px;
            height: 52px;
            font-weight: 700;
        }

        .customer-import-actions .btn-cancel {
            background: #1b2850;
            color: #fff;
            border-color: #1b2850;
        }

        .customer-import-actions .btn-cancel:hover {
            background: #14203d;
            color: #fff;
        }

        .sample-btn {
            background: #ff9f43;
            color: #fff !important;
            border: 1px solid #ff9f43;
            font-weight: 700;
            padding: 10px 18px;
            border-radius: 6px;
            white-space: nowrap;
        }

        .sample-btn:hover {
            background: #f9912e;
            color: #fff !important;
        }

        .customer-field-guide {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }

        .customer-field-guide td {
            border: 1px solid #d9d9d9;
            padding: 12px 14px;
            vertical-align: middle;
            font-size: 14px;
        }

        .customer-field-guide td:first-child {
            width: 50%;
            color: #111827;
        }

        .customer-field-guide td:last-child {
            width: 50%;
        }

        .field-required {
            color: #10b981;
        }

        .field-optional {
            color: #3b82f6;
        }

        .import-error {
            display: none;
            margin-top: 10px;
        }

        @media (max-width: 991.98px) {
            .customer-import-grid {
                grid-template-columns: 1fr;
            }

            .customer-import-left {
                min-height: auto;
            }
        }

        @media (max-width: 575.98px) {
            .customer-import-wrap .card-body {
                padding: 16px;
            }

            .customer-import-toolbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .customer-import-actions {
                flex-wrap: wrap;
            }

            .customer-import-actions .btn {
                width: 100%;
            }
        }
    </style>

    <div class="content import-page customer-import-wrap">
        <div class="page-header">
            <div class="page-title">
                <h4>Import Customers</h4>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="customer-import-toolbar">
                    <div>
                        <h5>Upload CSV File</h5>
                    </div>
                    <a href="{{ route('customer.import.sample') }}" class="sample-btn">
                        <i class="fas fa-download me-1"></i>Download Sample File
                    </a>
                </div>

                <div class="customer-import-grid">
                    <div class="customer-import-left">
                        <div class="form-group">
                            <div class="image-upload">
                                <input type="file" name="csv_file" id="csv_file" accept=".csv,text/csv">
                                <div class="image-uploads">
                                    <img src="{{ env('ImagePath') . '/admin/assets/img/icons/upload.svg' }}" alt="upload icon">
                                    <h4 class="upload-message">Drag and drop a file to upload</h4>
                                </div>
                            </div>
                        </div>

                        <div class="import-error text-danger" id="upload-error"></div>

                        <div class="customer-import-actions">
                            <button type="button" class="btn btn-submit" id="importSubmitBtn">
                                <span class="spinner-border spinner-border-sm me-2 d-none" id="importSpinner" role="status" aria-hidden="true"></span>
                                <span id="importButtonText">Submit</span>
                            </button>
                            <a href="{{ route('customer.list') }}" class="btn btn-cancel">Cancel</a>
                        </div>
                    </div>

                    <div class="customer-import-right">
                        <div class="productdetails productdetailnew mb-3">
                            <ul class="product-bar">
                                <li>
                                    <h4>Name</h4>
                                    <h6 class="manitorygreen">This Field is required</h6>
                                </li>
                                <li>
                                    <h4>Phone</h4>
                                    <h6 class="manitorygreen">This Field is required (10 digits)</h6>
                                </li>
                                <li>
                                    <h4>Email</h4>
                                    <h6 class="manitoryblue">Field optional</h6>
                                </li>
                                <li>
                                    <h4>GST Number</h4>
                                    <h6 class="manitoryblue">Field optional (15 chars)</h6>
                                </li>
                                <li>
                                    <h4>PAN Number</h4>
                                    <h6 class="manitoryblue">Field optional (10 chars)</h6>
                                </li>
                                <li>
                                    <h4>Company Name</h4>
                                    <h6 class="manitoryblue">Field optional</h6>
                                </li>
                                <li>
                                    <h4>Address</h4>
                                    <h6 class="manitorygreen">This Field is required</h6>
                                </li>
                                <li>
                                    <h4>City</h4>
                                    <h6 class="manitoryblue">Field optional</h6>
                                </li>
                                <li>
                                    <h4>Country</h4>
                                    <h6 class="manitoryblue">Field optional</h6>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            const $btn = $('#importSubmitBtn');
            const $spinner = $('#importSpinner');
            const $text = $('#importButtonText');
            const $error = $('#upload-error');
            const $fileInput = $('#csv_file');
            const $message = $('.upload-message');

            function setLoading(isLoading) {
                $btn.prop('disabled', isLoading);
                $spinner.toggleClass('d-none', !isLoading);
                $text.text(isLoading ? 'Uploading...' : 'Submit');
            }

            $fileInput.on('change', function() {
                const fileName = this.files[0]?.name;
                $message.text(fileName || 'Drag and drop a file to upload');
            });

            $btn.on('click', function() {
                const file = $fileInput[0].files[0];

                $error.hide().text('');

                if (!file) {
                    $error.text('Please select a CSV file.').show();
                    return;
                }

                const formData = new FormData();
                formData.append('csv_file', file);

                setLoading(true);

                $.ajax({
                    url: "{{ route('customer.import.store') }}",
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        setLoading(false);

                        Swal.fire({
                            icon: response.status ? 'success' : 'error',
                            title: response.status ? 'Import Completed' : 'Import Failed',
                            html: response.message || 'Done',
                            confirmButtonColor: '#ff9f43'
                        }).then(function() {
                            if (response.status) {
                                $fileInput.val('');
                                $message.text('Drag and drop a file to upload');
                                window.location.href = "{{ route('customer.list') }}";
                            }
                        });
                    },
                    error: function(xhr) {
                        setLoading(false);
                        const message = xhr.responseJSON?.message || 'An unexpected error occurred.';
                        $error.text(message).show();
                        Swal.fire({
                            icon: 'error',
                            title: 'Import Failed',
                            text: message,
                            confirmButtonColor: '#ff9f43'
                        });
                    }
                });
            });
        });
    </script>
@endpush
