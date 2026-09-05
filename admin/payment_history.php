<?php
session_start();
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require_once '../config/Database.php';
$database = new Database();
$db = $database->getConnection();

try {
    // Relate multi-table matrix arrays to assemble deep chronological financial profiles
    $query = "SELECT p.id as payment_id, p.amount_paid, p.payment_date, p.status, s.name as student_name, s.admission_no, f.fee_type, f.amount as total_invoice_amount 
              FROM payments p 
              JOIN students s ON p.student_id = s.id 
              JOIN fees f ON p.fee_id = f.id 
              ORDER BY p.payment_date DESC";
    $ledger = $db->query($query)->fetchAll();
} catch (PDOException $e) {
    die("Historical database transaction retrieval failure exception event: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark m-0">Transaction Ledger & Balance Auditing</h4>
    <a href="fees.php" class="btn btn-primary fw-medium">
        <i class="fa-solid fa-cash-register me-2"></i>Return to Cashier Counter Register
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4 text-dark bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Receipt Reference Mapping</th>
                        <th>Student / Admission No</th>
                        <th>Invoice Target Category</th>
                        <th>Paid Installment Base</th>
                        <th>Receipt Creation Timestamp</th>
                        <th class="text-end pe-4">Verification Check Flag</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($ledger)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No accounting transactions logged inside ledger registry archives.</td></tr>
                    <?php else: ?>
                        <?php foreach($ledger as $row): ?>
                            <tr>
                                <td class="ps-4 font-monospace small text-secondary">REC-TXN-<?php echo str_pad($row['payment_id'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <div class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($row['student_name']); ?></div>
                                    <small class="text-muted text-uppercase tracking-wider font-monospace"><?php echo htmlspecialchars($row['admission_no']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row['fee_type']); ?></td>
                                <td class="fw-bold text-dark">$<?php echo number_format($row['amount_paid'], 2); ?></td>
                                <td class="text-muted small"><i class="fa-regular fa-clock me-2"></i><?php echo date('M d, Y - h:i A', strtotime($row['payment_date'])); ?></td>
                                <td class="text-end pe-4">
                                    <?php if($row['status'] === 'Paid'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill"><i class="fa-solid fa-circle-check me-1"></i>Settled</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill"><i class="fa-solid fa-circle-exclamation me-1"></i>Partial Allocation</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>