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
    $subjects = $db->query("SELECT * FROM subjects ORDER BY subject_name ASC")->fetchAll();
} catch (PDOException $e) {
    die("Subject catalog retrieval failure: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark m-0">Subject Curriculum Catalog</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
        <i class="fa-solid fa-book-medical me-2"></i>Create New Subject
    </button>
</div>

<?php if(isset($_SESSION['status_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['status_msg']; unset($_SESSION['status_msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 text-dark bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Subject Code</th>
                        <th>Subject Name</th>
                        <th class="text-end pg-4 pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($subjects)): ?>
                        <tr><td colspan="3" class="text-center py-4 text-muted">No course units cataloged.</td></tr>
                    <?php else: ?>
                        <?php foreach($subjects as $row): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-secondary"><?php echo htmlspecialchars($row['subject_code']); ?></td>
                                <td class="fw-medium"><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                <td class="text-end pe-4">
                                    <a href="subject_process.php?action=delete&id=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-light border" 
                                       onclick="return confirm('Purge this subject? This clears all matching academic records from results tables.');">
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

<div class="modal fade" id="addSubjectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="subject_process.php?action=create" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Add Subject Entry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-medium">Subject Code</label>
                    <input type="text" name="subject_code" class="form-control" placeholder="e.g. CS-101" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Subject Name</label>
                    <input type="text" name="subject_name" class="form-control" placeholder="e.g. Intro to Databases" required>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Save Course Unit</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>