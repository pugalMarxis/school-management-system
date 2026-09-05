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
    $class_id    = intval($_POST['class_id']);
    $target_date = $_POST['date'];
    $attendance  = $_POST['attendance'] ?? []; // Array of [student_id => status]

    if(empty($class_id) || empty($target_date) || empty($attendance)) {
        $_SESSION['status_msg'] = "Critical payload missing. Roster operation aborted.";
        header("Location: attendance.php");
        exit();
    }

    try {
        $db->beginTransaction();

        // Use standard Insert but Update it if the record (Student ID + Date) already exists
        $query = "INSERT INTO attendance (student_id, class_id, date, status) 
                  VALUES (:student_id, :class_id, :date, :status)
                  ON DUPLICATE KEY UPDATE status = :update_status";
        
        $stmt = $db->prepare($query);

        foreach ($attendance as $student_id => $status) {
            $stmt->execute([
                ':student_id'    => intval($student_id),
                ':class_id'      => $class_id,
                ':date'          => $target_date,
                ':status'        => $status,
                ':update_status' => $status
            ]);
        }

        $db->commit();
        $_SESSION['status_msg'] = "Bulk attendance roster successfully recorded to system ledger.";
        
    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Bulk attendance processor fault: " . $e->getMessage());
        $_SESSION['status_msg'] = "Fatal database processing interruption.";
    }

    // Redirect back to the same class and date view so the user sees the saved data instantly
    header("Location: attendance.php?class_id=" . $class_id . "&date=" . $target_date);
    exit();
} else {
    header("Location: attendance.php");
    exit();
}