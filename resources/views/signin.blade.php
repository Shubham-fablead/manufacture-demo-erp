<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="Fablead Inventory-Billing Software">
    <meta name="keywords"
        content="inventory management, billing system, invoice generator, purchase orders, inventory control, POS system, admin dashboard">
    <meta name="author" content="Fablead Developers Technolab">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login</title>

    <link rel="shortcut icon" type="image/x-icon" href="https://erp-demo.fableadtech.com/public//storage/favicons/YY2brRHM2c0vlHHzLgHeXKO4Bk9ifMVjMYK71uGg.webp">

    <link rel="stylesheet" href="{{ env('ImagePath') . 'admin/assets/css/bootstrap.min.css' }}">

    <link rel="stylesheet" href="{{ env('ImagePath') . 'admin/assets/plugins/fontawesome/css/fontawesome.min.css'}}">
    <link rel="stylesheet" href="{{ env('ImagePath') . 'admin/assets/plugins/fontawesome/css/all.min.css'}}">

    <link rel="stylesheet" href="{{ env('ImagePath') . 'admin/assets/css/style.css' }}">
    <style>
        /* .logo-img {
            margin-left: 9rem;
        } */
         .logo-img {

    margin-left: 130px !important;

}




        body.account-page {
            padding-bottom: 50px; /* Space for sticky footer */
        }

        @media screen and (max-width: 768px) {
            .login-wrapper .login-content {
                width: 90% !important;
            }

            .logo-img {
                margin-left: 55px !important;
            }
        }
    </style>
</head>
@php
    use App\Models\Setting;
    $settings = Setting::first();
@endphp

<body class="account-page">

    <div class="main-wrapper">
        <div class="account-content">
            <div class="login-wrapper">
                <div class="login-content">
                    <div class="login-userset">
                        <div class="login-logo">
                            <img src="{{ $settings?->logo ? env('ImagePath') . '/storage/' . $settings->logo : 'https://fableadtechnolabs.com/static/media/250x150%20(1).b3f5a4db48c7770366ef.webp'}}"
                                alt="img" class="logo-img">
                        </div>
                        <div class="login-userheading">
                            <h3>Sign In</h3>
                            <h4>Please login to your account</h4>
                        </div>
                        <form id="loginForm">
                            <div class="form-login">
                                <label>Email</label>
                                <div class="form-addons">
                                    <input type="text" id="email" placeholder="Enter your email address">
                                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/mail.svg'}}" alt="img">
                                </div>
                                <small id="emailError" class="text-danger"></small>
                            </div>

                            <div class="form-login">
                                <label>Password</label>
                                <div class="pass-group">
                                    <input type="password" id="password" class="pass-input"
                                        placeholder="Enter your password">
                                    <span class="fas toggle-password fa-eye-slash"></span>
                                </div>
                                <small id="passwordError" class="text-danger"></small>
                            </div>

                            <div class="form-login">
                                <div class="alreadyuser">
                                    <!-- <h4><a href="forgetpassword.html" class="hover-a">Forgot Password?</a></h4> -->
                                </div>
                            </div>

                            <div class="form-login">
                                <button type="submit" class="btn btn-login">Sign In</button>
                            </div>

                            <div id="loginMessage" class="text-danger"></div>

                            {{-- ── Face Login ─────────────────────────────────────── --}}
                            <div class="form-login mt-2">
                                <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                                    <hr style="flex:1;border-color:#e2e8f0;">
                                    <span style="color:#94a3b8;font-size:13px;font-weight:500;">OR</span>
                                    <hr style="flex:1;border-color:#e2e8f0;">
                                </div>
                                <button type="button" id="faceLoginBtn"
                                    style="width:100%;padding:13px 20px;border-radius:10px;border:2px solid #ff9f43;
                                           background:#fff;color:#f97316;font-weight:700;font-size:14px;
                                           cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;
                                           transition:all .2s ease;">
                                    <i class="fas fa-camera" style="font-size:15px;"></i> Login with Face
                                </button>
                                <div id="faceLoginMessage" class="text-danger mt-2" style="font-size:13px;"></div>
                            </div>
                        </form>

                    </div>
                </div>
                <!-- <div class="login-img">
                    <img src="admin/assets/img/login.jpg" alt="img">
                </div> -->
            </div>
        </div>
    </div>
    <footer style="position: fixed; bottom: 0; left: 0; right: 0; text-align: center; padding: 10px 0; background-color: #f4f4f4; height: 50px; z-index: 1000; box-shadow: 0 -2px 5px rgba(0,0,0,0.1);">
        <h1 style="font-size: 14px; font-weight: 600; margin: 0;">© <?= date('Y') ?> <a href="https://fableadtechnolabs.com" target="_blank" style="color: inherit; text-decoration: none;">Copyright - Fablead Developers Technolab</a></h1>
    </footer>

    <script src="{{ env('ImagePath') . 'admin/assets/js/jquery-3.6.0.min.js'}}"></script>

    <script src="{{ env('ImagePath') . 'admin/assets/js/feather.min.js'}}"></script>

    <script src="{{ env('ImagePath') . 'admin/assets/js/bootstrap.bundle.min.js'}}"></script>

    <script src="{{ env('ImagePath') . 'admin/assets/js/script.js'}}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- face-api.js from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

    {{-- ── Face Recognition Modal & Script Partials ─────── --}}
    @include('partials.face-recognition-modal')
    @include('partials.face-recognition-script')

    <script>
        $(document).ready(function () {
            $("#loginForm").submit(function (e) {
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
// console.log(selectedSubAdminId);

        e.preventDefault();

        let email = $("#email").val().trim();
        let password = $("#password").val().trim();
        $("#emailError, #passwordError, #loginMessage").text("");

        if (!email) {
            $("#emailError").text("Email is required.");
            return;
        }
        if (!password) {
            $("#passwordError").text("Password is required.");
            return;
        }

        let $submitBtn = $("#loginForm button[type='submit']");
        let originalText = $submitBtn.html();
        $submitBtn.html('<span class="spinner-border spinner-border-sm"></span> Logging in...').prop("disabled", true);

        function performLogin(forceLogin = false) {
            $.ajax({
                url: "/",
                type: "POST",
                dataType: "json",
                data: {
                    email: email,
                    selectedSubAdminId: selectedSubAdminId,
                    password: password,
                    force: forceLogin ? 1 : 0,
                    _token: $('meta[name="csrf-token"]').attr('content'),
                },
                success: function (response) {
                    // console.log("Response:", response);

                    // ⚠️ Show warning popup if already checked in/out
                    // if (response.warning && response.status === true && !forceLogin) {
                    //     Swal.fire({
                    //         title: "Warning",
                    //         text: response.message,
                    //         icon: "warning",
                    //         showCancelButton: true,
                    //         confirmButtonText: "Yes, log me in",
                    //         cancelButtonText: "Cancel"
                    //     }).then((result) => {
                    //         if (result.isConfirmed) {
                    //             performLogin(true);
                    //         }
                    //     });
                    //     return;
                    // }

                    // ✅ Successful login
                    if (response.status && response.token) {
                        // console.log('response.user.branch_id', response.user.branch_id);
                        var selectedId;
                        if(response.user.branch_id != null){
                            selectedId = response.user.branch_id;
                            // console.log('Using branch_id:', selectedId);
                        } else {
                            selectedId = response.user.id;
                            // console.log('Using user.id:', selectedId);
                        }

                        if (selectedId) {
                            // console.log('selectedId', selectedId);

                            // Set localStorage with the correct selectedId
                            localStorage.setItem('selectedSubAdminId', selectedId);

                            // Get the value from localStorage after setting it (not the old constant)
                            var storedSubAdminId = localStorage.getItem('selectedSubAdminId');
                            // console.log('selectedSubAdminId-get from local storage after setting:', storedSubAdminId);

                            $.post('/set-subadmin-session', {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                subAdminId: selectedId
                            }, function() {
                                // console.log('Session set with subAdminId:', selectedId);
                                // Redirect to dashboard after session is set
                                // window.location.href = "{{ route('auth.dashboard') }}";
                            });

                        } else if (selectedText === "Main Branch") {
                            $.post('/clear-subadmin-session', {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            }, function() {
                                // console.log('Session cleared');
                                localStorage.removeItem('selectedSubAdminId');
                                // window.location.href = '/dashboard';
                            });
                        }
                        localStorage.setItem("authToken", response.token);
                        localStorage.setItem("selectedSubAdminId", selectedId); // Use selectedId instead of response.user.id
                        window.location.href = response.redirect;
                    } else if (response.error) {
                        $("#loginMessage").text(response.error).css("color", "red");
                    }
                },
                error: function (xhr) {
                    // console.error("Error:", xhr.responseText);
                    let msg = xhr.responseJSON?.message || xhr.responseJSON?.error || "Login failed.";
                    $("#loginMessage").text(msg).css("color", "red");
                },
                complete: function () {
                    $submitBtn.html(originalText).prop("disabled", false);
                }
            });
        }

        // 🚀 Start normal login (no force)
        performLogin(false);
    });

            /* ── Face Login ──────────────────────────────────────────── */
            const $faceBtn = $('#faceLoginBtn');
            const $faceMsgBox = $('#faceLoginMessage');

            $faceBtn.on('click', function () {
                $faceMsgBox.text('');
                let fr;
                try {
                    fr = window.OmsaiFaceRecognition.init();
                } catch (err) {
                    $faceMsgBox.text('Face recognition UI failed to initialise. Please refresh.');
                    return;
                }

                fr.open({
                    title:        'Login with Face',
                    subtitle:     'Look straight at the camera. We will log you in automatically.',
                    autoDetect:   true,
                    onCapture: async function (descriptor, modal) {
                        modal.setStatus('⏳ Verifying face...', 'info');

                        try {
                            const response = await $.ajax({
                                url:  '{{ route("auth.face-login") }}',
                                type: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    face_descriptor: descriptor,
                                    _token: $('meta[name="csrf-token"]').attr('content'),
                                }),
                                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            });

                            if (response.status && response.redirect) {
                                modal.setStatus('✅ Face matched! Redirecting...', 'success');

                                // Store auth data same as password login
                                if (response.token) {
                                    localStorage.setItem('authToken', response.token);
                                    localStorage.setItem('token', response.token);
                                }
                                if (response.user) {
                                    const userId = response.user.branch_id ?? response.user.id;
                                    localStorage.setItem('selectedSubAdminId', userId);
                                    $.post('/set-subadmin-session', {
                                        _token: $('meta[name="csrf-token"]').attr('content'),
                                        subAdminId: userId
                                    }, function() {});
                                }

                                setTimeout(() => {
                                    modal.close();
                                    window.location.href = response.redirect;
                                }, 800);
                            } else {
                                throw new Error(response.error || 'Face login failed.');
                            }
                        } catch (err) {
                            const msg = err.responseJSON?.error || err.responseJSON?.message || err.message || 'Face login failed.';
                            throw new Error(msg);
                        }
                    },
                });
            });

            // Hover effects for face login button
            $faceBtn.on('mouseenter', function () {
                $(this).css({ 'background': 'linear-gradient(135deg,#ff9f43,#f97316)', 'color': '#fff' });
            }).on('mouseleave', function () {
                $(this).css({ 'background': '#fff', 'color': '#f97316' });
            });
        });
    </script>
</body>

</html>
