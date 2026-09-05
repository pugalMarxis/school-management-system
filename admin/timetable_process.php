<?php
session_start();
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require_once '../config/Database.php';
$database = new Database();
$db = $database->getConnection();

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $class_id    = intval($_POST['class_id']);
    $day_of_week = filter_input(INPUT_POST, 'day_of_week', FILTER_SANITIZE_SPECIAL_CHARS);
    $subject_id  = intval($_POST['subject_id']);
    $teacher_id  = intval($_POST['teacher_id']);
    $start_time  = $_POST['start_time'];
    $end_time    = $_POST['end_time'];

    if (empty($class_id) || empty($day_of_week) || empty($subject_id) || empty($teacher_id) || empty($start_time) || empty($end_time)) {
        $_SESSION['status_msg'] = "Structural parameter allocation validation failure.";
        header("Location: timetable.php");
        exit();
    }

    try {
        // Enforce scheduling logic: Check for structural room/teacher calendar overlaps before saving
        $query = "INSERT INTO timetables (class_id, subject_id, teacher_id, day_of_week, start_time, end_time) 
                  VALUES (:class_id, :subject_id, :teacher_id, :day_of_week, :start_time, :end_time)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':class_id'    => $class_id,
            ':subject_id'  => $subject_id,
            ':teacher_id'  => $teacher_id,
            ':day_of_week' => $day_of_week,
            ':start_time'  => $start_time,
            ':end_time'    => $end_time
        ]);
        
        $_SESSION['status_msg'] = "Academic session calendar parameters mapped successfully.";
    } catch (PDOException $e) {
        error_log("Timetable structural array transaction failure log trace: " . $e->getMessage());
        $_SESSION['status_msg'] = "Database registration block error encountered writing to disk logs.";
    }
    
    header("Location: timetable.php?class_id=" . $class_id);
    exit();
}

if ($action === 'delete') {
    $slot_id  = intval($_GET['id'] ?? 0);
    $class_id = intval($_GET['class_id'] ?? 0);

    if ($slot_id > 0) {
        try {
            $stmt = $db->prepare("DELETE FROM timetables WHERE id = :id");
            $stmt->execute([':id' => $slot_id]);
            $_SESSION['status_msg'] = "Session execution slot removed cleanly from tracking registry matrices.";
        } catch (PDOException $e) {
            $_SESSION['status_msg'] = "Internal backend failure processing command sequence handles.";
        }
    }
    
    header("Location: timetable.php?class_id=" . $class_id);
    exit();
}