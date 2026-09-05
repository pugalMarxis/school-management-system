<?php
session_start();
require_once 'config/Database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize user inputs
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS));
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "All authentication fields are strictly mandatory.";
        header("Location: index.php");
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    try {
        // Query authorization profiles using secure parameters
        $query = "SELECT id, username, password, role FROM users WHERE username = :username LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch();
            
            // Cryptographic confirmation evaluation check
            if (password_verify($password, $user['password'])) {
                // Regenerate session ID to mitigate hijacking vectors
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];

                // Contextual rule redirection
                switch($user['role']) {
                    case 'admin':
                        header("Location: admin/dashboard.php");
                        break;
                    case 'teacher':
                        header("Location: teacher/dashboard.php");
                        break;
                    case 'student':
                        header("Location: student/dashboard.php");
                        break;
                    default:
                        $_SESSION['login_error'] = "Unauthorized system role assignment mapping.";
                        header("Location: index.php");
                }
                exit();
            }
        }
        
        // Generic warning strategy to mitigate account harvest maps
        $_SESSION['login_error'] = "Invalid access verification credentials.";
        header("Location: index.php");
        exit();

    } catch (PDOException $e) {
        error_log("Authentication processing exception event: " . $e->getMessage());
        $_SESSION['login_error'] = "Internal gateway error during authorization processing.";
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}