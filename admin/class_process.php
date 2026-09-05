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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $class_name = trim(filter_input(INPUT_POST, 'class_name', FILTER_SANITIZE_SPECIAL_CHARS));
        $section    = trim(filter_input(INPUT_POST, 'section', FILTER_SANITIZE_SPECIAL_CHARS));
        $room_no    = trim(filter_input(INPUT_POST, 'room_no', FILTER_SANITIZE_SPECIAL_CHARS));

        try {
            $stmt = $db->prepare("INSERT INTO classes (class_name, section, room_no) VALUES (:class_name, :section, :room_no)");
            $stmt->execute([':class_name' => $class_name, ':section' => $section, ':room_no' => $room_no]);
            $_SESSION['status_msg'] = "Class profile successfully generated.";
        } catch (PDOException $e) {
            // Error 23000 is a unique constraint violation (duplicate class+section)
            if($e->getCode() == 23000) {
                $_SESSION['status_msg'] = "Error: This Class and Section combination already exists.";
            } else {
                $_SESSION['status_msg'] = "System architecture error: " . $e->getMessage();
            }
        }
        header("Location: classes.php");
        exit();
    }

    if ($action === 'update') {
        $class_id   = intval($_POST['class_id']);
        $class_name = trim(filter_input(INPUT_POST, 'class_name', FILTER_SANITIZE_SPECIAL_CHARS));
        $section    = trim(filter_input(INPUT_POST, 'section', FILTER_SANITIZE_SPECIAL_CHARS));
        $room_no    = trim(filter_input(INPUT_POST, 'room_no', FILTER_SANITIZE_SPECIAL_CHARS));

        try {
            $stmt = $db->prepare("UPDATE classes SET class_name = :class_name, section = :section, room_no = :room_no WHERE id = :id");
            $stmt->execute([':class_name' => $class_name, ':section' => $section, ':room_no' => $room_no, ':id' => $class_id]);
            $_SESSION['status_msg'] = "Class configuration updated.";
        } catch (PDOException $e) {
            $_SESSION['status_msg'] = "Modification failure.";
        }
        header("Location: classes.php");
        exit();
    }
}

if ($action === 'delete') {
    $class_id = intval($_GET['id']);
    try {
        $stmt = $db->prepare("DELETE FROM classes WHERE id = :id");
        $stmt->execute([':id' => $class_id]);
        $_SESSION['status_msg'] = "Class entirely purged from database.";
    } catch(PDOException $e) {
        $_SESSION['status_msg'] = "Failed to clear class tracking data.";
    }
    header("Location: classes.php");
    exit();
}