@php
    $user = auth()->user();
@endphp

<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                {{-- Dashboard --}}
                @if (app('hasPermission')(0, 'view'))
                    <li>
                        <a href="{{ route('auth.dashboard') }}"><img
                                src="{{ env('ImagePath') . '/admin/assets/img/icons/dashboard.svg' }}"
                                alt="img"><span>
                                Dashboard</span> </a>
                    </li>
                @endif
                {{-- Products --}}
                @if (app('hasPermission')(1, 'view') || app('hasPermission')(1, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);"><img
                                src="{{ env('ImagePath') . '/admin/assets/img/icons/product.svg' }}"
                                alt="img"><span>
                                Products</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(1, 'view'))
                                <li><a href="{{ route('product.list') }}">All Products</a></li>
                            @endif
                            @if (app('hasPermission')(1, 'add'))
                                <li><a href="{{ route('product.add') }}">New Product</a></li>
                            @endif
                            @if (app('hasPermission')(1, 'view'))
                                <li><a href="{{ route('row_material.list') }}">All Row Materials</a></li>
                            @endif
                            {{-- @if (app('hasPermission')(1, 'add'))
                                <li><a href="{{ route('row_material.add') }}">New Row Material</a></li>
                            @endif --}}
                            @if (app('hasPermission')(6, 'view'))
                                <li><a href="{{ route('category.list') }}">All Categories</a></li>
                                <li><a href="{{ route('category.add') }}">New Category</a></li>
                                <li><a href="{{ route('brand.list') }}">All Brands</a></li>
                                <li><a href="{{ route('brand.add') }}">New Brand</a></li>
                                <li><a href="{{ route('unit.list') }}">All Units</a></li>
                                <li><a href="{{ route('labour_item.all_labour_item') }}">All Labour Items</a></li>
                                <li><a href="{{ route('product.import') }}">Import Products</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                    {{-- Customers --}}
                @if (app('hasPermission')(9, 'view') || app('hasPermission')(9, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            {{-- <img src="{{ env('ImagePath') . '/admin/assets/img/icons/users1.svg' }}"
                                alt="img"> --}}
                            <i class="fa fa-users"></i>
                            <span> Customers</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(9, 'view'))
                                <li><a href="{{ route('customer.list') }}">All Customers</a></li>
                            @endif
                            @if (app('hasPermission')(9, 'add'))
                                <li><a href="{{ route('customer.add') }}">New Customer</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Manage Leads --}}
                @if (app('hasPermission')(32, 'view') || app('hasPermission')(32, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fa fa-bullhorn"></i>
                            <span> Manage Leads</span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (app('hasPermission')(32, 'view'))
                                <li><a href="{{ route('lead.list') }}">All Leads</a></li>
                            @endif
                            @if (app('hasPermission')(32, 'add'))
                                <li><a href="{{ route('lead.add') }}">New Lead</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                    {{-- Follow Ups --}}
                @if (app('hasPermission')(30, 'view') || app('hasPermission')(30, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fa fa-calendar-check"></i>
                            <span> Follow Ups</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(30, 'view'))
                                <li><a href="{{ route('followup.list') }}">All Follow Ups</a></li>
                            @endif
                            @if (app('hasPermission')(30, 'add'))
                                <li><a href="{{ route('followup.add') }}">New Follow Up</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                   {{-- Meetings --}}
                @if (app('hasPermission')(31, 'view') || app('hasPermission')(31, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fa fa-handshake"></i>
                            <span> Meetings</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(31, 'view'))
                                <li><a href="{{ route('meeting.list') }}">All Meetings</a></li>
                            @endif
                            @if (app('hasPermission')(31, 'add'))
                                    <li><a href="{{ route('meeting.add') }}">New Meeting</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Sales and Orders --}}
                {{-- @if (app('hasPermission')(2, 'view') || app('hasPermission')(2, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);"><img
                                src="{{ env('ImagePath') . '/admin/assets/img/icons/sales1.svg' }}"
                                alt="img"><span> Sales & Orders</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(2, 'view'))
                                <li><a href="{{ route('sales.list') }}">All Sales & Orders</a></li>
                            @endif
                            @if (app('hasPermission')(2, 'add'))
                                <li><a href="{{ route('sales.add') }}">New Order</a></li>
                            @endif
                        </ul>
                    </li>
                @endif --}}
                   {{-- Sales and Orders --}}
                @if (app('hasPermission')(2, 'view') || app('hasPermission')(2, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);"><img
                                src="{{ env('ImagePath') . '/admin/assets/img/icons/sales1.svg' }}"
                                alt="img"><span> Sales & Bills</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(2, 'view'))
                                <li><a href="{{ route('sales.list') }}">All Sales & Bills</a></li>
                            @endif
                            @if (app('hasPermission')(2, 'add'))
                                <li><a href="{{ route('sales.add', ['new_bill' => 1]) }}">New Bill</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                 {{-- Vendors --}}
                @if (app('hasPermission')(10, 'view') || app('hasPermission')(10, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            {{-- <img src="{{ env('ImagePath') . '/admin/assets/img/icons/users1.svg' }}"
                                alt="img"> --}}
                            <i class="fa fa-handshake"></i>
                            <span> Vendors</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(10, 'view'))
                                <li><a href="{{ route('vendor.list') }}">All Vendors</a></li>
                            @endif
                            @if (app('hasPermission')(10, 'add'))
                                <li><a href="{{ route('vendor.add') }}">New Vendor </a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                {{-- Purchases --}}
                @if (app('hasPermission')(3, 'view') || app('hasPermission')(3, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);"><img
                                src="{{ env('ImagePath') . '/admin/assets/img/icons/purchase1.svg' }}"
                                alt="img"><span>Purchases</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(3, 'view'))
                                <li><a href="{{ route('purchase.lists') }}">All Purchases</a></li>
                            @endif
                            @if (app('hasPermission')(3, 'add'))
                                <li><a href="{{ route('purchase.add') }}">New Purchase</a></li>
                                <li><a href="{{ route('purchase.row_material.lists') }}">Row Material Purchases</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (app('hasPermission')(17, 'view') || app('hasPermission')(28, 'view') || app('hasPermission')(29, 'view'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fa fa-warehouse"></i>
                            <span>Manage Inventory</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (app('hasPermission')(17, 'view'))
                                <li><a href="{{ route('inventory.list') }}">Stock Inventory</a></li>
                                <li><a href="{{ route('row_material.inventory') }}">Material Inventory</a></li>
                            @endif
                            @if (app('hasPermission')(28, 'view'))
                                <li><a href="{{ route('inventory.bom.list') }}">Bill of Materials</a></li>
                            @endif
                            @if (app('hasPermission')(29, 'view'))
                                <li><a href="{{ route('inventory.production.list') }}">Production</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (app('hasPermission')(16, 'view'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            {{-- <img src="{{ env('ImagePath') . '/admin/assets/img/icons/purchase1.svg' }}" alt="img"> --}}
                            <i class="fa fa-calculator"></i>
                            <span>Manage Accounting</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (app('hasPermission')(16, 'view'))
                                <li><a href="{{ route('account_ledger.add') }}">Account Ledger</a></li>
                            @endif

                            @if (app('hasPermission')(16, 'view'))
                                <li><a href="{{ route('income-statement.index') }}">Income Statement</a></li>
                            @endif
                            @if (app('hasPermission')(16, 'view'))
                                <li><a href="{{ route('accounting.balance-sheet') }}">Balance Sheet</a></li>
                            @endif
                            @if (app('hasPermission')(16, 'view'))
                                <li><a href="{{ route('banks.index') }}">Banks</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if (app('hasPermission')(16, 'view') || app('hasPermission')(16, 'add'))
                    <li>
                        <a href="{{ route('sales.receipt.index') }}">
                            <i class="fa fa-receipt"></i>
                            <span>Receipt & Payment</span>
                        </a>
                    </li>
                @endif

                {{-- Invoices --}}
                @if (app('hasPermission')(4, 'view') || app('hasPermission')(4, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fa fa-clipboard"></i>
                            <span>Invoices</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(4, 'view'))
                                <li><a href="{{ route('custom_invoice.lists') }}">All Invoices</a></li>
                            @endif
                            @if (app('hasPermission')(4, 'add'))
                                <li><a href="{{ route('custom_invoice.add') }}">New Invoice</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                {{-- Expenses --}}
                @if (app('hasPermission')(5, 'view') || app('hasPermission')(5, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);"><img
                                src="{{ env('ImagePath') . '/admin/assets/img/icons/expense1.svg' }}"
                                alt="img"><span>
                                Expenses</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(5, 'view'))
                                <li><a href="{{ route('expense.list') }}">All Expenses</a></li>
                            @endif
                            @if (app('hasPermission')(5, 'add'))
                                <li><a href="{{ route('expense.add') }}">New Expense</a></li>
                            @endif
                            @if (app('hasPermission')(5, 'view'))
                                <li><a href="{{ route('expensetype.list') }}">All Expense Type</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                {{-- transaction --}}
                @if (app('hasPermission')(27, 'view'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fas fa-book"></i>
                            <span>Cash & Bank</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(27, 'view'))
                                <li><a href="{{ route('transaction.bankbook') }}">Bank Book</a></li>
                            @endif
                            @if (app('hasPermission')(27, 'view'))
                                <li><a href="{{ route('transaction.cashbook') }}">Cash Book</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Credit Notes --}}
                @if (app('hasPermission')(27, 'view'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fa fa-credit-card me-2"></i>



                            <span>Credit/Debit Notes</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(27, 'view'))
                                <li>
                                    <a href="{{ route('credit-notes-items.index') }}">
                                        Credit Notes
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('debit-notes-items.index') }}">
                                        Debit Notes
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('credit-notes.index') }}">
                                        Credit/Debit Notes Type
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif




                {{-- Returns --}}
                @if (
                    (app('hasPermission')(2, 'add') && app('hasPermission')(2, 'edit')) ||
                        (app('hasPermission')(3, 'add') && app('hasPermission')(3, 'edit')))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/return1.svg' }}" alt="img">
                            <span>Returns</span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            {{-- Sales Return: show only if user has add & edit permission on module 2 --}}
                            @if (app('hasPermission')(2, 'add') && app('hasPermission')(2, 'edit'))
                                <li><a href="{{ route('salesreturn.list') }}">Sales Return</a></li>
                            @endif

                            {{-- Purchase Return: show only if user has add & edit permission on module 3 --}}
                            @if (app('hasPermission')(3, 'add') && app('hasPermission')(3, 'edit'))
                                <li><a href="{{ route('purchasereturn.list') }}">Purchase Return</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                  {{-- GST Reports --}}
                @if (app('hasPermission')(20, 'view'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fas fa-chart-line"></i>

                            <span> GST Reports</span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (app('hasPermission')(20, 'view'))
                                <li><a href="{{ route('gst.sales_list') }}">GST Report</a></li>
                            @endif
                            <!-- @if (app('hasPermission')(20, 'view'))
<li><a href="{{ route('gst.reports.purchase') }}">GST Purchase Report</a></li>
@endif -->

                        </ul>
                    </li>
                @endif
                {{-- Reports --}}
                @if (app('hasPermission')(2, 'view') || app('hasPermission')(3, 'view') || app('hasPermission')(5, 'view'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/time.svg' }}" alt="img">
                            <span> Reports</span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (app('hasPermission')(2, 'view'))
                                <li><a href="{{ route('sales.report') }}">Sales Report</a></li>
                            @endif

                            @if (app('hasPermission')(3, 'view'))
                                <li><a href="{{ route('purchase.report') }}">Purchase Report</a></li>
                            @endif

                            @if (app('hasPermission')(5, 'view'))
                                <li><a href="{{ route('expense.report') }}">Expenses Report</a></li>
                            @endif
                        </ul>
                    </li>
                @endif


                @if (in_array(auth()->user()->role, ['admin', 'hr', 'sub-admin', 'staff']))
                {{-- ====== HR MODULE SECTION ====== --}}
                <li style="padding: 14px 16px 6px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; color: #FF9F43;">
                    HR Workspace
                </li>

                 {{-- Staff --}}
                @if (app('hasPermission')(8, 'view'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/users1.svg' }}" alt="img">
                            <span> Staff</span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li><a href="{{ route('staff.list') }}">All Staff</a></li>
                            <li><a href="{{ route('staff.add') }}">New Staff</a></li>
                        </ul>
                    </li>
                @endif

                {{-- Attendance --}}
                @if (app('hasPermission')(26, 'view') || app('hasPermission')(26, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fa fa-clock"></i>
                            <span> Attendance</span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (app('hasPermission')(26, 'add') || app('hasPermission')(26, 'view'))
                                <li><a href="{{ route('attendence.calendar') }}">Manage Attendance</a></li>
                                <li><a href="{{ route('attendence.summary') }}">Attendance</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Leaves --}}
                @if (app('hasPermission')(28, 'view') || app('hasPermission')(28, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fa fa-calendar-alt"></i>
                            <span> Leaves</span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (in_array(auth()->user()->role, ['admin', 'sub-admin', 'hr', 'staff']))
                                @if (app('hasPermission')(28, 'view'))
                                    <li><a href="{{ route('leave.view') }}">All Leaves</a></li>
                                @endif
                            @endif
                            @if (app('hasPermission')(28, 'view'))
                                <li><a href="{{ route('leave.request') }}">Leave Request</a></li>
                            @endif
                            @if (app('hasPermission')(28, 'add'))
                                <li><a href="{{ route('leave.add') }}">Add Leave</a></li>
                            @endif
                            @if (app('hasPermission')(14, 'view'))
                                <li><a href="{{ route('leave-type.view') }}">Leave Type</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Payroll --}}
                @if (app('hasPermission')(29, 'view') || app('hasPermission')(29, 'add') || auth()->user()->role === 'staff')
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fa fa-money-check-dollar"></i>
                            <span> Payroll</span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (in_array(auth()->user()->role, ['staff']))
                                <li><a href="{{ route('payroll.list') }}">View Payroll</a></li>
                            @endif
                            @if (in_array(auth()->user()->role, ['hr', 'admin']))
                                @if (app('hasPermission')(29, 'view'))
                                    <li><a href="{{ route('payroll.list') }}">Manage Payroll</a></li>
                                @endif
                                @if (app('hasPermission')(29, 'add'))
                                    <li><a href="{{ route('payroll.create') }}">Add Payroll</a></li>
                                @endif
                                @if (app('hasPermission')(29, 'view') || app('hasPermission')(29, 'add'))
                                    <li><a href="{{ route('payroll.salary-details') }}">Group Salary</a></li>
                                @endif
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Advance Pay --}}
                @if (auth()->user()->role === 'admin')
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fa fa-money-bill-wave"></i>
                            <span>Advance Pay</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (app('hasPermission')(23, 'view'))
                                <li><a href="{{ route('advance_pay.index') }}">All Advance Pay</a></li>
                            @endif
                            @if (app('hasPermission')(23, 'add'))
                                <li><a href="{{ route('advance_pay.create') }}">New Advance Pay</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                @endif

                {{-- Settings --}}
                @if (app('hasPermission')(14, 'view') || app('hasPermission')(15, 'view'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/settings.svg' }}"
                                alt="img">
                            <span> Settings</span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (app('hasPermission')(14, 'view'))
                                <li><a href="{{ route('setting.generalsettings') }}">Shop Settings</a></li>
                            @endif
                            @if (app('hasPermission')(14, 'view'))
                                <li><a href="{{ route('setting.smtpsettings') }}">Smtp Settings</a></li>
                            @endif
                            @if (app('hasPermission')(14, 'view'))
                                <li><a href="{{ route('setting.facebookappconfiguration') }}">WhatsApp
                                        Configuration</a></li>
                            @endif
                            @if (app('hasPermission')(15, 'view'))
                                <li><a href="{{ route('auth.taxrates') }}">Tax Rates</a></li>
                            @endif
                            @if (app('hasPermission')(14, 'view'))
                                <li><a href="{{ route('setting.connecteddevices') }}">Connected Devices</a></li>
                            @endif
                            {{-- HR Settings --}}
                            @if (app('hasPermission')(14, 'view'))
                                <li><a href="{{ route('department.view') }}">Departments</a></li>
                                <li><a href="{{ route('designation.view') }}">Designations</a></li>
                                <li><a href="{{ route('leave-type.view') }}">Leave Types</a></li>
                                <li><a href="{{ route('holidays.index') }}">Manage Holidays</a></li>
                                <li><a href="{{ route('holidays.calendar') }}">Holiday Calendar</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
