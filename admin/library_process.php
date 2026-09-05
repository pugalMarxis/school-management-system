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

// ----------------------------------------------------
// TRANSACTION: INVENTORY MANAGEMENT COMMAND CHAINS
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if ($action === 'add_book') {
        $isbn     = trim(filter_input(INPUT_POST, 'isbn', FILTER_SANITIZE_SPECIAL_CHARS));
        $title    = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS));
        $author   = trim(filter_input(INPUT_POST, 'author', FILTER_SANITIZE_SPECIAL_CHARS));
        $quantity = intval($_POST['quantity']);

        try {
            $stmt = $db->prepare("INSERT INTO library_books (title, author, isbn, quantity) VALUES (:title, :author, :isbn, :quantity)");
            $stmt->execute([
                ':title'    => $title,
                ':author'   => $author,
                ':isbn'     => $isbn,
                ':quantity' => $quantity
            ]);
            $_SESSION['status_msg'] = "Book logged into library repository system stacks index map.";
        } catch (PDOException $e) {
            $_SESSION['status_msg'] = ($e->getCode() == 23000) ? "Error: ISBN identification signature collision detected." : "Processing failure.";
        }
        header("Location: library.php");
        exit();
    }

    if ($action === 'issue_book') {
        $book_id    = intval($_POST['book_id']);
        $user_id    = intval($_POST['user_id']);
        $issue_date = $_POST['issue_date'];

        try {
            // Begin isolated database transactions to secure balance allocations
            $db->beginTransaction();

            // 1. Double check book stock balance counts safety
            $check = $db->prepare("SELECT quantity FROM library_books WHERE id = :id FOR UPDATE");
            $check->execute([':id' => $book_id]);
            $currentStock = $check->fetchColumn();

            if ($currentStock > 0) {
                // 2. Draft issuance line ledger item entry
                $issueStmt = $db->prepare("INSERT INTO book_issues (book_id, user_id, issue_date, status) VALUES (:book_id, :user_id, :issue_date, 'Issued')");
                $issueStmt->execute([
                    ':book_id'    => $book_id,
                    ':user_id'    => $user_id,
                    ':issue_date' => $issue_date
                ]);

                // 3. Decrement structural stock values count mapping
                $decrement = $db->prepare("UPDATE library_books SET quantity = quantity - 1 WHERE id = :id");
                $decrement->execute([':id' => $book_id]);

                $db->commit();
                $_SESSION['status_msg'] = "Book copy cleanly signed-out to member profile ledger.";
            } else {
                $db->rollBack();
                $_SESSION['status_msg'] = "Error: Material depletion limit encountered. Checkout canceled.";
            }
        } catch (PDOException $e) {
            $db->rollBack();
            error_log("Library vault structural transaction error event log: " . $e->getMessage());
            $_SESSION['status_msg'] = "Internal engine failure processing issuance parameters asset loop handles.";
        }
        header("Location: library.php");
        exit();
    }
}

// ----------------------------------------------------
// TRANSACTION: MATERIAL LINE RETURN LOGIC
// ----------------------------------------------------
if ($action === 'return_book') {
    $issue_id = intval($_GET['issue_id'] ?? 0);
    $book_id  = intval($_GET['book_id'] ?? 0);

    if ($issue_id > 0 && $book_id > 0) {
        try {
            $db->beginTransaction();

            // 1. Terminate material loan state parameters mapping
            $returnStmt = $db->prepare("UPDATE book_issues SET return_date = CURRENT_DATE, status = 'Returned' WHERE id = :id");
            $returnStmt->execute([':id' => $issue_id]);

            // 2. Re-increment physical stack levels inside database records
            $incrementStmt = $db->prepare("UPDATE library_books SET quantity = quantity + 1 WHERE id = :id");
            $incrementStmt->execute([':id' => $book_id]);

            $db->commit();
            $_SESSION['status_msg'] = "Volume return transaction logged. Inventory accounts balanced.";
        } catch (PDOException $e) {
            $db->rollBack();
            error_log("Library material return exception sequence stack: " . $e->getMessage());
            $_SESSION['status_msg'] = "Error clearing rental profile trace configurations.";
        }
    }
    header("Location: library.php");
    exit();
}

if ($action === 'delete_book') {
    $book_id = intval($_GET['id'] ?? 0);
    try {
        $db->prepare("DELETE FROM library_books WHERE id = :id")->execute([':id' => $book_id]);
        $_SESSION['status_msg'] = "Material title entirely scrubbed from server indexes.";
    } catch (PDOException $e) {
        $_SESSION['status_msg'] = "Constraint restriction limits action processing execution loops.";
    }
    header("Location: library.php");
    exit();
}