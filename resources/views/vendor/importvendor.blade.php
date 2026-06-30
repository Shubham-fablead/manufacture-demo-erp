@extends('layout.app')

@section('title', 'Import Vendors')

@section('content')
    <style>
        .vendor-import-wrap .card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        .vendor-import-wrap .card-body {
            padding: 24px;
        }

        .vendor-import-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .vendor-import-toolbar h5 {
            margin-bottom: 0;
            font-size: 15px;
            font-weight: 600;
        }

        .vendor-import-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(360px, 520px);
            gap: 18px;
            align-items: start;
        }

        .vendor-import-left {
            min-height: 440px;
        }

        .vendor-import-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 26px;
        }

        .vendor-import-actions .btn {
            min-width: 120px;
            height: 52px;
            font-weight: 700;
        }

        .vendor-import-actions .btn-cancel {
            background: #1b2850;
            color: #fff;
            border-color: #1b2850;
        }

        .vendor-import-actions .btn-cancel:hover {
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

        .import-error {
            display: none;
            margin-top: 10px;
        }

        .image-upload input[type="file"] {
            cursor: pointer;
        }

        @media (max-width: 991.98px) {
            .vendor-import-grid {
                grid-template-columns: 1fr;
            }

            .vendor-import-left {
                min-height: auto;
            }
        }

        @media (max-width: 575.98px) {
            .vendor-import-wrap .card-body {
                padding: 16px;
            }

            .vendor-import-toolbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .vendor-import-actions {
                flex-wrap: wrap;
            }

            .vendor-import-actions .btn {
                width: 100%;
            }
        }
    </style>

    <div class="content vendor-import-wrap">
        <div class="page-header">
            <div class="page-title">
                <h4>Import Vendors</h4>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="vendor-import-toolbar">
                    <div>
                        <h5>Upload CSV File</h5>
                    </div>
                    <a href="{{ route('vendor.import.sample') }}" class="sample-btn">
                        <i class="fas fa-download me-1"></i>Download Sample File
                    </a>
                </div>

                <div class="vendor-import-grid">
                    <div class="vendor-import-left">
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

                        <div class="vendor-import-actions">
                            <button type="button" class="btn btn-submit" id="importSubmitBtn">
                                <span class="spinner-border spinner-border-sm me-2 d-none" id="importSpinner" role="status" aria-hidden="true"></span>
                                <span id="importButtonText">Submit</span>
                            </button>
                            <a href="{{ route('vendor.list') }}" class="btn btn-cancel">Cancel</a>
                        </div>
                    </div>

                    <div class="vendor-import-right">
                        <div class="productdetails productdetailnew mb-3">
                            <ul class="product-bar">
                                <li>
                                    <h4>Vendor Name</h4>
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
                                    <h4>Country</h4>
                                    <h6 class="manitoryblue">Field optional</h6>
                                </li>
                                <li>
                                    <h4>City</h4>
                                    <h6 class="manitoryblue">Field optional</h6>
                                </li>
                                <li>
                                    <h4>State Code</h4>
                                    <h6 class="manitoryblue">Field optional</h6>
                                </li>
                                <li>
                                    <h4>Address</h4>
                                    <h6 class="manitorygreen">This Field is required</h6>
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
                    url: "{{ route('vendor.import.store') }}",
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
                                window.location.href = "{{ route('vendor.list') }}";
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
