<?php
session_start();
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require_once '../config/Database.php';
$database = new Database();
$db = $database->getConnection();

// Fetch students with their class details
try {
   // AFTER (Line 12)
$query = "SELECT s.*, c.class_name, c.section, u.email, u.username 
          FROM students s 
          LEFT JOIN users u ON s.user_id = u.id 
          LEFT JOIN classes c ON s.class_id = c.id 
          ORDER BY s.id DESC";
    $students = $db->query($query)->fetchAll();

    // Fetch classes for dropdown selectors
    $classes = $db->query("SELECT * FROM classes ORDER BY class_name ASC")->fetchAll();
} catch (PDOException $e) {
    die("Data fetch failure: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark m-0">Student Registry</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
        <i class="fa-solid fa-user-plus me-2"></i>Enroll New Student
    </button>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-8">
                <input type="text" id="studentSearch" class="form-control" placeholder="Search by name, admission number, or email...">
            </div>
            <div class="col-md-4">
                <select id="classFilter" class="form-select">
                    <option value="">All Classes</option>
                    <?php foreach($classes as $class): ?>
                        <option value="<?php echo htmlspecialchars($class['class_name'] . ' - ' . $class['section']); ?>">
                            <?php echo htmlspecialchars($class['class_name'] . ' (' . $class['section'] . ')'); ?>
                        </option>
                    <?php endforeach; ?> 
                 </select>
            </div>
        </div>
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
            <table class="table table-hover align-middle mb-0" id="studentTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Admission No</th>
                        <th>Full Name</th>
                        <th>Assigned Class</th>
                        <th>Contact Number</th>
                        <th>System Account</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($students)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No student accounts found.</td></tr>
                    <?php else: ?>
                        <?php foreach($students as $row): ?>
                            <tr>
                                <td class="ps-4 fw-semibold"><?php echo htmlspecialchars($row['admission_no']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1.5 rounded-pill">
                                        <?php echo $row['class_name'] ? htmlspecialchars($row['class_name'] . ' - ' . $row['section']) : 'Unassigned'; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                                <td><small class="text-muted"><?php echo htmlspecialchars($row['username']); ?></small></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-light border edit-student-btn" 
                                            data-id="<?php echo $row['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                            data-dob="<?php echo $row['dob']; ?>"
                                            data-phone="<?php echo htmlspecialchars($row['phone']); ?>"
                                            data-class="<?php echo $row['class_id']; ?>">
                                        <i class="fa-solid fa-pen-to-square text-primary"></i>
                                    </button>
                                    <a href="student_process.php?action=delete&id=<?php echo $row['id']; ?>&user_id=<?php echo $row['user_id']; ?>" 
                                       class="btn btn-sm btn-light border ms-1" 
                                       onclick="return confirm('Permanently remove this student registry profile? This destroys matching user credentials.');">
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

<div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="student_process.php?action=create" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold">Enroll Student</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-with="modal" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-medium">Admission System ID</label>
                    <input type="text" name="admission_no" class="form-control" placeholder="e.g. STU-2026-001" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Full Legal Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter student name" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-medium">Date of Birth</label>
                        <input type="date" name="dob" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-medium">Assigned Class</label>
                        <select name="class_id" class="form-select">
                            <option value="">Select Class Group</option>
                            <?php foreach($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>">
                                    <?php echo htmlspecialchars($class['class_name'] . ' (' . $class['section'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Primary Contact Number</label>
                    <input type="tel" name="phone" class="form-control" placeholder="Contact number">
                </div>
                <hr class="my-4">
                <h6 class="fw-bold text-secondary mb-3">System Access Credentials</h6>
                <div class="mb-3">
                    <label class="form-label fw-medium">Unique Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Login identifier" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="student@school.com" required>
                </div>
                <div class="mb-1">
                    <label class="form-label fw-medium">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Min 6 characters" required>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Save Entry</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="student_process.php?action=update" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="student_id" id="edit_student_id">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Modify Student Metadata</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-medium">Full Legal Name</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-medium">Date of Birth</label>
                        <input type="date" name="dob" id="edit_dob" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-medium">Assigned Class</label>
                        <select name="class_id" id="edit_class_id" class="form-select">
                            <option value="">Select Class Group</option>
                            <?php foreach($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['class_name'] . ' (' . $class['section'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Primary Contact Number</label>
                    <input type="tel" name="phone" id="edit_phone" class="form-control">
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel Changes</button>
                <button type="submit" class="btn btn-primary px-4">Commit Modification</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Dynamic Table Filtering Search System
    const searchField = document.getElementById('studentSearch');
    const classField = document.getElementById('classFilter');
    const tableRows = document.querySelectorAll('#studentTable tbody tr');

    function executeFilter() {
        const queryText = searchField.value.toLowerCase();
        const selectedClass = classField.value.toLowerCase();

        tableRows.forEach(row => {
            if(row.cells.length < 2) return; // Skip empty states
            const matchSearch = row.innerText.toLowerCase().includes(queryText);
            const classText = row.cells[2].innerText.toLowerCase();
            const matchClass = !selectedClass || classText.includes(selectedClass);

            row.style.display = (matchSearch && matchClass) ? '' : 'none';
        });
    }
    
    searchField.addEventListener('keyup', executeFilter);
    classField.addEventListener('change', executeFilter);

    // Event Delegation: Bind Data to Edit Modal Fields Dynamically
    document.querySelectorAll('.edit-student-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_student_id').value = this.dataset.id;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_dob').value = this.dataset.dob;
            document.getElementById('edit_phone').value = this.dataset.phone;
            document.getElementById('edit_class_id').value = this.dataset.class;
            
            const editModal = new bootstrap.Modal(document.getElementById('editStudentModal'));
            editModal.show();
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>