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
    // Fetch all active invoice categories defined
    $feesList = $db->query("SELECT * FROM fees ORDER BY due_date DESC")->fetchAll();
    
    // Fetch students list for the payment collector dropdown search map
    $studentsList = $db->query("SELECT id, admission_no, name FROM students ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    die("Financial record system mapping failure: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark m-0">Institutional Billing & Fee Register</h4>
    <a href="payment_history.php" class="btn btn-outline-dark fw-medium">
        <i class="fa-solid fa-receipt me-2"></i>View Transaction History
    </a>
</div>

<?php if(isset($_SESSION['status_msg'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['status_msg']; unset($_SESSION['status_msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
            <div class="card-header bg-white border-bottom p-4">
                <h6 class="m-0 fw-bold text-dark"><i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i>Configure Fee Type</h6>
            </div>
            <div class="card-body p-4">
                <form action="fee_process.php?action=create_fee" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Fee Allocation Description</label>
                        <input type="text" name="fee_type" class="form-control" placeholder="e.g. Fall Term Tuition 2026" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Fixed Invoiced Amount ($)</label>
                        <input type="number" step="0.01" min="0" name="amount" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Payment Deadline Due Date</label>
                        <input type="date" name="due_date" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2.5">Generate Invoiced Class Structure</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
            <div class="card-header bg-white border-bottom p-4">
                <h6 class="m-0 fw-bold text-dark"><i class="fa-solid fa-list-check text-secondary me-2"></i>Active Billing Allocations</h6>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Billing Fee Allocation</th>
                            <th>Cost Base</th>
                            <th>Target Due Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($feesList)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No institutional invoices exist.</td></tr>
                        <?php else: ?>
                            <?php foreach($feesList as $fee): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark"><?php echo htmlspecialchars($fee['fee_type']); ?></td>
                                    <td class="fw-medium text-success">$<?php echo number_format($fee['amount'], 2); ?></td>
                                    <td class="text-muted"><i class="fa-regular fa-calendar-times me-2"></i><?php echo date('M d, Y', strtotime($fee['due_date'])); ?></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-success px-3 collect-fee-btn" 
                                                data-fee-id="<?php echo $fee['id']; ?>"
                                                data-fee-name="<?php echo htmlspecialchars($fee['fee_type']); ?>"
                                                data-fee-amount="<?php echo $fee['amount']; ?>">
                                            <i class="fa-solid fa-cash-register me-1"></i>Collect
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="collectFeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="fee_process.php?action=record_payment" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="fee_id" id="modal_fee_id">
            
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">Process Fee Receipt</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3 p-3 bg-light rounded-3 border-start border-success border-4">
                    <p class="small text-muted m-0">Target Fee Allocation Invoice Category</p>
                    <h6 class="fw-bold text-dark m-0 mt-1" id="modal_fee_title">---</h6>
                    <p class="small text-muted m-0 mt-2">Standard Minimum Base Liability: <strong class="text-success" id="modal_fee_cost">$0.00</strong></p>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Select Student Profile Account</label>
                    <select name="student_id" class="form-select form-select-lg" required>
                        <option value="">-- Search Enrolled Profile Index --</option>
                        <?php foreach($studentsList as $stu): ?>
                            <option value="<?php echo $stu['id']; ?>">
                                <?php echo htmlspecialchars($stu['name'] . ' (ID: ' . $stu['admission_no'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted">Amount Received Paid ($)</label>
                        <input type="number" step="0.01" min="0.01" name="amount_paid" id="modal_payment_input" class="form-control form-control-lg text-success fw-bold" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted">Transaction Status Flag</label>
                        <select name="status" class="form-select form-select-lg" required>
                            <option value="Paid" selected>Fully Paid</option>
                            <option value="Partial">Partial Payment</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel Transaction</button>
                <button type="submit" class="btn btn-success px-4 text-white fw-bold">Commit Transaction Receipt</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Intercept action button parameters to populating tracking metrics directly into modal markup targets
    document.querySelectorAll('.collect-fee-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('modal_fee_id').value = this.dataset.feeId;
            document.getElementById('modal_fee_title').innerText = this.dataset.feeName;
            document.getElementById('modal_fee_cost').innerText = '$' + parseFloat(this.dataset.feeAmount).toFixed(2);
            document.getElementById('modal_payment_input').value = this.dataset.feeAmount;
            
            new bootstrap.Modal(document.getElementById('collectFeeModal')).show();
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>