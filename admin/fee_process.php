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
    // SUB-ACTION OPERATION: DEFINE PRIMARY INVOICE BALANCES
    // ----------------------------------------------------
    if ($action === 'create_fee') {
        $fee_type = trim(filter_input(INPUT_POST, 'fee_type', FILTER_SANITIZE_SPECIAL_CHARS));
        $amount   = floatval($_POST['amount']);
        $due_date = $_POST['due_date'];

        if (empty($fee_type) || $amount <= 0 || empty($due_date)) {
            $_SESSION['status_msg'] = "Error: Invalid fee profile configurations supplied.";
            header("Location: fees.php");
            exit();
        }

        try {
            $stmt = $db->prepare("INSERT INTO fees (fee_type, amount, due_date) VALUES (:fee_type, :amount, :due_date)");
            $stmt->execute([
                ':fee_type' => $fee_type,
                ':amount'   => $amount,
                ':due_date' => $due_date
            ]);
            $_SESSION['status_msg'] = "Billing structural fee type successfully integrated.";
        } catch (PDOException $e) {
            error_log("Fee profile writing processing failure track: " . $e->getMessage());
            $_SESSION['status_msg'] = "Internal error mapping category structures to schema indexes.";
        }
        header("Location: fees.php");
        exit();
    }

    // ----------------------------------------------------
    // SUB-ACTION OPERATION: WRITE BALANCES PAYMENT ENTRIES
    // ----------------------------------------------------
    if ($action === 'record_payment') {
        $fee_id      = intval($_POST['fee_id']);
        $student_id  = intval($_POST['student_id']);
        $amount_paid = floatval($_POST['amount_paid']);
        $status      = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);

        if ($fee_id <= 0 || $student_id <= 0 || $amount_paid <= 0) {
            $_SESSION['status_msg'] = "Critical error tracking transactional allocation keys.";
            header("Location: fees.php");
            exit();
        }

        try {
            // Write to the payments database ledger
            $stmt = $db->prepare("INSERT INTO payments (student_id, fee_id, amount_paid, status) VALUES (:student_id, :fee_id, :amount_paid, :status)");
            $stmt->execute([
                ':student_id'  => $student_id,
                ':fee_id'      => $fee_id,
                ':amount_paid' => $amount_paid,
                ':status'      => $status
            ]);
            $_SESSION['status_msg'] = "Payment transaction receipt recorded and cleared successfully.";
        } catch (PDOException $e) {
            error_log("Financial transaction entry mapping fault trace: " . $e->getMessage());
            $_SESSION['status_msg'] = "Failed to record payment transaction into ledger.";
        }
        header("Location: fees.php");
        exit();
    }
} else {
    header("Location: fees.php");
    exit();
}