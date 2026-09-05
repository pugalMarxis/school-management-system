<?php
session_start();
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require_once '../config/Database.php';
$database = new Database();
$db = $database->getConnection();

try {
    // 1. Core Analytics Compilation Queries
    $totalRevenue  = $db->query("SELECT SUM(amount_paid) FROM payments")->fetchColumn() ?? 0.00;
    $totalPending  = $db->query("SELECT (SELECT SUM(amount) FROM fees) - (SELECT SUM(amount_paid) FROM payments)")->fetchColumn() ?? 0.00;
    $attendanceAvg = $db->query("SELECT (SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) / COUNT(*)) * 100 FROM attendance")->fetchColumn() ?? 100.00;

    // 2. Fetch parameter arrays for the selection filters
    $classes  = $db->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();
    $exams    = $db->query("SELECT * FROM exams ORDER BY date DESC")->fetchAll();
    $subjects = $db->query("SELECT * FROM subjects ORDER BY subject_name ASC")->fetchAll();
} catch (PDOException $e) {
    die("Analytics processing engine failure: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<h4 class="fw-bold text-dark mb-4">Institutional Reports & System Analytics</h4>

<div class="row g-4 mb-4">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 border-start border-success border-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Gross Revenue Collected</h6>
                    <h4 class="fw-bold text-success m-0">$<?php echo number_format($totalRevenue, 2); ?></h4>
                </div>
                <div class="p-2 bg-success bg-opacity-10 rounded text-success"><i class="fa-solid fa-vault fa-xl"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 border-start border-danger border-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Outstanding Receivables</h6>
                    <h4 class="fw-bold text-danger m-0">$<?php echo number_format($totalPending < 0 ? 0.00 : $totalPending, 2); ?></h4>
                </div>
                <div class="p-2 bg-danger bg-opacity-10 rounded text-danger"><i class="fa-solid fa-hand-holding-dollar fa-xl"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 border-start border-primary border-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Net Institutional Attendance Ratio</h6>
                    <h4 class="fw-bold text-primary m-0"><?php echo number_format($attendanceAvg, 1); ?>%</h4>
                </div>
                <div class="p-2 bg-primary bg-opacity-10 rounded text-primary"><i class="fa-solid fa-chart-line fa-xl"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="p-2 bg-primary bg-opacity-10 rounded text-primary me-3"><i class="fa-solid fa-clipboard-user"></i></div>
                        <h6 class="m-0 fw-bold text-dark">Attendance Audit Ledger</h6>
                    </div>
                    <p class="small text-muted mb-4">Compiles complete historical trends, absences, and presentation margins for dynamic tracking evaluation.</p>
                </div>
                <form action="generate_report.php" method="GET" target="_blank">
                    <input type="hidden" name="type" value="attendance">
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <select name="class_id" class="form-select" required>
                                <option value="">-- Choose Class --</option>
                                <?php foreach($classes as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['class_name'].' - '.$c['section']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <input type="month" name="month" class="form-control" value="<?php echo date('Y-m'); ?>" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100 fw-semibold py-2">Generate Attendance Document</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="p-2 bg-success bg-opacity-10 rounded text-success me-3"><i class="fa-solid fa-graduation-cap"></i></div>
                        <h6 class="m-0 fw-bold text-dark">Academic Grade Report Sheets</h6>
                    </div>
                    <p class="small text-muted mb-4">Pulls student exam performance data, calculated averages, and grading charts directly from score sheet tables.</p>
                </div>
                <form action="generate_report.php" method="GET" target="_blank">
                    <input type="hidden" name="type" value="grades">
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <select name="exam_id" class="form-select" required>
                                <option value="">-- Choose Assessment --</option>
                                <?php foreach($exams as $e): ?>
                                    <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['exam_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <select name="class_id" class="form-select" required>
                                <option value="">-- Choose Class Group --</option>
                                <?php foreach($classes as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['class_name'].' - '.$c['section']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-success btn-sm w-100 fw-semibold py-2">Compile Performance Report</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>