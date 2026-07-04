<?php
require_once 'includes/header.php';

// Fetch specialization options
$specializations = ['Cardiologist', 'Pediatrician', 'General Physician', 'Dermatologist', 'Orthopedician', 'Gynecologist', 'Neurologist', 'Endocrinologist', 'Oncologist', 'ENT Specialist', 'Ophthalmologist', 'Psychiatrist', 'Urologist', 'Gastroenterologist', 'Dentist'];

// Current query states
$selectedSpec = $_GET['specialization'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$selectedSort = $_GET['sort'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 8;
$offset = ($page - 1) * $limit;

// Build SQL
$sql = "SELECT * FROM doctors WHERE 1=1";
$params = [];

if (!empty($selectedSpec)) {
    $sql .= " AND specialization = ?";
    $params[] = $selectedSpec;
}

if (!empty($searchQuery)) {
    $sql .= " AND (name LIKE ? OR specialization LIKE ?)";
    $params[] = "%" . $searchQuery . "%";
    $params[] = "%" . $searchQuery . "%";
}

if ($selectedSort === 'fee_asc') {
    $sql .= " ORDER BY fee ASC";
} elseif ($selectedSort === 'fee_desc') {
    $sql .= " ORDER BY fee DESC";
} elseif ($selectedSort === 'experience') {
    $sql .= " ORDER BY experience DESC";
} else {
    $sql .= " ORDER BY id ASC";
}

$countSql = str_replace("SELECT *", "SELECT COUNT(*)", $sql);

$sql .= " LIMIT $limit OFFSET $offset";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $doctors = $stmt->fetchAll();

    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalItems = (int)$countStmt->fetchColumn();
    $totalPages = ceil($totalItems / $limit);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container my-4">
    <!-- Header banner -->
    <div class="mb-4">
        <h2 class="fw-bold text-success" id="doctorsTitle" data-testid="doctors-title">Consult Experienced Doctors</h2>
        <p class="text-muted">Book virtual consultations or in-clinic slots with leading medical specialists</p>
    </div>

    <!-- Controls Row -->
    <div class="row mb-4 align-items-center g-3">
        <!-- Search bar -->
        <div class="col-md-6">
            <form method="GET" action="doctors.php" id="doctorSearchForm" data-testid="doctor-search-form">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search by doctor name or speciality..." name="search" id="docSearchInput" data-testid="doc-search-input" value="<?= htmlspecialchars($searchQuery) ?>">
                    <button class="btn btn-success" type="submit" id="docSearchSubmit" data-testid="doc-search-submit">Search</button>
                </div>
            </form>
        </div>

        <!-- Sorting -->
        <div class="col-md-3 ms-auto">
            <div class="d-flex align-items-center gap-2">
                <label for="sortBy" class="text-nowrap small fw-bold">Sort By:</label>
                <select class="form-select" id="sortBy" name="sort" data-testid="sort-dropdown" onchange="applyFilters()">
                    <option value="" <?= empty($selectedSort) ? 'selected' : '' ?>>Default</option>
                    <option value="fee_asc" <?= $selectedSort === 'fee_asc' ? 'selected' : '' ?>>Fee: Low to High</option>
                    <option value="fee_desc" <?= $selectedSort === 'fee_desc' ? 'selected' : '' ?>>Fee: High to Low</option>
                    <option value="experience" <?= $selectedSort === 'experience' ? 'selected' : '' ?>>Years of Experience</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="card glass-card border-0 p-4 sticky-top" style="top: 90px; z-index: 100;" id="filtersCard" data-testid="filters-card">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Filter Speciality</h5>

                <!-- Speciality Checkboxes -->
                <div style="max-height: 350px; overflow-y: auto; padding-right: 5px;" class="mb-4">
                    <?php foreach ($specializations as $spec): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input filter-speciality" type="checkbox" value="<?= $spec ?>" id="spec-<?= str_replace(' ', '', $spec) ?>" data-testid="filter-speciality-<?= strtolower(str_replace(' ', '-', $spec)) ?>" <?= $selectedSpec === $spec ? 'checked' : '' ?> onchange="applyFilters()">
                            <label class="form-check-label small" for="spec-<?= str_replace(' ', '', $spec) ?>">
                                <?= $spec ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Clear button -->
                <button class="btn btn-outline-danger btn-sm w-100" onclick="clearFilters()" id="clearFiltersBtn" data-testid="clear-filters-btn">Clear All Filters</button>
            </div>
        </div>

        <!-- Catalog List -->
        <div class="col-lg-9">
            <div class="row g-4" id="doctorsContainer" data-testid="doctors-container">
                <?php if (empty($doctors)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-search-heart text-muted fs-1 mb-2"></i>
                        <p class="text-muted">No doctors found matching the search criteria.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($doctors as $doc): ?>
                        <?php 
                        $sched = json_decode($doc['availability'], true);
                        $days = implode(', ', $sched['days'] ?? []);
                        $time = $sched['time'] ?? '';
                        ?>
                        <div class="col-md-6 doctor-item-col">
                            <div class="card glass-card h-100 p-3 border-0" data-testid="doctor-card" id="doc-card-<?= $doc['id'] ?>">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <!-- Placeholder profile image -->
                                    <img src="https://placehold.co/100x100/eef7f2/0a6c42?text=MD" class="rounded-circle border" alt="<?= htmlspecialchars($doc['name']) ?>" style="width: 75px; height: 75px; object-fit: cover;">
                                    <div>
                                        <h5 class="fw-bold text-dark mb-1" id="doc-name-<?= $doc['id'] ?>" data-testid="doc-name"><?= htmlspecialchars($doc['name']) ?></h5>
                                        <span class="badge bg-success-subtle text-success border mb-1" data-testid="doc-specialization"><?= $doc['specialization'] ?></span>
                                        <div class="text-muted small"><i class="bi bi-award-fill text-warning"></i> <?= $doc['experience'] ?> Years Experience</div>
                                    </div>
                                </div>

                                <div class="mb-3 border-top pt-2">
                                    <div class="small text-muted mb-1"><i class="bi bi-translate text-success me-1"></i> Languages: <strong class="text-dark"><?= htmlspecialchars($doc['languages']) ?></strong></div>
                                    <div class="small text-muted"><i class="bi bi-calendar3 text-success me-1"></i> Availability: <strong class="text-dark"><?= $days ?> (<?= $time ?>)</strong></div>
                                </div>

                                <div class="d-flex align-items-baseline gap-2 mb-3 bg-light p-2 rounded">
                                    <span class="small text-muted fw-bold">Consultation Fee:</span>
                                    <span class="fs-5 fw-bold text-success" id="doc-fee-<?= $doc['id'] ?>" data-testid="doc-fee">₹<?= number_format($doc['fee'], 2) ?></span>
                                </div>

                                <div class="mt-auto">
                                    <a href="doctor-details.php?id=<?= $doc['id'] ?>" class="btn btn-primary-custom w-100" id="bookBtn-<?= $doc['id'] ?>" data-testid="book-appointment-link">Book Appointment Slot</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav class="mt-5" id="paginationNav" data-testid="pagination-nav">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page-1 ?>&specialization=<?= $selectedSpec ?>&sort=<?= $selectedSort ?>&search=<?= urlencode($searchQuery) ?>" id="paginationPrev" data-testid="pagination-prev">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $page === $i ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&specialization=<?= $selectedSpec ?>&sort=<?= $selectedSort ?>&search=<?= urlencode($searchQuery) ?>" id="paginationPage-<?= $i ?>" data-testid="pagination-page-<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page+1 ?>&specialization=<?= $selectedSpec ?>&sort=<?= $selectedSort ?>&search=<?= urlencode($searchQuery) ?>" id="paginationNext" data-testid="pagination-next">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
function applyFilters() {
    const sortBy = document.getElementById('sortBy').value;
    const search = document.getElementById('docSearchInput').value.trim();
    
    let specialization = '';
    document.querySelectorAll('.filter-speciality:checked').forEach(cb => {
        specialization = cb.value;
    });

    let url = `doctors.php?sort=${sortBy}&search=${encodeURIComponent(search)}`;
    if (specialization) url += `&specialization=${encodeURIComponent(specialization)}`;

    window.location.href = url;
}

function clearFilters() {
    window.location.href = 'doctors.php';
}
</script>

<?php
require_once 'includes/footer.php';
?>
