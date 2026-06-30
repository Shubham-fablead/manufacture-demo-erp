@extends('layout.app')

@section('title', 'Import Staff')

@section('content')
    <style>
        .staff-import-wrap .card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        .staff-import-wrap .card-body {
            padding: 24px;
        }

        .staff-import-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .staff-import-toolbar h5 {
            margin-bottom: 0;
            font-size: 15px;
            font-weight: 600;
        }

        .staff-import-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(360px, 520px);
            gap: 18px;
            align-items: start;
        }

        .staff-import-left {
            min-height: 440px;
        }

        .staff-import-right {
            width: 100%;
        }

        .staff-import-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 26px;
        }

        .staff-import-actions .btn {
            min-width: 120px;
            height: 52px;
            font-weight: 700;
        }

        .staff-import-actions .btn-cancel {
            background: #1b2850;
            color: #fff;
            border-color: #1b2850;
        }

        .staff-import-actions .btn-cancel:hover {
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

        .staff-field-guide {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            table-layout: fixed;
        }

        .staff-field-guide td {
            border: 1px solid #d9d9d9;
            padding: 12px 14px;
            vertical-align: top;
            font-size: 14px;
            line-height: 1.45;
            word-break: break-word;
        }

        .staff-field-guide td:first-child {
            width: 48%;
            color: #111827;
        }

        .staff-field-guide td:last-child {
            width: 52%;
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

        .image-upload input[type="file"] {
            cursor: pointer;
        }

        @media (max-width: 991.98px) {
            .staff-import-grid {
                grid-template-columns: 1fr;
            }

            .staff-import-left {
                min-height: auto;
            }
        }

        @media (max-width: 575.98px) {
            .staff-import-wrap .card-body {
                padding: 16px;
            }

            .staff-import-toolbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .staff-import-actions {
                flex-wrap: wrap;
            }

            .staff-import-actions .btn {
                width: 100%;
            }
        }
    </style>

    <div class="content staff-import-wrap">
        <div class="page-header">
            <div class="page-title">
                <h4>Import Staff</h4>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="staff-import-toolbar">
                    <div>
                        <h5>Upload CSV File</h5>
                    </div>
                    <a href="{{ route('staff.import.sample') }}" class="sample-btn">
                        <i class="fas fa-download me-1"></i>Download Sample File
                    </a>
                </div>

                <div class="staff-import-grid">
                    <div class="staff-import-left">
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

                        <div class="staff-import-actions">
                            <button type="button" class="btn btn-submit" id="importSubmitBtn">
                                <span class="spinner-border spinner-border-sm me-2 d-none" id="importSpinner" role="status" aria-hidden="true"></span>
                                <span id="importButtonText">Submit</span>
                            </button>
                            <a href="{{ route('staff.list') }}" class="btn btn-cancel">Cancel</a>
                        </div>
                    </div>

                    <div class="staff-import-right">
                        <div class="table-responsive">
                            <table class="staff-field-guide">
                                <tbody>
                                    <tr>
                                        <td>Name</td>
                                        <td class="field-required">This Field is required</td>
                                    </tr>
                                    <tr>
                                        <td>Phone</td>
                                        <td class="field-required">This Field is required (10 digits)</td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td class="field-optional">Field optional</td>
                                    </tr>
                                    <tr>
                                        <td>Role</td>
                                        <td class="field-required">This Field is required<br>staff or hr</td>
                                    </tr>
                                    <tr>
                                        <td>Password</td>
                                        <td class="field-required">This Field is required (min 6 chars)</td>
                                    </tr>
                                    <tr>
                                        <td>Address</td>
                                        <td class="field-optional">Field optional</td>
                                    </tr>
                                    <tr>
                                        <td>City</td>
                                        <td class="field-optional">Field optional</td>
                                    </tr>
                                    <tr>
                                        <td>Country</td>
                                        <td class="field-optional">Field optional</td>
                                    </tr>
                                    <tr>
                                        <td>Joining Date</td>
                                        <td class="field-optional">Field optional (YYYY-MM-DD)</td>
                                    </tr>
                                    <tr>
                                        <td>Salary</td>
                                        <td class="field-optional">Field optional</td>
                                    </tr>
                                </tbody>
                            </table>
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
                    url: "{{ route('staff.import.store') }}",
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
                                window.location.href = "{{ route('staff.list') }}";
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
