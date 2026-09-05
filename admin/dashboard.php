<?php
session_start();
// Enforce strict authorization checks
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require_once '../config/Database.php';

// Fetch key dashboard statistics directly from our normalized tables
$database = new Database();
$db = $database->getConnection();

try {
    $studentCount = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $teacherCount = $db->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
    $classCount   = $db->query("SELECT COUNT(*) FROM classes")->fetchColumn();
} catch (PDOException $e) {
    error_log("Dashboard metrics collection exception: " . $e->getMessage());
    $studentCount = $teacherCount = $classCount = 0;
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted text-uppercase small fw-bold tracking-wider mb-1">Enrolled Students</h6>
                    <h3 class="fw-bold m-0 text-dark"><?php echo $studentCount; ?></h3>
                </div>
                <div class="p-3 bg-primary bg-opacity-10 rounded-3 text-primary">
                    <i class="fa-solid fa-user-graduate fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted text-uppercase small fw-bold tracking-wider mb-1">Active Faculty</h6>
                    <h3 class="fw-bold m-0 text-dark"><?php echo $teacherCount; ?></h3>
                </div>
                <div class="p-3 bg-success bg-opacity-10 rounded-3 text-success">
                    <i class="fa-solid fa-chalkboard-user fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted text-uppercase small fw-bold tracking-wider mb-1">Total Classes</h6>
                    <h3 class="fw-bold m-0 text-dark"><?php echo $classCount; ?></h3>
                </div>
                <div class="p-3 bg-warning bg-opacity-10 rounded-3 text-warning">
                    <i class="fa-solid fa-school fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-bold text-dark mb-3">System Operational Stream</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Activity Event</th>
                            <th>Target Scope</th>
                            <th>Timestamp</th>
                            <th>Status Flag</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><i class="fa-solid fa-circle-check text-success me-2"></i>Database Initialization</td>
                            <td>System Core Infrastructure</td>
                            <td>Just Now</td>
                            <td><span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill">Operational</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>