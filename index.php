<?php
session_start();

// Redirect if user is already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else if ($_SESSION['user_role'] === 'teacher') {
        header("Location: teacher/dashboard.php");
    } else {
        header("Location: student/dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduManage Portal - Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --dark-bg: #0f172a;
            --card-bg: rgba(255, 255, 255, 0.95);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--dark-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient Animated Glowing Backdrops */
        .glow-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.45;
            z-index: 0;
            animation: floatGlow 10s ease-in-out infinite alternate;
        }
        .glow-1 {
            width: 450px;
            height: 450px;
            background: #6366f1;
            top: -100px;
            left: -100px;
        }
        .glow-2 {
            width: 400px;
            height: 400px;
            background: #a855f7;
            bottom: -100px;
            right: -100px;
            animation-delay: -5s;
        }

        @keyframes floatGlow {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(40px, 30px) scale(1.1); }
        }

        /* Split-Card Container */
        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 28px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
            overflow: hidden;
            width: 100%;
            max-width: 960px;
            z-index: 1;
            margin: 20px;
            animation: cardScaleIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Left Hero Panel */
        .login-hero {
            background: linear-gradient(135deg, #4f46e5 0%, #312e81 100%);
            color: #ffffff;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .login-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            pointer-events: none;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.825rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            width: fit-content;
        }

        /* Right Form Panel */
        .login-form-wrapper {
            padding: 3.5rem 3rem;
        }

        .form-control-icon-wrapper {
            position: relative;
        }

        .form-control-icon-wrapper .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            transition: color 0.2s ease;
        }

        .form-control-icon-wrapper .form-control {
            padding-left: 48px;
            padding-right: 48px;
            height: 52px;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            font-weight: 500;
            transition: all 0.25s ease;
        }

        .form-control-icon-wrapper .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.18);
        }

        .form-control-icon-wrapper .form-control:focus + .input-icon {
            color: var(--primary);
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
            transition: color 0.2s ease;
            border: none;
            background: none;
            padding: 0;
        }

        .password-toggle:hover {
            color: var(--primary);
        }

        .btn-submit {
            height: 52px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 1rem;
            color: #ffffff;
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 26px -5px rgba(99, 102, 241, 0.5);
            color: #ffffff;
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        @keyframes cardScaleIn {
            from {
                opacity: 0;
                transform: scale(0.96) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
    </style>
</head>
<body>

    <!-- Ambient Glowing Orbs -->
    <div class="glow-orb glow-1"></div>
    <div class="glow-orb glow-2"></div>

    <div class="container d-flex justify-content-center align-items-center">
        <div class="login-card">
            <div class="row g-0">
                
                <!-- Left Hero Panel -->
                <div class="col-lg-5 login-hero d-none d-lg-flex">
                    <div>
                        <div class="hero-badge mb-4">
                            <i class="fa-solid fa-shield-halved"></i> v2.0 Enterprise Portal
                        </div>
                        <h2 class="fw-bold display-6 mb-3" style="letter-spacing: -0.02em;">EduManage System</h2>
                        <p class="text-white-50 leading-relaxed mb-0">
                            Unified academic dashboard for administration, faculty instruction, and student success.
                        </p>
                    </div>

                    <div class="mt-5">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white bg-opacity-10 p-3 rounded-4">
                                <i class="fa-solid fa-graduation-cap fa-2x text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">Modular Campus Management</h6>
                                <small class="text-white-50">Attendance • Grades • Finance • Library</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Form Panel -->
                <div class="col-lg-7">
                    <div class="login-form-wrapper">
                        <div class="text-center text-lg-start mb-4">
                            <h3 class="fw-bold text-dark mb-1" style="letter-spacing: -0.02em;">Welcome Back</h3>
                            <p class="text-muted small">Please sign in to access your dashboard</p>
                        </div>

                        <!-- Session Error Alert -->
                        <?php if (isset($_SESSION['error']) || isset($_SESSION['status_msg'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm py-2.5 px-3 mb-4" role="alert">
                                <div class="d-flex align-items-center gap-2 small fw-semibold">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <div>
                                        <?php 
                                            if (isset($_SESSION['error'])) {
                                                echo $_SESSION['error']; 
                                                unset($_SESSION['error']);
                                            } else {
                                                echo $_SESSION['status_msg']; 
                                                unset($_SESSION['status_msg']);
                                            }
                                        ?>
                                    </div>
                                </div>
                                <button type="button" class="btn-close small" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="login_process.php" method="POST">
                            <!-- Username Input -->
                            <div class="mb-3.5 mb-3">
                                <label class="form-label text-secondary small fw-bold mb-1">Username or Email</label>
                                <div class="form-control-icon-wrapper">
                                    <input type="text" name="username" class="form-control" placeholder="Enter your username" required autofocus>
                                    <i class="fa-regular fa-user input-icon"></i>
                                </div>
                            </div>

                            <!-- Password Input -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label text-secondary small fw-bold mb-0">Password</label>
                                </div>
                                <div class="form-control-icon-wrapper">
                                    <input type="password" name="password" id="passwordInput" class="form-control" placeholder="••••••••" required>
                                    <i class="fa-solid fa-lock input-icon"></i>
                                    <button type="button" class="password-toggle" id="togglePasswordBtn">
                                        <i class="fa-regular fa-eye" id="togglePasswordIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-submit w-100 mb-3">
                                Sign In <i class="fa-solid fa-arrow-right ms-2"></i>
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <span class="text-muted extra-small" style="font-size: 0.8rem;">
                                &copy; <?php echo date('Y'); ?> EduManage System. All rights reserved.
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Password Visibility Toggle JS -->
    <script>
        const togglePasswordBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('passwordInput');
        const togglePasswordIcon = document.getElementById('togglePasswordIcon');

        togglePasswordBtn.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            togglePasswordIcon.classList.toggle('fa-eye');
            togglePasswordIcon.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>