<?php
$role = $_SESSION['user_role'] ?? 'student';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="border-end" id="sidebar-wrapper">
    <div class="sidebar-heading fw-bold text-center border-bottom border-secondary">
        <i class="fa-solid fa-graduation-cap me-2 text-primary"></i>EduManage
    </div>
    <div class="list-group list-group-flush p-2">
        <a href="../<?php echo $role; ?>/dashboard.php" class="list-group-item list-group-item-action bg-transparent border-0 rounded p-3 <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-chart-pie me-3"></i>Dashboard Base
        </a>
        
        <?php if($role === 'admin'): ?>
            <a href="../admin/students.php" class="list-group-item list-group-item-action bg-transparent border-0 rounded p-3 <?php echo ($current_page == 'students.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-user-graduate me-3"></i>Students
            </a>
            <a href="../admin/teachers.php" class="list-group-item list-group-item-action bg-transparent border-0 rounded p-3 <?php echo ($current_page == 'teachers.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-chalkboard-user me-3"></i>Teachers
            </a>
            <a href="../admin/classes.php" class="list-group-item list-group-item-action bg-transparent border-0 rounded p-3 <?php echo ($current_page == 'classes.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-school me-3"></i>Classes
            </a>
            <a href="../admin/subjects.php" class="list-group-item list-group-item-action bg-transparent border-0 rounded p-3 <?php echo ($current_page == 'subjects.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-book me-3"></i>Subjects
            </a>
            <a href="../admin/fees.php" class="list-group-item list-group-item-action bg-transparent border-0 rounded p-3 <?php echo ($current_page == 'fees.php' || $current_page == 'payment_history.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-wallet me-3"></i>Fee Register
            </a>
            <a href="../admin/library.php" class="list-group-item list-group-item-action bg-transparent border-0 rounded p-3 <?php echo ($current_page == 'library.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-book-bookmark me-3"></i>Library Vault
            </a>
            <a href="../admin/timetable.php" class="list-group-item list-group-item-action bg-transparent border-0 rounded p-3 <?php echo ($current_page == 'timetable.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-calendar-week me-3"></i>Timetable Matrix
            </a>
            <a href="../admin/reports.php" class="list-group-item list-group-item-action bg-transparent border-0 rounded p-3 <?php echo ($current_page == 'reports.php' || $current_page == 'generate_report.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-line me-3"></i>System Analytics
            </a>
        <?php endif; ?>

        <?php if($role === 'teacher' || $role === 'admin'): ?>
            <a href="../admin/attendance.php" class="list-group-item list-group-item-action bg-transparent border-0 rounded p-3 <?php echo ($current_page == 'attendance.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-clipboard-user me-3"></i>Attendance
            </a>
            <a href="../admin/exams.php" class="list-group-item list-group-item-action bg-transparent border-0 rounded p-3 <?php echo ($current_page == 'exams.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-calendar-days me-3"></i>Exam Schedules
            </a>
            <a href="../admin/results.php" class="list-group-item list-group-item-action bg-transparent border-0 rounded p-3 <?php echo ($current_page == 'results.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-file-invoice me-3"></i>Grade Book (Results)
            </a>
        <?php endif; ?>
        
        <a href="../logout.php" class="list-group-item list-group-item-action bg-transparent border-0 text-danger rounded mt-5 p-3">
            <i class="fa-solid fa-right-from-bracket me-3"></i>Terminate Session
        </a>
    </div>
</div>

<div id="page-content-wrapper" class="d-flex flex-column">
    <nav class="navbar navbar-expand-lg top-navbar px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
        
        <div class="d-flex align-items-center">
            <button class="btn btn-light me-3 border-0" id="menu-toggle">
                <i class="fa-solid fa-bars-staggered"></i>
            </button>
            <h5 class="m-0 fw-semibold text-capitalize text-secondary">System Context: <?php echo $role; ?> Panel</h5>
        </div>

        <div class="dropdown">
            <button class="btn btn-light dropdown-toggle fw-medium" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-regular fa-user-circle me-2"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="profileDropdown">
                <li><a class="dropdown-menu-item dropdown-item py-2" href="#"><i class="fa-solid fa-sliders me-2 text-muted"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2 text-danger" href="../logout.php"><i class="fa-solid fa-power-off me-2"></i>Logout</a></li>
            </ul>
        </div>
    </nav>
    <main class="container-fluid p-4">