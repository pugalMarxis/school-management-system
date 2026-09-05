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
    $subject_code = trim(filter_input(INPUT_POST, 'subject_code', FILTER_SANITIZE_SPECIAL_CHARS));
    $subject_name = trim(filter_input(INPUT_POST, 'subject_name', FILTER_SANITIZE_SPECIAL_CHARS));

    try {
        $stmt = $db->prepare("INSERT INTO subjects (subject_code, subject_name) VALUES (:subject_code, :subject_name)");
        $stmt->execute([':subject_code' => $subject_code, ':subject_name' => $subject_name]);
        $_SESSION['status_msg'] = "Subject added to institutional catalog.";
    } catch (PDOException $e) {
        $_SESSION['status_msg'] = ($e->getCode() == 23000) ? "Error: Subject Code must be unique." : "Processing failure.";
    }
    header("Location: subjects.php");
    exit();
}

if ($action === 'delete') {
    $subject_id = intval($_GET['id'] ?? 0);
    try {
        $stmt = $db->prepare("DELETE FROM subjects WHERE id = :id");
        $stmt->execute([':id' => $subject_id]);
        $_SESSION['status_msg'] = "Subject unit deleted.";
    } catch (PDOException $e) {
        $_SESSION['status_msg'] = "Failed to clear subject mapping dependencies.";
    }
    header("Location: subjects.php");
    exit();
}