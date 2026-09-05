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
    // Fetch classes and calculate the total number of students enrolled in each
    $query = "SELECT c.*, COUNT(s.id) as student_count 
              FROM classes c 
              LEFT JOIN students s ON c.id = s.class_id 
              GROUP BY c.id 
              ORDER BY c.class_name ASC, c.section ASC";
    $classes = $db->query($query)->fetchAll();
} catch (PDOException $e) {
    die("Class fetch failure: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark m-0">Class & Section Management</h4>
    <button class="btn btn-warning text-dark fw-medium" data-bs-toggle="modal" data-bs-target="#addClassModal">
        <i class="fa-solid fa-layer-group me-2"></i>Create New Class
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
                        <th class="ps-4">Class Level</th>
                        <th>Section</th>
                        <th>Room Assignment</th>
                        <th>Enrolled Students</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($classes)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">No classes configured yet.</td></tr>
                    <?php else: ?>
                        <?php foreach($classes as $row): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary"><?php echo htmlspecialchars($row['class_name']); ?></td>
                                <td><span class="badge bg-dark rounded-pill px-3 py-2"><?php echo htmlspecialchars($row['section']); ?></span></td>
                                <td><?php echo htmlspecialchars($row['room_no'] ?? 'Unassigned'); ?></td>
                                <td>
                                    <span class="badge <?php echo ($row['student_count'] > 0) ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $row['student_count']; ?> Students
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-light border edit-class-btn" 
                                            data-id="<?php echo $row['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['class_name']); ?>"
                                            data-section="<?php echo htmlspecialchars($row['section']); ?>"
                                            data-room="<?php echo htmlspecialchars($row['room_no']); ?>">
                                        <i class="fa-solid fa-pen text-primary"></i>
                                    </button>
                                    <a href="class_process.php?action=delete&id=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-light border ms-1" 
                                       onclick="return confirm('Delete this class? Enrolled students will be marked as Unassigned.');">
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

<div class="modal fade" id="addClassModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="class_process.php?action=create" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">Define New Class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-medium">Class/Grade Level</label>
                    <input type="text" name="class_name" class="form-control" placeholder="e.g. Grade 10" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Section</label>
                    <input type="text" name="section" class="form-control" placeholder="e.g. A, B, Science, Arts" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Room Number (Optional)</label>
                    <input type="text" name="room_no" class="form-control" placeholder="e.g. Room 104">
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning fw-medium text-dark px-4">Create Class</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editClassModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="class_process.php?action=update" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="class_id" id="edit_class_id">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold">Edit Class Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-medium">Class/Grade Level</label>
                    <input type="text" name="class_name" id="edit_class_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Section</label>
                    <input type="text" name="section" id="edit_section" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Room Number</label>
                    <input type="text" name="room_no" id="edit_room" class="form-control">
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.edit-class-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_class_id').value = this.dataset.id;
            document.getElementById('edit_class_name').value = this.dataset.name;
            document.getElementById('edit_section').value = this.dataset.section;
            document.getElementById('edit_room').value = this.dataset.room;
            
            new bootstrap.Modal(document.getElementById('editClassModal')).show();
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>