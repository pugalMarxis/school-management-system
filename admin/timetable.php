<?php
session_start();
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require_once '../config/Database.php';
$database = new Database();
$db = $database->getConnection();

$selected_class = $_GET['class_id'] ?? '';
$timetable_grid = [];

// Define weekdays array for structural iteration layout grids
$weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

try {
    // Fetch dropdown option data arrays
    $classes = $db->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();
    $subjects = $db->query("SELECT * FROM subjects ORDER BY subject_name ASC")->fetchAll();
    $teachers = $db->query("SELECT * FROM teachers ORDER BY name ASC")->fetchAll();

    if (!empty($selected_class)) {
        // Fetch schedule items grouped by day and sorted by execution hour timeline
        $query = "SELECT t.*, s.subject_name, s.subject_code, tch.name as teacher_name 
                  FROM timetables t 
                  JOIN subjects s ON t.subject_id = s.id 
                  JOIN teachers tch ON t.teacher_id = tch.id 
                  WHERE t.class_id = :class_id 
                  ORDER BY t.start_time ASC";
        $stmt = $db->prepare($query);
        $stmt->execute([':class_id' => $selected_class]);
        
        // Structure results directly into an accessible associative day matrix array
        while ($row = $stmt->fetch()) {
            $timetable_grid[$row['day_of_week']][] = $row;
        }
    }
} catch (PDOException $e) {
    die("Timetable system configuration access fault: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark m-0">Academic Schedule & Timetable Matrix</h4>
    <?php if(!empty($selected_class)): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
            <i class="fa-solid fa-calendar-plus me-2"></i>Allocate Time Slot
        </button>
    <?php endif; ?>
</div>

<?php if(isset($_SESSION['status_msg'])): ?>
    <div class="alert alert-info alert-dismissible fade show">
        <?php echo $_SESSION['status_msg']; unset($_SESSION['status_msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-9">
                <label class="form-label small fw-bold text-muted">Select Target Class Group Profile</label>
                <select name="class_id" class="form-select form-select-lg" required onchange="this.form.submit()">
                    <option value="">-- Choose Class Group to View Matrix --</option>
                    <?php foreach($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo ($selected_class == $c['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['class_name'] . ' - Section ' . $c['section'] . ' (Room: ' . ($c['room_no'] ?? 'N/A') . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-dark btn-lg w-100 fw-medium">Load Schedule Matrix</button>
            </div>
        </form>
    </div>
</div>

<?php if(!empty($selected_class)): ?>
    <div class="row g-3 row-cols-1 row-cols-md-2 row-cols-xl-3">
        <?php foreach($weekdays as $day): ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4 bg-white">
                    <div class="card-header bg-dark text-white p-3 border-0 rounded-top-4 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold tracking-wide"><i class="fa-regular fa-calendar me-2"></i><?php echo $day; ?></h6>
                        <span class="badge bg-light text-dark small rounded-pill">
                            <?php echo isset($timetable_grid[$day]) ? count($timetable_grid[$day]) : 0; ?> Slots Assigned
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <?php if(empty($timetable_grid[$day])): ?>
                            <div class="text-center py-5 text-muted small bg-light rounded-3 border border-dashed">
                                <i class="fa-solid fa-calendar-xmark text-black-50 mb-2 fa-lg"></i><br>No educational periods allocated.
                            </div>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach($timetable_grid[$day] as $slot): ?>
                                    <div class="p-3 bg-light rounded-3 border-start border-primary border-4 position-relative shadow-sm transition-hover">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <span class="badge bg-primary-subtle text-primary font-monospace mb-1.5 px-2 py-1 small">
                                                    <?php echo htmlspecialchars($slot['subject_code']); ?>
                                                </span>
                                                <h6 class="fw-bold text-dark mb-1 small"><?php echo htmlspecialchars($slot['subject_name']); ?></h6>
                                                <p class="small text-muted m-0 mb-1"><i class="fa-solid fa-user-tie me-1"></i><?php echo htmlspecialchars($slot['teacher_name']); ?></p>
                                                <small class="text-secondary fw-semibold">
                                                    <i class="fa-regular fa-clock me-1"></i>
                                                    <?php echo date('h:i A', strtotime($slot['start_time'])); ?> - <?php echo date('h:i A', strtotime($slot['end_time'])); ?>
                                                </small>
                                            </div>
                                            <a href="timetable_process.php?action=delete&id=<?php echo $slot['id']; ?>&class_id=<?php echo $selected_class; ?>" 
                                               class="btn btn-sm btn-outline-danger border-0 position-absolute top-0 end-0 mt-2 me-2 p-1 px-2"
                                               onclick="return confirm('Purge this operational timetable slot allocation record?');">
                                                <i class="fa-solid fa-xmark"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="timetable_process.php?action=create" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($selected_class); ?>">
            
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Map Timetable Matrix Slot</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Target Week Day Assignment</label>
                    <select name="day_of_week" class="form-select" required>
                        <option value="">-- Choose Target Day --</option>
                        <?php foreach($weekdays as $d): ?>
                            <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Subject Core Allocation</label>
                    <select name="subject_id" class="form-select" required>
                        <option value="">-- Choose Subject Course Unit --</option>
                        <?php foreach($subjects as $sub): ?>
                            <option value="<?php echo $sub['id']; ?>"><?php echo htmlspecialchars($sub['subject_name'] . ' ['.$sub['subject_code'].']'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Handling Faculty Member</label>
                    <select name="teacher_id" class="form-select" required>
                        <option value="">-- Select Lecturer / Instructor --</option>
                        <?php foreach($teachers as $t): ?>
                            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name'] . ' [ID: '.$t['employee_id'].']'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted">Start Session Time</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted">End Session Time</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Dismiss</button>
                <button type="submit" class="btn btn-primary px-4 fw-bold">Commit Grid Slot</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>