<?php
session_start();
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require_once '../config/Database.php';
$database = new Database();
$db = $database->getConnection();

// Default variables for the search form
$target_date = $_GET['date'] ?? date('Y-m-d');
$class_id = $_GET['class_id'] ?? '';
$students = [];

try {
    $classes = $db->query("SELECT * FROM classes ORDER BY class_name ASC")->fetchAll();

    // If a class is selected, fetch students and their current attendance for that date
    if(!empty($class_id)) {
        $query = "SELECT s.id, s.admission_no, s.name, a.status 
                  FROM students s 
                  LEFT JOIN attendance a ON s.id = a.student_id AND a.date = :target_date 
                  WHERE s.class_id = :class_id 
                  ORDER BY s.name ASC";
        $stmt = $db->prepare($query);
        $stmt->execute([':target_date' => $target_date, ':class_id' => $class_id]);
        $students = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    die("Attendance Data Error: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark m-0">Daily Roster & Attendance Tracking</h4>
</div>

<?php if(isset($_SESSION['status_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $_SESSION['status_msg']; unset($_SESSION['status_msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 mb-4 bg-primary bg-opacity-10 border-start border-primary border-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold text-primary">Select Tracking Date</label>
                <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($target_date); ?>" required>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-bold text-primary">Target Class Configuration</label>
                <select name="class_id" class="form-select" required>
                    <option value="">-- Choose Class Group --</option>
                    <?php foreach($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo ($class_id == $c['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['class_name'] . ' (' . $c['section'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100 fw-medium">Load Roster Grid</button>
            </div>
        </form>
    </div>
</div>

<?php if(!empty($class_id)): ?>
    <div class="card border-0 shadow-sm rounded-4 bg-white">
        <form action="attendance_process.php" method="POST">
            <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($class_id); ?>">
            <input type="hidden" name="date" value="<?php echo htmlspecialchars($target_date); ?>">

            <div class="card-header bg-white border-bottom p-4">
                <h6 class="m-0 fw-bold">Registering Attendance For: <span class="text-primary"><?php echo date('F j, Y', strtotime($target_date)); ?></span></h6>
            </div>
            
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle m-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Admission No</th>
                            <th>Student Name</th>
                            <th class="text-center pe-4">Attendance Status Marking</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($students)): ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted">No students currently enrolled in this group.</td></tr>
                        <?php else: ?>
                            <?php foreach($students as $row): 
                                $status = $row['status'] ?? 'Present'; // Default to Present to save time
                            ?>
                                <tr>
                                    <td class="ps-4 fw-semibold text-muted"><?php echo htmlspecialchars($row['admission_no']); ?></td>
                                    <td class="fw-medium"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td class="text-center pe-4">
                                        <div class="btn-group" role="group">
                                            <input type="radio" class="btn-check" name="attendance[<?php echo $row['id']; ?>]" id="pres_<?php echo $row['id']; ?>" value="Present" <?php echo ($status == 'Present') ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-success px-4" for="pres_<?php echo $row['id']; ?>">Present</label>

                                            <input type="radio" class="btn-check" name="attendance[<?php echo $row['id']; ?>]" id="late_<?php echo $row['id']; ?>" value="Late" <?php echo ($status == 'Late') ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-warning px-4" for="late_<?php echo $row['id']; ?>">Late</label>

                                            <input type="radio" class="btn-check" name="attendance[<?php echo $row['id']; ?>]" id="abs_<?php echo $row['id']; ?>" value="Absent" <?php echo ($status == 'Absent') ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-danger px-4" for="abs_<?php echo $row['id']; ?>">Absent</label>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if(!empty($students)): ?>
            <div class="card-footer bg-light p-3 text-end rounded-bottom-4">
                <button type="submit" class="btn btn-primary px-5 py-2 fw-medium">
                    <i class="fa-solid fa-cloud-arrow-up me-2"></i>Commit Daily Attendance
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>