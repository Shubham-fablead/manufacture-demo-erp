@extends('layout.app')

@section('title', 'View Lead')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
    .lead-preview {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        min-height: 100%;
    }

    .lead-main-img {
        height: 355px;
        object-fit: contain;
        width: 100%;
        background: #f8fafc;
    }

    .lead-avatar-fallback {
        height: 360px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #ff9f43 0%, #ffbf6b 100%);
        color: #fff;
        font-size: 78px;
        font-weight: 800;
        letter-spacing: 2px;
    }

    .lead-detail-panel {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 18px;
        min-height: 100%;
    }

    .lead-title {
        font-size: 30px;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 12px;
        color: #1b2850;
    }

    .lead-subtitle {
        color: #6b7280;
        margin-bottom: 12px;
        font-size: 14px;
    }

    .lead-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-new { background: #e0f2fe; color: #0369a1; }
    .status-qualified { background: #dcfce7; color: #15803d; }
    .status-working { background: #fef3c7; color: #b45309; }
    .status-ready-to-close { background: #ede9fe; color: #6d28d9; }
    .status-closed-won { background: #d1fae5; color: #047857; }
    .status-closed-lost { background: #fee2e2; color: #dc2626; }

    .lead-meta-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }

    .lead-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #f8fafc;
        color: #1b2850;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid #e5e7eb;
    }

    .lead-info-grid {
        margin-top: 6px;
    }

    .lead-info-item {
        padding: 7px 0;
        border-bottom: 1px solid #edf2f7;
        min-height: 38px;
    }

    .lead-info-item:last-child {
        border-bottom: none;
    }

    .lead-info-label {
        color: #1b2850;
        font-weight: 700;
        margin-right: 6px;
        white-space: nowrap;
    }

    .lead-info-value {
        color: #111827;
        word-break: break-word;
    }

    .lead-barcode {
        padding-top: 10px;
        border-top: 1px solid #edf2f7;
        margin-top: 10px;
    }

    .lead-summary-strip {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin: 14px 0 10px;
    }

    .lead-summary-box {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 12px;
        background: #fafafa;
    }

    .lead-summary-label {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6b7280;
        margin-bottom: 4px;
        font-weight: 700;
    }

    .lead-summary-value {
        display: block;
        color: #1b2850;
        font-weight: 700;
        line-height: 1.25;
    }

    @media (max-width: 767.98px) {
        .lead-title {
            font-size: 24px;
        }

        .lead-main-img,
        .lead-avatar-fallback {
            height: 260px;
        }

        .lead-detail-panel {
            padding: 14px 14px 6px;
        }

        .lead-summary-strip {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .lead-summary-strip {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Lead Details</h4>
        </div>
        <div class="page-btn d-flex gap-2">
            @if (app('hasPermission')(32, 'edit'))
                <a href="javascript:void(0);" id="editLeadBtn" class="btn btn-added">
                    <i class="bi bi-pencil-square me-1"></i> Edit
                </a>
            @endif
            @if (app('hasPermission')(32, 'view'))
                <a href="{{ route('lead.list') }}" class="btn" style="background: #1b2850; color: #fff;">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            @endif
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="row align-items-start" id="lead-details">
                {{-- <div class="col-md-5">
                <div class="lead-preview">
                    <div id="leadImageHolder">
                        <div class="lead-avatar-fallback">L</div>
                    </div>
                </div> --}}
                </div>
                <div class="col-md-12">
                    <div class="lead-detail-panel">
                    <div class="lead-title" id="leadName">Loading...</div>
                    <div class="lead-subtitle" id="leadSubtitle">Lead overview and contact details</div>
                    <div class="lead-meta-row" id="leadStatusWrap">
                        <span class="lead-badge" id="leadStatusBadge">-</span>
                    </div>

                    <div class="lead-summary-strip">
                        <div class="lead-summary-box">
                            <span class="lead-summary-label">Phone</span>
                            <span class="lead-summary-value" id="leadPhoneSummary">-</span>
                        </div>
                        <div class="lead-summary-box">
                            <span class="lead-summary-label">Lead Source</span>
                            <span class="lead-summary-value" id="leadSourceSummary">-</span>
                        </div>
                        <div class="lead-summary-box">
                            <span class="lead-summary-label">Assigned To</span>
                            <span class="lead-summary-value" id="leadAssignedSummary">-</span>
                        </div>
                        <div class="lead-summary-box">
                            <span class="lead-summary-label">Company</span>
                            <span class="lead-summary-value" id="leadCompanySummary">-</span>
                        </div>
                    </div>

                    <div class="row lead-info-grid" id="leadInfoGrid"></div>

                        <div class="lead-barcode d-none" id="leadBarcodeWrap">
                            <div class="d-flex flex-column flex-sm-row align-items-start">
                                <strong class="me-2"><i class="bi bi-upc me-1 mt-1"></i>Barcode:</strong>
                                <div class="ms-sm-3 mt-2 mt-sm-0">
                                    <div id="leadBarcodeHtml"></div>
                                    <div class="text-muted small text-center mt-2" id="leadBarcodeText"></div>
                                </div>
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
    $(document).ready(function() {
        const authToken = localStorage.getItem('authToken');
        const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
        const leadId = "{{ $id }}";

        function capitalizeWords(str) {
            if (!str || String(str).trim() === '') return 'N/A';
            return String(str).replace(/\b\w/g, char => char.toUpperCase());
        }

        function statusClass(status) {
            return 'status-' + String(status || '').toLowerCase().replace(/\s+/g, '-');
        }

        function renderInfo(label, value) {
            return `
                <div class="col-6">
                    <div class="lead-info-item">
                        <span class="lead-info-label">${label}:</span>
                        <span class="lead-info-value">${value || 'N/A'}</span>
                    </div>
                </div>
            `;
        }

        $.ajax({
            url: `/api/lead/${leadId}/show${selectedSubAdminId ? '?selectedSubAdminId=' + selectedSubAdminId : ''}`,
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + authToken },
            success: function(resp) {
                if (!resp.status) return;

                const lead = resp.data || {};
                const name = lead.name || 'Lead Details';
                const initials = name
                    .split(' ')
                    .filter(Boolean)
                    .map(part => part.charAt(0))
                    .join('')
                    .substring(0, 2)
                    .toUpperCase() || 'L';

                $('#leadName').text(name);
                $('#leadSubtitle').text(lead.company_name ? `Company: ${lead.company_name}` : 'Lead overview and contact details');
                $('#leadStatusBadge')
                    .text(lead.lead_status || 'N/A')
                    .removeClass()
                    .addClass('lead-badge ' + statusClass(lead.lead_status));

                $('#leadPhoneSummary').text(lead.phone || '-');
                $('#leadSourceSummary').text(lead.lead_source || '-');
                $('#leadAssignedSummary').text(lead.assigned_user?.name || lead.assignedUser?.name || '-');
                $('#leadCompanySummary').text(lead.company_name || '-');

                const imagePath = "{{ env('ImagePath') }}";
                if (lead.image) {
                    const imgSrc = lead.image.startsWith('http')
                        ? lead.image
                        : `${imagePath}/storage/${lead.image}`;

                    $('#leadImageHolder').html(`
                        <img src="${imgSrc}" class="d-block w-100 lead-main-img"
                             onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\\'lead-avatar-fallback\\'>${initials}</div>';">
                    `);
                } else {
                    $('#leadImageHolder').html(`<div class="lead-avatar-fallback">${initials}</div>`);
                }

                $('#leadInfoGrid').html(`
                  ${renderInfo('Email', lead.email || '-')}
                    ${renderInfo('WhatsApp', lead.whatsapp || '-')}
                     ${renderInfo('Created By', lead.creator?.name || '-')}
                    ${renderInfo('SIC Code', lead.sic_code || '-')}
                    ${renderInfo('Address', lead.address || '-')}
                    ${renderInfo('Comment', lead.comment || '-')}
                `);

                if (lead.barcode_html) {
                    $('#leadBarcodeHtml').html(lead.barcode_html);
                    $('#leadBarcodeText').text(lead.barcode || 'N/A');
                    $('#leadBarcodeWrap').removeClass('d-none');
                }

                $('#editLeadBtn').attr('href', `/edit-lead/${leadId}`);
            },
            error: function() {
                $('#lead-details').html('<div class="col-12 text-center text-danger">Unable to load lead details.</div>');
            }
        });
    });
</script>
@endpush
