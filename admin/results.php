<?php
session_start();
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require_once '../config/Database.php';
$database = new Database();
$db = $database->getConnection();

$exam_id    = $_GET['exam_id'] ?? '';
$class_id   = $_GET['class_id'] ?? '';
$subject_id = $_GET['subject_id'] ?? '';
$students   = [];

try {
    $exams    = $db->query("SELECT * FROM exams ORDER BY date DESC")->fetchAll();
    $classes  = $db->query("SELECT * FROM classes ORDER BY class_name ASC")->fetchAll();
    $subjects = $db->query("SELECT * FROM subjects ORDER BY subject_name ASC")->fetchAll();

    if (!empty($exam_id) && !empty($class_id) && !empty($subject_id)) {
        // Fetch students in the selected class along with their existing marks for this exam/subject
        $query = "SELECT s.id as student_id, s.admission_no, s.name, r.marks_obtained, r.max_marks 
                  FROM students s 
                  LEFT JOIN results r ON s.id = r.student_id AND r.exam_id = :exam_id AND r.subject_id = :subject_id
                  WHERE s.class_id = :class_id 
                  ORDER BY s.name ASC";
        $stmt = $db->prepare($query);
        $stmt->execute([':exam_id' => $exam_id, ':subject_id' => $subject_id, ':class_id' => $class_id]);
        $students = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    die("Grade book instantiation breakdown: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<h4 class="fw-bold text-dark mb-4">Academic Grade Book Entry Ledger</h4>

<?php if(isset($_SESSION['status_msg'])): ?>
    <div class="alert alert-info alert-dismissible fade show"><?php echo $_SESSION['status_msg']; unset($_SESSION['status_msg']); ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 mb-4 bg-dark bg-opacity-5 border-start border-dark border-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Target Exam Term</label>
                <select name="exam_id" class="form-select" required>
                    <option value="">-- Choose Exam --</option>
                    <?php foreach($exams as $e): ?>
                        <option value="<?php echo $e['id']; ?>" <?php echo ($exam_id == $e['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($e['exam_name'] . ' ('.$e['term'].')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Class Group</label>
                <select name="class_id" class="form-select" required>
                    <option value="">-- Choose Class --</option>
                    <?php foreach($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo ($class_id == $c['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['class_name'] . ' - ' . $c['section']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Curriculum Subject</label>
                <select name="subject_id" class="form-select" required>
                    <option value="">-- Choose Subject --</option>
                    <?php foreach($subjects as $sub): ?>
                        <option value="<?php echo $sub['id']; ?>" <?php echo ($subject_id == $sub['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($sub['subject_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-dark w-100 fw-medium">Open Score Card Sheet</button>
            </div>
        </form>
    </div>
</div>

<?php if(!empty($exam_id) && !empty($class_id) && !empty($subject_id)): ?>
    <div class="card border-0 shadow-sm rounded-4 bg-white">
        <form action="results_process.php" method="POST">
            <input type="hidden" name="exam_id" value="<?php echo htmlspecialchars($exam_id); ?>">
            <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($subject_id); ?>">
            <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($class_id); ?>">

            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Admission Number</th>
                            <th>Student Name</th>
                            <th style="width: 200px;">Marks Obtained</th>
                            <th style="width: 200px;">Maximum Marks Limit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($students)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No students found assigned to this tracking node.</td></tr>
                        <?php else: ?>
                            <?php foreach($students as $row): ?>
                                <tr>
                                    <td class="ps-4 font-monospace small"><?php echo htmlspecialchars($row['admission_no']); ?></td>
                                    <td class="fw-medium"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="100" 
                                               name="marks[<?php echo $row['student_id']; ?>]" 
                                               class="form-control form-control-sm border-secondary-subtle" 
                                               value="<?php echo isset($row['marks_obtained']) ? htmlspecialchars($row['marks_obtained']) : ''; ?>" 
                                               placeholder="Enter Score" required>
                                    </td>
                                    <td>
                                        <input type="number" name="max_marks[<?php echo $row['student_id']; ?>]" 
                                               class="form-control form-control-sm bg-light" 
                                               value="<?php echo htmlspecialchars($row['max_marks'] ?? '100'); ?>" readonly>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if(!empty($students)): ?>
                <div class="card-footer bg-light p-3 text-end rounded-bottom-4">
                    <button type="submit" class="btn btn-primary px-5 fw-bold">Commit Grading Ledger Matrix</button>
                </div>
            <?php endif; ?>
        </form>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>