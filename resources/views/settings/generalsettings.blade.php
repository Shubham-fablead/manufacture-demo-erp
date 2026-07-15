@extends('layout.app')

@section('title', 'Shop Settings')

@section('content')
    <style>
        @media screen and (max-width: 768px) {
            .form-group {
                margin-bottom: 15px !important;
            }

            .image-upload input[type=file] {
                height: 115px !important;
            }
        }

        @media screen and (min-width: 1200px) {
            .dashboard-subsection-col {
                flex: 0 0 20%;
                max-width: 20%;
            }
        }

        .form-select:disabled,
        .form-control:disabled {
            background-color: #e9ecef;
            cursor: not-allowed;
            opacity: 1;
        }

        /* ===== MOBILE + TABLET MULTI-STEP WIZARD (up to 1199px) ===== */
        @media screen and (max-width: 1199px) {

            /* Step indicator bar */
            .shop-wizard-steps {
                display: flex !important;
                align-items: center;
                justify-content: center;
                gap: 0;
                margin-bottom: 24px;
                background: #f8f9fa;
                border-radius: 50px;
                padding: 5px;
            }
            .shop-wizard-steps .wizard-step-btn {
                flex: 1;
                text-align: center;
                padding: 8px 10px;
                border-radius: 50px;
                font-size: 13px;
                font-weight: 500;
                color: #6c757d;
                background: transparent;
                border: none;
                cursor: pointer;
                transition: background .2s, color .2s;
                white-space: nowrap;
            }
            .shop-wizard-steps .wizard-step-btn.active {
                background: #ff9f43;
                color: #fff;
                box-shadow: 0 2px 8px rgba(255,159,67,.35);
            }

            /* Hide all wizard panels by default; show active */
            .shop-wizard-panel {
                display: none !important;
            }
            .shop-wizard-panel.wizard-active {
                display: block !important;
            }

            /* Each field is half-width (2 per row) */
            #shop-settings .shop-wizard-panel .col-lg-3,
            #shop-settings .shop-wizard-panel .col-sm-6,
            #shop-settings .shop-wizard-panel .col-6 {
                flex: 0 0 50% !important;
                max-width: 50% !important;
            }
            /* Address & GPS take full width */
            #shop-settings .shop-wizard-panel .col-sm-6.full-tablet {
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }

            /* Next / Back / Submit wizard buttons */
            .wizard-nav-btns {
                display: flex !important;
                gap: 10px;
                margin-top: 16px;
            }
            .wizard-nav-btns .btn-wizard-next,
            .wizard-nav-btns .btn-wizard-back,
            .wizard-nav-btns .btn-wizard-submit {
                flex: 1;
                padding: 10px;
                border-radius: 8px;
                font-weight: 600;
                font-size: 14px;
            }
            .wizard-nav-btns .btn-wizard-next,
            .wizard-nav-btns .btn-wizard-submit {
                background: #ff9f43;
                color: #fff;
                border: none;
            }
            .wizard-nav-btns .btn-wizard-back {
                background: #f8f9fa;
                color: #495057;
                border: 1px solid #dee2e6;
            }

            /* Hide the original desktop submit row */
            .desktop-submit {
                display: none !important;
            }
        }

        /* On small phones, make step labels shorter so they fit */
        @media screen and (max-width: 400px) {
            .shop-wizard-steps .wizard-step-btn {
                font-size: 11px;
                padding: 7px 6px;
            }
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Shop & Company Settings</h4>
            </div>
            <div class="page-btn">
                <a href="{{ route('auth.profile') }}" class="btn btn-added">View Profile</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="shop-tab" data-bs-toggle="tab" data-bs-target="#shop-settings"
                            type="button" role="tab" aria-controls="shop-settings" aria-selected="true">
                            🏪 Shop Settings
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="rules-tab" data-bs-toggle="tab" data-bs-target="#company-rules"
                            type="button" role="tab" aria-controls="company-rules" aria-selected="false">
                            🏢 Company Rules
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="delivery-terms-tab" data-bs-toggle="tab"
                            data-bs-target="#delivery-terms" type="button" role="tab"
                            aria-controls="delivery-terms" aria-selected="false">
                            Delivery Terms &amp; Conditions
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard-settings"
                            type="button" role="tab" aria-controls="dashboard-settings" aria-selected="false">
                            📊 Dashboard
                        </button>
                    </li>
                  
                   
                </ul>

                <!-- Tab Content -->
                <div class="tab-content mt-4" id="settingsTabsContent">
                    <!-- ================= SHOP SETTINGS TAB ================= -->
                    <div class="tab-pane fade show active" id="shop-settings" role="tabpanel" aria-labelledby="shop-tab">

                        {{-- ── Tablet wizard step indicator (hidden on desktop & phone via CSS) ── --}}
                        <div class="shop-wizard-steps" id="shopWizardSteps" style="display:none;">
                            <button type="button" class="wizard-step-btn active" data-step="1">Basic Info</button>
                            <button type="button" class="wizard-step-btn" data-step="2">Finance & Address</button>
                            <button type="button" class="wizard-step-btn" data-step="3">Media & Delivery</button>
                        </div>

                        <div class="row">

                            {{-- ══════════════ STEP 1 – Basic Info ══════════════ --}}
                            <div class="shop-wizard-panel wizard-active col-12 px-0" id="shopWizardPanel1">
                                <div class="row">
                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>Shop Name<span class="manitory">*</span></label>
                                            <input type="text" id="shop_name" placeholder="Enter Title">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>Shop Email<span class="manitory">*</span></label>
                                            <input type="text" id="email" placeholder="Enter email">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>Shop Phone<span class="manitory">*</span></label>
                                            <input type="text" id="phone" placeholder="Enter Phone">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>State Code</label>
                                            <input type="text" id="state_code" placeholder="Enter State Code">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>GST Number</label>
                                            <input type="text" id="gst_num" placeholder="Enter GST Number">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>CIN Number</label>
                                            <input type="text" id="cin_no" placeholder="Enter CIN Number">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>Bank Name<span class="manitory">*</span></label>
                                            <input type="text" id="bank_name" placeholder="Enter Bank Name">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>Branch<span class="manitory">*</span></label>
                                            <input type="text" id="branch" placeholder="Enter Branch">
                                        </div>
                                    </div>
                                </div>

                                {{-- Wizard nav for step 1 --}}
                                <div class="wizard-nav-btns" style="display:none;">
                                    <button type="button" class="btn btn-wizard-next" data-wizard-next="2">Next</button>
                                </div>
                            </div>{{-- /panel 1 --}}

                            {{-- ══════════════ STEP 2 – Finance & Address ══════════════ --}}
                            <div class="shop-wizard-panel col-12 px-0" id="shopWizardPanel2">
                                <div class="row">
                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>A/C No.<span class="manitory">*</span></label>
                                            <input type="number" id="ac_no" class="form-control" placeholder="Enter A/C No.">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>IFSC Code<span class="manitory">*</span></label>
                                            <input type="text" id="ifsc_code" placeholder="Enter IFSC Code">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>Shop Currency Symbol <span class="manitory">*</span></label>
                                            <select id="currency_symbol" class="form-select">
                                                <option value="₹" selected>₹ (Indian Rupee)</option>
                                                <option value="$">$ (US Dollar)</option>
                                                <option value="€">€ (Euro)</option>
                                                <option value="£">£ (British Pound)</option>
                                                <option value="¥">¥ (Japanese Yen)</option>
                                                <option value="₩">₩ (South Korean Won)</option>
                                                <option value="₽">₽ (Russian Ruble)</option>
                                                <option value="₺">₺ (Turkish Lira)</option>
                                                <option value="₫">₫ (Vietnamese Dong)</option>
                                                <option value="฿">฿ (Thai Baht)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label for="currency_position" class="form-label">
                                                Currency Symbol Position
                                            </label>
                                            <select class="form-select" id="currency_position" name="currency_position"
                                                required>
                                                <option value="left">Left (e.g. ₹100 or $100)</option>
                                                <option value="right">Right (e.g. 100₹ or 100$)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>Low Stock Warning Quantity</label>
                                            <input type="number" id="low_stock" class="form-control"
                                                placeholder="Enter Low Stock Warning Quantity">
                                            <small id="lowStockError" class="text-danger d-none">Low Stock Quantity cannot be
                                                negative.</small>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 full-tablet">
                                        <div class="form-group">
                                            <label>Shop Address<span class="manitory">*</span></label>
                                            <textarea id="address" placeholder="Enter Address" rows="3" class="form-control"></textarea>
                                            <div class="d-flex justify-content-between align-items-start mt-1">
                                                <small id="coordinateLookupStatus" class="text-muted" style="line-height:1.2;"></small>
                                                <button type="button" class="btn btn-sm btn-primary ms-2 flex-shrink-0" id="btnFetchCoordinates" style="padding: 2px 8px; font-size: 11px;">Get GPS from Address</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>Office Latitude</label>
                                            <input type="text" id="office_latitude" class="form-control" placeholder="e.g. 21.1268432">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>Office Longitude</label>
                                            <input type="text" id="office_longitude" class="form-control" placeholder="e.g. 73.1051204">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>Radius (m)</label>
                                            <input type="number" id="office_radius" class="form-control" placeholder="e.g. 200" min="0" value="200">
                                            <small class="text-muted">Staff must be within this distance from office GPS to clock in.</small>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>Meeting / Follow-up reminder (hours before)</label>
                                            <input type="number" id="appointment_reminder_hours_before"
                                                name="appointment_reminder_hours_before"
                                                class="form-control" placeholder="e.g. 3" min="1">
                                            <small class="text-muted">IST. Default 3. Use matching WhatsApp
                                                templates (e.g. meeting_reminder_3_hours_before).</small>
                                        </div>
                                    </div>
                                     <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>Invoice Size</label>
                                            <select id="invoice_size" name="invoice_size" class="form-select">
                                                <option value="small"
                                                    {{ old('invoice_size', $setting->invoice_size ?? 'big') == 'small' ? 'selected' : '' }}>
                                                    Small Size Invoice</option>
                                                <option value="big"
                                                    {{ old('invoice_size', $setting->invoice_size ?? 'big') == 'big' ? 'selected' : '' }}>
                                                    Big Size Invoice</option>
                                            </select>
                                        </div>
                                    </div>
                                     <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>Send Mail</label>
                                            <select id="send_mail" name="send_mail" class="form-select">
                                                <option value="1">On</option>
                                                <option value="0">Off</option>
                                            </select>
                                        </div>
                                    </div>

                                </div>

                                {{-- Wizard nav for step 2 --}}
                                <div class="wizard-nav-btns" style="display:none;">
                                    <button type="button" class="btn btn-wizard-back" data-wizard-back="1">Back</button>
                                    <button type="button" class="btn btn-wizard-next" data-wizard-next="3">Next</button>
                                </div>
                            </div>{{-- /panel 2 --}}

                            {{-- ══════════════ STEP 3 – Media & Delivery ══════════════ --}}
                            <div class="shop-wizard-panel col-12 px-0" id="shopWizardPanel3">
                                <div class="row">
                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>Shop Logo</label>
                                            <div class="image-upload">
                                                <input type="file" id="logo" accept="image/*">
                                                <div class="image-uploads">
                                                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/upload.svg' }}"
                                                        alt="img">
                                                    <h4>Drag and drop a file to upload</h4>
                                                </div>
                                            </div>
                                            <img id="logo_preview" src="" alt="Logo Preview"
                                                style="display:none; max-width: 100px; margin-top: 10px;">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>Shop Favicon</label>
                                            <div class="image-upload">
                                                <input type="file" id="favicon" accept="image/*">
                                                <div class="image-uploads">
                                                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/upload.svg' }}"
                                                        alt="img">
                                                    <h4>Drag and drop a file to upload</h4>
                                                </div>
                                            </div>
                                            <img id="favicon_preview" src="" alt="Favicon Preview"
                                                style="display:none; max-width: 50px; margin-top: 10px;">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>Shop QR Code Image</label>
                                            <div class="image-upload">
                                                <input type="file" id="qr_code" accept="image/*">
                                                <div class="image-uploads">
                                                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/upload.svg' }}"
                                                        alt="img">
                                                    <h4>Drag and drop a file to upload</h4>
                                                </div>
                                            </div>
                                            <img id="qr_preview" src="" alt="QR Preview"
                                                style="display:none; max-width: 50px; margin-top: 10px;">
                                        </div>
                                    </div>

                                   
                                   

                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>Financial Year</label>
                                            <select id="financial_year" name="financial_year" class="form-select">
                                                <option value="1">On</option>
                                                <option value="0">Off</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-6">
                                        <div class="form-group">
                                            <label>TDS Apply</label>
                                            <select id="tds_apply" name="tds_apply" class="form-select">
                                                <option value="1">On</option>
                                                <option value="0">Off</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-12 col-6">
                                        <div class="form-group">
                                            <label>Customer WhatsApp Message</label>
                                            <select id="customer_whatsapp_message" name="customer_whatsapp_message"
                                                class="form-select">
                                                <option value="1">On</option>
                                                <option value="0">Off</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-12 col-6">
                                        <div class="form-group">
                                            <label>Admin WhatsApp Message</label>
                                            <select id="admin_whatsapp_message" name="admin_whatsapp_message"
                                                class="form-select">
                                                <option value="1">On</option>
                                                <option value="0">Off</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- Wizard nav for step 3 (has Submit) --}}
                                <div class="wizard-nav-btns" style="display:none;">
                                    <button type="button" class="btn btn-wizard-back" data-wizard-back="2">Back</button>
                                    <a href="javascript:void(0);" class="btn btn-wizard-submit"
                                        id="btn-setting-submit-wizard">Submit</a>
                                </div>
                            </div>{{-- /panel 3 --}}

                        </div>{{-- /.row --}}

                        {{-- Desktop submit (hidden on tablet via CSS) --}}
                        <div class="row desktop-submit">
                            <div class="col-lg-12">
                                <a href="javascript:void(0);" class="btn btn-submit me-2"
                                    id="btn-setting-submit">Submit</a>
                            </div>
                        </div>
                    </div>

                    <!-- ================= DELIVERY TERMS TAB ================= -->
                    <div class="tab-pane fade" id="delivery-terms" role="tabpanel" aria-labelledby="delivery-terms-tab">
                        <div class="row">
                            <div class="col-lg-6 col-sm-6">
                                <div class="form-group">
                                    <label>Delivery Terms &amp; Conditions (English)</label>
                                    <textarea id="terms_condition_eng" class="form-control" rows="10"
                                        placeholder="Enter English delivery terms and conditions"></textarea>
                                </div>
                            </div>

                            <div class="col-lg-6 col-sm-6">
                                <div class="form-group">
                                    <label>Delivery Terms &amp; Conditions (Gujarati)</label>
                                    <textarea id="terms_condition_guj" class="form-control" rows="10"
                                        placeholder="Enter Gujarati delivery terms and conditions"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-lg-12">
                                <a href="javascript:void(0);" class="btn btn-submit me-2"
                                    id="btn-setting-submit-delivery">Update</a>
                            </div>
                        </div>
                    </div>

                    <!-- ================= COMPANY RULES TAB ================= -->
                    <div class="tab-pane fade" id="company-rules" role="tabpanel" aria-labelledby="rules-tab">
                        <div class="row">
                            <div class="col-lg-3 col-sm-6">
                                <div class="form-group">
                                    <label>Working Hours (per day)<span class="manitory">*</span></label>
                                    <input type="number" step="0.5" id="working_hours" class="form-control" placeholder="e.g. 8.5">
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6">
                                <div class="form-group">
                                    <label>Sunday Off?</label>
                                    <div class="d-flex align-items-center gap-3 mt-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="sunday_off"
                                                id="sunday_yes" value="yes">
                                            <label class="form-check-label" for="sunday_yes">Yes</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="sunday_off"
                                                id="sunday_no" value="no" checked>
                                            <label class="form-check-label" for="sunday_no">No</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6">
                                <div class="form-group">
                                    <label>Saturday Off?</label>
                                    <div class="d-flex align-items-center gap-3 mt-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="saturday_off"
                                                id="saturday_yes" value="yes">
                                            <label class="form-check-label" for="saturday_yes">Yes</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="saturday_off"
                                                id="saturday_no" value="no" checked>
                                            <label class="form-check-label" for="saturday_no">No</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6">
                                <div class="form-group">
                                    <label>Grace Period (minutes)<span class="manitory">*</span></label>
                                    <input type="number" id="grace_period" class="form-control" placeholder="e.g. 30" min="0">
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6">
                                <div class="form-group">
                                    <label>Lunch Break</label>
                                    <input type="number" id="lunch_break" class="form-control" placeholder="Enter minutes">
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6">
                                <div class="form-group">
                                    <label>Company Open Time<span class="manitory">*</span></label>
                                    <input type="time" id="open_time" class="form-control">
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6">
                                <div class="form-group">
                                    <label>Company Close Time<span class="manitory">*</span></label>
                                    <input type="time" id="close_time" class="form-control">
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6">
                                <div class="form-group">
                                    <label>Overtime After (hours)</label>
                                    <input type="number" step="0.5" id="overtime_after_hours" class="form-control" placeholder="e.g. 9">
                                    <small class="text-muted">Hours after which overtime starts (e.g. 9 for 8h shift + 1h grace)</small>
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6">
                                <div class="form-group">
                                    <label>Tax Deduction Amount</label>
                                    <input type="number" step="0.01" id="tax_deduction_amount" class="form-control" placeholder="e.g. 200.00">
                                    <small class="text-muted">Tax deduction will be applied only when the salary amount exceeds this value.</small>
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6">
                                <div class="form-group">
                                    <label>Salary Amount Exceeds</label>
                                    <input type="number" step="0.01" id="salary_amount_exceeds" class="form-control" placeholder="e.g. 13999.98">
                                </div>
                            </div>

                            <div class="col-lg-12 mt-4">
                                <h5>Security Settings</h5>
                                <hr>
                            </div>

                            <div class="col-lg-6 col-sm-12">
                                <div class="form-group">
                                    <label>Location Check on Login</label>
                                    <div class="d-flex align-items-center gap-3 mt-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="location_check_enabled"
                                                id="location_check_on" value="1">
                                            <label class="form-check-label" for="location_check_on">ON (Check GPS location)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="location_check_enabled"
                                                id="location_check_off" value="0" checked>
                                            <label class="form-check-label" for="location_check_off">OFF (Allow anywhere)</label>
                                        </div>
                                    </div>
                                    <small class="text-muted">If ON, staff must be within the radius of their assigned office location to log in and clock in.</small>
                                </div>
                            </div>

                            <div class="col-lg-12 mt-3">
                                <a href="javascript:void(0);" class="btn btn-submit me-2" id="saveCompanyRules">Save
                                    Rules</a>
                            </div>
                        </div>
                    </div>

                    <!-- ================= DASHBOARD TAB ================= -->
                    <div class="tab-pane fade" id="dashboard-settings" role="tabpanel" aria-labelledby="dashboard-tab">
                        <!-- Main Dashboard Sections -->
                        <div class="row mb-4">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>CRM Section On Dashboard</label>
                                    <select id="crm_section_enabled" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                    <small class="text-muted d-block mt-1">When disabled, the entire CRM section is hidden from the dashboard.</small>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>HR Portal Section On Dashboard</label>
                                    <select id="hr_section_enabled" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                    <small class="text-muted d-block mt-1">When disabled, the staff, attendance, and salary section is hidden.</small>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>ERP Section On Dashboard</label>
                                    <select id="erp_section_enabled" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                    <small class="text-muted d-block mt-1">When disabled, the entire ERP section is hidden from the dashboard.</small>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- CRM Dashboard Subsections -->
                        <h5 class="mb-3">CRM Dashboard Subsections</h5>
                        <p class="text-muted mb-3"><small>Control visibility of individual CRM dashboard components</small></p>
                        <div class="row mb-4">
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Lead Pipeline Box</label>
                                    <select id="crm_lead_pipeline_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Conversion Box</label>
                                    <select id="crm_conversion_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Follow-up Lead Box</label>
                                    <select id="crm_followup_lead_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Meeting Momentum Box</label>
                                    <select id="crm_meeting_momentum_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Lead Status Mix Chart</label>
                                    <select id="crm_lead_status_mix_chart" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>CRM Activity Trend Chart</label>
                                    <select id="crm_activity_trend_chart" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Pipeline Quality Table</label>
                                    <select id="crm_pipeline_quality_table" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Recent Leads Table</label>
                                    <select id="crm_recent_leads_table" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Next 7 Days Table</label>
                                    <select id="crm_next_7_days_table" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- HR Dashboard Subsections -->
                        <h5 class="mb-3">HR Dashboard Subsections</h5>
                        <p class="text-muted mb-3"><small>Control visibility of individual HR dashboard components</small></p>
                        <div class="row mb-4">
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Staff Strength Box</label>
                                    <select id="hr_staff_strength_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Active Staff Box</label>
                                    <select id="hr_active_staff_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Monthly Attendance Box</label>
                                    <select id="hr_monthly_attendance_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Personal Progress Box</label>
                                    <select id="hr_personal_progress_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>7 Day Attendance Pattern Chart</label>
                                    <select id="hr_7day_attendance_chart" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Salary Payout Trend Chart</label>
                                    <select id="hr_salary_payout_trend_chart" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Payroll Snapshot Table</label>
                                    <select id="hr_payroll_snapshot_table" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Attendance Watch Table</label>
                                    <select id="hr_attendance_watch_table" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Payroll Status Table</label>
                                    <select id="hr_payroll_status_table" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- ERP Dashboard Subsections -->
                        <h5 class="mb-3">ERP Dashboard Subsections</h5>
                        <p class="text-muted mb-3"><small>Control visibility of individual ERP dashboard components</small></p>
                        <div class="row mb-4">
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Total Sales Amount Box</label>
                                    <select id="erp_total_sales_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Total Purchase Amount Box</label>
                                    <select id="erp_total_purchase_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Total Expense Amount Box</label>
                                    <select id="erp_total_expense_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Sales Invoice Count Box</label>
                                    <select id="erp_sales_invoice_count_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Purchase Invoice Count Box</label>
                                    <select id="erp_purchase_invoice_count_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Customers Count Box</label>
                                    <select id="erp_customers_count_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Vendors Count Box</label>
                                    <select id="erp_vendors_count_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Sales Chart</label>
                                    <select id="erp_sales_chart" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Purchase Chart</label>
                                    <select id="erp_purchase_chart" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Latest Sales Table</label>
                                    <select id="erp_latest_sales_table" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 dashboard-subsection-col">
                                <div class="form-group">
                                    <label>Latest Purchases Table</label>
                                    <select id="erp_latest_purchases_table" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12 mt-3">
                            <a href="javascript:void(0);" class="btn btn-submit me-2" id="saveDashboardSettings">Save
                                Dashboard Settings</a>
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
            function initDashboardMobileSubsectionTabs() {
                const $dashboardRow = $("#dashboard-settings > .row");
                if (!$dashboardRow.length) return;

                const findHeadingPanel = (title) => $dashboardRow.children(".col-lg-12").filter(function() {
                    return $(this).find("h5").first().text().trim() === title;
                }).first();

                const sections = [
                    { key: "crm", heading: findHeadingPanel("CRM Dashboard Subsections") },
                    { key: "hr", heading: findHeadingPanel("HR Dashboard Subsections") },
                    { key: "erp", heading: findHeadingPanel("ERP Dashboard Subsections") },
                ];
                const $savePanel = $("#saveDashboardSettings").closest(".col-lg-12");

                sections.forEach((section, index) => {
                    if (!section.heading.length) return;
                    const $nextBoundary = sections[index + 1]?.heading?.length ? sections[index + 1].heading : $savePanel;
                    section.items = section.heading.add(section.heading.nextUntil($nextBoundary));
                    section.items.addClass(`dashboard-mobile-panel dashboard-mobile-panel-${section.key}`);
                });

                function activateDashboardSubsection(key) {
                    $(".dashboard-mobile-subsection-tab").removeClass("active");
                    $(`.dashboard-mobile-subsection-tab[data-dashboard-subsection="${key}"]`).addClass("active");
                    $(".dashboard-mobile-panel").removeClass("active");
                    $(`.dashboard-mobile-panel-${key}`).addClass("active");
                }

                activateDashboardSubsection("crm");

                $(document).on("click", ".dashboard-mobile-subsection-tab", function() {
                    activateDashboardSubsection($(this).data("dashboard-subsection"));
                });
            }

            initDashboardMobileSubsectionTabs();



            var authToken = localStorage.getItem("authToken");
            const ImagePath = "{{ env('ImagePath') }}";
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            const $generalSettingsBtn = $("#btn-setting-submit");
            const $addressField = $("#address");
            const $officeLatitudeField  = $("#office_latitude");
            const $officeLongitudeField = $("#office_longitude");
            const $coordinateLookupStatus = $("#coordinateLookupStatus");
            const generalSettingsBtnDefaultHtml = $generalSettingsBtn.html();
            let lastGeocodedAddress = "";
            let geocodeDebounceTimer = null;

            function toggleGeneralSettingsBtnLoading(isLoading) {
                if (isLoading) {
                    $generalSettingsBtn
                        .addClass("disabled")
                        .attr("aria-disabled", "true")
                        .html(
                            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...'
                        );
                    $('.btn-submit-mobile')
                        .addClass("disabled")
                        .attr("aria-disabled", "true")
                        .html(
                            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...'
                        );
                } else {
                    $generalSettingsBtn
                        .removeClass("disabled")
                        .removeAttr("aria-disabled")
                        .html(generalSettingsBtnDefaultHtml);
                    $('.btn-submit-mobile')
                        .removeClass("disabled")
                        .removeAttr("aria-disabled")
                        .html('Submit');
                }
            }

            let url = "{{ route('general-settings.show') }}";
            if (selectedSubAdminId) {
                url += "?selectedSubAdminId=" + selectedSubAdminId;
            }

            function loadGeneralSettings() {
                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        const settings = response.settings;

                        // 🏢 Company Rules
                        $("#working_hours").val(settings.working_hours ?? '');
                        $("#grace_period").val(settings.grace_period ?? '');
                        $("#lunch_break").val(settings.lunch_break ?? '');
                        $("#open_time").val(settings.open_time ? settings.open_time.substring(0, 5) :
                            '');
                        $("#close_time").val(settings.close_time ? settings.close_time.substring(0, 5) :
                            '');
                        $("#overtime_after_hours").val(settings.overtime_after_hours ?? '');

                        // Location Check Enabled
                        if (settings.location_check_enabled) {
                            $("#location_check_on").prop("checked", true);
                        } else {
                            $("#location_check_off").prop("checked", true);
                        }

                        // Sunday Off
                        if (settings.sunday_off === "yes") {
                            $("#sunday_yes").prop("checked", true);
                        } else {
                            $("#sunday_no").prop("checked", true);
                        }

                        // Saturday Off
                        if (settings.saturday_off === "yes") {
                            $("#saturday_yes").prop("checked", true);
                        } else {
                            $("#saturday_no").prop("checked", true);
                        }

                        // 🏦 General Info (already working)
                        $("#low_stock").val(settings.low_stock);
                        $("#shop_name").val(settings.name);
                        $("#gst_num").val(settings.gst_num);
                        $("#cin_no").val(settings.cin_no);
                        $("#email").val(settings.email);
                        $("#phone").val(settings.phone);
                        $("#state_code").val(settings.state_code);
                        $("#terms_condition_eng").val(settings.terms_condition_eng);
                        $("#terms_condition_guj").val(settings.terms_condition_guj);
                        $addressField.val(settings.address);
                        $officeLatitudeField.val(settings.office_latitude ?? '');
                        $officeLongitudeField.val(settings.office_longitude ?? '');
                        $("#office_radius").val(settings.office_radius ?? 200);
                        lastGeocodedAddress = (settings.address || '').trim();
                        $("#bank_name").val(settings.bank_name);
                        $("#branch").val(settings.branch);
                        $("#ac_no").val(settings.ac_no);
                        $("#ifsc_code").val(settings.ifsc_code);
                        $("#invoice_size").val(settings.invoice_size || 'big');
                        $("#tax_deduction_amount").val(settings.tax_deduction_amount ?? '');
                        $("#salary_amount_exceeds").val(settings.salary_amount_exceeds ?? '');
                        $("#send_mail").val(
                            settings.send_mail === null || settings.send_mail === undefined
                            ? '1'
                            : String(Number(settings.send_mail))
                        );
                        $("#financial_year").val(
                            settings.financial_year === null || settings.financial_year === undefined
                            ? '1'
                            : String(Number(settings.financial_year))
                        );
                        $("#tds_apply").val(
                            settings.tds_apply === null || settings.tds_apply === undefined
                            ? '1'
                            : String(Number(settings.tds_apply))
                        );
                        $("#crm_section_enabled").val(normalizeDashboardSetting(
                            settings.crm_section_enabled,
                            settings.show_crm_dashboard,
                            'Enable'
                        ));
                        $("#hr_section_enabled").val(normalizeDashboardSetting(
                            settings.hr_section_enabled,
                            settings.show_hr_dashboard,
                            'Enable'
                        ));
                        $("#erp_section_enabled").val(normalizeDashboardSetting(
                            settings.erp_section_enabled,
                            settings.show_erp_dashboard,
                            'Enable'
                        ));

                        // CRM Subsection Settings
                        $("#crm_lead_pipeline_box").val(normalizeDashboardSetting(
                            settings.crm_lead_pipeline_box,
                            settings.show_crm_lead_pipeline,
                            'Enable'
                        ));
                        $("#crm_conversion_box").val(normalizeDashboardSetting(
                            settings.crm_conversion_box,
                            settings.show_crm_conversion,
                            'Enable'
                        ));
                        $("#crm_followup_lead_box").val(normalizeDashboardSetting(
                            settings.crm_followup_lead_box,
                            settings.show_crm_followup_load,
                            'Enable'
                        ));
                        $("#crm_meeting_momentum_box").val(normalizeDashboardSetting(
                            settings.crm_meeting_momentum_box,
                            settings.show_crm_meeting_momentum,
                            'Enable'
                        ));
                        $("#crm_lead_status_mix_chart").val(normalizeDashboardSetting(
                            settings.crm_lead_status_mix_chart,
                            settings.show_crm_lead_status_mix,
                            'Enable'
                        ));
                        $("#crm_activity_trend_chart").val(normalizeDashboardSetting(
                            settings.crm_activity_trend_chart,
                            settings.show_crm_activity_trend,
                            'Enable'
                        ));
                        $("#crm_pipeline_quality_table").val(normalizeDashboardSetting(
                            settings.crm_pipeline_quality_table,
                            settings.show_crm_pipeline_quality,
                            'Enable'
                        ));
                        $("#crm_recent_leads_table").val(normalizeDashboardSetting(
                            settings.crm_recent_leads_table,
                            settings.show_crm_recent_leads,
                            'Enable'
                        ));
                        $("#crm_next_7_days_table").val(normalizeDashboardSetting(
                            settings.crm_next_7_days_table,
                            settings.show_crm_next_7_days,
                            'Enable'
                        ));

                        // HR Subsection Settings
                        $("#hr_staff_strength_box").val(normalizeDashboardSetting(
                            settings.hr_staff_strength_box,
                            settings.show_hr_staff_strength,
                            'Enable'
                        ));
                        $("#hr_active_staff_box").val(normalizeDashboardSetting(
                            settings.hr_active_staff_box,
                            settings.show_hr_active_staff,
                            'Enable'
                        ));
                        $("#hr_monthly_attendance_box").val(normalizeDashboardSetting(
                            settings.hr_monthly_attendance_box,
                            settings.show_hr_monthly_attendance,
                            'Enable'
                        ));
                        $("#hr_personal_progress_box").val(normalizeDashboardSetting(
                            settings.hr_personal_progress_box,
                            settings.show_hr_personal_progress,
                            'Enable'
                        ));
                        $("#hr_7day_attendance_chart").val(normalizeDashboardSetting(
                            settings.hr_7day_attendance_chart,
                            settings.show_hr_attendance_pattern,
                            'Enable'
                        ));
                        $("#hr_salary_payout_trend_chart").val(normalizeDashboardSetting(
                            settings.hr_salary_payout_trend_chart,
                            settings.show_hr_salary_payroll_trend,
                            'Enable'
                        ));
                        $("#hr_payroll_snapshot_table").val(normalizeDashboardSetting(
                            settings.hr_payroll_snapshot_table,
                            settings.show_hr_payroll_snapshot,
                            'Enable'
                        ));
                        $("#hr_attendance_watch_table").val(normalizeDashboardSetting(
                            settings.hr_attendance_watch_table,
                            settings.show_hr_attendance_watch,
                            'Enable'
                        ));
                        $("#hr_payroll_status_table").val(normalizeDashboardSetting(
                            settings.hr_payroll_status_table,
                            settings.show_hr_payroll_status,
                            'Enable'
                        ));

                        // ERP Subsection Settings
                        $("#erp_total_sales_box").val(normalizeDashboardSetting(
                            settings.erp_total_sales_box,
                            settings.show_erp_total_sales,
                            'Enable'
                        ));
                        $("#erp_total_purchase_box").val(normalizeDashboardSetting(
                            settings.erp_total_purchase_box,
                            settings.show_erp_total_purchase,
                            'Enable'
                        ));
                        $("#erp_total_expense_box").val(normalizeDashboardSetting(
                            settings.erp_total_expense_box,
                            settings.show_erp_total_expense,
                            'Enable'
                        ));
                        $("#erp_sales_invoice_count_box").val(normalizeDashboardSetting(
                            settings.erp_sales_invoice_count_box,
                            settings.show_erp_sales_invoice_count,
                            'Enable'
                        ));
                        $("#erp_purchase_invoice_count_box").val(normalizeDashboardSetting(
                            settings.erp_purchase_invoice_count_box,
                            settings.show_erp_purchase_invoice_count,
                            'Enable'
                        ));
                        $("#erp_customers_count_box").val(normalizeDashboardSetting(
                            settings.erp_customers_count_box,
                            settings.show_erp_customers_count,
                            'Enable'
                        ));
                        $("#erp_vendors_count_box").val(normalizeDashboardSetting(
                            settings.erp_vendors_count_box,
                            settings.show_erp_vendors_count,
                            'Enable'
                        ));
                        $("#erp_sales_chart").val(normalizeDashboardSetting(
                            settings.erp_sales_chart,
                            settings.show_erp_sales_chart,
                            'Enable'
                        ));
                        $("#erp_purchase_chart").val(normalizeDashboardSetting(
                            settings.erp_purchase_chart,
                            settings.show_erp_purchase_chart,
                            'Enable'
                        ));
                        $("#erp_latest_sales_table").val(normalizeDashboardSetting(
                            settings.erp_latest_sales_table,
                            settings.show_erp_recent_sales,
                            'Enable'
                        ));
                        $("#erp_latest_purchases_table").val(normalizeDashboardSetting(
                            settings.erp_latest_purchases_table,
                            settings.show_erp_recent_purchases,
                            'Enable'
                        ));
                        if (settings.currency_position) {
                            $("#currency_position").val(settings.currency_position).trigger("change");
                        }
                        $("#currency_symbol").val(settings.currency_symbol);

                        // WhatsApp & Reminder Settings
                        $("#customer_whatsapp_message").val(
                            settings.customer_whatsapp_message === null || settings.customer_whatsapp_message === undefined
                            ? '1'
                            : String(Number(settings.customer_whatsapp_message))
                        );
                        $("#admin_whatsapp_message").val(
                            settings.admin_whatsapp_message === null || settings.admin_whatsapp_message === undefined
                            ? '1'
                            : String(Number(settings.admin_whatsapp_message))
                        );
                        $("#appointment_reminder_hours_before").val(settings.appointment_reminder_hours_before ?? 3);

                        // Logos and Images
                        if (settings.logo) {
                            $("#logo_preview").attr("src", ImagePath + '/storage/' + settings.logo)
                                .show();
                        }
                        if (settings.favicon) {
                            $("#favicon_preview").attr("src", ImagePath + '/storage/' + settings
                                .favicon).show();
                        }
                        if (settings.qr_code) {
                            $("#qr_preview").attr("src", ImagePath + '/storage/' + settings.qr_code)
                                .show();
                        }

                        syncDashboardSectionState();
                    }
                });
            }


            loadGeneralSettings(); // Load on page load

            function normalizeDashboardSetting(currentValue, legacyValue, defaultValue) {
                if (currentValue === "Enable" || currentValue === "Disable") {
                    return currentValue;
                }

                if (legacyValue !== null && legacyValue !== undefined && legacyValue !== "") {
                    return String(Number(legacyValue)) === "1" ? "Enable" : "Disable";
                }

                return defaultValue;
            }

            const dashboardMainSectionMap = {
                crm: "#crm_section_enabled",
                hr: "#hr_section_enabled",
                erp: "#erp_section_enabled"
            };
            const dashboardSubsectionMap = {
                crm: "#crm_lead_pipeline_box, #crm_conversion_box, #crm_followup_lead_box, #crm_meeting_momentum_box, #crm_lead_status_mix_chart, #crm_activity_trend_chart, #crm_pipeline_quality_table, #crm_recent_leads_table, #crm_next_7_days_table",
                hr: "#hr_staff_strength_box, #hr_active_staff_box, #hr_monthly_attendance_box, #hr_personal_progress_box, #hr_7day_attendance_chart, #hr_salary_payout_trend_chart, #hr_payroll_snapshot_table, #hr_attendance_watch_table, #hr_payroll_status_table",
                erp: "#erp_total_sales_box, #erp_total_purchase_box, #erp_total_expense_box, #erp_sales_invoice_count_box, #erp_purchase_invoice_count_box, #erp_customers_count_box, #erp_vendors_count_box, #erp_sales_chart, #erp_purchase_chart, #erp_latest_sales_table, #erp_latest_purchases_table"
            };

            function syncDashboardSectionState() {
                Object.keys(dashboardMainSectionMap).forEach(function(section) {
                    const isEnabled = $(dashboardMainSectionMap[section]).val() === "Enable";
                    $(dashboardSubsectionMap[section]).prop("disabled", !isEnabled);
                });
            }

            function setDashboardSubsectionState(sectionKey, value) {
                $(dashboardSubsectionMap[sectionKey]).val(value);
            }

            function applyMainSectionState(sectionKey) {
                const isEnabled = $(dashboardMainSectionMap[sectionKey]).val() === "Enable";
                setDashboardSubsectionState(sectionKey, isEnabled ? "Enable" : "Disable");
                syncDashboardSectionState();
            }

            $("#crm_section_enabled, #hr_section_enabled, #erp_section_enabled").on("change", function() {
                const id = $(this).attr("id");
                const sectionKey = id === "crm_section_enabled" ? "crm" : (id === "hr_section_enabled" ? "hr" : "erp");
                applyMainSectionState(sectionKey);
            });

            // =====================================================================
            // GEOCODING ENGINE  (Nominatim + Photon — free, no API key)
            // =====================================================================

            function setCoordinateStatus(message, type) {
                type = type || 'muted';
                $coordinateLookupStatus
                    .removeClass('text-muted text-success text-danger text-warning')
                    .addClass('text-' + type)
                    .text(message || '');
            }

            function fillCoordinateFields(coordinates) {
                $officeLatitudeField.val(coordinates && coordinates.latitude != null ? coordinates.latitude : '');
                $officeLongitudeField.val(coordinates && coordinates.longitude != null ? coordinates.longitude : '');
            }

            // Scoring helper — how many address keywords appear in Nominatim display_name
            function scoreResult(result, keywords) {
                var name = (result.display_name || '').toLowerCase();
                var score = 0;
                keywords.forEach(function(kw) {
                    if (kw.length > 2 && name.indexOf(kw.toLowerCase()) !== -1) score++;
                });
                var addr = result.address || {};
                var pc = addr.postcode || '';
                keywords.forEach(function(kw) {
                    if (/^\d{6}$/.test(kw) && pc === kw) score += 10;
                });
                return score;
            }

            function extractPincode(address) {
                var m = address.match(/\b(\d{6})\b/);
                return m ? m[1] : '';
            }

            function extractKeywords(address) {
                return address.split(',').map(function(p) { return p.trim(); }).filter(Boolean);
            }

            function extractBuildingName(address) {
                var SKIP = [
                    /^(shop|office|flat|unit|room|plot|door|house|floor|gf|ff)\b/i,
                    /^\d+(st|nd|rd|th)?\s*(floor|fl)?\.?$/i,
                    /^[a-z]?\d+$/i,
                    /^\d+[-–\/]\d+$/i
                ];
                var parts = address.split(',').map(function(p){ return p.trim(); }).filter(Boolean);
                for (var i = 0; i < parts.length; i++) {
                    var p = parts[i].replace(/^\d+[,\s]*/, '').trim();
                    var skip = false;
                    for (var s = 0; s < SKIP.length; s++) {
                        if (SKIP[s].test(parts[i]) || SKIP[s].test(p)) { skip = true; break; }
                    }
                    if (!skip && p.length > 3 && !/^\d/.test(p)) return p;
                }
                return '';
            }

            function extractCity(address) {
                var parts = address.split(',').map(function(p){ return p.trim().replace(/[-–]\s*\d{6}/, '').trim(); }).filter(Boolean);
                var states = ['gujarat','maharashtra','rajasthan','karnataka','tamil nadu','andhra pradesh',
                    'telangana','uttar pradesh','madhya pradesh','west bengal','kerala','punjab','haryana',
                    'bihar','odisha','goa','delhi','assam','himachal pradesh','uttarakhand'];
                for (var i = parts.length - 1; i >= 0; i--) {
                    var lower = parts[i].toLowerCase();
                    if (/^\d{6}$/.test(parts[i])) continue;
                    if (states.indexOf(lower) !== -1) continue;
                    if (/^india$/i.test(parts[i])) continue;
                    if (parts[i].length > 2) return parts[i];
                }
                return '';
            }

            function nominatimFetch(q, keywords) {
                var url = 'https://nominatim.openstreetmap.org/search?'
                    + 'q=' + encodeURIComponent(q)
                    + '&format=json&addressdetails=1&countrycodes=in&limit=10&accept-language=en';
                return fetch(url, { headers: { 'User-Agent': 'inventory-billing/1.0' } })
                    .then(function(r) { return r.ok ? r.json() : []; })
                    .then(function(data) {
                        if (!data || !data.length) return null;
                        var sorted = data.slice().sort(function(a, b) {
                            return scoreResult(b, keywords) - scoreResult(a, keywords);
                        });
                        var best = sorted[0];
                        return {
                            lat: parseFloat(best.lat).toFixed(7),
                            lon: parseFloat(best.lon).toFixed(7),
                            name: best.display_name.substring(0, 100),
                            attemptStr: 'Nominatim'
                        };
                    })
                    .catch(function() { return null; });
            }

            function photonFetch(q, keywords) {
                var url = 'https://photon.komoot.io/api/?q=' + encodeURIComponent(q)
                    + '&limit=5&lang=en&bbox=68.0,8.0,97.5,37.6';
                return fetch(url, { headers: { 'User-Agent': 'inventory-billing/1.0' } })
                    .then(function(r) { return r.ok ? r.json() : {}; })
                    .then(function(data) {
                        var features = (data && data.features) ? data.features : [];
                        if (!features.length) return null;
                        var scored = features.map(function(f) {
                            var props = f.properties || {};
                            var display = [props.name, props.street, props.city, props.state, props.postcode]
                                .filter(Boolean).join(', ');
                            return { feature: f, display: display, score: scoreResult({ display_name: display, address: { postcode: props.postcode || '' } }, keywords) };
                        }).sort(function(a, b) { return b.score - a.score; });
                        var best = scored[0];
                        var coords = best.feature.geometry.coordinates; // [lon, lat]
                        return {
                            lat: parseFloat(coords[1]).toFixed(7),
                            lon: parseFloat(coords[0]).toFixed(7),
                            name: best.display.substring(0, 100),
                            attemptStr: 'Photon'
                        };
                    })
                    .catch(function() { return null; });
            }

            function geocodeWaterfall(address) {
                var keywords = extractKeywords(address);
                var pincode  = extractPincode(address);
                var building = extractBuildingName(address);
                var city     = extractCity(address);

                var cleanParts = address.split(',')
                    .map(function(p) { return p.trim(); })
                    .filter(function(p) {
                        if (!p || p.length < 2) return false;
                        if (/^(nr\.?|near|behind|opp\.?|opposite|above|below|beside)\b/i.test(p)) return false;
                        if (/^[A-Za-z]?\/?[0-9]+/.test(p) && p.length < 8) return false;
                        return true;
                    });
                var shortQuery = cleanParts.slice(-4).join(', ');

                var p1 = pincode ? nominatimFetch(pincode + ', India', [pincode]) : Promise.resolve(null);
                var p2 = function() { return shortQuery ? nominatimFetch(shortQuery, keywords) : Promise.resolve(null); };
                var p3 = function() { return (building && city && pincode) ? nominatimFetch(building + ', ' + city + ', ' + pincode + ', India', keywords) : Promise.resolve(null); };
                var p4 = function() { return (building && city) ? nominatimFetch(building + ', ' + city + ', India', keywords) : Promise.resolve(null); };
                var p5 = function() { return nominatimFetch(address, keywords); };

                return p1
                    .then(function(r) { return r || p2(); })
                    .then(function(r) { return r || p3(); })
                    .then(function(r) { return r || p4(); })
                    .then(function(r) { return r || p5(); });
            }

            // "Get GPS from Address" button
            $('#btnFetchCoordinates').on('click', function() {
                var address = $addressField.val().trim();
                if (!address) {
                    setCoordinateStatus('Please enter an address first.', 'danger');
                    return;
                }
                var $btn = $(this);
                $btn.prop('disabled', true).text('Searching…');
                setCoordinateStatus('🔍 Searching…');

                var keywords = extractKeywords(address);
                var pincode  = extractPincode(address);
                var building = extractBuildingName(address);
                var photonQ  = building ? (building + ', ' + (extractCity(address) || 'India')) : address;

                photonFetch(photonQ, keywords)
                    .then(function(result) {
                        return result || geocodeWaterfall(address);
                    })
                    .then(function(result) {
                        if (!result) {
                            setCoordinateStatus('❌ Address not found. Try entering the PIN code only (e.g. 395009).', 'danger');
                            return;
                        }
                        $officeLatitudeField.val(result.lat);
                        $officeLongitudeField.val(result.lon);
                        lastGeocodedAddress = address;
                        var via = result.attemptStr ? ' [via ' + result.attemptStr + ']' : '';
                        var displayLower = (result.name || '').toLowerCase();
                        var keywordHit = keywords.some(function(kw) {
                            return kw.length > 3 && displayLower.indexOf(kw.toLowerCase()) !== -1;
                        });
                        var pincodeOk = !pincode || displayLower.indexOf(pincode) !== -1;
                        if (!pincodeOk && !keywordHit) {
                            setCoordinateStatus('⚠️ Possible mismatch — verify manually: ' + result.name + via, 'warning');
                        } else {
                            setCoordinateStatus('✅ Found' + via + ': ' + result.name, 'success');
                        }
                    })
                    .catch(function() {
                        setCoordinateStatus('❌ Network error. Check your internet connection.', 'danger');
                    })
                    .finally(function() {
                        $btn.prop('disabled', false).text('Get GPS from Address');
                    });
            });

            // Show Image Preview
            function previewImage(input, previewId) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $(previewId).attr("src", e.target.result).show();
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#logo").change(function() {
                previewImage(this, "#logo_preview");
            });

            $("#favicon").change(function() {
                previewImage(this, "#favicon_preview");
            });

            $("#qr_code").change(function() {
                previewImage(this, "#qr_preview");
            });

            // Update General Settings
                $("#btn-setting-submit").on("click", function(e) {
                    e.preventDefault(); // prevent form submission if there are errors

                let lowStock = parseFloat($('#low_stock').val()) || 0;

                if (lowStock < 0) {
                    // console.log('asd');

                    $("#lowStockError").removeClass("d-none"); // show error
                    $('#low_stock').val(0); // reset to 0
                    return false;
                } else {
                    $("#lowStockError").addClass("d-none"); // hide error
                }

                $(".text-danger").remove(); // clear previous errors

                let hasError = false;
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                const fields = [{
                        id: "low_stock",
                        name: "Low Stock Warning Quantity"
                    },
                    {
                        id: "shop_name",
                        name: "Shop Name"
                    },
                    {
                        id: "email",
                        name: "Email"
                    },
                    {
                        id: "phone",
                        name: "Phone"
                    },
                    {
                        id: "address",
                        name: "Address"
                    },
                    // { id: "bank_name", name: "Bank Name" },
                    // { id: "branch", name: "Branch" },
                    // { id: "ac_no", name: "A/C No." },
                    // { id: "ifsc_code", name: "IFSC Code" },
                    {
                        id: "currency_position",
                        name: "Currency Position"
                    },
                    {
                        id: "currency_symbol",
                        name: "Currency Symbol"
                    },
                ];

                // Validate each required field
                fields.forEach(field => {
                    const value = $("#" + field.id).val();
                    if (!value) {
                        $("#" + field.id)
                            .after(`<div class="text-danger mt-1">${field.name} is required</div>`);
                        hasError = true;
                    }
                });



                // Check if logo and favicon are selected (optional: remove this if not mandatory)
                let logo = $("#logo")[0].files[0];
                let favicon = $("#favicon")[0].files[0];
                let qr_code = $("#qr_code")[0].files[0];

                if (hasError) return; // stop submission if errors found

                // Prepare FormData
                let formData = new FormData();
                formData.append("low_stock", $("#low_stock").val());
                formData.append("shop_name", $("#shop_name").val());
                formData.append("gst_num", $("#gst_num").val());
                formData.append("cin_no", $("#cin_no").val());
                formData.append("email", $("#email").val());
                formData.append("phone", $("#phone").val());
                formData.append("state_code", $("#state_code").val());
                formData.append("terms_condition_eng", $("#terms_condition_eng").val());
                formData.append("terms_condition_guj", $("#terms_condition_guj").val());
                formData.append("address", $addressField.val());
                formData.append("office_latitude", $officeLatitudeField.val());
                formData.append("office_longitude", $officeLongitudeField.val());
                formData.append("office_radius", $("#office_radius").val() || 200);
                formData.append("bank_name", $("#bank_name").val());
                formData.append("branch", $("#branch").val());
                formData.append("ac_no", $("#ac_no").val());
                formData.append("ifsc_code", $("#ifsc_code").val());
                formData.append("currency_position", $("#currency_position").val());
                formData.append("currency_symbol", $("#currency_symbol").val());
                formData.append("sunday_off", $("input[name='sunday_off']:checked").val() || "no");
                formData.append("saturday_off", $("input[name='saturday_off']:checked").val() || "no");
                formData.append("selectedSubAdminId", selectedSubAdminId);
                if (logo) formData.append("logo", logo);
                if (favicon) formData.append("favicon", favicon);
                if (qr_code) formData.append("qr_code", qr_code);
                formData.append("_token", "{{ csrf_token() }}");
                formData.append("invoice_size", $("#invoice_size").val());
                formData.append("tax_deduction_amount", $("#tax_deduction_amount").val());
                formData.append("salary_amount_exceeds", $("#salary_amount_exceeds").val());
                formData.append("send_mail", $("#send_mail").val());
                formData.append("financial_year", $("#financial_year").val());
                formData.append("tds_apply", $("#tds_apply").val());
                formData.append("customer_whatsapp_message", $("#customer_whatsapp_message").val());
                formData.append("admin_whatsapp_message", $("#admin_whatsapp_message").val());
                formData.append("appointment_reminder_hours_before", $("#appointment_reminder_hours_before").val() || 3);

                // Send AJAX
                $.ajax({
                    url: "{{ route('general-settings.update') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        toggleGeneralSettingsBtnLoading(true);
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        Swal.fire({
                            title: "Success!",
                            text: response.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#ff9f43",
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire("Error!", xhr.responseJSON.message, "error");
                    },
                    complete: function() {
                        toggleGeneralSettingsBtnLoading(false);
                    }
                });
                });

                $(document).on("click", "#btn-setting-submit-delivery", function(e) {
                    e.preventDefault();
                    $("#btn-setting-submit").trigger("click");
                });

                // ================== COMPANY RULES SAVE ==================
                $("#saveCompanyRules").on("click", function(e) {
                e.preventDefault();

                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                const authToken = localStorage.getItem("authToken");

                // Clear previous validation errors
                $(".text-danger").remove();

                let formData = new FormData();
                formData.append("working_hours", $("#working_hours").val());
                formData.append("sunday_off", $("input[name='sunday_off']:checked").val());
                formData.append("saturday_off", $("input[name='saturday_off']:checked").val());
                formData.append("grace_period", $("#grace_period").val());
                formData.append("lunch_break", $("#lunch_break").val());
                formData.append("open_time", $("#open_time").val());
                formData.append("close_time", $("#close_time").val());
                formData.append("overtime_after_hours", $("#overtime_after_hours").val());
                formData.append("location_check_enabled", $("input[name='location_check_enabled']:checked").val() || 0);
                formData.append("selectedSubAdminId", selectedSubAdminId);
                formData.append("_token", "{{ csrf_token() }}");

                $.ajax({
                    url: "{{ route('general-company-settings.update') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        Swal.fire({
                            title: "Success!",
                            text: response.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#ff9f43",
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;

                            // Display validation messages below each input
                            $.each(errors, function(key, value) {
                                const field = $("#" + key);
                                if (field.length) {
                                    field
                                        .closest(".form-group")
                                        .append('<div class="text-danger mt-1">' +
                                            value[0] + "</div>");
                                }
                            });
                        } else {
                            Swal.fire("Error!", xhr.responseJSON.message ||
                                "Something went wrong!", "error");
                        }
                    },
                });
            });

            // ================== DASHBOARD SETTINGS SAVE ==================
            $("#saveDashboardSettings").on("click", function(e) {
                e.preventDefault();

                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                const authToken = localStorage.getItem("authToken");

                let formData = new FormData();

                // Main sections
                formData.append("crm_section_enabled", $("#crm_section_enabled").val());
                formData.append("hr_section_enabled", $("#hr_section_enabled").val());
                formData.append("erp_section_enabled", $("#erp_section_enabled").val());

                // CRM Subsections
                formData.append("crm_lead_pipeline_box", $("#crm_lead_pipeline_box").val());
                formData.append("crm_conversion_box", $("#crm_conversion_box").val());
                formData.append("crm_followup_lead_box", $("#crm_followup_lead_box").val());
                formData.append("crm_meeting_momentum_box", $("#crm_meeting_momentum_box").val());
                formData.append("crm_lead_status_mix_chart", $("#crm_lead_status_mix_chart").val());
                formData.append("crm_activity_trend_chart", $("#crm_activity_trend_chart").val());
                formData.append("crm_pipeline_quality_table", $("#crm_pipeline_quality_table").val());
                formData.append("crm_recent_leads_table", $("#crm_recent_leads_table").val());
                formData.append("crm_next_7_days_table", $("#crm_next_7_days_table").val());

                // HR Subsections
                formData.append("hr_staff_strength_box", $("#hr_staff_strength_box").val());
                formData.append("hr_active_staff_box", $("#hr_active_staff_box").val());
                formData.append("hr_monthly_attendance_box", $("#hr_monthly_attendance_box").val());
                formData.append("hr_personal_progress_box", $("#hr_personal_progress_box").val());
                formData.append("hr_7day_attendance_chart", $("#hr_7day_attendance_chart").val());
                formData.append("hr_salary_payout_trend_chart", $("#hr_salary_payout_trend_chart").val());
                formData.append("hr_payroll_snapshot_table", $("#hr_payroll_snapshot_table").val());
                formData.append("hr_attendance_watch_table", $("#hr_attendance_watch_table").val());
                formData.append("hr_payroll_status_table", $("#hr_payroll_status_table").val());

                // ERP Subsections
                formData.append("erp_total_sales_box", $("#erp_total_sales_box").val());
                formData.append("erp_total_purchase_box", $("#erp_total_purchase_box").val());
                formData.append("erp_total_expense_box", $("#erp_total_expense_box").val());
                formData.append("erp_sales_invoice_count_box", $("#erp_sales_invoice_count_box").val());
                formData.append("erp_purchase_invoice_count_box", $("#erp_purchase_invoice_count_box").val());
                formData.append("erp_customers_count_box", $("#erp_customers_count_box").val());
                formData.append("erp_vendors_count_box", $("#erp_vendors_count_box").val());
                formData.append("erp_sales_chart", $("#erp_sales_chart").val());
                formData.append("erp_purchase_chart", $("#erp_purchase_chart").val());
                formData.append("erp_latest_sales_table", $("#erp_latest_sales_table").val());
                formData.append("erp_latest_purchases_table", $("#erp_latest_purchases_table").val());

                formData.append("selectedSubAdminId", selectedSubAdminId);
                formData.append("_token", "{{ csrf_token() }}");

                $.ajax({
                    url: "{{ route('general-settings.update') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        Swal.fire({
                            title: "Success!",
                            text: response.message || "Dashboard settings saved successfully!",
                            icon: "success",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#ff9f43",
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire("Error!", xhr.responseJSON.message || "Something went wrong!", "error");
                    },
                });
            });


            // ================== SHOP SETTINGS TABLET WIZARD ==================
            (function initShopWizard() {
                var TABLET_MIN = 0, TABLET_MAX = 1199;

                function isTablet() {
                    return window.innerWidth >= TABLET_MIN && window.innerWidth <= TABLET_MAX;
                }

                function goToStep(step) {
                    $(".shop-wizard-panel").removeClass("wizard-active");
                    $("#shopWizardPanel" + step).addClass("wizard-active");
                    $(".wizard-step-btn").removeClass("active");
                    $('.wizard-step-btn[data-step="' + step + '"]').addClass("active");
                    $("html, body").animate({ scrollTop: $("#shop-settings").offset().top - 80 }, 200);
                }

                function applyWizardMode() {
                    if (isTablet()) {
                        $("#shopWizardSteps").show();
                        $(".shop-wizard-panel .wizard-nav-btns").show();
                        // Only show the active panel
                        var activeStep = parseInt($(".wizard-step-btn.active").data("step")) || 1;
                        $(".shop-wizard-panel").removeClass("wizard-active");
                        $("#shopWizardPanel" + activeStep).addClass("wizard-active");
                    } else {
                        $("#shopWizardSteps").hide();
                        $(".shop-wizard-panel .wizard-nav-btns").hide();
                        // All panels visible on desktop/phone
                        $(".shop-wizard-panel").addClass("wizard-active");
                    }
                }

                $(document).on("click", ".wizard-step-btn", function () {
                    if (!isTablet()) return;
                    goToStep(parseInt($(this).data("step")));
                });

                $(document).on("click", ".btn-wizard-next", function () {
                    if (!isTablet()) return;
                    goToStep(parseInt($(this).data("wizard-next")));
                });

                $(document).on("click", ".btn-wizard-back", function () {
                    if (!isTablet()) return;
                    goToStep(parseInt($(this).data("wizard-back")));
                });

                // Wizard submit button → delegates to the real submit handler
                $(document).on("click", "#btn-setting-submit-wizard", function (e) {
                    e.preventDefault();
                    $("#btn-setting-submit").trigger("click");
                });

                applyWizardMode();

                var resizeTimer;
                $(window).on("resize", function () {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(applyWizardMode, 150);
                });
            })();


        });
    </script>
@endpush
