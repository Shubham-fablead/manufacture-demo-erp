@extends('layout.app')

@section('title', 'View Meeting')

@section('content')
    @php
        $canEditMeeting = app('hasPermission')(31, 'edit');
        $canDeleteMeeting = app('hasPermission')(31, 'delete');
        $canManageMeeting = $canEditMeeting || $canDeleteMeeting;
    @endphp
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        .info-row {
            margin-bottom: 0;
        }

        .info-row p {
            margin-bottom: 0.4rem;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .status-scheduled {
            background-color: #dbeafe;
            color: #2563eb;
        }

        .status-completed {
            background-color: #d1fae5;
            color: #059669;
        }

        .status-cancelled {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .agenda-box {
            background-color: #f8f9fa;
            border-left: 4px solid #ff9f43;
            border-radius: 6px;
            padding: 12px 15px;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.6;
        }

        .address-box {
            background-color: #f8f9fa;
            border-left: 4px solid #6c757d;
            border-radius: 6px;
            padding: 10px 15px;
            word-break: break-word;
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: #092C4C;
            padding-bottom: 8px;
            border-bottom: 2px solid #ff9f43;
            margin-bottom: 16px;
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Meeting Details</h4>
            </div>
            <div class="page-btn d-flex gap-2">
                @if ($canEditMeeting)
                    <a href="{{ route('meeting.edit', ['id' => $id]) }}" class="btn btn-added">
                        <i class="bi bi-pencil-square me-1"></i> Edit
                    </a>
                @endif
                {{-- @if ($canDeleteMeeting)
                    <button class="btn btn-danger delete-meeting" data-id="{{ $id }}">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                @endif --}}
                @if (app('hasPermission')(31, 'view'))
                    <a href="{{ route('meeting.list') }}" class="btn" style="background: #1b2850; color: #fff;">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div id="meetingDetails">
                    <div class="text-center py-4">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading meeting details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        const canManageMeeting = @json((bool) $canManageMeeting);
        const canDeleteMeeting = @json((bool) $canDeleteMeeting);

        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            const normalizedSelectedSubAdminId = (selectedSubAdminId && selectedSubAdminId !== 'null' && selectedSubAdminId !== 'undefined') ? selectedSubAdminId : '';
            const meetingId = "{{ $id }}";

            function loadMeetingDetails() {
                let url = `/meeting/${meetingId}/show`;
                if (normalizedSelectedSubAdminId) {
                    url += `?selectedSubAdminId=${encodeURIComponent(normalizedSelectedSubAdminId)}`;
                }

                $.ajax({
                    url: url,
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            displayMeetingDetails(response.data);
                        } else {
                            showError(response.message || 'Meeting not found.');
                        }
                    },
                    error: function() {
                        showError('Failed to load meeting details. Please try again.');
                    }
                });
            }

            function displayMeetingDetails(meeting) {
                const statusClass = `status-${(meeting.status || '').toLowerCase()}`;
                const statusBadge = `<span class="status-badge ${statusClass}">${meeting.status || 'N/A'}</span>`;

                const customerHtml = meeting.customer
                    ? `<strong>${meeting.customer.name}</strong>`
                    : 'N/A';

                const assignedHtml = meeting.assigned_user
                    ? `<strong>${meeting.assigned_user.name}</strong>`
                    : 'N/A';

                const addressHtml = meeting.address
                    ? `<div class="address-box mt-1">${meeting.address}</div>`
                    : 'N/A';

                const agendaHtml = meeting.agenda
                    ? `<div class="agenda-box mt-1">${meeting.agenda}</div>`
                    : 'N/A';

                const createdAt = meeting.created_at ? new Date(meeting.created_at).toLocaleString() : 'N/A';
                const updatedAt = meeting.updated_at ? new Date(meeting.updated_at).toLocaleString() : 'N/A';

                const html = `
                    <div class="row align-items-start mt-2">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <h5 class="section-title"><i class="bi bi-calendar-event me-2"></i>Meeting Information</h5>

                            <div class="info-row">
                                <p class="mb-1"><i class="bi bi-card-heading me-2"></i><strong>Title:</strong> ${meeting.meeting_title || 'N/A'}</p>
                                <hr class="my-2">
                            </div>
                            <div class="info-row">
                                <p class="mb-1"><i class="bi bi-diagram-3 me-2"></i><strong>Type:</strong> ${meeting.meeting_type || 'N/A'}</p>
                                <hr class="my-2">
                            </div>
                            <div class="info-row">
                                <p class="mb-1 d-flex align-items-center gap-2"><i class="bi bi-toggle-on me-2"></i><strong>Status:</strong> ${statusBadge}</p>
                                <hr class="my-2">
                            </div>
                            <div class="info-row">
                                <p class="mb-1"><i class="bi bi-clock me-2"></i><strong>Scheduled On:</strong> ${meeting.formatted_scheduled_on || 'N/A'}</p>
                                <hr class="my-2">
                            </div>
                            <div class="info-row">
                                <p class="mb-1"><i class="bi bi-building me-2"></i><strong>Branch:</strong> ${meeting.branch ? meeting.branch.name : 'N/A'}</p>
                                <hr class="my-2">
                            </div>
                            <div class="info-row">
                                <p class="mb-1"><i class="bi bi-calendar-plus me-2"></i><strong>Created At:</strong> <small class="text-muted">${createdAt}</small></p>
                                <hr class="my-2">
                            </div>
                            <div class="info-row">
                                <p class="mb-1"><i class="bi bi-calendar-check me-2"></i><strong>Updated At:</strong> <small class="text-muted">${updatedAt}</small></p>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6 mt-4 mt-md-0">
                            <h5 class="section-title"><i class="bi bi-people me-2"></i>Participants & Details</h5>

                            <div class="info-row">
                                <p class="mb-1"><i class="bi bi-person me-2"></i><strong>Customer:</strong></p>
                                <div class="ms-4 mb-2">${customerHtml}</div>
                                <hr class="my-2">
                            </div>
                            <div class="info-row">
                                <p class="mb-1"><i class="bi bi-person-check me-2"></i><strong>Assigned To:</strong></p>
                                <div class="ms-4 mb-2">${assignedHtml}</div>
                                <hr class="my-2">
                            </div>
                            <div class="info-row">
                                <p class="mb-1"><i class="bi bi-geo-alt me-2"></i><strong>Address:</strong></p>
                                <div class="ms-4 mb-2">${addressHtml}</div>
                                <hr class="my-2">
                            </div>
                            <div class="info-row">
                                <p class="mb-1"><i class="bi bi-journal-text me-2"></i><strong>Agenda:</strong></p>
                                <div class="ms-4 mb-2">${agendaHtml}</div>
                            </div>
                        </div>
                    </div>
                `;

                $('#meetingDetails').html(html);
            }

            function showError(message) {
                $('#meetingDetails').html(`
                    <div class="alert alert-danger text-center">
                        <i class="bi bi-exclamation-triangle me-2"></i>${message}
                    </div>
                `);
            }

            // Delete meeting
            // $(document).on('click', '.delete-meeting', function() {
            //     var id = $(this).data('id');
            //     Swal.fire({
            //         title: "Are you sure?",
            //         text: "You won't be able to revert this!",
            //         icon: "warning",
            //         showCancelButton: true,
            //         confirmButtonColor: "#ff9f43",
            //         cancelButtonColor: "#6c757d",
            //         confirmButtonText: "Yes, delete it!"
            //     }).then((result) => {
            //         if (result.isConfirmed) {
            //             $.ajax({
            //                 url: `/meeting/${id}/delete`,
            //                 type: "DELETE",
            //                 data: normalizedSelectedSubAdminId ? { selectedSubAdminId: normalizedSelectedSubAdminId } : {},
            //                 headers: {
            //                     "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            //                     "Authorization": "Bearer " + authToken,
            //                 },
            //                 success: function(response) {
            //                     if (response.status) {
            //                         Swal.fire({
            //                             title: "Deleted!",
            //                             text: "Meeting has been deleted.",
            //                             icon: "success",
            //                             confirmButtonColor: "#ff9f43"
            //                         }).then(() => {
            //                             window.location.href = "{{ route('meeting.list') }}";
            //                         });
            //                     } else {
            //                         Swal.fire({
            //                             title: "Error!",
            //                             text: response.message || "Failed to delete meeting.",
            //                             icon: "error",
            //                             confirmButtonColor: "#ff9f43"
            //                         });
            //                     }
            //                 },
            //                 error: function() {
            //                     Swal.fire({
            //                         title: "Error!",
            //                         text: "Failed to delete meeting. Please try again.",
            //                         icon: "error",
            //                         confirmButtonColor: "#ff9f43"
            //                     });
            //                 }
            //             });
            //         }
            //     });
            // });

            loadMeetingDetails();
        });
    </script>
@endpush
