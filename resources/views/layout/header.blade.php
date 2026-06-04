@php
    $settings = App\Models\Setting::first();
    $admin = App\Models\User::where('role', 'admin')->first();
@endphp

<style>
    .select2-container--default .select2-selection--single .select2-selection__clear {
        display: none;
    }

    .web_button {
        width: 25px;
        height: 24px;
        cursor: pointer;
        display: flex;
        align-items: center;
    }

    .bar-icon span {
        display: block;
        height: 3px;
        width: 100%;
        background-color: #333;
        margin: 3px 0;
        transition: all 0.3s ease;
        pointer-events: none;
    }

    .search-view {
        width: 465px;
    }

    .header .header-left .logo .logo-view {
        max-width: 45% !important;
        width: auto;
        height: auto;
        object-fit: contain;
    }

    @media (max-width: 991px) {
        .web_button {
            display: none;
        }
    }

    /* iPad landscape specific fixes */
    @media (min-width: 992px) and (max-width: 1024px) {
        .web_button {
            display: flex !important;
            z-index: 1000;
            position: relative;
        }

        #toggle_btn1 {
            pointer-events: auto !important;
            cursor: pointer !important;
        }
    }

    /* iPad Mini / iPad Air portrait header alignment */
    @media (min-width: 768px) and (max-width: 991px) {
        .header {
            position: relative;
            display: flex;
            align-items: center;
            min-height: 60px;
            padding: 0 14px;
        }

        .header-left {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: auto !important;
            margin: 0;
            padding: 0;
            z-index: 2;
        }

        .header-left .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .header-left .logo .logo-view {
            max-width: 130px !important;
            margin: 0 auto !important;
        }

        #mobile_btn {
            display: flex !important;
            margin-right: auto;
            z-index: 3;
        }

        #toggle_btn1 {
            display: none !important;
        }

        .nav.user-menu {
            margin-left: auto;
            align-items: center;
            z-index: 3;
        }

        .nav.user-menu .user-img img {
            width: 34px;
            height: 34px;
            object-fit: cover;
            border-radius: 50%;
        }
    }

    /* iPad Pro specific (1024px) */
    @media screen and (min-width: 992px) and (max-width: 1180px) {
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            /* padding: 0 12px; */
            min-height: 68px;
        }


        .header-left {
            position: static;
            transform: none;
            width: 260px !important;
            min-width: 260px !important;
            max-width: 260px !important;
            flex: 0 0 260px;
            margin-right: 0;
            padding: 0 14px;
            border-right: 1px solid #eef0f2;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-left .logo {
            flex: 1 1 auto;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin: 0;
            min-width: 0;
        }

        .header-left .logo .logo-view {
            width: auto !important;
            max-width: 92px !important;
        }

        .header-left .logo-small {
            display: none;
        }

        #toggle_btn1 {
            margin-left: auto;
        }

        body.mini-sidebar .header-left {
            width: 80px !important;
            min-width: 80px !important;
            max-width: 80px !important;
            padding: 0;
            justify-content: center;
        }

        body.mini-sidebar .header-left .logo {
            display: none;
        }

        body.mini-sidebar .header-left .logo-small {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        body.mini-sidebar .header-left .logo-small img {
            width: 32px;
            height: auto;
        }

        .web_button {
            display: flex !important;
            flex: 0 0 auto;
            margin-left: 8px;
        }

        .mobile_btn {
            display: none !important;
        }

        .nav.user-menu {
            display: flex !important;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: nowrap;
            gap: 8px;
            margin-left: auto;
            min-width: 0;
            width: auto;
        }

        .nav.user-menu > * {
            flex: 0 0 auto;
        }

        .header .header-search-container.tab-view {
            display: flex !important;
            align-items: center;
            flex-wrap: nowrap;
            gap: 8px;
            min-width: 0;
            flex: 1 1 auto;
        }

        .header .header-search {
            width: 190px;
            flex: 0 0 190px;
        }

        .header #subBranchContainer {
            display: block !important;
            width: 160px !important;
            margin-right: 0 !important;
            margin-left: 0;
            flex: 0 0 160px;
        }

        .header #subBranchContainer .d-flex {
            width: 160px;
        }

        .header #subBrandSelect,
        .header #subBranchContainer .select2-container {
            width: 160px !important;
        }

        .header .header-new-order-button {
            height: 36px !important;
            padding: 0 12px !important;
            white-space: nowrap;
        }

        .header .notification-wrapper {
            margin-right: 0 !important;
            flex: 0 0 auto;
        }

        .header .main-drop {
            flex: 0 0 auto;
            margin-left: 2px;
        }

        .header .user-img img {
            width: 38px;
            height: 38px;
            object-fit: cover;
        }
    }

    @media (max-width: 983px) and (min-width: 575px) {
        .logo-view {
            float: left;
            margin-left: 3rem;
        }

        .search-view {
            width: 366px;
        }
    }

    div#subBranchContainer {
        width: 200px !important;
    }

    /* Mobile Bottom Navigation */
    .mobile-bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: #ff9f43;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        display: none;
        justify-content: space-around;
        align-items: center;
        padding: 8px 0;
        z-index: 1050;
    }

    .mobile-bottom-nav .nav-item {
        text-align: center;
        color: #fff;
        text-decoration: none;
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: color 0.3s;
    }

    .mobile-bottom-nav .nav-item i {
        font-size: 18px;
        margin-bottom: 4px;
    }

    .mobile-bottom-nav .nav-item span {
        font-size: 11px;
        font-weight: 500;
    }

    .mobile-bottom-nav .nav-item.active {
        color: #1b2850;
    }

    .notification-wrapper {
        position: relative;
    }

    /* Bell */
    .notification-bell {
        font-size: 20px;
        color: #1b2850;
        transition: all 0.3s ease;
    }

    .notification-bell:hover {
        color: #ff9f43;
    }

    /* Badge */
    .notification-badge {
        position: absolute;
        top: -4px;
        right: -0px;
        background: #ff3b30;
        color: #fff;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 50px;
        min-width: 18px;
        text-align: center;
        font-weight: bold;
        animation: pulse 2s infinite;
    }

    /* Dropdown */
    .notification-dropdown {
        position: absolute;
        top: 45px;
        right: 0;
        width: 350px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        display: none;
        overflow: hidden;
        z-index: 9999;
        border: 1px solid #eaeaea;
    }

    /* Header */
    .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        font-weight: 600;
        border-bottom: 1px solid #eaeaea;
        background: #fff;
    }

    .notification-header a {
        font-size: 12px;
        color: #ff9f43;
        text-decoration: none;
        font-weight: 500;
    }

    .notification-header a:hover {
        text-decoration: underline;
    }

    /* Body */
    .notification-body {
        max-height: 380px;
        overflow-y: auto;
        background: #fff;
    }

    /* Empty State */
    .empty-notification {
        padding: 40px 20px;
        text-align: center;
        color: #8c8c8c;
        font-size: 14px;
    }

    .empty-notification i {
        font-size: 40px;
        margin-bottom: 10px;
        display: block;
        color: #ddd;
    }

    /* Notification Item */
    .notification-item {
        padding: 12px 15px;
        background: #fff;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: 0.2s;
        position: relative;
    }

    .notification-item:hover {
        background: #fef9f0;
    }

    .notification-item.unread-notification {
        background: #fff9f0;
        border-left: 3px solid #ff9f43;
    }

    .notification-dot {
        width: 8px;
        height: 8px;
        background-color: #ff3b30;
        border-radius: 50%;
        position: absolute;
        top: 15px;
        right: 15px;
    }

    .notification-title {
        font-size: 14px;
        font-weight: 600;
        color: #1b2850;
        display: block;
        margin-bottom: 4px;
    }

    .notification-message {
        font-size: 12px;
        color: #6c757d;
        display: block;
        margin-bottom: 6px;
        line-height: 1.4;
    }

    .notification-time {
        font-size: 11px;
        color: #adb5bd;
    }

    .notification-time i {
        font-size: 10px;
        margin-right: 3px;
    }

    .notification-footer {
        padding: 10px;
        border-top: 1px solid #eaeaea;
        text-align: center;
        background: #fff;
        position: sticky;
        bottom: 0;
    }

    .notification-footer button {
        color: #ff9f43;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
    }

    .notification-footer button:hover {
        text-decoration: underline;
    }

    .today-alert-toggle {
        width: 38px;
        height: 38px;
        /* border-radius: 6px; */
        /* border: 1px solid #d9dde3; */
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        color: #1b2850;
        transition: all 0.2s ease;
    }

    .today-alert-toggle:hover {
        color: #ff9f43;
        border-color: #ff9f43;
    }

    .today-alert-toggle i {
        font-size: 18px;
    }

    .today-alert-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.55);
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .today-alert-modal {
        width: min(650px, 96vw);
        background: #fff;
        border-radius: 4px;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.18);
        border: 1px solid #e5e7eb;
    }

    .today-alert-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 18px;
        border-bottom: 1px solid #e5e7eb;
    }

    .today-alert-modal-header h5 {
        margin: 0;
        color: #1f2937;
        font-size: 18px;
        font-weight: 700;
    }

    .today-alert-close {
        border: 0;
        background: transparent;
        color: #ef4444;
        font-size: 18px;
        line-height: 1;
        font-weight: 700;
    }

    .today-alert-modal-body {
        padding: 18px;
    }

    .today-alert-tabs {
        display: flex;
        align-items: center;
        gap: 18px;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 14px;
    }

    .today-alert-tab {
        border: 0;
        background: transparent;
        padding: 0 4px 10px;
        color: #6b7280;
        font-size: 13px;
        font-weight: 700;
        border-bottom: 1px solid transparent;
    }

    .today-alert-tab.active {
        color: #ff9f43;
        border-bottom-color: #ff9f43;
    }

    .today-alert-tab .count-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 18px;
        height: 18px;
        padding: 0 6px;
        margin-left: 5px;
        border-radius: 999px;
        background: #fff2e4;
        color: #ff9f43;
        font-size: 11px;
    }

    .today-alert-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .today-alert-table th {
        background: #f8f9fb;
        color: #1f2937;
        padding: 10px;
        font-weight: 700;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }

    .today-alert-table td {
        padding: 10px;
        color: #52677a;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    .today-alert-table .action-link {
        color: #1b2850;
        text-decoration: none;
    }

    .today-alert-empty {
        padding: 28px 12px;
        text-align: center;
        color: #8c8c8c;
        border: 1px dashed #e5e7eb;
        border-radius: 6px;
    }

    .today-alert-footer {
        padding: 14px 18px 16px;
    }

    .today-alert-footer .btn {
        min-width: 116px;
        height: 46px;
        border-radius: 3px;
        background: #1f2937;
        border-color: #1f2937;
        font-weight: 700;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.1);
        }
        100% {
            transform: scale(1);
        }
    }

    @media (max-width: 991px) {
        .mobile-bottom-nav {
            display: flex;
        }

        body {
            padding-bottom: 60px !important;
        }

        .sidebar {
            bottom: 60px !important;
        }

        .sidebar-inner,
        .slimScrollDiv {
            height: calc(100vh - 120px) !important;
        }

        .tab-view {
            display: none !important;
        }

        .notification-dropdown {
            width: 320px;
            right: -10px;
        }
    }

    @media (max-width: 575px) {
        .header {
            left: 50%;
            right: auto;
            width: 450px;
            max-width: calc(100vw - 4px);
            transform: translateX(-50%);
            height: 56px;
            display: flex;
            align-items: center;
            padding: 0 10px;
            box-sizing: border-box;
        }

        .header .mobile_btn {
            order: 1;
            position: static;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 56px;
            line-height: 1;
            padding: 0;
            flex: 0 0 30px;
        }

        .header .mobile_btn .bar-icon {
            width: 24px;
            margin-top: 0;
        }

        .header .mobile_btn .bar-icon span {
            width: 24px;
            height: 2px;
            margin-bottom: 6px;
            background-color: #333;
        }

        .header .mobile_btn .bar-icon span:nth-child(2) {
            width: 14px;
        }

        .header .header-left {
            order: 2;
            position: static;
            width: 78px !important;
            height: 56px;
            padding: 0;
            margin: 0 4px 0 30px;
            border-right: 0;
            transform: none;
            flex: 0 0 78px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header .header-left .logo {
            width: auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header .header-left .logo .logo-view {
            width: 90px !important;
            max-width: 90px !important;
            height: auto !important;
            object-fit: contain;
            margin: 0 !important;
        }

        .header .user-menu {
            order: 3;
            display: flex !important;
            align-items: center;
            justify-content: flex-end;
            height: 56px;
            margin: 0;
            /* padding-right: 0; */
            flex: 1 1 auto;
        }

        .header .header-search-container.tab-view {
            display: flex !important;
            align-items: center;
            gap: 5px;
        }

        .header .header-search,
        .header #todayAlertToggle,
        .header .header-new-order-button,
        .header .hide-on-ipad-pro,
        .header .main-drop {
            display: none !important;
        }

        .header #subBranchContainer {
            display: block !important;
            position: relative;
            width: 84px !important;
            margin-right: 4px !important;
            margin-left: -4px;
        }

        .header #subBranchContainer .d-flex {
            width: 100px;
        }

        .header #subBrandSelect,
        .header #subBranchContainer .select2-container {
            width: 130px !important;
        }

        .header #subBranchContainer .select2-selection--single {
            height: 30px !important;
            min-height: 30px !important;
            border-color: #d9dde3;
            border-radius: 4px;
        }

        .header #subBranchContainer .select2-selection__rendered {
            line-height: 28px !important;
            padding-left: 8px !important;
            padding-right: 22px !important;
            font-size: 10px;
            color: #1b2850;
        }

        .header #subBranchContainer .select2-selection__arrow {
            height: 28px !important;
            right: 2px;
        }

        .header #subBranchContainer .select2-dropdown {
            width: 100px !important;
            min-width: 100px;
            z-index: 1060;
        }

        .header #subBranchContainer .select2-results__option {
            font-size: 11px;
            padding: 6px 8px;
            white-space: normal;
        }

        .header .notification-wrapper {
            margin-right: 0 !important;
            width: 24px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header #notificationToggle {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 34px;
            line-height: 1;
            padding: 0;
        }

        .header #notificationToggle i {
            line-height: 1;
        }

        .header .notification-bell {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            color: #1b2850;
        }

        .header .notification-badge {
            top: -6px;
            right: -9px;
            min-width: 16px;
            height: 16px;
            line-height: 14px;
            font-size: 9px;
            padding: 1px 4px;
            z-index: 2;
        }

        .header .mobile-user-menu {
            order: 4;
            position: relative;
            display: block;
            width: 18px;
            height: 56px;
            line-height: 56px;
            padding: 0;
            flex: 0 0 18px;
            text-align: center;
        }

        .header .mobile-user-menu a {
            color: #ff9f43;
            font-size: 18px;
        }

        .header .mobile-user-menu .dropdown-menu {
            position: absolute;
            top: 52px !important;
            right: 0;
            left: auto;
            min-width: 126px;
            padding: 4px 0;
            transform: none !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
            border: 1px solid #eef0f2;
            border-radius: 2px;
        }

        .header .mobile-user-menu .dropdown-menu .dropdown-item {
            line-height: 28px;
            padding: 4px 10px;
            font-size: 13px;
            color: #333;
        }
    }
</style>

@php
    $user = auth()->user();
    $logoRedirectRoute = $user && $user->role === 'staff' ? route('auth.profile') : route('auth.dashboard');
@endphp

<div class="header">
    <div class="header-left active">
        <a href="{{ $logoRedirectRoute }}" class="logo">
            <img src="{{ !empty($settings) && !empty($settings->logo) ? env('ImagePath') . '/storage/' . $settings->logo : 'https://fableadtechnolabs.com/static/media/250x150%20(1).b3f5a4db48c7770366ef.webp' }}"
                alt="" class="logo-view">
        </a>
        <a href="{{ $logoRedirectRoute }}" class="logo-small">
            <img src="{{ !empty($settings) && !empty($settings->favicon) ? env('ImagePath') . '/storage/' . $settings->favicon : 'https://fableadtechnolabs.com/favicon-192x192.webp' }}"
                alt="">
        </a>

        <div id="toggle_btn1" class="web_button">
            <span class="bar-icon">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </div>
    </div>

    <a id="mobile_btn" class="mobile_btn" href="#sidebar">
        <span class="bar-icon">
            <span></span>
            <span></span>
            <span></span>
        </span>
    </a>

    <ul class="nav user-menu">
        <div class="d-flex align-items-center header-search-container tab-view">
            @if (in_array($user->role, ['admin']))
                <div class="me-3" id="subBranchContainer" style="display: none;">
                    <div class="d-flex align-items-center">
                        <select id="subBrandSelect" class="form-select form-select-sm" style="width: 300px;">
                        </select>
                        <div id="currentSelection" class="ms-2 text-muted d-none" style="font-size: 12px;"></div>
                    </div>
                </div>
            @endif

            @if ($user->role === 'staff')
            <div class="d-none d-lg-flex align-items-center me-3">
                <button class="btn-check-in"
                    style="display:none; align-items:center; height:38px; background:#ff9f43; color:#fff;
                           border:none; border-radius:6px; padding:0 15px; font-weight:600;
                           font-size:13px; cursor:pointer; gap:6px; white-space:nowrap;">
                    <i class="fa fa-sign-in-alt"></i>&nbsp;Check In
                </button>
                <button class="btn-check-out"
                    style="display:none; align-items:center; height:38px; background:#ff9f43; color:#fff;
                           border:none; border-radius:6px; padding:0 15px; font-weight:600;
                           font-size:13px; cursor:pointer; gap:6px; white-space:nowrap;">
                    <i class="fa fa-sign-out-alt"></i>&nbsp;Check Out
                </button>
            </div>
            @endif

            <!-- Search Field Container -->
            <div class="header-search d-flex align-items-center position-relative me-3 ">
                <!-- Search Icon -->
                <img src="{{ env('ImagePath') . '/admin/assets/img/icons/search.svg' }}" alt="Search"
                    style="position: absolute; left: 12px; width: 18px; height: 18px; z-index: 10; opacity: 0.6;">

                <!-- Input Field -->
                <input type="text" id="customerSearch" class="form-control form-control-sm rounded px-3 ps-5"
                    placeholder="Search..." autocomplete="off" style="height: 38px; font-size: 14px;">

                <!-- Search Results -->
                <div id="searchResults" class="list-group bg-white position-absolute rounded shadow mt-1 w-100"
                    style="z-index: 1050; max-height: 400px; overflow-y: auto; display: none; top: 100%; left: 0;">
                </div>
            </div>

            <li class="nav-item me-3 notification-wrapper">
                <a href="javascript:void(0);" class="nav-link position-relative today-alert-toggle" id="todayAlertToggle" title="Today Alerts">
                     <i class="fa-regular fa-clock" style="font-size: 18px;color: #1b2850;"></i>
                    <span id="todayAlertCount" class="notification-badge d-none">0</span>
                </a>
            </li>

           <!-- Notifications -->
           @if ($user->role !== 'staff')
<li class="nav-item dropdown me-3 notification-wrapper">

    <a href="javascript:void(0);" class="nav-link position-relative" id="notificationToggle">
        <i class="fa fa-bell notification-bell"></i>
        <span id="notificationCount" class="notification-badge d-none">0</span>
    </a>

    <!-- Notification Dropdown -->
    <div id="notificationMenu" class="notification-dropdown">
        <div class="notification-header">
            <span>Notifications</span>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('notifications.index') }}" class="text-decoration-none">View All</a>
                {{-- <button onclick="markAllNotificationsAsRead()" id="markAllReadBtn" class="btn btn-link btn-sm p-0 text-decoration-none" style="display: none;">Mark all as read</button> --}}
            </div>
        </div>
        <div class="notification-body" id="notificationList">
            <div class="empty-notification">
                <i class="fa fa-bell-slash"></i>
                No notifications
            </div>
        </div>
        <div class="notification-footer">
            <a href="{{ route('notifications.index') }}" class="text-decoration-none">View all notifications →</a>
        </div>
    </div>
</li>
@endif

            <!-- New Order Button -->
            {{-- @if (in_array($user->role, ['sales-manager', 'inventory-manager', 'admin']))
                <a href="/add-sales"
                    class="btn btn-sm d-flex align-items-center justify-content-center me-3 header-new-order-button"
                    style="height: 38px; background-color: #ff9f43; color: white; border-radius: 6px;">
                    <i class="fa fa-plus me-1"></i> New Order
                </a>
            @endif --}}
              @if (in_array($user->role, ['sales-manager', 'inventory-manager', 'admin']))
                <div class="dropdown me-1 hide-on-ipad-pro" >
                    <button
                        class="btn btn-sm d-flex align-items-center justify-content-center header-new-order-button dropdown-toggle"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                        style="height: 38px; background-color: #ff9f43; color: white; border-radius: 6px; border: none;">
                        <i class="fa fa-plus me-1"></i>New Bill
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('sales.add', ['sale_type' => 'sales']) }}">
                                New Sales
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('sales.add', ['sale_type' => 'quotation']) }}">
                                New Quotation
                            </a>
                        </li>
                    </ul>
                </div>
            @endif

        </div>

        <li class="nav-item dropdown has-arrow main-drop">
            <a href="javascript:void(0);" class="dropdown-toggle nav-link userset" data-bs-toggle="dropdown">
                <span class="user-img">
                    <img src="{{ !empty($user->profile_image) ? env('ImagePath') . '/storage/' . $user->profile_image : env('ImagePath') . '/admin/assets/img/customer/customer5.jpg' }}"
                        alt="">
                    <span class="status online"></span>
                </span>
            </a>
            <div class="dropdown-menu menu-drop-user">
                <div class="profilename">
                    <div class="profileset">
                        <span class="user-img">
                            <img src="{{ !empty($user->profile_image) ? env('ImagePath') . '/storage/' . $user->profile_image : env('ImagePath') . '/admin/assets/img/customer/customer5.jpg' }}"
                                alt="">
                        </span>
                        <div class="profilesets">
                            <h6>{{ $user->name ?? 'User' }}</h6>
                            <h5>{{ ucfirst($user->role ?? 'user') }}</h5>
                        </div>
                    </div>
                    <hr class="m-0">
                    <a class="dropdown-item" href="{{ route('auth.profile') }}">
                        <i class="me-2" data-feather="user"></i> My Profile
                    </a>
                    @if ($user->role === 'admin')
                        <a class="dropdown-item" href="{{ route('setting.generalsettings') }}">
                            <i class="me-2" data-feather="settings"></i> Settings
                        </a>
                        <a class="dropdown-item new_branch" href="{{ route('subbranch.list') }}">
                            <i class="me-2" data-feather="layers"></i> My Branch
                        </a>
                    @endif
                    <hr class="m-0">
                    <a class="dropdown-item logout pb-0" href="{{ route('logout') }}">
                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/log-out.svg' }}" class="me-2"
                            alt="img"> Logout
                    </a>
                </div>
            </div>
        </li>
    </ul>

    @if ($user->role === 'staff')
    <div class="mobile-attendance-btn d-lg-none" style="position: absolute; right: 55px; top: 13px;">
        <button class="btn-check-in"
            style="display:flex; align-items:center; height:34px; background:#ff9f43; color:#fff;
                   border:none; border-radius:6px; padding:0 12px; font-weight:600;
                   font-size:12px; cursor:pointer; gap:4px; white-space:nowrap;">
            <i class="fa fa-sign-in-alt"></i>&nbsp;Check In
        </button>
        <button class="btn-check-out"
            style="display:none; align-items:center; height:34px; background:#ff9f43; color:#fff;
                   border:none; border-radius:6px; padding:0 12px; font-weight:600;
                   font-size:12px; cursor:pointer; gap:4px; white-space:nowrap;">
            <i class="fa fa-sign-out-alt"></i>&nbsp;Check Out
        </button>
    </div>
    @endif

    <div class="dropdown mobile-user-menu">
        <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"
            aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
        <div class="dropdown-menu dropdown-menu-right">
            <a class="dropdown-item" href="{{ route('auth.profile') }}">My Profile</a>
            @if ($user->role === 'admin')
                <a class="dropdown-item" href="{{ route('setting.generalsettings') }}">Settings</a>
                <a class="dropdown-item new_branch" href="{{ route('subbranch.list') }}">My Branch</a>
            @endif
            <a class="dropdown-item" href="{{ route('logout') }}">Logout</a>
        </div>
    </div>
</div>

<div id="todayAlertModalBackdrop" class="today-alert-modal-backdrop">
    <div class="today-alert-modal" role="dialog" aria-modal="true" aria-labelledby="todayAlertModalTitle">
        <div class="today-alert-modal-header">
            <h5 id="todayAlertModalTitle">Today Alerts</h5>
            <button type="button" class="today-alert-close" id="todayAlertCloseBtn" aria-label="Close">x</button>
        </div>
        <div class="today-alert-modal-body">
            <div class="today-alert-tabs">
                <button type="button" class="today-alert-tab active" data-alert-tab="meetings">
                    Meetings <span class="count-pill" id="todayMeetingTabCount">0</span>
                </button>
                <button type="button" class="today-alert-tab" data-alert-tab="followups">
                    Follow Ups <span class="count-pill" id="todayFollowUpTabCount">0</span>
                </button>
                <button type="button" class="today-alert-tab" data-alert-tab="lowstock">
                    Low Stock <span class="count-pill" id="todayLowStockTabCount">0</span>
                </button>
            </div>
            <div id="todayAlertContent">
                <div class="today-alert-empty">Loading today alerts...</div>
            </div>
        </div>
        <div class="today-alert-footer">
            <button type="button" class="btn btn-dark" id="todayAlertFooterCloseBtn">Close</button>
        </div>
    </div>
</div>

@push('js')
<script>
    const currentUserRole = "{{ auth()->user()->role }}";
    const currentUserId = "{{ auth()->user()->id }}";
</script>

<script>
    // ==================== TODAY ALERTS FUNCTIONALITY ====================
    let todayAlertData = {
        meetings: [],
        followups: [],
        lowstock: []
    };
    let todayLowStockThreshold = 0;
    let activeTodayAlertTab = 'meetings';

    document.addEventListener('DOMContentLoaded', function() {
        initializeTodayAlerts();
    });

    function initializeTodayAlerts() {
        const toggle = document.getElementById('todayAlertToggle');
        const backdrop = document.getElementById('todayAlertModalBackdrop');
        const closeBtn = document.getElementById('todayAlertCloseBtn');
        const footerCloseBtn = document.getElementById('todayAlertFooterCloseBtn');

        if (!toggle || !backdrop) return;

        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            openTodayAlertModal();
        });

        [closeBtn, footerCloseBtn].forEach(button => {
            if (button) {
                button.addEventListener('click', closeTodayAlertModal);
            }
        });

        backdrop.addEventListener('click', function(e) {
            if (e.target === backdrop) {
                closeTodayAlertModal();
            }
        });

        document.querySelectorAll('.today-alert-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                setActiveTodayAlertTab(this.dataset.alertTab || 'meetings');
                renderTodayAlerts();
            });
        });

        loadTodayAlerts();
        setInterval(loadTodayAlerts, 60000);
    }

    function openTodayAlertModal(tabName) {
        const backdrop = document.getElementById('todayAlertModalBackdrop');
        if (!backdrop) return;

        if (tabName) {
            setActiveTodayAlertTab(tabName);
        }
        backdrop.style.display = 'flex';
        loadTodayAlerts().then(renderTodayAlerts);
    }

    window.openTodayAlertModal = openTodayAlertModal;

    function setActiveTodayAlertTab(tabName) {
        activeTodayAlertTab = tabName || 'meetings';
        document.querySelectorAll('.today-alert-tab').forEach(item => {
            item.classList.toggle('active', item.dataset.alertTab === activeTodayAlertTab);
        });
    }

    function closeTodayAlertModal() {
        const backdrop = document.getElementById('todayAlertModalBackdrop');
        if (backdrop) {
            backdrop.style.display = 'none';
        }
    }

    function getApiHeaders() {
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        };
        const authToken = localStorage.getItem('authToken');
        if (authToken) {
            headers.Authorization = 'Bearer ' + authToken;
        }
        return headers;
    }

    async function loadTodayAlerts() {
        const selectedSubAdminId = localStorage.getItem('selectedSubAdminId') || '';
        const query = `per_page=100&selectedSubAdminId=${encodeURIComponent(selectedSubAdminId)}`;

        try {
            const [meetingsResponse, followUpsResponse, dashboardResponse] = await Promise.all([
                fetch(`/api/getAllMeetings?${query}`, { headers: getApiHeaders(), credentials: 'same-origin' }),
                fetch(`/api/getAllFollowUps?${query}`, { headers: getApiHeaders(), credentials: 'same-origin' }),
                fetch(`/api/dashboard-api?selectedSubAdminId=${encodeURIComponent(selectedSubAdminId)}`, { headers: getApiHeaders(), credentials: 'same-origin' })
            ]);

            const meetingsJson = meetingsResponse.ok ? await meetingsResponse.json() : { data: [] };
            const followUpsJson = followUpsResponse.ok ? await followUpsResponse.json() : { data: [] };
            const dashboardJson = dashboardResponse.ok ? await dashboardResponse.json() : null;

            todayAlertData.meetings = (meetingsJson.data || []).filter(item => isTodayAlertDate(item.scheduled_on, item.formatted_scheduled_on));
            todayAlertData.followups = (followUpsJson.data || []).filter(item => isTodayAlertDate(item.follow_up_datetime, item.formatted_follow_up_datetime));
            const lowStock = dashboardJson?.data?.lowStock;
            todayLowStockThreshold = parseFloat(lowStock?.threshold || 0);
            todayAlertData.lowstock = Array.isArray(lowStock?.products) ? lowStock.products : [];

            updateTodayAlertCounts();
            renderTodayAlerts();
        } catch (error) {
            console.error('Error loading today alerts:', error);
            const content = document.getElementById('todayAlertContent');
            if (content) {
                content.innerHTML = '<div class="today-alert-empty text-danger">Failed to load today alerts</div>';
            }
        }
    }

    function isTodayAlertDate(rawDate, formattedDate) {
        const todayLabel = formatTodayLabel();

        if (formattedDate && String(formattedDate).startsWith(todayLabel)) {
            return true;
        }

        if (!rawDate) return false;

        const date = new Date(String(rawDate).replace(' ', 'T'));
        if (isNaN(date.getTime())) return false;

        const today = new Date();
        return date.getFullYear() === today.getFullYear()
            && date.getMonth() === today.getMonth()
            && date.getDate() === today.getDate();
    }

    function formatTodayLabel() {
        const today = new Date();
        return [
            String(today.getDate()).padStart(2, '0'),
            String(today.getMonth() + 1).padStart(2, '0'),
            today.getFullYear()
        ].join('-');
    }

    function updateTodayAlertCounts() {
        const meetingCount = todayAlertData.meetings.length;
        const followUpCount = todayAlertData.followups.length;
        const lowStockCount = todayAlertData.lowstock.length;
        const totalCount = meetingCount + followUpCount + lowStockCount;
        const totalBadge = document.getElementById('todayAlertCount');
        const meetingBadge = document.getElementById('todayMeetingTabCount');
        const followUpBadge = document.getElementById('todayFollowUpTabCount');
        const lowStockBadge = document.getElementById('todayLowStockTabCount');

        if (meetingBadge) meetingBadge.innerText = meetingCount;
        if (followUpBadge) followUpBadge.innerText = followUpCount;
        if (lowStockBadge) lowStockBadge.innerText = lowStockCount;

        if (totalBadge) {
            if (totalCount > 0) {
                totalBadge.innerText = totalCount > 99 ? '99+' : totalCount;
                totalBadge.classList.remove('d-none');
            } else {
                totalBadge.classList.add('d-none');
            }
        }
    }

    function renderTodayAlerts() {
        const content = document.getElementById('todayAlertContent');
        if (!content) return;

        const rows = todayAlertData[activeTodayAlertTab] || [];
        if (!rows.length) {
            const emptyLabel = activeTodayAlertTab === 'meetings'
                ? 'meetings'
                : (activeTodayAlertTab === 'followups' ? 'follow ups' : 'low stock products');
            content.innerHTML = `<div class="today-alert-empty">No ${emptyLabel} for today</div>`;
            return;
        }

        if (activeTodayAlertTab === 'meetings') {
            content.innerHTML = renderMeetingAlertTable(rows);
            return;
        }

        content.innerHTML = activeTodayAlertTab === 'followups'
            ? renderFollowUpAlertTable(rows)
            : renderLowStockAlertTable(rows);
    }

    window.setTodayLowStockAlerts = function(lowStock) {
        todayLowStockThreshold = parseFloat(lowStock?.threshold || 0);
        todayAlertData.lowstock = Array.isArray(lowStock?.products) ? lowStock.products : [];
        updateTodayAlertCounts();
        if (activeTodayAlertTab === 'lowstock') {
            renderTodayAlerts();
        }
    };

    function renderMeetingAlertTable(rows) {
        return `
            <div class="table-responsive">
                <table class="today-alert-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Date & Time</th>
                            <th>Assigned</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows.map(item => `
                            <tr>
                                <td>${escapeHtml(item.meeting_title || 'N/A')}</td>
                                <td>${escapeHtml(item.customer?.name || 'N/A')}</td>
                                <td>${escapeHtml(item.status || 'N/A')}</td>
                                <td>${escapeHtml(item.formatted_scheduled_on || formatAlertDate(item.scheduled_on))}</td>
                                <td>${escapeHtml(item.assigned_user?.name || 'N/A')}</td>
                                <td><a class="action-link" href="/meeting-view/${item.id}" title="View"><i class="fa fa-eye"></i></a></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    function renderFollowUpAlertTable(rows) {
        return `
            <div class="table-responsive">
                <table class="today-alert-table">
                    <thead>
                        <tr>
                            <th>Purpose</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Date & Time</th>
                            <th>Assigned</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows.map(item => `
                            <tr>
                                <td>${escapeHtml(item.purpose || 'N/A')}</td>
                                <td>${escapeHtml(item.subject_name || item.customer?.name || item.lead?.name || 'N/A')}</td>
                                <td>${escapeHtml(item.status || 'N/A')}</td>
                                <td>${escapeHtml(item.formatted_follow_up_datetime || formatAlertDate(item.follow_up_datetime))}</td>
                                <td>${escapeHtml(item.assigned_user?.name || 'N/A')}</td>
                                <td><a class="action-link" href="/follow-up-view/${item.id}" title="View"><i class="fa fa-eye"></i></a></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    function renderLowStockAlertTable(rows) {
        return `
            <div style="font-size:13px;color:#555;margin-bottom:10px;">
                The following products are below the threshold of <strong>${todayLowStockThreshold}</strong> units:
            </div>
            <div class="table-responsive">
                <table class="today-alert-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Current Qty</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows.map(item => {
                            const qty = parseFloat(item.quantity || 0);
                            const label = qty <= 0 ? 'Out of Stock' : 'Low Stock';
                            return `
                                <tr>
                                    <td>${escapeHtml(item.name || 'N/A')}</td>
                                    <td>${qty.toFixed(3)}</td>
                                    <td><span class="badges ${qty <= 0 ? 'bg-lightred' : 'bg-lightyellow'}" style="font-size:11px;">${label}</span></td>
                                    <td><a class="action-link" href="/product-view/${item.id}" title="View"><i class="fa fa-eye"></i></a></td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    function formatAlertDate(rawDate) {
        if (!rawDate) return 'N/A';
        const date = new Date(String(rawDate).replace(' ', 'T'));
        if (isNaN(date.getTime())) return rawDate;
        return date.toLocaleString('en-IN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        }).replace(',', '');
    }

    // // ==================== SEARCH FUNCTIONALITY ====================
    // document.addEventListener('DOMContentLoaded', function() {
    //     const searchInput = document.getElementById('customerSearch');
    //     const resultBox = document.getElementById('searchResults');

    //     if (searchInput) {
    //         searchInput.addEventListener('input', function() {
    //             const query = this.value.trim();

    //             if (query.length < 1) {
    //                 resultBox.style.display = 'none';
    //                 return;
    //             }

    //             fetch(`/search-users?query=${encodeURIComponent(query)}`)
    //                 .then(response => response.json())
    //                 .then(data => {
    //                     resultBox.innerHTML = '';
    //                     let hasResults = false;

    //                     // Customers section
    //                     if (['admin', 'sales-manager', 'inventory-manager'].includes(currentUserRole) && data.users?.length > 0) {
    //                         hasResults = true;
    //                         resultBox.innerHTML += `<div class="list-group-item fw-bold bg-light">Customers</div>`;
    //                         data.users.forEach(user => {
    //                             const profileUrl = `/customer-view/${user.id}`;
    //                             resultBox.innerHTML += `
    //                                 <a href="${profileUrl}" class="list-group-item list-group-item-action d-flex align-items-center">
    //                                     <img src="${user.profile_image}" alt="User Image" class="rounded-circle me-2" style="width:35px; height:35px; object-fit: cover;">
    //                                     <div>
    //                                         <strong>${escapeHtml(user.name) ?? 'N/A'}</strong><br>
    //                                         <small class="text-muted">${escapeHtml(user.email) ?? 'N/A'}</small>
    //                                     </div>
    //                                 </a>
    //                             `;
    //                         });
    //                     }

    //                     // Vendors section
    //                     if (['admin', 'purchase-manager', 'inventory-manager'].includes(currentUserRole) && data.vendors?.length > 0) {
    //                         hasResults = true;
    //                         resultBox.innerHTML += `<div class="list-group-item fw-bold bg-light mt-2">Vendors</div>`;
    //                         data.vendors.forEach(vendor => {
    //                             const vendorUrl = `/vendor-view/${vendor.id}`;
    //                             resultBox.innerHTML += `
    //                                 <a href="${vendorUrl}" class="list-group-item list-group-item-action d-flex align-items-center">
    //                                     <img src="${vendor.profile_image}" alt="Vendor Image" class="rounded-circle me-2" style="width:35px; height:35px; object-fit: cover;">
    //                                     <div>
    //                                         <strong>${escapeHtml(vendor.name) ?? 'N/A'}</strong><br>
    //                                         <small class="text-muted">${escapeHtml(vendor.email) ?? 'N/A'}</small>
    //                                     </div>
    //                                 </a>
    //                             `;
    //                         });
    //                     }

    //                     // Products section
    //                     if (['admin', 'purchase-manager', 'inventory-manager'].includes(currentUserRole) && data.products?.length > 0) {
    //                         hasResults = true;
    //                         resultBox.innerHTML += `<div class="list-group-item fw-bold bg-light mt-2">Products</div>`;
    //                         data.products.forEach(product => {
    //                             resultBox.innerHTML += `
    //                                 <a href="/product-view/${product.id}" class="list-group-item list-group-item-action d-flex align-items-center">
    //                                     <img src="${product.image}" alt="Product Image" class="rounded me-2" style="width:35px; height:35px; object-fit: cover;">
    //                                     <div>
    //                                         <strong>${escapeHtml(product.name) ?? 'N/A'}</strong><br>
    //                                         <small class="text-muted">Price: ₹${product.price ?? 'N/A'}</small>
    //                                     </div>
    //                                 </a>
    //                             `;
    //                         });
    //                     }

    //                     // Orders section
    //                     if (['admin', 'sales-manager', 'inventory-manager'].includes(currentUserRole) && data.orders?.length > 0) {
    //                         hasResults = true;
    //                         resultBox.innerHTML += `<div class="list-group-item fw-bold bg-light mt-2">Orders</div>`;
    //                         data.orders.forEach(order => {
    //                             resultBox.innerHTML += `
    //                                 <a href="/sales-details/${order.id}" class="list-group-item list-group-item-action">
    //                                     <div class="d-flex align-items-center mb-1">
    //                                         <img src="{{ env('ImagePath') . '/admin/assets/img/icons/cart.svg' }}" width="20" height="20" class="me-2" alt="cart">
    //                                         <strong>Order #: ${order.order_number ?? 'N/A'}</strong>
    //                                     </div>
    //                                     <div>
    //                                         <small>Customer: ${escapeHtml(order.user_name) ?? 'N/A'}</small><br>
    //                                         <small>Total: ₹${order.total_amount ?? 'N/A'} | Status: ${order.payment_status ?? 'N/A'}</small>
    //                                     </div>
    //                                 </a>
    //                             `;
    //                         });
    //                     }

    //                     if (!hasResults) {
    //                         resultBox.innerHTML = '<div class="list-group-item text-center text-muted">No results found</div>';
    //                     }

    //                     resultBox.style.display = 'block';
    //                 })
    //                 .catch(error => {
    //                     console.error('Search error:', error);
    //                     resultBox.innerHTML = '<div class="list-group-item text-center text-danger">Error loading results</div>';
    //                     resultBox.style.display = 'block';
    //                 });
    //         });
    //     }

    //     // Hide dropdown on outside click
    //     document.addEventListener('click', function(e) {
    //         if (searchInput && resultBox && !searchInput.contains(e.target) && !resultBox.contains(e.target)) {
    //             resultBox.style.display = 'none';
    //         }
    //     });
    // });

    // // Helper function to escape HTML
    // function escapeHtml(text) {
    //     if (!text) return '';
    //     const div = document.createElement('div');
    //     div.textContent = text;
    //     return div.innerHTML;
    // }

    // // ==================== SIDEBAR TOGGLE FUNCTIONALITY ====================
    // $(document).on('mouseover', function(e) {
    //     const $toggleBtn = $('#toggle_btn1');
    //     const isTabletSize = $(window).width() >= 768 && $(window).width() <= 1024;
    //     const isButtonAvailable = $toggleBtn.length > 0 && (isTabletSize || $toggleBtn.is(':visible'));

    //     if ($('body').hasClass('mini-sidebar') && isButtonAvailable) {
    //         const isInsideSidebar = $(e.target).closest('.sidebar').length;
    //         if (isInsideSidebar) {
    //             $('body').addClass('expand-menu');
    //             $('.subdrop + ul').slideDown();
    //         } else {
    //             $('body').removeClass('expand-menu');
    //             $('.subdrop + ul').slideUp();
    //         }
    //     }
    // });

    // // Toggle button handler
    // $(document).on('click', '#toggle_btn1', function(e) {
    //     e.preventDefault();
    //     e.stopPropagation();

    //     if ($(this).data('processing')) {
    //         return false;
    //     }
    //     $(this).data('processing', true);

    //     const body = $('body');
    //     const $btn = $(this);

    //     if (body.hasClass('mini-sidebar')) {
    //         body.removeClass('mini-sidebar');
    //         $btn.addClass('active');
    //         $('.subdrop + ul').slideDown();
    //         localStorage.setItem('screenModeNightTokenState', 'night');
    //         setTimeout(function() {
    //             body.removeClass('mini-sidebar');
    //             $('.header-left').addClass('active');
    //         }, 100);
    //     } else {
    //         body.addClass('mini-sidebar');
    //         $btn.removeClass('active');
    //         $('.subdrop + ul').slideUp();
    //         localStorage.removeItem('screenModeNightTokenState');
    //         setTimeout(function() {
    //             body.addClass('mini-sidebar');
    //             $('.header-left').removeClass('active');
    //         }, 100);
    //     }

    //     setTimeout(() => {
    //         $btn.data('processing', false);
    //     }, 300);

    //     return false;
    // });

    // // ==================== BRANCH DROPDOWN FUNCTIONALITY ====================
    // (function initializeBranchDropdown() {
    //     const container = document.getElementById('subBranchContainer');
    //     const select = document.getElementById('subBrandSelect');
    //     if (container) {
    //         container.style.display = 'block';
    //     }
    //     if (select) {
    //         select.innerHTML = "";
    //         const mainOption = document.createElement('option');
    //         mainOption.value = "";
    //         mainOption.textContent = 'Main Branch';
    //         select.appendChild(mainOption);
    //         if (window.$ && $.fn && $.fn.select2) {
    //             $('#subBrandSelect').select2({
    //                 placeholder: 'Select a branch',
    //                 allowClear: true
    //             });
    //         }
    //     }
    // })();

    // fetch('/api/get_subadmin', {
    //     headers: {
    //         "Authorization": "Bearer " + localStorage.getItem("authToken"),
    //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //     }
    // })
    // .then(res => res.json())
    // .then(response => {
    //     if (!response) {
    //         console.warn('No response from /api/get_subadmin');
    //         return;
    //     }

    //     const select = document.getElementById("subBrandSelect");
    //     if (!select) return;

    //     select.innerHTML = "";
    //     document.getElementById('subBranchContainer').style.display = 'block';

    //     const mainOption = document.createElement("option");
    //     mainOption.value = "";
    //     mainOption.textContent = "Main Branch";
    //     select.appendChild(mainOption);

    //     (response.data || []).forEach(function(item) {
    //         const option = document.createElement("option");
    //         option.value = item.id;
    //         option.textContent = item.name;
    //         select.appendChild(option);
    //     });

    //     if (window.$ && $.fn && $.fn.select2) {
    //         $('#subBrandSelect').select2({
    //             placeholder: 'Select a branch',
    //             allowClear: true
    //         });
    //     }

    //     const savedId = localStorage.getItem('selectedSubAdminId');
    //     if (savedId) {
    //         const exists = (response.data || []).some(function(item) {
    //             return String(item.id) === String(savedId);
    //         });
    //         if (exists) {
    //             $('#subBrandSelect').val(savedId).trigger('change.select2');
    //         } else {
    //             $.post('/clear-subadmin-session', {
    //                 _token: $('meta[name="csrf-token"]').attr('content')
    //             }, function() {
    //                 localStorage.removeItem('selectedSubAdminId');
    //             });
    //         }
    //     }
    // })
    // .catch(function(error) {
    //     console.error('Failed to load sub-admin list:', error);
    // });

    // $('#subBrandSelect').on('change', function() {
    //     const selectedId = $(this).val();
    //     const selectedText = $(this).find('option:selected').text() || '';
    //     $('#currentSelection').text(selectedId ? ('Selected: ' + selectedText) : '');

    //     if (selectedId) {
    //         localStorage.setItem('selectedSubAdminId', selectedId);
    //         $.post('/set-subadmin-session', {
    //             _token: $('meta[name="csrf-token"]').attr('content'),
    //             subAdminId: selectedId
    //         }, function() {
    //             window.location.href = "{{ route('auth.dashboard') }}";
    //         });
    //     } else if (selectedText === "Main Branch") {
    //         $.post('/clear-subadmin-session', {
    //             _token: $('meta[name="csrf-token"]').attr('content')
    //         }, function() {
    //             localStorage.removeItem('selectedSubAdminId');
    //             window.location.href = '/dashboard';
    //         });
    //     }
    // });

    // // ==================== NOTIFICATION FUNCTIONALITY ====================
    // let notificationRefreshInterval = null;

    // document.addEventListener('DOMContentLoaded', function() {
    //     initializeNotifications();

    //     // Auto-refresh every 30 seconds
    //     if (notificationRefreshInterval) {
    //         clearInterval(notificationRefreshInterval);
    //     }
    //     notificationRefreshInterval = setInterval(() => {
    //         const menu = document.getElementById('notificationMenu');
    //         if (menu && menu.style.display === 'block') {
    //             loadNotifications();
    //         }
    //     }, 30000);
    // });

    // function initializeNotifications() {
    //     const notificationToggle = document.getElementById('notificationToggle');
    //     const notificationMenu = document.getElementById('notificationMenu');

    //     if (notificationToggle) {
    //         // Remove any existing event listeners
    //         const newToggle = notificationToggle.cloneNode(true);
    //         notificationToggle.parentNode.replaceChild(newToggle, notificationToggle);

    //         newToggle.addEventListener('click', function(e) {
    //             e.preventDefault();
    //             e.stopPropagation();

    //             if (notificationMenu.style.display === 'block') {
    //                 notificationMenu.style.display = 'none';
    //             } else {
    //                 loadNotifications();
    //                 notificationMenu.style.display = 'block';
    //             }
    //         });
    //     }

    //     // Close dropdown when clicking outside
    //     document.addEventListener('click', function(e) {
    //         if (notificationToggle && notificationMenu) {
    //             if (!notificationToggle.contains(e.target) && !notificationMenu.contains(e.target)) {
    //                 notificationMenu.style.display = 'none';
    //             }
    //         }
    //     });

    //     // Initial load
    //     loadNotifications();
    // }

    // function loadNotifications() {
    //     const token = document.querySelector('meta[name="csrf-token"]');
    //     if (!token) {
    //         console.error('CSRF token not found');
    //         return;
    //     }

    //     fetch('/notifications', {
    //         headers: {
    //             'X-CSRF-TOKEN': token.getAttribute('content'),
    //             'Content-Type': 'application/json',
    //             'Accept': 'application/json'
    //         },
    //         credentials: 'same-origin'
    //     })
    //     .then(response => {
    //         if (!response.ok) {
    //             throw new Error('Network response was not ok');
    //         }
    //         return response.json();
    //     })
    //     .then(response => {
    //         const list = document.getElementById('notificationList');
    //         const count = document.getElementById('notificationCount');
    //         const markAllBtn = document.getElementById('markAllReadBtn');

    //         if (!list) return;

    //         list.innerHTML = '';

    //         if (!response.status || !response.data || response.data.length === 0) {
    //             list.innerHTML = `
    //                 <div class="empty-notification">
    //                     <i class="fa fa-bell-slash"></i>
    //                     No notifications
    //                 </div>
    //             `;
    //             if (count) count.classList.add('d-none');
    //             if (markAllBtn) markAllBtn.style.display = 'none';
    //             return;
    //         }

    //         // Update count badge
    //         if (count) {
    //             const unreadCount = response.count || response.data.filter(n => !n.is_read).length;
    //             if (unreadCount > 0) {
    //                 count.innerText = unreadCount > 99 ? '99+' : unreadCount;
    //                 count.classList.remove('d-none');
    //             } else {
    //                 count.classList.add('d-none');
    //             }
    //         }

    //         // Show mark all button if there are unread notifications
    //         if (markAllBtn) {
    //             const hasUnread = response.data.some(n => !n.is_read);
    //             markAllBtn.style.display = hasUnread ? 'block' : 'none';
    //         }

    //         // Render notifications
    //         response.data.forEach(item => {
    //             const notificationItem = document.createElement('div');
    //             notificationItem.className = `notification-item ${!item.is_read ? 'unread-notification' : ''}`;
    //             notificationItem.setAttribute('data-id', item.id);
    //             notificationItem.onclick = function() {
    //                 handleNotificationClick(item.id, item.link);
    //             };

    //             notificationItem.innerHTML = `
    //                 <div class="d-flex justify-content-between align-items-start">
    //                     <div class="flex-grow-1">
    //                         <strong class="notification-title">${escapeHtml(item.title)}</strong>
    //                         <span class="notification-message">${escapeHtml(item.message)}</span>
    //                         <small class="notification-time">
    //                             <i class="fa fa-clock-o"></i>
    //                             ${formatDate(item.created_at)}
    //                         </small>
    //                     </div>
    //                     ${!item.is_read ? '<span class="notification-dot"></span>' : ''}
    //                 </div>
    //             `;

    //             list.appendChild(notificationItem);
    //         });
    //     })
    //     .catch(error => {
    //         console.error('Error loading notifications:', error);
    //         const list = document.getElementById('notificationList');
    //         if (list) {
    //             list.innerHTML = `
    //                 <div class="empty-notification text-danger">
    //                     <i class="fa fa-exclamation-circle"></i>
    //                     Failed to load notifications
    //                 </div>
    //             `;
    //         }
    //     });
    // }

    // function handleNotificationClick(id, link) {
    //     // Mark as read
    //     const token = document.querySelector('meta[name="csrf-token"]');
    //     if (!token) return;

    //     fetch(`/notifications/${id}/read`, {
    //         method: 'POST',
    //         headers: {
    //             'X-CSRF-TOKEN': token.getAttribute('content'),
    //             'Content-Type': 'application/json'
    //         },
    //         credentials: 'same-origin'
    //     })
    //     .then(() => {
    //         // Update the UI
    //         const notificationItem = document.querySelector(`.notification-item[data-id="${id}"]`);
    //         if (notificationItem) {
    //             notificationItem.classList.remove('unread-notification');
    //             const dot = notificationItem.querySelector('.notification-dot');
    //             if (dot) dot.remove();
    //         }

    //         // Update count
    //         updateNotificationCount();

    //         // Redirect if link exists
    //         if (link && link !== '#') {
    //             window.location.href = link;
    //         }
    //     })
    //     .catch(error => {
    //         console.error('Error marking notification as read:', error);
    //         // Still redirect even if marking fails
    //         if (link && link !== '#') {
    //             window.location.href = link;
    //         }
    //     });
    // }

    // function markAllNotificationsAsRead() {
    //     const token = document.querySelector('meta[name="csrf-token"]');
    //     if (!token) return;

    //     fetch('/notifications/mark-all-read', {
    //         method: 'POST',
    //         headers: {
    //             'X-CSRF-TOKEN': token.getAttribute('content'),
    //             'Content-Type': 'application/json'
    //         },
    //         credentials: 'same-origin'
    //     })
    //     .then(response => response.json())
    //     .then(() => {
    //         // Reload notifications to update UI
    //         loadNotifications();
    //     })
    //     .catch(error => {
    //         console.error('Error marking all as read:', error);
    //     });
    // }

    // function updateNotificationCount() {
    //     const token = document.querySelector('meta[name="csrf-token"]');
    //     if (!token) return;

    //     fetch('/notifications', {
    //         headers: {
    //             'X-CSRF-TOKEN': token.getAttribute('content'),
    //             'Content-Type': 'application/json'
    //         },
    //         credentials: 'same-origin'
    //     })
    //     .then(response => response.json())
    //     .then(response => {
    //         const count = document.getElementById('notificationCount');
    //         if (count) {
    //             const unreadCount = response.count || (response.data ? response.data.filter(n => !n.is_read).length : 0);
    //             if (unreadCount > 0) {
    //                 count.innerText = unreadCount > 99 ? '99+' : unreadCount;
    //                 count.classList.remove('d-none');
    //             } else {
    //                 count.classList.add('d-none');
    //             }
    //         }
    //     })
    //     .catch(error => {
    //         console.error('Error updating count:', error);
    //     });
    // }

    // function formatDate(dateString) {
    //     const date = new Date(dateString);
    //     const now = new Date();
    //     const diffMs = now - date;
    //     const diffMins = Math.floor(diffMs / 60000);
    //     const diffHours = Math.floor(diffMs / 3600000);
    //     const diffDays = Math.floor(diffMs / 86400000);

    //     if (diffMins < 1) return 'Just now';
    //     if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    //     if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    //     if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;

    //     return date.toLocaleDateString('en-IN', {
    //         day: 'numeric',
    //         month: 'short',
    //         year: 'numeric'
    //     });
    // }

// ==================== SEARCH FUNCTIONALITY ====================
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('customerSearch');
        const resultBox = document.getElementById('searchResults');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();

                if (query.length < 1) {
                    resultBox.style.display = 'none';
                    return;
                }

                fetch(`/search-users?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        resultBox.innerHTML = '';
                        let hasResults = false;

                        // ✅ Customers section
                        if (['admin', 'sales-manager', 'inventory-manager', 'staff'].includes(currentUserRole) && data.users?.length > 0) {
                            hasResults = true;
                            resultBox.innerHTML += `<div class="list-group-item fw-bold bg-light">Customers</div>`;
                            data.users.forEach(user => {
                                const profileUrl = `/customer-view/${user.id}`;
                                resultBox.innerHTML += `
                                    <a href="${profileUrl}" class="list-group-item list-group-item-action d-flex align-items-center">
                                        <img src="${user.profile_image}" alt="User Image" class="rounded-circle me-2" style="width:35px; height:35px; object-fit: cover;">
                                        <div>
                                            <strong>${escapeHtml(user.name) ?? 'N/A'}</strong><br>
                                            <small class="text-muted">${escapeHtml(user.email) ?? 'N/A'}</small>
                                        </div>
                                    </a>
                                `;
                            });
                        }

                        // ✅ Vendors section
                        if (['admin', 'purchase-manager', 'inventory-manager', 'staff'].includes(currentUserRole) && data.vendors?.length > 0) {
                            hasResults = true;
                            resultBox.innerHTML += `<div class="list-group-item fw-bold bg-light mt-2">Vendors</div>`;
                            data.vendors.forEach(vendor => {
                                const vendorUrl = `/vendor-view/${vendor.id}`;
                                resultBox.innerHTML += `
                                    <a href="${vendorUrl}" class="list-group-item list-group-item-action d-flex align-items-center">
                                        <img src="${vendor.profile_image}" alt="Vendor Image" class="rounded-circle me-2" style="width:35px; height:35px; object-fit: cover;">
                                        <div>
                                            <strong>${escapeHtml(vendor.name) ?? 'N/A'}</strong><br>
                                            <small class="text-muted">${escapeHtml(vendor.email) ?? 'N/A'}</small>
                                        </div>
                                    </a>
                                `;
                            });
                        }

                        // ✅ Products section
                        if (['admin', 'purchase-manager', 'inventory-manager', 'staff'].includes(currentUserRole) && data.products?.length > 0) {
                            hasResults = true;
                            resultBox.innerHTML += `<div class="list-group-item fw-bold bg-light mt-2">Products</div>`;
                            data.products.forEach(product => {
                                resultBox.innerHTML += `
                                    <a href="/product-view/${product.id}" class="list-group-item list-group-item-action d-flex align-items-center">
                                        <img src="${product.image}" alt="Product Image" class="rounded me-2" style="width:35px; height:35px; object-fit: cover;">
                                        <div>
                                            <strong>${escapeHtml(product.name) ?? 'N/A'}</strong><br>
                                            <small class="text-muted">Price: ₹${product.price ?? 'N/A'}</small>
                                        </div>
                                    </a>
                                `;
                            });
                        }

                        // ✅ Orders section
                        if (['admin', 'sales-manager', 'inventory-manager', 'staff'].includes(currentUserRole) && data.orders?.length > 0) {
                            hasResults = true;
                            resultBox.innerHTML += `<div class="list-group-item fw-bold bg-light mt-2">Orders</div>`;
                            data.orders.forEach(order => {
                                resultBox.innerHTML += `
                                    <a href="/sales-details/${order.id}" class="list-group-item list-group-item-action">
                                        <div class="d-flex align-items-center mb-1">
                                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/cart.svg' }}" width="20" height="20" class="me-2" alt="cart">
                                            <strong>Order #: ${order.order_number ?? 'N/A'}</strong>
                                        </div>
                                        <div>
                                            <small>Customer: ${escapeHtml(order.user_name) ?? 'N/A'}</small><br>
                                            <small>Total: ₹${order.total_amount ?? 'N/A'} | Status: ${order.payment_status ?? 'N/A'}</small>
                                        </div>
                                    </a>
                                `;
                            });
                        }

                        if (!hasResults) {
                            resultBox.innerHTML = '<div class="list-group-item text-center text-muted">No results found</div>';
                        }

                        resultBox.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        resultBox.innerHTML = '<div class="list-group-item text-center text-danger">Error loading results</div>';
                        resultBox.style.display = 'block';
                    });
            });
        }

        // Hide dropdown on outside click
        document.addEventListener('click', function(e) {
            if (searchInput && resultBox && !searchInput.contains(e.target) && !resultBox.contains(e.target)) {
                resultBox.style.display = 'none';
            }
        });
    });

    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ==================== SIDEBAR TOGGLE FUNCTIONALITY ====================
    $(document).on('mouseover', function(e) {
        const $toggleBtn = $('#toggle_btn1');
        const isTabletSize = $(window).width() >= 768 && $(window).width() <= 1024;
        const isButtonAvailable = $toggleBtn.length > 0 && (isTabletSize || $toggleBtn.is(':visible'));

        if ($('body').hasClass('mini-sidebar') && isButtonAvailable) {
            const isInsideSidebar = $(e.target).closest('.sidebar').length;
            if (isInsideSidebar) {
                $('body').addClass('expand-menu');
                $('.subdrop + ul').slideDown();
            } else {
                $('body').removeClass('expand-menu');
                $('.subdrop + ul').slideUp();
            }
        }
    });

    // Toggle button handler
    $(document).on('click', '#toggle_btn1', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if ($(this).data('processing')) {
            return false;
        }
        $(this).data('processing', true);

        const body = $('body');
        const $btn = $(this);

        if (body.hasClass('mini-sidebar')) {
            body.removeClass('mini-sidebar');
            $btn.addClass('active');
            $('.subdrop + ul').slideDown();
            localStorage.setItem('screenModeNightTokenState', 'night');
            setTimeout(function() {
                body.removeClass('mini-sidebar');
                $('.header-left').addClass('active');
            }, 100);
        } else {
            body.addClass('mini-sidebar');
            $btn.removeClass('active');
            $('.subdrop + ul').slideUp();
            localStorage.removeItem('screenModeNightTokenState');
            setTimeout(function() {
                body.addClass('mini-sidebar');
                $('.header-left').removeClass('active');
            }, 100);
        }

        setTimeout(() => {
            $btn.data('processing', false);
        }, 300);

        return false;
    });

    // ==================== BRANCH DROPDOWN FUNCTIONALITY ====================
    function initializeSubBranchSelect2() {
        if (!(window.$ && $.fn && $.fn.select2)) return;

        const $select = $('#subBrandSelect');
        const $container = $('#subBranchContainer');
        if (!$select.length) return;

        $select.select2({
            placeholder: 'Select a branch',
            allowClear: true,
            width: 'resolve',
            dropdownParent: $container.length ? $container : $(document.body)
        });
    }

    (function initializeBranchDropdown() {
        const container = document.getElementById('subBranchContainer');
        const select = document.getElementById('subBrandSelect');
        if (container) {
            container.style.display = 'block';
        }
        if (select) {
            select.innerHTML = "";
            const mainOption = document.createElement('option');
            mainOption.value = "";
            mainOption.textContent = 'Main Branch';
            select.appendChild(mainOption);
            initializeSubBranchSelect2();
        }
    })();

    fetch('/api/get_subadmin', {
        headers: {
            "Authorization": "Bearer " + localStorage.getItem("authToken"),
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .then(res => res.json())
    .then(response => {
        if (!response) {
            console.warn('No response from /api/get_subadmin');
            return;
        }

        const select = document.getElementById("subBrandSelect");
        if (!select) return;

        select.innerHTML = "";
        document.getElementById('subBranchContainer').style.display = 'block';

        const mainOption = document.createElement("option");
        mainOption.value = "";
        mainOption.textContent = "Main Branch";
        select.appendChild(mainOption);

        (response.data || []).forEach(function(item) {
            const option = document.createElement("option");
            option.value = item.id;
            option.textContent = item.name;
            select.appendChild(option);
        });

        initializeSubBranchSelect2();

        const savedId = localStorage.getItem('selectedSubAdminId');
        if (savedId) {
            const exists = (response.data || []).some(function(item) {
                return String(item.id) === String(savedId);
            });
            if (exists) {
                $('#subBrandSelect').val(savedId).trigger('change.select2');
            } else {
                $.post('/clear-subadmin-session', {
                    _token: $('meta[name="csrf-token"]').attr('content')
                }, function() {
                    localStorage.removeItem('selectedSubAdminId');
                });
            }
        }
    })
    .catch(function(error) {
        console.error('Failed to load sub-admin list:', error);
    });

    $('#subBrandSelect').on('change', function() {
        const selectedId = $(this).val();
        const selectedText = $(this).find('option:selected').text() || '';
        $('#currentSelection').text(selectedId ? ('Selected: ' + selectedText) : '');

        if (selectedId) {
            localStorage.setItem('selectedSubAdminId', selectedId);
            $.post('/set-subadmin-session', {
                _token: $('meta[name="csrf-token"]').attr('content'),
                subAdminId: selectedId
            }, function() {
                window.location.href = "{{ route('auth.dashboard') }}";
            });
        } else if (selectedText === "Main Branch") {
            $.post('/clear-subadmin-session', {
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function() {
                localStorage.removeItem('selectedSubAdminId');
                window.location.href = '/dashboard';
            });
        }
    });


    // ==================== NOTIFICATION FUNCTIONALITY ====================
let notificationRefreshInterval = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeNotifications();

    // Auto-refresh every 30 seconds
    if (notificationRefreshInterval) {
        clearInterval(notificationRefreshInterval);
    }
    notificationRefreshInterval = setInterval(() => {
        const menu = document.getElementById('notificationMenu');
        if (menu && menu.style.display === 'block') {
            loadNotifications();
        }
    }, 30000);
});

function initializeNotifications() {
    const notificationToggle = document.getElementById('notificationToggle');
    const notificationMenu = document.getElementById('notificationMenu');

    if (notificationToggle) {
        // Remove any existing event listeners
        const newToggle = notificationToggle.cloneNode(true);
        notificationToggle.parentNode.replaceChild(newToggle, notificationToggle);

        newToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (notificationMenu.style.display === 'block') {
                notificationMenu.style.display = 'none';
            } else {
                loadNotifications();
                notificationMenu.style.display = 'block';
            }
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (notificationToggle && notificationMenu) {
            if (!notificationToggle.contains(e.target) && !notificationMenu.contains(e.target)) {
                notificationMenu.style.display = 'none';
            }
        }
    });

    // Initial load
    loadNotifications();
}

function loadNotifications() {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (!token) {
        console.error('CSRF token not found');
        return;
    }

    // Get selected subadmin ID from localStorage if exists
    const selectedSubAdminId = localStorage.getItem('selectedSubAdminId') || '';

    fetch('/notifications?selectedSubAdminId=' + selectedSubAdminId, {
        headers: {
            'X-CSRF-TOKEN': token.getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(response => {
        const list = document.getElementById('notificationList');
        const count = document.getElementById('notificationCount');
        const markAllBtn = document.getElementById('markAllReadBtn');

        if (!list) return;

        list.innerHTML = '';

        if (!response.status || !response.data || response.data.length === 0) {
            list.innerHTML = `
                <div class="empty-notification">
                    <i class="fa fa-bell-slash"></i>
                    No notifications
                </div>
            `;
            if (count) count.classList.add('d-none');
            if (markAllBtn) markAllBtn.style.display = 'none';
            return;
        }

        // Update count badge
        if (count) {
            const unreadCount = response.count || response.data.filter(n => !n.is_read).length;
            if (unreadCount > 0) {
                count.innerText = unreadCount > 99 ? '99+' : unreadCount;
                count.classList.remove('d-none');
            } else {
                count.classList.add('d-none');
            }
        }

        // Show mark all button if there are unread notifications
        if (markAllBtn) {
            const hasUnread = response.data.some(n => !n.is_read);
            markAllBtn.style.display = hasUnread ? 'block' : 'none';
        }

        // Render notifications
        response.data.forEach(item => {
            const notificationItem = document.createElement('div');
            notificationItem.className = `notification-item ${!item.is_read ? 'unread-notification' : ''}`;
            notificationItem.setAttribute('data-id', item.id);
            notificationItem.onclick = function(e) {
                // Prevent click if clicking on action buttons
                if (e.target.closest('.notification-action')) {
                    return;
                }
                handleNotificationClick(item.id, item.link);
            };

            notificationItem.innerHTML = `
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <strong class="notification-title">${escapeHtml(item.title)}</strong>
                        <span class="notification-message">${escapeHtml(item.message)}</span>
                        <small class="notification-time">
                            <i class="fa fa-clock-o"></i>
                            ${formatDate(item.created_at)}
                        </small>
                    </div>
                    ${!item.is_read ? '<span class="notification-dot"></span>' : ''}
                </div>
            `;

            list.appendChild(notificationItem);
        });
    })
    .catch(error => {
        console.error('Error loading notifications:', error);
        const list = document.getElementById('notificationList');
        if (list) {
            list.innerHTML = `
                <div class="empty-notification text-danger">
                    <i class="fa fa-exclamation-circle"></i>
                    Failed to load notifications
                </div>
            `;
        }
    });
}

function handleNotificationClick(id, link) {
    // Mark as read
    const token = document.querySelector('meta[name="csrf-token"]');
    if (!token) return;

    fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token.getAttribute('content'),
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(() => {
        // Update the UI
        const notificationItem = document.querySelector(`.notification-item[data-id="${id}"]`);
        if (notificationItem) {
            notificationItem.classList.remove('unread-notification');
            const dot = notificationItem.querySelector('.notification-dot');
            if (dot) dot.remove();
        }

        // Update count
        updateNotificationCount();

        // Redirect if link exists
        if (link && link !== '#') {
            window.location.href = link;
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
        // Still redirect even if marking fails
        if (link && link !== '#') {
            window.location.href = link;
        }
    });
}

function markAllNotificationsAsRead() {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (!token) return;

    const selectedSubAdminId = localStorage.getItem('selectedSubAdminId') || '';

    fetch('/notifications/mark-all-read?selectedSubAdminId=' + selectedSubAdminId, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token.getAttribute('content'),
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(() => {
        // Reload notifications to update UI
        loadNotifications();
    })
    .catch(error => {
        console.error('Error marking all as read:', error);
    });
}

function updateNotificationCount() {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (!token) return;

    const selectedSubAdminId = localStorage.getItem('selectedSubAdminId') || '';

    fetch('/notifications?selectedSubAdminId=' + selectedSubAdminId, {
        headers: {
            'X-CSRF-TOKEN': token.getAttribute('content'),
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(response => {
        const count = document.getElementById('notificationCount');
        if (count) {
            const unreadCount = response.count || 0;
            if (unreadCount > 0) {
                count.innerText = unreadCount > 99 ? '99+' : unreadCount;
                count.classList.remove('d-none');
            } else {
                count.classList.add('d-none');
            }
        }
    })
    .catch(error => {
        console.error('Error updating count:', error);
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;

    return date.toLocaleDateString('en-IN', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<style>
    /* Additional styles for search results */
    .list-group-item-action {
        transition: all 0.2s ease;
    }

    .list-group-item-action:hover {
        background-color: #fef9f0 !important;
        transform: translateX(2px);
    }

    /* Notification animation */
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .notification-dropdown {
        animation: slideDown 0.2s ease;
    }

    /* Scrollbar styling */
    .notification-body::-webkit-scrollbar {
        width: 5px;
    }

    .notification-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .notification-body::-webkit-scrollbar-thumb {
        background: #ff9f43;
        border-radius: 5px;
    }

    .notification-body::-webkit-scrollbar-thumb:hover {
        background: #ff8c2e;
    }

    /* Search input focus */
    #customerSearch:focus {
        border-color: #ff9f43;
        box-shadow: 0 0 0 0.2rem rgba(255, 159, 67, 0.25);
    }
</style>

    {{-- ==================== ATTENDANCE CHECK IN/OUT (same as omsai-ERP) ==================== --}}
    <script>
        (function() {
            // Guard: only run for staff role
            console.log('[Attendance] currentUserRole =', typeof currentUserRole !== 'undefined' ? currentUserRole : 'UNDEFINED');
            if (typeof currentUserRole === 'undefined' || currentUserRole !== 'staff') {
                console.log('[Attendance] Skipping — user is not staff.');
                return;
            }

            var btnIns  = document.querySelectorAll('.btn-check-in');
            var btnOuts = document.querySelectorAll('.btn-check-out');

            console.log('[Attendance] check-in buttons found:', btnIns.length, '| check-out buttons found:', btnOuts.length);

            if (btnIns.length === 0 || btnOuts.length === 0) return;

            var csrfMeta  = document.querySelector('meta[name="csrf-token"]');
            var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

            // ── State helpers ──────────────────────────────────────────────
            function showCheckIn() {
                btnIns.forEach(function(btn) { btn.style.display = 'flex'; });
                btnOuts.forEach(function(btn) { btn.style.display = 'none'; });
                console.log('[Attendance] → Showing Check In');
            }
            function showCheckOut() {
                btnIns.forEach(function(btn) { btn.style.display = 'none'; });
                btnOuts.forEach(function(btn) { btn.style.display = 'flex'; });
                console.log('[Attendance] → Showing Check Out');
            }

            // ── Fetch today's status on page load ──────────────────────────
            function fetchStatus() {
                fetch("{{ route('staff.checkstatus', [], false) }}", {
                    method: 'GET',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                })
                .then(function(r) {
                    console.log('[Attendance] status response HTTP:', r.status);
                    return r.ok ? r.json() : Promise.reject('HTTP ' + r.status);
                })
                .then(function(data) {
                    console.log('[Attendance] status data:', data);
                    if (data.status === 'checked_in') {
                        showCheckOut();
                    } else {
                        showCheckIn();
                    }
                })
                .catch(function(err) {
                    console.warn('[Attendance] status fetch failed:', err);
                    showCheckIn(); // safe fallback
                });
            }

            fetchStatus();

            // ── Check In click ─────────────────────────────────────────────
            btnIns.forEach(function(btn) {
                btn.addEventListener('click', function() {

                    // Disable all check-in buttons and show spinner
                    btnIns.forEach(function(b) {
                        b.disabled = true;
                        b.innerHTML = '<i class="fa fa-spinner fa-spin"></i>&nbsp;Getting GPS...';
                    });

                    function resetBtnIns() {
                        btnIns.forEach(function(b) {
                            b.disabled = false;
                            b.innerHTML = '<i class="fa fa-sign-in-alt"></i>&nbsp;Check In';
                        });
                    }

                    async function doCheckIn(latitude, longitude) {
                        btnIns.forEach(function(b) {
                            b.innerHTML = '<i class="fa fa-spinner fa-spin"></i>&nbsp;Checking In...';
                        });

                        var body = {};
                        if (latitude !== undefined && longitude !== undefined) {
                            body.check_in_latitude  = latitude;
                            body.check_in_longitude = longitude;

                            try {
                                btnIns.forEach(function(b) { b.innerHTML = '<i class="fa fa-spinner fa-spin"></i>&nbsp;Getting Address...'; });
                                let geoRes = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`);
                                let geoData = await geoRes.json();
                                if (geoData && geoData.display_name) {
                                    body.check_in_location_name = geoData.display_name;
                                }
                            } catch (e) {
                                console.warn('[CheckIn] Geocoding failed:', e);
                            }

                            btnIns.forEach(function(b) { b.innerHTML = '<i class="fa fa-spinner fa-spin"></i>&nbsp;Checking In...'; });
                        }

                        fetch("{{ route('staff.checkin', [], false) }}", {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify(body)
                        })
                        .then(async function(r) {
                            var text = await r.text();
                            try {
                                var data = JSON.parse(text);
                                return { ok: r.ok, status: r.status, data: data };
                            } catch (e) {
                                console.error('[CheckIn] Server returned non-JSON:', text);
                                throw new Error('Server returned an invalid response (HTTP ' + r.status + ')');
                            }
                        })
                        .then(function(res) {
                            var data = res.data;
                            if (res.ok || data.success) {
                                Swal.fire({ toast:true, position:'top', icon:'success', title: data.message || 'Checked In successfully.', showConfirmButton:false, timer:2000, timerProgressBar:true })
                                    .then(function() { window.location.reload(); });
                                showCheckOut();
                            } else {
                                Swal.fire({ toast:true, position:'top', icon:'error', title: data.error || 'Check In failed.', showConfirmButton:false, timer:5000 });
                            }
                        })
                        .catch(function(err) {
                            console.error('[CheckIn] Request failed:', err);
                            Swal.fire({ toast:true, position:'top', icon:'error', title: err.message || 'Check In failed. Please try again.', showConfirmButton:false, timer:5000 });
                        })
                        .finally(resetBtnIns);
                    }

                    // Get GPS then check in
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                var lat      = position.coords.latitude;
                                var lon      = position.coords.longitude;
                                var accuracy = position.coords.accuracy;
                                console.log('[CheckIn] GPS: ' + lat + ', ' + lon + ' | Accuracy: ' + accuracy + 'm');
                                doCheckIn(lat, lon);
                            },
                            function(error) {
                                console.warn('[CheckIn] GPS error (code ' + error.code + '):', error.message);
                                if (error.code === 1) {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Location Access Required',
                                        html: 'Please allow location access in your browser and try again.<br><br><small>On iPhone: Settings → Safari → Location → Allow</small>',
                                        confirmButtonText: 'OK'
                                    });
                                    resetBtnIns();
                                } else {
                                    // GPS timeout / unavailable — proceed without coords
                                    doCheckIn();
                                }
                            },
                            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                        );
                    } else {
                        doCheckIn();
                    }

                }); // end click listener
            }); // end btnIns.forEach

            // ── Check Out click ────────────────────────────────────────────
            btnOuts.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    btnOuts.forEach(function(b) {
                        b.disabled = true;
                        b.innerHTML = '<i class="fa fa-spinner fa-spin"></i>&nbsp;Getting GPS...';
                    });

                    async function doCheckOut(latitude, longitude) {
                        btnOuts.forEach(function(b) {
                            b.innerHTML = '<i class="fa fa-spinner fa-spin"></i>&nbsp;Checking Out...';
                        });

                        var body = {};
                        if (latitude !== undefined && longitude !== undefined) {
                            body.check_out_latitude  = latitude;
                            body.check_out_longitude = longitude;

                            try {
                                btnOuts.forEach(function(b) { b.innerHTML = '<i class="fa fa-spinner fa-spin"></i>&nbsp;Getting Address...'; });
                                let geoRes = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`);
                                let geoData = await geoRes.json();
                                if (geoData && geoData.display_name) {
                                    body.check_out_location_name = geoData.display_name;
                                }
                            } catch (e) {
                                console.warn('[CheckOut] Geocoding failed:', e);
                            }

                            btnOuts.forEach(function(b) { b.innerHTML = '<i class="fa fa-spinner fa-spin"></i>&nbsp;Checking Out...'; });
                        }

                        fetch("{{ route('staff.checkout', [], false) }}", {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify(body)
                        })
                        .then(async function(r) {
                            var text = await r.text();
                            try {
                                var data = JSON.parse(text);
                                return { ok: r.ok, status: r.status, data: data };
                            } catch (e) {
                                console.error('[CheckOut] Server returned non-JSON:', text);
                                throw new Error('Server returned an invalid response (HTTP ' + r.status + ')');
                            }
                        })
                        .then(function(res) {
                            var data = res.data;
                            if (res.ok || data.success) {
                                Swal.fire({ toast:true, position:'top', icon:'success', title: data.message || 'Checked Out successfully.', showConfirmButton:false, timer:2000, timerProgressBar:true })
                                    .then(function() { window.location.reload(); });
                                showCheckIn();
                            } else {
                                Swal.fire({ toast:true, position:'top', icon:'error', title: data.error || 'Check Out failed.', showConfirmButton:false, timer:5000 });
                            }
                        })
                        .catch(function(err) {
                            console.error('[CheckOut] Request failed:', err);
                            Swal.fire({ toast:true, position:'top', icon:'error', title: err.message || 'Check Out failed. Please try again.', showConfirmButton:false, timer:5000 });
                        })
                        .finally(function() {
                            btnOuts.forEach(function(b) {
                                b.disabled = false;
                                b.innerHTML = '<i class="fa fa-sign-out-alt"></i>&nbsp;Check Out';
                            });
                        });
                    }

                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                var lat = position.coords.latitude;
                                var lon = position.coords.longitude;
                                doCheckOut(lat, lon);
                            },
                            function(error) {
                                console.warn('[CheckOut] GPS error:', error.message);
                                if (error.code === 1) {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Location Access Required',
                                        html: 'Please allow location access in your browser and try again.',
                                        confirmButtonText: 'OK'
                                    });
                                    btnOuts.forEach(function(b) {
                                        b.disabled = false;
                                        b.innerHTML = '<i class="fa fa-sign-out-alt"></i>&nbsp;Check Out';
                                    });
                                } else {
                                    doCheckOut();
                                }
                            },
                            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                        );
                    } else {
                        doCheckOut();
                    }
                });
            });

        })();
    </script>
 <style>
        /* Additional styles for search results */
        .list-group-item-action {
            transition: all 0.2s ease;
        }

        .list-group-item-action:hover {
            background-color: #fef9f0 !important;
            transform: translateX(2px);
        }

        /* Notification animation */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notification-dropdown {
            animation: slideDown 0.2s ease;
        }

        /* Scrollbar styling */
        .notification-body::-webkit-scrollbar {
            width: 5px;
        }

        .notification-body::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .notification-body::-webkit-scrollbar-thumb {
            background: #ff9f43;
            border-radius: 5px;
        }

        .notification-body::-webkit-scrollbar-thumb:hover {
            background: #ff8c2e;
        }

        .mobile-header-notification {
            display: none;
            position: absolute;
            right: 84px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            margin: 0;
        }

        .mobile-header-notification .nav-link {
            padding: 8px 10px;
            /* margin: -15px !important; */
        }

        @media (max-width: 1023px) {
            .mobile-header-notification {
                display: block;
            }
        }

        @media (max-width: 575px) {
            .mobile-header-notification {
                right: 40px;
            }

            .mobile-header-notification .notification-dropdown {
                right: -34px;
                width: min(320px, calc(100vw - 24px));
            }
        }

        /* Search input focus */
        #customerSearch:focus {
            border-color: #ff9f43;
            box-shadow: 0 0 0 0.2rem rgba(255, 159, 67, 0.25);
        }
    </style>
    <style>
        @media only screen and (max-width: 767px) {
            #subBranchContainer .select2-container {
                min-width: unset !important;
            }
        }

        /* iPhone SE */
        @media screen and (max-width: 340px) {
            #subBranchContainer {
                width: 108px !important;
            }

            #subBranchContainer .d-flex {
                width: 108px;
            }

            #subBrandSelect,
            #subBranchContainer .select2-container {
                width: 108px !important;
            }

            #subBranchContainer .select2-dropdown {
                width: 108px !important;
                min-width: 108px;
            }

            #subBranchContainer .select2-container {
                width: 108px !important;
            }
        }

        @media screen and (min-width: 341px) and (max-width: 374px) {
            #subBranchContainer {
                width: 112px !important;
            }

            #subBranchContainer .d-flex {
                width: 112px;
            }

            #subBrandSelect,
            #subBranchContainer .select2-container {
                width: 112px !important;
            }

            #subBranchContainer .select2-dropdown {
                width: 112px !important;
                min-width: 112px;
            }

            #subBranchContainer .select2-container {
                width: 112px !important;
            }
        }

        /* iPhone XR / iPhone 11 */
        @media screen and (min-width: 375px) and (max-width: 390px) {
            #subBranchContainer {
                width: 118px !important;
            }

            #subBranchContainer .d-flex {
                width: 118px;
            }

            #subBrandSelect,
            #subBranchContainer .select2-container {
                width: 118px !important;
            }

            #subBranchContainer .select2-dropdown {
                width: 118px !important;
                min-width: 118px;
            }

            #subBranchContainer .select2-container {
                width: 118px !important;
            }
        }

        /* iPhone XR / iPhone 11 */
        @media screen and (min-width: 391px) and (max-width: 420px) {
            #subBranchContainer {
                width: 126px !important;
            }

            #subBranchContainer .d-flex {
                width: 126px;
            }

            #subBrandSelect,
            #subBranchContainer .select2-container {
                width: 126px !important;
            }

            #subBranchContainer .select2-dropdown {
                width: 126px !important;
                min-width: 126px;
            }

            #subBranchContainer .select2-container {
                width: 126px !important;
            }
        }

        /* iPhone XR / iPhone 11 */
        @media screen and (min-width: 430px) and (max-width: 480px) {
            #subBranchContainer {
                width: 134px !important;
            }

            #subBranchContainer .d-flex {
                width: 134px;
            }

            #subBrandSelect,
            #subBranchContainer .select2-container {
                width: 134px !important;
            }

            #subBranchContainer .select2-dropdown {
                width: 134px !important;
                min-width: 134px;
            }

            #subBranchContainer .select2-container {
                width: 134px !important;
            }
        }

        @media screen and (min-width: 768px) and (max-width: 819px) {
            #subBranchContainer .select2-container {
                min-width: auto !important;
            }

            #subBranchContainer .select2-container {
                width: 340px !important;
            }
        }

        @media screen and (min-width: 820px) and (max-width: 1023px) {
            #subBranchContainer .select2-container {
                min-width: auto !important;
            }

            #subBranchContainer .select2-container {
                width: 368px !important;
            }

            .header-new-order-button {
                display: none !important;
            }

            #toggle_btn1 {
                display: none !important;
            }


            .header-search {
                display: none !important;
            }

            .header-search-container {
                display: none !important;
            }

            .logo-view {
                float: none;
            }
        }

        @media screen and (width: 768px) and (height: 1024px) {
            .header-new-order-button {
                display: none !important;
            }

            .header-search {
                display: none !important;
            }

            .header-search-container {
                display: none !important;
            }

            .logo-view {
                float: none;
            }

            #toggle_btn1 {
                display: none !important;
            }
        }


        @media screen and (width: 1024px) and (height: 1366px) {
            .header .header-search-container.tab-view {
                display: flex !important;
                align-items: center;
                flex-wrap: nowrap;
                gap: 12px;
                min-width: 0;
                flex: 1 1 auto;
            }

            .header .header-search,
            .header .header-new-order-button,
            .header .hide-on-ipad-pro {
                display: none !important;
            }

            #subBranchContainer .select2-container {
                min-width: auto !important;
            }

            #subBranchContainer {
                width: 180px !important;
                margin-right: 8px !important;
            }

            #subBranchContainer .d-flex {
                width: 180px;
            }

            #subBrandSelect,
            #subBranchContainer .select2-container {
                width: 180px !important;
            }

            #subBranchContainer .select2-dropdown {
                width: 160px !important;
                min-width: 160px;
            }

            #subBranchContainer .select2-results__option {
                white-space: normal;
            }

            /* .header .header-left {
                width: 78px !important;
               } */
            /* .web_button{
                position: absolute;
                z-index: 9999;
               }


               #toggle_btn1  {
                display: none !important;
               }
               .header .mobile_btn {
                display: block !important;
               }
        */
            /* .header-search{
                display: none !important;
               }
               .header-search-container{
                display: none !important;
               } */
            .search-view {
                width: 285px !important;
            }

            .logo-view {
                float: none;
                /* display: none !important; */
            }

            /* .header-new-order-button{
                display: none !important;
               } */
            .mini-sidebar .header-left .logo-small {
                display: none !important;
            }

            .hide-on-ipad-pro {
                display: none !important;
            }
        }

        @media screen and (width: 540px) and (height: 720px) {
            #subBranchContainer .select2-container {
                min-width: auto !important;
            }

            #subBranchContainer .select2-container {
                width: 225px !important;
            }
        }

        @media screen and (width: 1024px) and (height: 600px) {
            #subBranchContainer .select2-container {
                min-width: auto !important;
            }

            #subBranchContainer .select2-container {
                width: 155px !important;
            }

            /* .header .header-left {
                width: 78px !important;
               } */
            .web_button {
                position: absolute;
                z-index: 9999;
            }

            .search-view {
                width: 285px !important;
            }

            .logo-view {
                float: none;
                /* display: none !important; */
            }

            .mini-sidebar .header-left .logo-small {
                display: none !important;
            }

            div#toggle_btn1 {
                position: relative !important;
            }
        }

        @media screen and (width: 1280px) and (height: 800px) {
            #subBranchContainer .select2-container {
                min-width: auto !important;
            }

            #subBranchContainer .select2-container {
                width: 215px !important;
            }

            /* .header .header-left {
                width: 78px !important;
               } */

            .web_button {
                position: absolute;
                z-index: 9999;
            }
        }

        .search-view {
            width: 285px !important;
        }

        .logo-view {
            float: none;
            /* display: none !important; */
        }

        .mini-sidebar .header-left .logo-small {
            display: none !important;
        }

        div#toggle_btn1 {
            position: relative !important;
        }
    </style>

@endpush
