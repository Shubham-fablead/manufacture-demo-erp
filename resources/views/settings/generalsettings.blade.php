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



                            <div class="col-lg-3 col-sm-12 col-6">
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

                            <div class="col-lg-3 col-sm-12 col-6">
                                <div class="form-group">
                                    <label for="currency_position" class="form-label">
                                        Currency Symbol Position
                                    </label>
                                    <select class="form-select" id="currency_position" name="currency_position" required>
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

                            <div class="col-lg-3 col-sm-12">
                                <div class="form-group">
                                    <label>Shop Address<span class="manitory">*</span></label>
                                    <textarea id="address" placeholder="Enter Address" rows="3" class="form-control"></textarea>
                                </div>
                            </div>
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

                            <div class="col-lg-3 col-sm-12 col-6">
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

                            <div class="col-lg-3 col-sm-12 col-6">
                                <div class="form-group">
                                    <label>Send Mail</label>
                                    <select id="send_mail" name="send_mail" class="form-select">
                                        <option value="1">On</option>
                                        <option value="0">Off</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-12 col-6">
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
                                    <label>Financial Year</label>
                                    <select id="financial_year" name="financial_year" class="form-select">
                                        <option value="1">On</option>
                                        <option value="0">Off</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <a href="javascript:void(0);" class="btn btn-submit me-2"
                                    id="btn-setting-submit">Submit</a>
                            </div>
                        </div>
                    </div>

                    <!-- ================= COMPANY RULES TAB ================= -->
                    <div class="tab-pane fade" id="company-rules" role="tabpanel" aria-labelledby="rules-tab">
                        <div class="row">
                            <div class="col-lg-3 col-sm-6">
                                <div class="form-group">
                                    <label>Working Hours (per day)<span class="manitory">*</span></label>
                                    <input type="time" id="working_hours" class="form-control" placeholder="e.g. 8">
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
                                    <label>Grace Period<span class="manitory">*</span></label>
                                    <input type="time" id="grace_period" class="form-control" placeholder="e.g. 10">
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
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Lead Pipeline Box</label>
                                    <select id="crm_lead_pipeline_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Conversion Box</label>
                                    <select id="crm_conversion_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Follow-up Lead Box</label>
                                    <select id="crm_followup_lead_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Meeting Momentum Box</label>
                                    <select id="crm_meeting_momentum_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Lead Status Mix Chart</label>
                                    <select id="crm_lead_status_mix_chart" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>CRM Activity Trend Chart</label>
                                    <select id="crm_activity_trend_chart" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Pipeline Quality Table</label>
                                    <select id="crm_pipeline_quality_table" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Recent Leads Table</label>
                                    <select id="crm_recent_leads_table" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
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
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Staff Strength Box</label>
                                    <select id="hr_staff_strength_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Active Staff Box</label>
                                    <select id="hr_active_staff_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Monthly Attendance Box</label>
                                    <select id="hr_monthly_attendance_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Personal Progress Box</label>
                                    <select id="hr_personal_progress_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>7 Day Attendance Pattern Chart</label>
                                    <select id="hr_7day_attendance_chart" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Salary Payout Trend Chart</label>
                                    <select id="hr_salary_payout_trend_chart" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Payroll Snapshot Table</label>
                                    <select id="hr_payroll_snapshot_table" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Attendance Watch Table</label>
                                    <select id="hr_attendance_watch_table" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
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
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Total Sales Amount Box</label>
                                    <select id="erp_total_sales_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Total Purchase Amount Box</label>
                                    <select id="erp_total_purchase_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Total Expense Amount Box</label>
                                    <select id="erp_total_expense_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Sales Invoice Count Box</label>
                                    <select id="erp_sales_invoice_count_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Purchase Invoice Count Box</label>
                                    <select id="erp_purchase_invoice_count_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Customers Count Box</label>
                                    <select id="erp_customers_count_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Vendors Count Box</label>
                                    <select id="erp_vendors_count_box" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Sales Chart</label>
                                    <select id="erp_sales_chart" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Purchase Chart</label>
                                    <select id="erp_purchase_chart" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>Latest Sales Table</label>
                                    <select id="erp_latest_sales_table" class="form-select">
                                        <option value="Enable">Enable</option>
                                        <option value="Disable">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
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
            var authToken = localStorage.getItem("authToken");
            const ImagePath = "{{ env('ImagePath') }}";
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            const $generalSettingsBtn = $("#btn-setting-submit");
            const generalSettingsBtnDefaultHtml = $generalSettingsBtn.html();

            function toggleGeneralSettingsBtnLoading(isLoading) {
                if (isLoading) {
                    $generalSettingsBtn
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
                        $("#working_hours").val(settings.working_hours ? settings.working_hours
                            .substring(0, 5) : '');
                        $("#grace_period").val(settings.grace_period ? settings.grace_period.substring(
                            0, 5) : '');
                        $("#lunch_break").val(settings.lunch_break ?? '');
                        $("#open_time").val(settings.open_time ? settings.open_time.substring(0, 5) :
                            '');
                        $("#close_time").val(settings.close_time ? settings.close_time.substring(0, 5) :
                            '');

                        // Sunday Off
                        if (settings.sunday_off === "yes") {
                            $("#sunday_yes").prop("checked", true);
                        } else {
                            $("#sunday_no").prop("checked", true);
                        }

                        // 🏦 General Info (already working)
                        $("#low_stock").val(settings.low_stock);
                        $("#shop_name").val(settings.name);
                        $("#gst_num").val(settings.gst_num);
                        $("#email").val(settings.email);
                        $("#phone").val(settings.phone);
                        $("#state_code").val(settings.state_code);
                        $("#address").val(settings.address);
                        $("#bank_name").val(settings.bank_name);
                        $("#branch").val(settings.branch);
                        $("#ac_no").val(settings.ac_no);
                        $("#ifsc_code").val(settings.ifsc_code);
                        $("#invoice_size").val(settings.invoice_size || 'big');
                        $("#send_mail").val(
                            settings.send_mail === null || settings.send_mail === undefined
                            ? '1'
                            : String(Number(settings.send_mail))
                        );
                        $("#tds_apply").val(
                            settings.tds_apply === null || settings.tds_apply === undefined
                            ? '0'
                            : String(Number(settings.tds_apply))
                        );
                        $("#financial_year").val(
                            settings.financial_year === null || settings.financial_year === undefined
                            ? '1'
                            : String(Number(settings.financial_year))
                        );

                        if (settings.currency_position) {
                            $("#currency_position").val(settings.currency_position).trigger("change");
                        }
                        $("#currency_symbol").val(settings.currency_symbol);

                        // Logos and Images
                        if (settings.logo_url) {
                            $("#logo_preview").attr("src", settings.logo_url).show();
                        }
                        if (settings.favicon_url) {
                            $("#favicon_preview").attr("src", settings.favicon_url).show();
                        }
                        if (settings.qr_code_url) {
                            $("#qr_preview").attr("src", settings.qr_code_url).show();
                        }

                        // 📊 Dashboard Settings
                        $("#crm_section_enabled").val(settings.crm_section_enabled || 'Enable');
                        $("#hr_section_enabled").val(settings.hr_section_enabled || 'Enable');
                        $("#erp_section_enabled").val(settings.erp_section_enabled || 'Enable');

                        // CRM Subsections
                        $("#crm_lead_pipeline_box").val(settings.crm_lead_pipeline_box || 'Enable');
                        $("#crm_conversion_box").val(settings.crm_conversion_box || 'Enable');
                        $("#crm_followup_lead_box").val(settings.crm_followup_lead_box || 'Enable');
                        $("#crm_meeting_momentum_box").val(settings.crm_meeting_momentum_box || 'Enable');
                        $("#crm_lead_status_mix_chart").val(settings.crm_lead_status_mix_chart || 'Enable');
                        $("#crm_activity_trend_chart").val(settings.crm_activity_trend_chart || 'Enable');
                        $("#crm_pipeline_quality_table").val(settings.crm_pipeline_quality_table || 'Enable');
                        $("#crm_recent_leads_table").val(settings.crm_recent_leads_table || 'Enable');
                        $("#crm_next_7_days_table").val(settings.crm_next_7_days_table || 'Enable');

                        // HR Subsections
                        $("#hr_staff_strength_box").val(settings.hr_staff_strength_box || 'Enable');
                        $("#hr_active_staff_box").val(settings.hr_active_staff_box || 'Enable');
                        $("#hr_monthly_attendance_box").val(settings.hr_monthly_attendance_box || 'Enable');
                        $("#hr_personal_progress_box").val(settings.hr_personal_progress_box || 'Enable');
                        $("#hr_7day_attendance_chart").val(settings.hr_7day_attendance_chart || 'Enable');
                        $("#hr_salary_payout_trend_chart").val(settings.hr_salary_payout_trend_chart || 'Enable');
                        $("#hr_payroll_snapshot_table").val(settings.hr_payroll_snapshot_table || 'Enable');
                        $("#hr_attendance_watch_table").val(settings.hr_attendance_watch_table || 'Enable');
                        $("#hr_payroll_status_table").val(settings.hr_payroll_status_table || 'Enable');

                        // ERP Subsections
                        $("#erp_total_sales_box").val(settings.erp_total_sales_box || 'Enable');
                        $("#erp_total_purchase_box").val(settings.erp_total_purchase_box || 'Enable');
                        $("#erp_total_expense_box").val(settings.erp_total_expense_box || 'Enable');
                        $("#erp_sales_invoice_count_box").val(settings.erp_sales_invoice_count_box || 'Enable');
                        $("#erp_purchase_invoice_count_box").val(settings.erp_purchase_invoice_count_box || 'Enable');
                        $("#erp_customers_count_box").val(settings.erp_customers_count_box || 'Enable');
                        $("#erp_vendors_count_box").val(settings.erp_vendors_count_box || 'Enable');
                        $("#erp_sales_chart").val(settings.erp_sales_chart || 'Enable');
                        $("#erp_purchase_chart").val(settings.erp_purchase_chart || 'Enable');
                        $("#erp_latest_sales_table").val(settings.erp_latest_sales_table || 'Enable');
                        $("#erp_latest_purchases_table").val(settings.erp_latest_purchases_table || 'Enable');
                    }
                });
            }


            loadGeneralSettings(); // Load on page load

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
                formData.append("email", $("#email").val());
                formData.append("phone", $("#phone").val());
                formData.append("state_code", $("#state_code").val());
                formData.append("address", $("#address").val());
                formData.append("bank_name", $("#bank_name").val());
                formData.append("branch", $("#branch").val());
                formData.append("ac_no", $("#ac_no").val());
                formData.append("ifsc_code", $("#ifsc_code").val());
                formData.append("currency_position", $("#currency_position").val());
                formData.append("currency_symbol", $("#currency_symbol").val());
                formData.append("selectedSubAdminId", selectedSubAdminId);
                if (logo) formData.append("logo", logo);
                if (favicon) formData.append("favicon", favicon);
                if (qr_code) formData.append("qr_code", qr_code);
                formData.append("_token", "{{ csrf_token() }}");
                formData.append("invoice_size", $("#invoice_size").val());
                formData.append("send_mail", $("#send_mail").val());
                formData.append("tds_apply", $("#tds_apply").val());
                formData.append("financial_year", $("#financial_year").val());

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
                formData.append("grace_period", $("#grace_period").val());
                formData.append("lunch_break", $("#lunch_break").val());
                formData.append("open_time", $("#open_time").val());
                formData.append("close_time", $("#close_time").val());
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



        });
    </script>
@endpush
