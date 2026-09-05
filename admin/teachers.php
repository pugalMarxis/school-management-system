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
    // UPDATED QUERY:
$query = "SELECT t.*, u.email, u.username 
          FROM teachers t 
          LEFT JOIN users u ON t.user_id = u.id 
          ORDER BY t.id DESC";
    $teachers = $db->query($query)->fetchAll();
} catch (PDOException $e) {
    die("Faculty fetch breakdown: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark m-0">Faculty Directory</h4>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
        <i class="fa-solid fa-user-plus me-2"></i>Add Faculty Member
    </button>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <input type="text" id="teacherSearch" class="form-control" placeholder="Search by faculty name, structural ID, field of specialization...">
    </div>
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
            <table class="table table-hover align-middle mb-0" id="teacherTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Employee ID</th>
                        <th>Name</th>
                        <th>Field Specialization</th>
                        <th>Phone Reference</th>
                        <th>System User Identifier</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($teachers)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No faculty entries currently mapped.</td></tr>
                    <?php else: ?>
                        <?php foreach($teachers as $row): ?>
                            <tr>
                                <td class="ps-4 fw-semibold"><?php echo htmlspecialchars($row['employee_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><span class="badge bg-success-subtle text-success border px-2.5 py-1.5 rounded-pill"><?php echo htmlspecialchars($row['specialization'] ?? 'Generalist'); ?></span></td>
                                <td><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                                <td><small class="text-muted"><?php echo htmlspecialchars($row['username']); ?></small></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-light border edit-teacher-btn" 
                                            data-id="<?php echo $row['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                            data-specialization="<?php echo htmlspecialchars($row['specialization']); ?>"
                                            data-phone="<?php echo htmlspecialchars($row['phone']); ?>">
                                        <i class="fa-solid fa-user-pen text-success"></i>
                                    </button>
                                    <a href="teacher_process.php?action=delete&id=<?php echo $row['id']; ?>&user_id=<?php echo $row['user_id']; ?>" 
                                       class="btn btn-sm btn-light border ms-1" 
                                       onclick="return confirm('Purge selected teacher? This structural action permanently severs authorization maps.');">
                                        <i class="fa-solid fa-trash-can text-danger"></i>
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

<div class="modal fade" id="addTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="teacher_process.php?action=create" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">Add Faculty Profile</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-with="modal" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-medium">Employee Identifier Code</label>
                    <input type="text" name="employee_id" class="form-control" placeholder="e.g. TCH-2026-04" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Full Legal Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter teacher name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Departmental Specialization</label>
                    <input type="text" name="specialization" class="form-control" placeholder="e.g. Advanced Data Architecture Computing">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Phone Core Line</label>
                    <input type="text" name="phone" class="form-control" placeholder="Phone reference number">
                </div>
                <hr class="my-4">
                <h6 class="fw-bold text-secondary mb-3">System Identity Profile Mapping Link</h6>
                <div class="mb-3">
                    <label class="form-label fw-medium">Username Mapping ID</label>
                    <input type="text" name="username" class="form-control" placeholder="System login handle" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Corporate Email Reference</label>
                    <input type="email" name="email" class="form-control" placeholder="faculty@school.com" required>
                </div>
                <div class="mb-1">
                    <label class="form-label fw-medium">Authorization Password Key</label>
                    <input type="password" name="password" class="form-control" placeholder="Min 6 alphanumeric characters" required>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success px-4 text-white">Record Faculty Profile</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="teacher_process.php?action=update" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="teacher_id" id="edit_teacher_id">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold">Update Faculty Profile Configuration</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-medium">Full Legal Name</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Specialization Department Field</label>
                    <input type="text" name="specialization" id="edit_specialization" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Contact Reference Line</label>
                    <input type="text" name="phone" id="edit_phone" class="form-control">
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel Modification</button>
                <button type="submit" class="btn btn-success px-4 text-white">Commit Configuration Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Dynamic Filter Mechanism Engine
    document.getElementById('teacherSearch').addEventListener('keyup', function() {
        const valueToken = this.value.toLowerCase();
        document.querySelectorAll('#teacherTable tbody tr').forEach(row => {
            if(row.cells.length < 2) return;
            row.style.display = row.innerText.toLowerCase().includes(valueToken) ? '' : 'none';
        });
    });

    // Modal Field Binding Execution Sequence Hook
    document.querySelectorAll('.edit-teacher-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_teacher_id').value = this.dataset.id;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_specialization').value = this.dataset.specialization;
            document.getElementById('edit_phone').value = this.dataset.phone;
            
            const schemaModal = new bootstrap.Modal(document.getElementById('editTeacherModal'));
            schemaModal.show();
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>