<?php
session_start();
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require_once '../config/Database.php';
$database = new Database();
$db = $database->getConnection();

// Fetch data from database
try {
    // 1. Fetch all catalog books
    $booksQuery = "SELECT * FROM library_books ORDER BY id DESC";
    $books = $db->query($booksQuery)->fetchAll();

    // 2. Fetch issued book records with student and user details
    $issueQuery = "SELECT bi.*, b.title, b.author, s.name AS student_name, u.username 
                   FROM book_issues bi 
                   LEFT JOIN library_books b ON bi.book_id = b.id 
                   LEFT JOIN students s ON bi.student_id = s.id 
                   LEFT JOIN users u ON bi.user_id = u.id 
                   ORDER BY bi.id DESC";
    $issuedBooks = $db->query($issueQuery)->fetchAll();

    // 3. Fetch students for book issuance modal
    $students = $db->query("SELECT id, name, admission_no FROM students ORDER BY name ASC")->fetchAll();

} catch (PDOException $e) {
    die("Library system mapping failure: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark m-0">Library Vault Management</h4>
    <div>
        <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#addBookModal">
            <i class="fa-solid fa-book-medical me-2"></i>Add New Book
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#issueBookModal">
            <i class="fa-solid fa-hand-holding-hand me-2"></i>Issue Book
        </button>
    </div>
</div>

<?php if(isset($_SESSION['status_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['status_msg']; unset($_SESSION['status_msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Tabs Navigation -->
<ul class="nav nav-pills mb-4" id="libraryTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-bold" id="catalog-tab" data-bs-toggle="tab" data-bs-target="#catalog-pane" type="button" role="tab">
            <i class="fa-solid fa-book me-2"></i>Book Catalog (<?php echo count($books); ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold" id="issues-tab" data-bs-toggle="tab" data-bs-target="#issues-pane" type="button" role="tab">
            <i class="fa-solid fa-list-check me-2"></i>Issued Logs (<?php echo count($issuedBooks); ?>)
        </button>
    </li>
</ul>

<div class="tab-content" id="libraryTabsContent">
    <!-- Book Catalog Tab -->
    <div class="tab-pane fade show active" id="catalog-pane" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Title</th>
                                <th>Author</th>
                                <th>ISBN</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($books)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No books currently logged in catalog.</td></tr>
                            <?php else: ?>
                                <?php foreach($books as $book): ?>
                                    <tr>
                                        <td class="ps-4 fw-semibold"><?php echo htmlspecialchars($book['title']); ?></td>
                                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                                        <td><code class="text-dark"><?php echo htmlspecialchars($book['isbn'] ?? 'N/A'); ?></code></td>
                                        <td>
                                            <?php if($book['status'] === 'Available'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill">Available</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1.5 rounded-pill">Issued</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="library_process.php?action=delete_book&id=<?php echo $book['id']; ?>" 
                                               class="btn btn-sm btn-light border text-danger" 
                                               onclick="return confirm('Remove this book record from the system catalog?');">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
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

    <!-- Circulation Log Tab -->
    <div class="tab-pane fade" id="issues-pane" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Book Title</th>
                                <th>Student Name</th>
                                <th>Issue Date</th>
                                <th>Return Date</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($issuedBooks)): ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">No circulation records logged.</td></tr>
                            <?php else: ?>
                                <?php foreach($issuedBooks as $issue): ?>
                                    <tr>
                                        <td class="ps-4 fw-semibold"><?php echo htmlspecialchars($issue['title'] ?? 'Unknown Book'); ?></td>
                                        <td><?php echo htmlspecialchars($issue['student_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($issue['issue_date']); ?></td>
                                        <td><?php echo htmlspecialchars($issue['return_date'] ?? 'Pending'); ?></td>
                                        <td>
                                            <?php if($issue['status'] === 'Returned'): ?>
                                                <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill">Returned</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill">Active Loan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <?php if($issue['status'] !== 'Returned'): ?>
                                                <a href="library_process.php?action=return_book&issue_id=<?php echo $issue['id']; ?>&book_id=<?php echo $issue['book_id']; ?>" 
                                                   class="btn btn-sm btn-outline-success">
                                                    <i class="fa-solid fa-check me-1"></i>Mark Returned
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small"><i class="fa-solid fa-circle-check text-success me-1"></i>Complete</span>
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
    </div>
</div>

<!-- Modal: Add Book -->
<div class="modal fade" id="addBookModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="library_process.php?action=add_book" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold">Add Book to Vault</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-medium">Book Title</label>
                    <input type="text" name="title" class="form-control" placeholder="Full title" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Author</label>
                    <input type="text" name="author" class="form-control" placeholder="Author name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">ISBN Number</label>
                    <input type="text" name="isbn" class="form-control" placeholder="e.g. 978-3-16-148410-0">
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Add Book</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Issue Book -->
<div class="modal fade" id="issueBookModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="library_process.php?action=issue_book" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Issue Book to Student</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-medium">Select Available Book</label>
                    <select name="book_id" class="form-select" required>
                        <option value="">Choose Book</option>
                        <?php foreach($books as $b): ?>
                            <?php if($b['status'] === 'Available'): ?>
                                <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['title'] . ' (by ' . $b['author'] . ')'); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Select Student</label>
                    <select name="student_id" class="form-select" required>
                        <option value="">Choose Student</option>
                        <?php foreach($students as $st): ?>
                            <option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['name'] . ' (' . $st['admission_no'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Issue Date</label>
                    <input type="date" name="issue_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Confirm Issue</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>