<?php
session_start();
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require_once '../config/Database.php';
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_id    = intval($_POST['exam_id']);
    $subject_id = intval($_POST['subject_id']);
    $class_id   = intval($_POST['class_id']);
    $marks      = $_POST['marks'] ?? [];
    $max_marks  = $_POST['max_marks'] ?? [];

    if(empty($exam_id) || empty($subject_id) || empty($marks)) {
        $_SESSION['status_msg'] = "Malformed processing array payload configuration metrics.";
        header("Location: results.php");
        exit();
    }

    try {
        $db->beginTransaction();

        // Check if an entry for this student, exam, and subject already exists
        $checkStmt  = $db->prepare("SELECT id FROM results WHERE exam_id = :exam_id AND student_id = :student_id AND subject_id = :subject_id LIMIT 1");
        $insertStmt = $db->prepare("INSERT INTO results (exam_id, student_id, subject_id, marks_obtained, max_marks) VALUES (:exam_id, :student_id, :subject_id, :marks_obtained, :max_marks)");
        $updateStmt = $db->prepare("UPDATE results SET marks_obtained = :marks_obtained, max_marks = :max_marks WHERE id = :id");

        foreach($marks as $student_id => $score) {
            $student_id = intval($student_id);
            $score_val  = floatval($score);
            $max_val    = floatval($max_marks[$student_id] ?? 100.00);

            $checkStmt->execute([
                ':exam_id'    => $exam_id,
                ':student_id' => $student_id,
                ':subject_id' => $subject_id
            ]);
            $existing = $checkStmt->fetch();

            if ($existing) {
                // Entry exists, update it
                $updateStmt->execute([
                    ':marks_obtained' => $score_val,
                    ':max_marks'      => $max_val,
                    ':id'             => $existing['id']
                ]);
            } else {
                // New record entry required
                $insertStmt->execute([
                    ':exam_id'        => $exam_id,
                    ':student_id'     => $student_id,
                    ':subject_id'     => $subject_id,
                    ':marks_obtained' => $score_val,
                    ':max_marks'      => $max_val
                ]);
            }
        }

        $db->commit();
        $_SESSION['status_msg'] = "Scores successfully recorded and verified.";
    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Results execution failure event trace: " . $e->getMessage());
        $_SESSION['status_msg'] = "Critical transactional error parsing score sheets structural matrix arrays.";
    }

    header("Location: results.php?exam_id=" . $exam_id . "&class_id=" . $class_id . "&subject_id=" . $subject_id);
    exit();
} else {
    header("Location: results.php");
    exit();
}