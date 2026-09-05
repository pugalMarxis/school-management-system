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
    // ACTION ROUTE: CREATE / ENROLL STUDENT
    // ----------------------------------------------------
    if ($action === 'create') {
        $admission_no = trim(filter_input(INPUT_POST, 'admission_no', FILTER_SANITIZE_SPECIAL_CHARS));
        $name         = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
        $dob          = $_POST['dob'];
        $phone        = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS));
        $class_id     = !empty($_POST['class_id']) ? intval($_POST['class_id']) : null;
        
        $username     = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS));
        $email        = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password     = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);

        if(!$email || empty($username) || empty($admission_no) || empty($name)) {
            $_SESSION['status_msg'] = "Structural validation parameters mismatched. Try processing again.";
            header("Location: students.php");
            exit();
        }

        try {
            // Begin transactional loop sequence
            $db->beginTransaction();

            // 1. Establish structural tracking user account mapping profile
            $userStmt = $db->prepare("INSERT INTO users (username, password, email, role) VALUES (:username, :password, :email, 'student')");
            $userStmt->execute([
                ':username' => $username,
                ':password' => $password,
                ':email'    => $email
            ]);
            $newUserId = $db->lastInsertId();

            // 2. Map structural link profile directly to student context card index
            $stuStmt = $db->prepare("INSERT INTO students (user_id, admission_no, name, dob, phone, class_id) VALUES (:user_id, :admission_no, :name, :dob, :phone, :class_id)");
            $stuStmt->execute([
                ':user_id'      => $newUserId,
                ':admission_no' => $admission_no,
                ':name'         => $name,
                ':dob'          => $dob,
                ':phone'        => $phone,
                ':class_id'     => $class_id
            ]);

            $db->commit();
            $_SESSION['status_msg'] = "Student entry and credential keys synchronized successfully.";
        } catch (PDOException $e) {
            $db->rollBack();
            error_log("Student registration structural error trace: " . $e->getMessage());
            $_SESSION['status_msg'] = "Error writing entry to disk repository system maps.";
        }
        header("Location: students.php");
        exit();
    }

    // ----------------------------------------------------
    // ACTION ROUTE: UPDATE TARGET STUDENT METADATA
    // ----------------------------------------------------
    if ($action === 'update') {
        $student_id = intval($_POST['student_id']);
        $name       = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
        $dob        = $_POST['dob'];
        $phone      = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS));
        $class_id   = !empty($_POST['class_id']) ? intval($_POST['class_id']) : null;

        try {
            $updateStmt = $db->prepare("UPDATE students SET name = :name, dob = :dob, phone = :phone, class_id = :class_id WHERE id = :id");
            $updateStmt->execute([
                ':name'     => $name,
                ':dob'      => $dob,
                ':phone'    => $phone,
                ':class_id' => $class_id,
                ':id'       => $student_id
            ]);
            $_SESSION['status_msg'] = "Student record structural configurations customized successfully.";
        } catch (PDOException $e) {
            error_log("Modification layer processing failure stack: " . $e->getMessage());
            $_SESSION['status_msg'] = "Failed to update record parameters.";
        }
        header("Location: students.php");
        exit();
    }
}

// ----------------------------------------------------
// ACTION ROUTE: REMOVE / PURGE TARGET STUDENT RECORD
// ----------------------------------------------------
if ($action === 'delete') {
    $student_id = intval($_GET['id'] ?? 0);
    $user_id    = intval($_GET['user_id'] ?? 0);

    if($user_id > 0) {
        try {
            // Cascade bindings automatically clear students tracking table link metrics index mapping records
            $delStmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $delStmt->execute([':id' => $user_id]);
            $_SESSION['status_msg'] = "Target student account cleanly deleted from storage system cluster maps.";
        } catch(PDOException $e) {
            error_log("Purge workflow failure sequence execution stack: " . $e->getMessage());
            $_SESSION['status_msg'] = "System architecture trace failure executing record extraction commands.";
        }
    }
    header("Location: students.php");
    exit();
}