<?php
session_start();
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access Denied: Unmapped infrastructure execution gateway token.");
}

require_once '../config/Database.php';
$database = new Database();
$db = $database->getConnection();

$type     = $_GET['type'] ?? '';
$class_id = intval($_GET['class_id'] ?? 0);
$dataset  = [];
$metaInfo = "";

try {
    // ----------------------------------------------------
    // COMPILATION EXECUTION LOGIC: ATTENDANCE SUMMARY
    // ----------------------------------------------------
    if ($type === 'attendance') {
        $monthToken = $_GET['month'] ?? date('Y-m');
        $metaInfo = "Attendance Audit Log Sheet for Month Period: " . date('F Y', strtotime($monthToken));
        
        $query = "SELECT s.admission_no, s.name, 
                         COUNT(a.id) as total_sessions,
                         SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) as present_days,
                         SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) as absent_days
                  FROM students s
                  LEFT JOIN attendance a ON s.id = a.student_id AND DATE_FORMAT(a.date, '%Y-%m') = :month_token
                  WHERE s.class_id = :class_id
                  GROUP BY s.id ORDER BY s.name ASC";
        $stmt = $db->prepare($query);
        $stmt->execute([':month_token' => $monthToken, ':class_id' => $class_id]);
        $dataset = $stmt->fetchAll();
    }

    // ----------------------------------------------------
    // COMPILATION EXECUTION LOGIC: ACADEMIC GRADES LEDGER
    // ----------------------------------------------------
    if ($type === 'grades') {
        $exam_id = intval($_GET['exam_id'] ?? 0);
        $metaInfo = "Academic Assessment Performance Score Spreadsheet Grid";
        
        $query = "SELECT s.admission_no, s.name as student_name, sub.subject_name, sub.subject_code, r.marks_obtained, r.max_marks
                  FROM students s
                  JOIN results r ON s.id = r.student_id AND r.exam_id = :exam_id
                  JOIN subjects sub ON r.subject_id = sub.id
                  WHERE s.class_id = :class_id
                  ORDER BY s.name ASC, sub.subject_code ASC";
        $stmt = $db->prepare($query);
        $stmt->execute([':exam_id' => $exam_id, ':class_id' => $class_id]);
        $dataset = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    die("Fatal execution error compiling analytical tables: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Institutional Document Archive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Courier New', Courier, monospace; color: #1e293b; background: #fff; }
        .document-header { border-bottom: 3px double #0f172a; padding-bottom: 1.5rem; margin-bottom: 2rem; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: #fff; }
        }
    </style>
</head>
<body class="py-5">
    <div class="container">
        <div class="no-print d-flex justify-content-between align-items-center mb-5 p-3 bg-light rounded border">
            <span class="small font-monospace text-muted">Document Ready. Enforcing System Compliance Formatting Guidelines.</span>
            <button class="btn btn-dark btn-sm px-4" onclick="window.print()">
                <i class="fa-solid fa-print me-2"></i>Trigger Device Printer / Export PDF
            </button>
        </div>

        <div class="document-header text-center">
            <h2 class="fw-bold text-uppercase tracking-wider m-0">EduManage Institutional Resource Core</h2>
            <p class="small text-muted font-monospace m-0 mt-1">Official Document Manifest Database Archive Verification System</p>
            <h6 class="fw-bold mt-4 text-secondary text-uppercase tracking-widest"><?php echo htmlspecialchars($metaInfo); ?></h6>
        </div>

        <?php if($type === 'attendance'): ?>
            <table class="table table-bordered align-middle">
                <thead class="table-light text-uppercase small">
                    <tr>
                        <th>Admission ID</th>
                        <th>Student Profile Name</th>
                        <th class="text-center">Recorded Days</th>
                        <th class="text-center text-success">Present Count</th>
                        <th class="text-center text-danger">Absent Count</th>
                        <th class="text-end">Ratio Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($dataset)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No attendance trace records compiled matching criteria factors.</td></tr>
                    <?php else: ?>
                        <?php foreach($dataset as $row): 
                            $ratio = $row['total_sessions'] > 0 ? ($row['present_days'] / $row['total_sessions']) * 100 : 100.00;
                        ?>
                            <tr class="font-monospace">
                                <td><?php echo htmlspecialchars($row['admission_no']); ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td class="text-center"><?php echo $row['total_sessions']; ?></td>
                                <td class="text-center text-success fw-bold"><?php echo $row['present_days']; ?></td>
                                <td class="text-center text-danger fw-bold"><?php echo $row['absent_days']; ?></td>
                                <td class="text-end fw-bold text-primary"><?php echo number_format($ratio, 1); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

        <?php elseif($type === 'grades'): ?>
            <table class="table table-bordered align-middle">
                <thead class="table-light text-uppercase small">
                    <tr>
                        <th>Admission ID</th>
                        <th>Student Name</th>
                        <th>Subject Course Unit</th>
                        <th class="text-end">Score Secured</th>
                        <th class="text-end">Max Cap Limit</th>
                        <th class="text-end">Performance Evaluation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($dataset)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No recorded grading ledger configurations mapped.</td></tr>
                    <?php else: ?>
                        <?php foreach($dataset as $row): 
                            $percentage = ($row['marks_obtained'] / $row['max_marks']) * 100;
                            // Calculate simple string-based grade classifications
                            if($percentage >= 75) $grade = 'Distinction (A)';
                            elseif($percentage >= 60) $grade = 'Credit Pass (B)';
                            elseif($percentage >= 45) $grade = 'Satisfactory (C)';
                            else $grade = 'Unsatisfactory (F)';
                        ?>
                            <tr class="font-monospace">
                                <td><?php echo htmlspecialchars($row['admission_no']); ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($row['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['subject_code'].' - '.$row['subject_name']); ?></td>
                                <td class="text-end fw-bold"><?php echo number_format($row['marks_obtained'], 2); ?></td>
                                <td class="text-end text-muted"><?php echo number_format($row['max_marks'], 2); ?></td>
                                <td class="text-end fw-bold <?php echo $percentage >= 45 ? 'text-success' : 'text-danger'; ?>"><?php echo $grade; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="mt-5 pt-5 row">
            <div class="col-6 offset-6 text-center font-monospace">
                <div class="border-bottom mx-auto w-70 mb-2" style="height: 40px; border-color: #000 !important;"></div>
                <span class="small text-muted text-uppercase fw-bold">Chief Executive Comptroller Approval Seal signature</span>
            </div>
        </div>
    </div>
</body>
</html>