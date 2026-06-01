@extends('layout.app')

@section('title', 'View Follow Up')

@section('content')
    @php
        $canViewFollowUp = app('hasPermission')(30, 'view');
        $canEditFollowUp = app('hasPermission')(30, 'edit');
    @endphp
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        .info-row p {
            margin-bottom: 0.4rem;
        }

        .priority-badge,
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .priority-high {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .priority-medium {
            background-color: #fed7aa;
            color: #ea580c;
        }

        .priority-low {
            background-color: #d1fae5;
            color: #059669;
        }

        .status-pending {
            background-color: #fed7aa;
            color: #ea580c;
        }

        .status-rescheduled {
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

        .comment-box {
            background-color: #f8f9fa;
            border-left: 4px solid #ff9f43;
            border-radius: 6px;
            padding: 12px 15px;
            min-height: 60px;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.6;
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
                <h4>Follow Up Details</h4>
            </div>
            <div class="page-btn d-flex gap-2">
                @if ($canEditFollowUp)
                    <a href="#" id="editFollowUpBtn" class="btn btn-added">
                        <i class="bi bi-pencil-square me-1"></i> Edit
                    </a>
                @endif
                @if ($canViewFollowUp)
                    <a href="{{ route('followup.list') }}" class="btn" style="background: #1b2850; color: #fff;">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div id="followUpDetails">
                    <div class="text-center py-4">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading follow up details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        const canEditFollowUp = @json((bool) $canEditFollowUp);

        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            const normalizedSelectedSubAdminId = (selectedSubAdminId && selectedSubAdminId !== 'null' && selectedSubAdminId !== 'undefined') ? selectedSubAdminId : '';
            const followUpId = "{{ $id }}";

            function loadFollowUpDetails() {
                let url = `/follow-up/${followUpId}/show`;
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
                            displayFollowUpDetails(response.data);
                        } else {
                            showError(response.message || "Follow up not found.");
                        }
                    },
                    error: function() {
                        showError("Failed to load follow up details.");
                    }
                });
            }

            function formatFollowUpDate(followUp) {
                if (followUp.formatted_follow_up_datetime) {
                    return followUp.formatted_follow_up_datetime;
                }
                if (!followUp.follow_up_datetime) return 'N/A';

                const rawDate = String(followUp.follow_up_datetime).replace(' ', 'T');
                const parsedDate = new Date(rawDate);
                if (isNaN(parsedDate.getTime())) return followUp.follow_up_datetime;

                const day    = String(parsedDate.getDate()).padStart(2, '0');
                const month  = String(parsedDate.getMonth() + 1).padStart(2, '0');
                const year   = parsedDate.getFullYear();
                const hours24 = parsedDate.getHours();
                const hours12 = String(hours24 % 12 || 12).padStart(2, '0');
                const minutes = String(parsedDate.getMinutes()).padStart(2, '0');
                const amPm   = hours24 >= 12 ? 'PM' : 'AM';

                return `${day}-${month}-${year} ${hours12}:${minutes} ${amPm}`;
            }

            function displayFollowUpDetails(followUp) {
                // Set edit button href once we have the ID
                if (canEditFollowUp) {
                    $('#editFollowUpBtn').attr('href', `/edit-follow-up/${followUp.id}`);
                }

                const priorityBadge = `<span class="priority-badge priority-${(followUp.priority || '').toLowerCase()}">${followUp.priority || 'N/A'}</span>`;
                const statusBadge   = `<span class="status-badge status-${(followUp.status || '').toLowerCase()}">${followUp.status || 'N/A'}</span>`;

                const assignedHtml = followUp.assigned_user
                    ? `<strong>${followUp.assigned_user.name}</strong>
                       <br><small class="text-muted">${followUp.assigned_user.email || ''}${followUp.assigned_user.phone ? ' | ' + followUp.assigned_user.phone : ''}</small>`
                    : 'Not Assigned';

                const html = `
                    <div class="row align-items-start mt-2">
                        <!-- Left Column: Basic Info -->
                        <div class="col-md-6">
                            <h5 class="section-title"><i class="bi bi-info-circle me-2"></i>Basic Information</h5>

                            <div class="info-row">
                                <p class="mb-1"><i class="bi bi-person me-2"></i><strong>Lead:</strong> ${followUp.subject_name || 'N/A'}</p>
                                <hr class="my-2">
                            </div>
                            <div class="info-row">
                                <p class="mb-1"><i class="bi bi-chat-square-text me-2"></i><strong>Purpose:</strong> ${followUp.purpose || 'N/A'}</p>
                                <hr class="my-2">
                            </div>
                            <div class="info-row">
                                <p class="mb-1 d-flex align-items-center gap-2"><i class="bi bi-flag me-2"></i><strong>Priority:</strong> ${priorityBadge}</p>
                                <hr class="my-2">
                            </div>
                            <div class="info-row">
                                <p class="mb-1 d-flex align-items-center gap-2"><i class="bi bi-toggle-on me-2"></i><strong>Status:</strong> ${statusBadge}</p>
                                <hr class="my-2">
                            </div>
                            <div class="info-row">
                                <p class="mb-1"><i class="bi bi-calendar-event me-2"></i><strong>Follow Up Date:</strong> ${formatFollowUpDate(followUp)}</p>
                            </div>
                        </div>

                        <!-- Right Column: Assignment & Comments -->
                        <div class="col-md-6 mt-4 mt-md-0">
                            <h5 class="section-title"><i class="bi bi-people me-2"></i>Assignment & Comments</h5>

                            <div class="info-row">
                                <p class="mb-1"><i class="bi bi-person-check me-2"></i><strong>Assigned To:</strong></p>
                                <div class="ms-4 mb-2">${assignedHtml}</div>
                                <hr class="my-2">
                            </div>
                            <div class="info-row">
                                <p class="mb-1"><i class="bi bi-chat-left-text me-2"></i><strong>Comment:</strong></p>
                                <div class="ms-4 mt-1">
                                    <div class="comment-box">${followUp.comment || 'No comments'}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                $('#followUpDetails').html(html);
            }

            function showError(message) {
                $('#followUpDetails').html(`
                    <div class="alert alert-danger text-center">
                        <i class="bi bi-exclamation-triangle me-2"></i>${message}
                    </div>
                `);
            }

            loadFollowUpDetails();
        });
    </script>
@endpush
