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
    // ----------------------------------------------------
    // ACTION ROUTE: COMMIT AND ASSIGN FACULTY PROFILE CARD
    // ----------------------------------------------------
    if ($action === 'create') {
        $employee_id    = trim(filter_input(INPUT_POST, 'employee_id', FILTER_SANITIZE_SPECIAL_CHARS));
        $name           = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
        $specialization = trim(filter_input(INPUT_POST, 'specialization', FILTER_SANITIZE_SPECIAL_CHARS));
        $phone          = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS));
        
        $username       = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS));
        $email          = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password       = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);

        if(!$email || empty($username) || empty($employee_id) || empty($name)) {
            $_SESSION['status_msg'] = "Structural credential mismatch error event trace processing registration.";
            header("Location: teachers.php");
            exit();
        }

        try {
            $db->beginTransaction();

            // 1. Core Profile Account Node Registry
            $userStmt = $db->prepare("INSERT INTO users (username, password, email, role) VALUES (:username, :password, :email, 'teacher')");
            $userStmt->execute([
                ':username' => $username,
                ':password' => $password,
                ':email'    => $email
            ]);
            $lastUserId = $db->lastInsertId();

            // 2. Structural Entity Details Index
            $facStmt = $db->prepare("INSERT INTO teachers (user_id, employee_id, name, phone, specialization) VALUES (:user_id, :employee_id, :name, :phone, :specialization)");
            $facStmt->execute([
                ':user_id'        => $lastUserId,
                ':employee_id'    => $employee_id,
                ':name'           => $name,
                ':phone'          => $phone,
                ':specialization' => $specialization
            ]);

            $db->commit();
            $_SESSION['status_msg'] = "Faculty member operational card synchronized smoothly.";
        } catch(PDOException $e) {
            $db->rollBack();
            error_log("Faculty instantiation transaction exception path trace: " . $e->getMessage());
            $_SESSION['status_msg'] = "Transaction error recording teacher credentials.";
        }
        header("Location: teachers.php");
        exit();
    }

    // ----------------------------------------------------
    // ACTION ROUTE: UPDATE TARGET FACULTY METADATA VALUES
    // ----------------------------------------------------
    if ($action === 'update') {
        $teacher_id     = intval($_POST['teacher_id']);
        $name           = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
        $specialization = trim(filter_input(INPUT_POST, 'specialization', FILTER_SANITIZE_SPECIAL_CHARS));
        $phone          = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS));

        try {
            $upstmt = $db->prepare("UPDATE teachers SET name = :name, specialization = :specialization, phone = :phone WHERE id = :id");
            $upstmt->execute([
                ':name'           => $name,
                ':specialization' => $specialization,
                ':phone'          => $phone,
                ':id'             => $teacher_id
            ]);
            $_SESSION['status_msg'] = "Faculty configuration data modified inside index records mapping tables.";
        } catch(PDOException $e) {
            error_log("Faculty metadata adjustment logic stack trace trace error: " . $e->getMessage());
            $_SESSION['status_msg'] = "Failed to update faculty configuration records matrix.";
        }
        header("Location: teachers.php");
        exit();
    }
}

// ----------------------------------------------------
// ACTION ROUTE: PURGE TARGET FACULTY RECORD ENTRIES
// ----------------------------------------------------
if ($action === 'delete') {
    $teacher_id = intval($_GET['id'] ?? 0);
    $user_id    = intval($_GET['user_id'] ?? 0);

    if($user_id > 0) {
        try {
            // Relational constraints safely clear entries across the tables automatically
            $delStmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $delStmt->execute([':id' => $user_id]);
            $_SESSION['status_msg'] = "Target faculty credential index trace completely cleared from system disks.";
        } catch(PDOException $e) {
            error_log("Purge configuration logic failed to finish processing command task chain: " . $e->getMessage());
            $_SESSION['status_msg'] = "Critical database protection tracking constraint error event execution failure.";
        }
    }
    header("Location: teachers.php");
    exit();
}