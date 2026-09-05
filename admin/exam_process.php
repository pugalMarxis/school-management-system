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
    $term      = trim(filter_input(INPUT_POST, 'term', FILTER_SANITIZE_SPECIAL_CHARS));
    $exam_name = trim(filter_input(INPUT_POST, 'exam_name', FILTER_SANITIZE_SPECIAL_CHARS));
    $date      = $_POST['date'];

    try {
        $stmt = $db->prepare("INSERT INTO exams (term, exam_name, date) VALUES (:term, :exam_name, :date)");
        $stmt->execute([':term' => $term, ':exam_name' => $exam_name, ':date' => $date]);
        $_SESSION['status_msg'] = "Exam schedule committed successfully.";
    } catch (PDOException $e) {
        $_SESSION['status_msg'] = "Error writing schedule configuration.";
    }
    header("Location: exams.php");
    exit();
}

if ($action === 'delete') {
    $exam_id = intval($_GET['id'] ?? 0);
    try {
        $db->prepare("DELETE FROM exams WHERE id = :id")->execute([':id' => $exam_id]);
        $_SESSION['status_msg'] = "Exam entry cleared.";
    } catch (PDOException $e) {
        $_SESSION['status_msg'] = "Dependency clearance fault occurs.";
    }
    header("Location: exams.php");
    exit();
}