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
    $exams = $db->query("SELECT * FROM exams ORDER BY date DESC")->fetchAll();
} catch (PDOException $e) {
    die("Exam retrieval error: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark m-0">Examination Schedules</h4>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addExamModal">
        <i class="fa-solid fa-calendar-plus me-2"></i>Schedule Assessment
    </button>
</div>

<?php if(isset($_SESSION['status_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $_SESSION['status_msg']; unset($_SESSION['status_msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 text-dark bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Assessment Term</th>
                        <th>Exam Configuration Name</th>
                        <th>Scheduled Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($exams)): ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">No active examinations scheduled.</td></tr>
                    <?php else: ?>
                        <?php foreach($exams as $row): ?>
                            <tr>
                                <td class="ps-4"><span class="badge bg-success-subtle text-success border rounded-pill px-3 py-1.5"><?php echo htmlspecialchars($row['term']); ?></span></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($row['exam_name']); ?></td>
                                <td class="text-muted"><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                <td class="text-end pe-4">
                                    <a href="exam_process.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-light border" onclick="return confirm('Purge this examination term?');">
                                        <i class="fa-solid fa-trash text-danger"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addExamModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="exam_process.php?action=create" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">Create Exam Schedule</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-medium">Academic Term</label>
                    <input type="text" name="term" class="form-control" placeholder="e.g. First Term, Mid-Year, Finals" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Exam Presentation Name</label>
                    <input type="text" name="exam_name" class="form-control" placeholder="e.g. Mathematics Comprehensive" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Scheduled Calendar Date</label>
                    <input type="date" name="date" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success text-white px-4">Publish Schedule</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>