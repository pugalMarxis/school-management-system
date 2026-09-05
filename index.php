<?php
session_start();
if(isset($_SESSION['user_role'])) {
    header("Location: " . $_SESSION['user_role'] . "/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Portal Gateway | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .login-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        .brand-header {
            background-color: #1e3a8a;
            color: #ffffff;
            border-radius: 16px 16px 0 0;
            padding: 2.5rem 2rem;
        }
        .btn-primary-action {
            background-color: #1e3a8a;
            border: none;
            transition: background 0.2s ease-in-out;
        }
        .btn-primary-action:hover {
            background-color: #172554;
        }
    </style>
</head>
<body class="d-flex align-items-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="card login-card">
                    <div class="brand-header text-center">
                        <h3 class="fw-bold m-0">EduManage Engine</h3>
                        <p class="text-white-50 m-0 mt-1">Unified Institutional Portal Architecture</p>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <?php if(isset($_SESSION['login_error'])): ?>
                            <div class="alert alert-danger role='alert'">
                                <?php 
                                    echo htmlspecialchars($_SESSION['login_error']); 
                                    unset($_SESSION['login_error']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <form action="login_process.php" method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label for="username" class="form-label fw-medium">System Username</label>
                                <input type="text" name="username" id="username" class="form-control form-control-lg" placeholder="Enter username" required>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label fw-medium">Security Password</label>
                                <input type="password" name="password" id="password" class="form-control form-control-lg" placeholder="••••••••" required>
                            </div>
                            <button type="submit" class="btn btn-primary-action btn-lg w-100 text-white fw-medium py-2.5">Authenticate Identity</button>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <small class="text-muted">&copy; 2026 EduManage Inc. Security Compliance Architecture Enforcement.</small>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>